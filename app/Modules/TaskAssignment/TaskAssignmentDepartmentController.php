<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentDepartment;
use App\Modules\TaskAssignment\Requests\BulkDestroyTaskAssignmentDepartmentRequest;
use App\Modules\TaskAssignment\Requests\BulkUpdateStatusTaskAssignmentDepartmentRequest;
use App\Modules\TaskAssignment\Requests\ChangeStatusTaskAssignmentDepartmentRequest;
use App\Modules\TaskAssignment\Requests\ImportTaskAssignmentDepartmentRequest;
use App\Modules\TaskAssignment\Requests\StoreTaskAssignmentDepartmentRequest;
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentDepartmentRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentDepartmentService;

/**
 * @group TaskAssignment - Phòng ban
 *
 * Quản lý danh mục phòng ban nội bộ module giao việc
 */
class TaskAssignmentDepartmentController extends Controller
{
    public function __construct(private TaskAssignmentDepartmentService $service) {}

    /**
     * Thống kê phòng ban
     *
     * @response 200 {"success": true, "data": {"total": 5, "active": 4, "inactive": 1}}
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách phòng ban
     */
    public function index(FilterRequest $request)
    {
        $data = $this->service->index($request->all(), (int) ($request->limit ?? 15));

        return $this->successCollection(new TaskAssignmentDepartmentCollection($data));
    }

    /**
     * Chi tiết phòng ban
     *
     * @urlParam taskAssignmentDepartment integer required ID phòng ban. Example: 1
     */
    public function show(TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        return $this->successResource(new TaskAssignmentDepartmentResource($this->service->show($taskAssignmentDepartment)));
    }

    /**
     * Tạo phòng ban mới
     */
    public function store(StoreTaskAssignmentDepartmentRequest $request)
    {
        $department = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentDepartmentResource($department), 'Phòng ban đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật phòng ban
     *
     * @urlParam taskAssignmentDepartment integer required ID phòng ban. Example: 1
     */
    public function update(UpdateTaskAssignmentDepartmentRequest $request, TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        $department = $this->service->update($taskAssignmentDepartment, $request->validated());

        return $this->successResource(new TaskAssignmentDepartmentResource($department), 'Phòng ban đã được cập nhật!');
    }

    /**
     * Xóa phòng ban
     *
     * @urlParam taskAssignmentDepartment integer required ID phòng ban. Example: 1
     */
    public function destroy(TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        $this->service->destroy($taskAssignmentDepartment);

        return $this->success(null, 'Phòng ban đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt phòng ban
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentDepartmentRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các phòng ban được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt phòng ban
     */
    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentDepartmentRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    /**
     * Thay đổi trạng thái phòng ban
     *
     * @urlParam taskAssignmentDepartment integer required ID phòng ban. Example: 1
     */
    public function changeStatus(ChangeStatusTaskAssignmentDepartmentRequest $request, TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        $department = $this->service->changeStatus($taskAssignmentDepartment, $request->status);

        return $this->successResource(new TaskAssignmentDepartmentResource($department), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất danh sách phòng ban
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Nhập danh sách phòng ban
     */
    public function import(ImportTaskAssignmentDepartmentRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import phòng ban thành công.');
    }
}
