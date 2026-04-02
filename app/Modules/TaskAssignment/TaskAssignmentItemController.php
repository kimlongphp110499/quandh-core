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
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentItemRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentItemService;

/**
 * @group TaskAssignment - Công việc
 *
 * Quản lý công việc thuộc văn bản giao việc, theo dõi tiến độ và thống kê
 */
class TaskAssignmentItemController extends Controller
{
    public function __construct(private TaskAssignmentItemService $service) {}

    /**
     * Thống kê công việc
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách công việc
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
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     */
    public function show(TaskAssignmentItem $taskAssignmentItem)
    {
        return $this->successResource(new TaskAssignmentItemResource($this->service->show($taskAssignmentItem)));
    }

    /**
     * Tạo công việc mới
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
     */
    public function destroy(TaskAssignmentItem $taskAssignmentItem)
    {
        $this->service->destroy($taskAssignmentItem);

        return $this->success(null, 'Công việc đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt công việc
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentItemRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các công việc được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt công việc
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
     */
    public function changeStatus(ChangeStatusTaskAssignmentItemRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        $item = $this->service->changeStatus($taskAssignmentItem, $request->status);

        return $this->successResource(new TaskAssignmentItemResource($item), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất danh sách công việc
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Nhập danh sách công việc
     */
    public function import(ImportTaskAssignmentItemRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import công việc thành công.');
    }

    /**
     * Thống kê công việc theo phòng ban
     */
    public function statsByDepartment(FilterRequest $request)
    {
        return $this->success($this->service->statsByDepartment($request->all()));
    }

    /**
     * Thống kê công việc theo người dùng
     */
    public function statsByUser(FilterRequest $request)
    {
        return $this->success($this->service->statsByUser($request->all()));
    }

    /**
     * Thống kê công việc theo thời gian
     *
     * @queryParam group_by string Nhóm theo: week, month, quarter. Example: month
     */
    public function statsByTime(FilterRequest $request)
    {
        return $this->success($this->service->statsByTime($request->all()));
    }

    /**
     * Danh sách công việc quá hạn
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
     * @queryParam days integer Số ngày sắp đến hạn (mặc định 3). Example: 3
     */
    public function upcomingDeadline(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemCollection(
            $this->service->upcomingDeadline($request->all())
        ));
    }
}
