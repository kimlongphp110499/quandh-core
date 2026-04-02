<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentItemType;
use App\Modules\TaskAssignment\Requests\BulkDestroyTaskAssignmentItemTypeRequest;
use App\Modules\TaskAssignment\Requests\BulkUpdateStatusTaskAssignmentItemTypeRequest;
use App\Modules\TaskAssignment\Requests\ChangeStatusTaskAssignmentItemTypeRequest;
use App\Modules\TaskAssignment\Requests\ImportTaskAssignmentItemTypeRequest;
use App\Modules\TaskAssignment\Requests\StoreTaskAssignmentItemTypeRequest;
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentItemTypeRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentItemTypeService;

/**
 * @group TaskAssignment - Loại công việc
 *
 * Quản lý danh mục loại công việc
 */
class TaskAssignmentItemTypeController extends Controller
{
    public function __construct(private TaskAssignmentItemTypeService $service) {}

    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemTypeCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    public function show(TaskAssignmentItemType $taskAssignmentItemType)
    {
        return $this->successResource(new TaskAssignmentItemTypeResource($this->service->show($taskAssignmentItemType)));
    }

    public function store(StoreTaskAssignmentItemTypeRequest $request)
    {
        $type = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentItemTypeResource($type), 'Loại công việc đã được tạo thành công!', 201);
    }

    public function update(UpdateTaskAssignmentItemTypeRequest $request, TaskAssignmentItemType $taskAssignmentItemType)
    {
        $type = $this->service->update($taskAssignmentItemType, $request->validated());

        return $this->successResource(new TaskAssignmentItemTypeResource($type), 'Loại công việc đã được cập nhật!');
    }

    public function destroy(TaskAssignmentItemType $taskAssignmentItemType)
    {
        $this->service->destroy($taskAssignmentItemType);

        return $this->success(null, 'Loại công việc đã được xóa thành công!');
    }

    public function bulkDestroy(BulkDestroyTaskAssignmentItemTypeRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các loại công việc được chọn!');
    }

    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentItemTypeRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    public function changeStatus(ChangeStatusTaskAssignmentItemTypeRequest $request, TaskAssignmentItemType $taskAssignmentItemType)
    {
        $type = $this->service->changeStatus($taskAssignmentItemType, $request->status);

        return $this->successResource(new TaskAssignmentItemTypeResource($type), 'Cập nhật trạng thái thành công!');
    }

    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    public function import(ImportTaskAssignmentItemTypeRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import loại công việc thành công.');
    }
}
