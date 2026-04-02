<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use App\Modules\TaskAssignment\Models\TaskAssignmentDocumentAttachment;
use App\Modules\TaskAssignment\Requests\AddAttachmentsTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\BulkDestroyTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\BulkUpdateStatusTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\ChangeStatusTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\ImportTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\SortAttachmentsTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\StoreTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentDocumentRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentDocumentService;

/**
 * @group TaskAssignment - Văn bản giao việc
 *
 * Quản lý văn bản giao việc và tệp đính kèm
 */
class TaskAssignmentDocumentController extends Controller
{
    public function __construct(private TaskAssignmentDocumentService $service) {}

    /**
     * Thống kê văn bản giao việc
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách văn bản giao việc
     */
    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentDocumentCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết văn bản giao việc
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     */
    public function show(TaskAssignmentDocument $taskAssignmentDocument)
    {
        return $this->successResource(new TaskAssignmentDocumentResource($this->service->show($taskAssignmentDocument)));
    }

    /**
     * Tạo văn bản giao việc mới
     */
    public function store(StoreTaskAssignmentDocumentRequest $request)
    {
        $document = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Văn bản giao việc đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật văn bản giao việc
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     */
    public function update(UpdateTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $document = $this->service->update($taskAssignmentDocument, $request->validated());

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Văn bản giao việc đã được cập nhật!');
    }

    /**
     * Xóa văn bản giao việc
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     */
    public function destroy(TaskAssignmentDocument $taskAssignmentDocument)
    {
        $this->service->destroy($taskAssignmentDocument);

        return $this->success(null, 'Văn bản giao việc đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt văn bản giao việc
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentDocumentRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các văn bản giao việc được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt
     */
    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentDocumentRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    /**
     * Thay đổi trạng thái văn bản (draft -> issued)
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     */
    public function changeStatus(ChangeStatusTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $document = $this->service->changeStatus($taskAssignmentDocument, $request->status);

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất danh sách văn bản giao việc
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Nhập danh sách văn bản giao việc
     */
    public function import(ImportTaskAssignmentDocumentRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import văn bản giao việc thành công.');
    }

    /**
     * Thêm tệp đính kèm cho văn bản
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     */
    public function addAttachments(AddAttachmentsTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $document = $this->service->addAttachments($taskAssignmentDocument, $request->file('files', []));

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Tệp đính kèm đã được thêm thành công!');
    }

    /**
     * Gỡ tệp đính kèm khỏi văn bản
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     * @urlParam attachment integer required ID tệp đính kèm. Example: 1
     */
    public function removeAttachment(TaskAssignmentDocument $taskAssignmentDocument, TaskAssignmentDocumentAttachment $attachment)
    {
        $this->service->removeAttachment($taskAssignmentDocument, $attachment);

        return $this->success(null, 'Tệp đính kèm đã được gỡ thành công!');
    }

    /**
     * Cập nhật thứ tự tệp đính kèm
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản. Example: 1
     */
    public function sortAttachments(SortAttachmentsTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $this->service->sortAttachments($taskAssignmentDocument, $request->ids);

        return $this->success(null, 'Cập nhật thứ tự tệp đính kèm thành công!');
    }
}
