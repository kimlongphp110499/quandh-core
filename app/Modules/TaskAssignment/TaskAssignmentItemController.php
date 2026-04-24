<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Requests\BulkDestroyTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Requests\BulkUpdateStatusTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Requests\ChangeStatusTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Requests\ImportTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Requests\StoreTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Requests\UpdateProgressTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentProgressLogResource;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentItemService;

/**
 * @group TaskAssignment - Công việc
 *
 * Quản lý công việc thuộc văn bản giao việc: thống kê, danh sách, chi tiết, tạo, cập nhật, xóa, thao tác hàng loạt, xuất/nhập, đổi trạng thái và các thống kê nâng cao theo phòng ban, người dùng, thời gian.
 */
class TaskAssignmentItemController extends Controller
{
    public function __construct(private TaskAssignmentItemService $service) {}

    /**
     * Thống kê công việc
     *
     * Trả về tổng số công việc và phân loại theo trạng thái xử lý, mức độ ưu tiên.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên công việc.
     * @queryParam status string Lọc theo trạng thái xử lý: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam priority string Lọc theo mức độ ưu tiên: low, medium, high, urgent.
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam task_assignment_item_type_id integer Lọc theo loại công việc. Example: 1
     * @queryParam department_id integer Lọc theo phòng ban thực hiện. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": {"total": 20, "todo": 5, "in_progress": 8, "done": 5, "overdue": 1, "paused": 1, "cancelled": 0}}
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách công việc
     *
     * Trả về danh sách công việc có phân trang, hỗ trợ lọc theo nhiều tiêu chí bao gồm văn bản, phòng ban, trạng thái và thời hạn.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên công việc.
     * @queryParam status string Lọc theo trạng thái xử lý: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam priority string Lọc theo mức độ ưu tiên: low, medium, high, urgent.
     * @queryParam deadline_type string Lọc theo loại thời hạn: has_deadline, no_deadline.
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam task_assignment_item_type_id integer Lọc theo loại công việc. Example: 1
     * @queryParam department_id integer Lọc theo phòng ban thực hiện. Example: 1
     * @queryParam user_id integer Lọc theo người thực hiện. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, name, priority, end_at, completion_percent, created_at, updated_at. Example: end_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: asc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentItemCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem paginate=15
     *
     * @apiResourceAdditional success=true
     */
    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết công việc
     *
     * Trả về thông tin đầy đủ của công việc bao gồm văn bản giao việc, loại công việc, danh sách phòng ban và người thực hiện kèm vai trò.
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentItem $taskAssignmentItem)
    {
        return $this->successResource(new TaskAssignmentItemResource($this->service->show($taskAssignmentItem)));
    }

    /**
     * Tạo công việc mới
     *
     * Tạo công việc thuộc một văn bản giao việc, có thể giao cho phòng ban và/hoặc cá nhân cụ thể.
     *
     * @bodyParam task_assignment_document_id integer required ID văn bản giao việc (phải tồn tại). Example: 1
     * @bodyParam name string required Tên công việc (tối đa 255 ký tự). Example: Xây dựng kế hoạch công tác tháng 5
     * @bodyParam description string Mô tả chi tiết công việc.
     * @bodyParam task_assignment_item_type_id integer ID loại công việc. Example: 1
     * @bodyParam deadline_type string required Loại thời hạn: has_deadline (có thời hạn), no_deadline (không có thời hạn). Example: has_deadline
     * @bodyParam start_at date Ngày bắt đầu (Y-m-d). Example: 2026-04-01
     * @bodyParam end_at date Ngày kết thúc (Y-m-d, bắt buộc khi deadline_type=has_deadline, phải sau hoặc bằng start_at). Example: 2026-04-30
     * @bodyParam processing_status string Trạng thái xử lý: todo, in_progress, done, overdue, paused, cancelled (mặc định todo). Example: todo
     * @bodyParam completion_percent integer Phần trăm hoàn thành (0-100). Example: 0
     * @bodyParam priority string Mức độ ưu tiên: low, medium, high, urgent. Example: medium
     * @bodyParam department_ids object[] Danh sách phòng ban thực hiện.
     * @bodyParam department_ids[].department_id integer required ID phòng ban (phải tồn tại). Example: 1
     * @bodyParam department_ids[].role string Vai trò phòng ban: main (chính), cooperate (phối hợp). Example: main
     * @bodyParam user_assignments object[] Danh sách người thực hiện.
     * @bodyParam user_assignments[].user_id integer required ID người dùng (phải tồn tại). Example: 5
     * @bodyParam user_assignments[].department_id integer required ID phòng ban của người dùng (phải tồn tại). Example: 1
     * @bodyParam user_assignments[].assignment_role string Vai trò cá nhân: main (chủ trì), support (phối hợp). Example: main
     * @bodyParam user_assignments[].assignment_status string Trạng thái giao việc: assigned, accepted, rejected, done. Example: assigned
     * @bodyParam user_assignments[].note string Ghi chú khi giao việc cho người dùng này.
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource status=201
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true message="Công việc đã được tạo thành công!"
     */
    public function store(StoreTaskAssignmentItemRequest $request)
    {
        $item = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentItemResource($item), 'Công việc đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật công việc
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @bodyParam name string Tên công việc (tối đa 255 ký tự). Example: Xây dựng kế hoạch công tác tháng 5
     * @bodyParam description string Mô tả chi tiết công việc.
     * @bodyParam task_assignment_item_type_id integer ID loại công việc. Example: 1
     * @bodyParam deadline_type string Loại thời hạn: has_deadline, no_deadline. Example: has_deadline
     * @bodyParam start_at date Ngày bắt đầu (Y-m-d). Example: 2026-04-01
     * @bodyParam end_at date Ngày kết thúc (Y-m-d). Example: 2026-04-30
     * @bodyParam processing_status string Trạng thái xử lý: todo, in_progress, done, overdue, paused, cancelled. Example: in_progress
     * @bodyParam completion_percent integer Phần trăm hoàn thành (0-100). Example: 50
     * @bodyParam priority string Mức độ ưu tiên: low, medium, high, urgent. Example: high
     * @bodyParam department_ids object[] Cập nhật danh sách phòng ban thực hiện (thay thế toàn bộ).
     * @bodyParam department_ids[].department_id integer required ID phòng ban. Example: 1
     * @bodyParam department_ids[].role string Vai trò: main, cooperate. Example: main
     * @bodyParam user_assignments object[] Cập nhật danh sách người thực hiện (thay thế toàn bộ).
     * @bodyParam user_assignments[].user_id integer required ID người dùng. Example: 5
     * @bodyParam user_assignments[].department_id integer required ID phòng ban. Example: 1
     * @bodyParam user_assignments[].assignment_role string Vai trò: main, support. Example: main
     * @bodyParam user_assignments[].assignment_status string Trạng thái: assigned, accepted, rejected, done. Example: accepted
     * @bodyParam user_assignments[].note string Ghi chú.
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true message="Công việc đã được cập nhật!"
     */
    public function update(UpdateTaskAssignmentItemRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        $item = $this->service->update($taskAssignmentItem, $request->validated());

        return $this->successResource(new TaskAssignmentItemResource($item), 'Công việc đã được cập nhật!');
    }

    /**
     * Xóa công việc
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @response 200 {"success": true, "message": "Công việc đã được xóa thành công!"}
     */
    public function destroy(TaskAssignmentItem $taskAssignmentItem)
    {
        $this->service->destroy($taskAssignmentItem);

        return $this->success(null, 'Công việc đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt công việc
     *
     * @bodyParam ids array required Danh sách ID công việc cần xóa. Example: [1,2,3]
     * @bodyParam ids[] integer required ID công việc. Example: 1
     *
     * @response 200 {"success": true, "message": "Đã xóa thành công các công việc được chọn!"}
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentItemRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các công việc được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt công việc
     *
     * @bodyParam ids array required Danh sách ID công việc. Example: [1,2,3]
     * @bodyParam ids[] integer required ID công việc. Example: 1
     * @bodyParam status string required Trạng thái mới: todo, in_progress, done, overdue, paused, cancelled. Example: in_progress
     *
     * @response 200 {"success": true, "message": "Cập nhật trạng thái thành công!"}
     */
    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentItemRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    /**
     * Thay đổi trạng thái xử lý công việc
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @bodyParam status string required Trạng thái xử lý mới: todo, in_progress, done, overdue, paused, cancelled. Example: done
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true message="Cập nhật trạng thái thành công!"
     */
    public function changeStatus(ChangeStatusTaskAssignmentItemRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        $item = $this->service->changeStatus($taskAssignmentItem, $request->status);

        return $this->successResource(new TaskAssignmentItemResource($item), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Cập nhật tiến độ công việc (dành cho admin/quản lý)
     *
     * Cập nhật trạng thái xử lý, phần trăm hoàn thành và ghi chú tiến độ.
     * Đồng thời ghi lịch sử cập nhật vào bảng progress_logs.
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @bodyParam processing_status string Trạng thái xử lý mới: todo, in_progress, done, paused, cancelled. Example: in_progress
     * @bodyParam completion_percent integer Phần trăm hoàn thành (0-100). Example: 60
     * @bodyParam note string Ghi chú tiến độ (tối đa 1000 ký tự). Example: Đang thực hiện.
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true message="Cập nhật tiến độ thành công!"
     */
    public function updateProgress(UpdateProgressTaskAssignmentItemRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        $item = $this->service->updateProgress($taskAssignmentItem, $request->validated());

        return $this->successResource(new TaskAssignmentItemResource($item), 'Cập nhật tiến độ thành công!');
    }

    /**
     * Xuất Excel danh sách công việc
     *
     * Xuất ra các trường: id, tên công việc, loại, văn bản giao việc, phòng ban, người chủ trì, trạng thái, ưu tiên, % hoàn thành, ngày bắt đầu, ngày kết thúc, created_by, updated_by, created_at, updated_at.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên công việc.
     * @queryParam status string Lọc theo trạng thái: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam priority string Lọc theo ưu tiên: low, medium, high, urgent.
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Import công việc từ Excel
     *
     * Cột bắt buộc: task_assignment_document_id, name, deadline_type. Cột không bắt buộc: description, task_assignment_item_type_id, start_at, end_at, processing_status, priority, completion_percent.
     *
     * @bodyParam file file required File Excel (xlsx, xls, csv). Cột theo chuẩn export.
     *
     * @response 200 {"success": true, "message": "Import công việc thành công."}
     */
    public function import(ImportTaskAssignmentItemRequest $request)
    {
        $result = $this->service->import($request->file('file'));

        $message = "Import hoàn tất: {$result['imported']} dòng thành công";
        if ($result['failed'] > 0) {
            $message .= ", {$result['failed']} dòng lỗi";
        }
        $message .= '.';

        return $this->success([
            'imported' => $result['imported'],
            'failed'   => $result['failed'],
            'failures' => $result['failures'],
        ], $message);
    }

    /**
     * Tải file mẫu import công việc
     *
     * Trả về file Excel mẫu gồm tiêu đề cột và dữ liệu ví dụ.
     * Người dùng tải về, điền dữ liệu công việc vào rồi upload qua API import.
     * Cột bắt buộc: .
     *
     * @response file Trả về file Excel mẫu (mau-import-cong-viec.xlsx)
     */
    public function downloadTemplate()
    {
        return $this->service->downloadTemplate();
    }

    /**
     * Thống kê công việc theo phòng ban
     *
     * Trả về số lượng và tỷ lệ hoàn thành công việc của từng phòng ban.
     *
     * @queryParam search string Từ khóa tìm kiếm.
     * @queryParam status string Lọc theo trạng thái: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": [{"department_id": 1, "department_name": "Phòng Kế hoạch", "total": 5, "done": 3, "in_progress": 2}]}
     */
    public function statsByDepartment(FilterRequest $request)
    {
        return $this->success($this->service->statsByDepartment($request->all()));
    }

    /**
     * Thống kê công việc theo người dùng
     *
     * Trả về số lượng và tỷ lệ hoàn thành công việc của từng người dùng được giao việc.
     *
     * @queryParam search string Từ khóa tìm kiếm.
     * @queryParam status string Lọc theo trạng thái: todo, in_progress, done, overdue, paused, cancelled.
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": [{"user_id": 5, "user_name": "Nguyễn Văn A", "total": 3, "done": 2, "in_progress": 1}]}
     */
    public function statsByUser(FilterRequest $request)
    {
        return $this->success($this->service->statsByUser($request->all()));
    }

    /**
     * Thống kê công việc theo thời gian
     *
     * Trả về số lượng công việc tạo mới và hoàn thành theo từng kỳ (tuần, tháng, quý).
     *
     * @queryParam group_by string Nhóm theo: week (tuần), month (tháng), quarter (quý). Example: month
     * @queryParam from_date date Lọc từ ngày (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày (Y-m-d). Example: 2026-12-31
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     *
     * @response 200 {"success": true, "data": [{"period": "2026-04", "total": 10, "done": 7, "in_progress": 2, "overdue": 1}]}
     */
    public function statsByTime(FilterRequest $request)
    {
        return $this->success($this->service->statsByTime($request->all()));
    }

    /**
     * Danh sách công việc quá hạn
     *
     * Trả về danh sách công việc có thời hạn đã qua mà chưa hoàn thành (trạng thái không phải done/cancelled).
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên công việc.
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     * @queryParam priority string Lọc theo ưu tiên: low, medium, high, urgent.
     * @queryParam sort_by string Sắp xếp theo: id, end_at, priority, created_at. Example: end_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: asc
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentItemCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true
     */
    public function overdue(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemCollection(
            $this->service->overdue($request->all())
        ));
    }

    /**
     * Danh sách công việc sắp đến hạn
     *
     * Trả về danh sách công việc sắp đến hạn trong số ngày tới (mặc định 3 ngày), chưa hoàn thành.
     *
     * @queryParam days integer Số ngày sắp đến hạn cần lấy (mặc định 3). Example: 3
     * @queryParam task_assignment_document_id integer Lọc theo văn bản giao việc. Example: 1
     * @queryParam department_id integer Lọc theo phòng ban. Example: 1
     * @queryParam priority string Lọc theo ưu tiên: low, medium, high, urgent.
     * @queryParam sort_by string Sắp xếp theo: id, end_at, priority. Example: end_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: asc
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentItemCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItem
     *
     * @apiResourceAdditional success=true
     */
    public function upcomingDeadline(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemCollection(
            $this->service->upcomingDeadline($request->all())
        ));
    }

    /**
     * Lịch sử cập nhật tiến độ công việc
     *
     * Trả về danh sách các lần cập nhật tiến độ của công việc,
     * bao gồm trạng thái cũ/mới, phần trăm cũ/mới, ghi chú và người cập nhật.
     * Sắp xếp theo thời gian mới nhất trước.
     *
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     *
     * @response 200 {"success": true, "data": [{"id": 1, "user_name": "Nguyễn Văn A", "old_processing_status": "todo", "new_processing_status": "in_progress", "old_completion_percent": 0, "new_completion_percent": 30, "note": "Đang thực hiện", "created_at": "09:00:00 23/04/2026"}]}
     */
    public function progressHistory(TaskAssignmentItem $taskAssignmentItem)
    {
        $logs = $this->service->getProgressHistory($taskAssignmentItem);

        return $this->success(TaskAssignmentProgressLogResource::collection($logs));
    }
}
