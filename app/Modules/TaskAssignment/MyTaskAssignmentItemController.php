<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Requests\UpdateProgressTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Resources\MyTaskAssignmentItemCollection;
use App\Modules\TaskAssignment\Resources\MyTaskAssignmentItemResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentItemService;

/**
 * @group TaskAssignment - Công việc của tôi
 *
 * API dành cho người dùng xem và cập nhật tiến độ các công việc được phân công cho bản thân.
 * Toàn bộ danh sách tự động lọc theo user đang đăng nhập.
 */
class MyTaskAssignmentItemController extends Controller
{
    public function __construct(private TaskAssignmentItemService $service) {}

    /**
     * Thống kê công việc của tôi
     *
     * Trả về tổng số và phân loại theo trạng thái các công việc được phân công cho user hiện tại.
     *
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     * @queryParam processing_status string Lọc theo trạng thái: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam priority string Lọc theo mức độ ưu tiên: low, medium, high, urgent.
     * @queryParam start_from date Lọc từ ngày bắt đầu (Y-m-d). Example: 2026-01-01
     * @queryParam start_to date Lọc đến ngày bắt đầu (Y-m-d). Example: 2026-12-31
     * @queryParam end_from date Lọc từ ngày kết thúc (Y-m-d). Example: 2026-01-01
     * @queryParam end_to date Lọc đến ngày kết thúc (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": {"total": 10, "todo": 2, "in_progress": 4, "done": 3, "overdue": 1, "paused": 0, "cancelled": 0}}
     */
    public function stats(FilterRequest $request)
    {
        $filters = array_merge($request->all(), ['user_id' => auth()->id()], ['document_issued' => 'issued']);

        return $this->success($this->service->stats($filters));
    }

    /**
     * Danh sách công việc của tôi
     *
     * Trả về danh sách công việc đã được phân công cho user đang đăng nhập, có phân trang và hỗ trợ nhiều bộ lọc.
     * Kèm theo thông tin văn bản giao việc, phòng ban, người giao, người phối hợp.
     *
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     * @queryParam processing_status string Lọc theo trạng thái: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam priority string Lọc theo mức độ ưu tiên: low, medium, high, urgent.
     * @queryParam start_from date Lọc từ ngày bắt đầu (Y-m-d). Example: 2026-01-01
     * @queryParam start_to date Lọc đến ngày bắt đầu (Y-m-d). Example: 2026-12-31
     * @queryParam end_from date Lọc từ ngày kết thúc (Y-m-d). Example: 2026-01-01
     * @queryParam end_to date Lọc đến ngày kết thúc (Y-m-d). Example: 2026-12-31
     * @queryParam search string Từ khóa tìm kiếm theo tên công việc.
     * @queryParam sort_by string Sắp xếp theo: id, end_at, priority, completion_percent, created_at. Example: end_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: asc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\MyTaskAssignmentItemCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem paginate=15
     *
     * @apiResourceAdditional success=true
     */
    public function index(FilterRequest $request)
    {
        $filters = array_merge($request->all(), ['user_id' => auth()->id()]);

        return $this->successCollection(new MyTaskAssignmentItemCollection(
            $this->service->myTasks($filters, (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết công việc của tôi
     *
     * Trả về thông tin đầy đủ của một công việc được phân công cho user hiện tại,
     * kèm văn bản giao việc, phòng ban, người giao, người phối hợp, báo cáo đã nộp.
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\MyTaskAssignmentItemResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentItem $taskAssignmentItem)
    {
        // Kiểm tra user có được phân công cho công việc này không
        $this->authorizeAssignment($taskAssignmentItem);

        return $this->successResource(new MyTaskAssignmentItemResource(
            $this->service->show($taskAssignmentItem)
        ));
    }

    /**
     * Cập nhật tiến độ công việc của tôi
     *
     * Cho phép user được phân công cập nhật trạng thái xử lý, phần trăm hoàn thành và ghi chú tiến độ.
     * Nghiệp vụ đồng bộ tự động:
     * - processing_status = done => completion_percent = 100, ghi nhận completed_at.
     * - completion_percent = 100 => tự chuyển processing_status = done.
     * - Quá end_at mà chưa hoàn thành => đánh dấu overdue.
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @bodyParam processing_status string Trạng thái xử lý mới: todo, in_progress, done, paused, cancelled. Example: in_progress
     * @bodyParam completion_percent integer Phần trăm hoàn thành (0-100). Example: 60
     * @bodyParam note string Ghi chú tiến độ (tối đa 1000 ký tự). Example: Đã hoàn thành phần phân tích, đang soạn báo cáo.
     *
     * @apiResource App\Modules\TaskAssignment\Resources\MyTaskAssignmentItemResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true message="Cập nhật tiến độ thành công!"
     */
    public function updateProgress(UpdateProgressTaskAssignmentItemRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        // Kiểm tra user có được phân công cho công việc này không
        $this->authorizeAssignment($taskAssignmentItem);

        $item = $this->service->updateProgress($taskAssignmentItem, $request->validated());

        return $this->successResource(new MyTaskAssignmentItemResource($item), 'Cập nhật tiến độ thành công!');
    }

    /**
     * Kiểm tra user hiện tại có được phân công cho công việc hay không.
     * Ném lỗi 403 nếu không thuộc danh sách người được giao.
     */
    private function authorizeAssignment(TaskAssignmentItem $item): void
    {
        $assigned = $item->users()->where('users.id', auth()->id())->exists();

        if (! $assigned) {
            abort(403, 'Bạn không có quyền truy cập công việc này.');
        }
    }
}
