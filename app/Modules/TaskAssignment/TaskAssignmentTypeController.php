<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentType;
use App\Modules\TaskAssignment\Requests\BulkDestroyTaskAssignmentTypeRequest;
use App\Modules\TaskAssignment\Requests\BulkUpdateStatusTaskAssignmentTypeRequest;
use App\Modules\TaskAssignment\Requests\ChangeStatusTaskAssignmentTypeRequest;
use App\Modules\TaskAssignment\Requests\ImportTaskAssignmentTypeRequest;
use App\Modules\TaskAssignment\Requests\StoreTaskAssignmentTypeRequest;
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentTypeRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentTypeCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentTypeResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentTypeService;

/**
 * @group TaskAssignment - Loại văn bản
 *
 * Quản lý danh mục loại văn bản giao việc
 */
class TaskAssignmentTypeController extends Controller
{
    public function __construct(private TaskAssignmentTypeService $service) {}

    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentTypeCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    public function show(TaskAssignmentType $taskAssignmentType)
    {
        return $this->successResource(new TaskAssignmentTypeResource($this->service->show($taskAssignmentType)));
    }

    public function store(StoreTaskAssignmentTypeRequest $request)
    {
        $type = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentTypeResource($type), 'Loại văn bản đã được tạo thành công!', 201);
    }

    public function update(UpdateTaskAssignmentTypeRequest $request, TaskAssignmentType $taskAssignmentType)
    {
        $type = $this->service->update($taskAssignmentType, $request->validated());

        return $this->successResource(new TaskAssignmentTypeResource($type), 'Loại văn bản đã được cập nhật!');
    }

    public function destroy(TaskAssignmentType $taskAssignmentType)
    {
        $this->service->destroy($taskAssignmentType);

        return $this->success(null, 'Loại văn bản đã được xóa thành công!');
    }

    public function bulkDestroy(BulkDestroyTaskAssignmentTypeRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các loại văn bản được chọn!');
    }

    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentTypeRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    public function changeStatus(ChangeStatusTaskAssignmentTypeRequest $request, TaskAssignmentType $taskAssignmentType)
    {
        $type = $this->service->changeStatus($taskAssignmentType, $request->status);

        return $this->successResource(new TaskAssignmentTypeResource($type), 'Cập nhật trạng thái thành công!');
    }

    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    public function import(ImportTaskAssignmentTypeRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import loại văn bản thành công.');
    }
}
