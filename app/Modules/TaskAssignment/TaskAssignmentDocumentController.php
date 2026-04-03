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
 * @header X-Organization-Id ID tổ chức cần làm việc (bắt buộc với endpoint yêu cầu auth). Example: 1
 *
 * Quản lý văn bản giao việc và tệp đính kèm: thống kê, danh sách, chi tiết, tạo, cập nhật, xóa, thao tác hàng loạt, xuất/nhập, đổi trạng thái và quản lý tệp đính kèm.
 */
class TaskAssignmentDocumentController extends Controller
{
    public function __construct(private TaskAssignmentDocumentService $service) {}

    /**
     * Thống kê văn bản giao việc
     *
     * Trả về tổng số văn bản và phân loại theo trạng thái (draft/issued).
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên văn bản.
     * @queryParam status string Lọc theo trạng thái: draft, issued.
     * @queryParam task_assignment_type_id integer Lọc theo loại văn bản. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": {"total": 10, "draft": 3, "issued": 7}}
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách văn bản giao việc
     *
     * Trả về danh sách văn bản giao việc có phân trang, hỗ trợ tìm kiếm và lọc nhiều chiều.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên văn bản.
     * @queryParam status string Lọc theo trạng thái: draft, issued.
     * @queryParam task_assignment_type_id integer Lọc theo loại văn bản. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, name, issue_date, created_at, updated_at. Example: issue_date
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: desc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDocument paginate=15
     *
     * @apiResourceAdditional success=true
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
     * Trả về thông tin đầy đủ của văn bản bao gồm loại văn bản, tệp đính kèm và số lượng công việc.
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDocument
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentDocument $taskAssignmentDocument)
    {
        return $this->successResource(new TaskAssignmentDocumentResource($this->service->show($taskAssignmentDocument)));
    }

    /**
     * Tạo văn bản giao việc mới
     *
     * @bodyParam name string required Tên văn bản giao việc (tối đa 255 ký tự). Example: Văn bản số 01/VB-UBND tháng 4/2026
     * @bodyParam summary string Tóm tắt nội dung văn bản.
     * @bodyParam issue_date date Ngày ban hành văn bản (Y-m-d). Example: 2026-04-01
     * @bodyParam task_assignment_type_id integer ID loại văn bản (phải tồn tại trong hệ thống). Example: 1
     * @bodyParam status string Trạng thái: draft, issued (mặc định draft). Example: draft
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentResource status=201
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDocument
     *
     * @apiResourceAdditional success=true message="Văn bản giao việc đã được tạo thành công!"
     */
    public function store(StoreTaskAssignmentDocumentRequest $request)
    {
        $document = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Văn bản giao việc đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật văn bản giao việc
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     *
     * @bodyParam name string Tên văn bản giao việc (tối đa 255 ký tự). Example: Văn bản số 01/VB-UBND tháng 4/2026
     * @bodyParam summary string Tóm tắt nội dung văn bản.
     * @bodyParam issue_date date Ngày ban hành văn bản (Y-m-d). Example: 2026-04-01
     * @bodyParam task_assignment_type_id integer ID loại văn bản. Example: 1
     * @bodyParam status string Trạng thái: draft, issued. Example: issued
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDocument
     *
     * @apiResourceAdditional success=true message="Văn bản giao việc đã được cập nhật!"
     */
    public function update(UpdateTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $document = $this->service->update($taskAssignmentDocument, $request->validated());

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Văn bản giao việc đã được cập nhật!');
    }

    /**
     * Xóa văn bản giao việc
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     *
     * @response 200 {"success": true, "message": "Văn bản giao việc đã được xóa thành công!"}
     */
    public function destroy(TaskAssignmentDocument $taskAssignmentDocument)
    {
        $this->service->destroy($taskAssignmentDocument);

        return $this->success(null, 'Văn bản giao việc đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt văn bản giao việc
     *
     * @bodyParam ids array required Danh sách ID văn bản giao việc cần xóa. Example: [1,2,3]
     * @bodyParam ids[] integer required ID văn bản giao việc. Example: 1
     *
     * @response 200 {"success": true, "message": "Đã xóa thành công các văn bản giao việc được chọn!"}
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentDocumentRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các văn bản giao việc được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt văn bản giao việc
     *
     * @bodyParam ids array required Danh sách ID văn bản giao việc. Example: [1,2,3]
     * @bodyParam ids[] integer required ID văn bản giao việc. Example: 1
     * @bodyParam status string required Trạng thái mới: draft, issued. Example: issued
     *
     * @response 200 {"success": true, "message": "Cập nhật trạng thái thành công!"}
     */
    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentDocumentRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    /**
     * Thay đổi trạng thái văn bản (draft -> issued)
     *
     * Dùng để ban hành chính thức một văn bản đang ở trạng thái nháp.
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     *
     * @bodyParam status string required Trạng thái mới: draft, issued. Example: issued
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDocument
     *
     * @apiResourceAdditional success=true message="Cập nhật trạng thái thành công!"
     */
    public function changeStatus(ChangeStatusTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $document = $this->service->changeStatus($taskAssignmentDocument, $request->status);

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất Excel danh sách văn bản giao việc
     *
     * Xuất ra các trường: id, name, summary, issue_date, status, loại văn bản, số công việc, created_by, updated_by, created_at, updated_at.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên văn bản.
     * @queryParam status string Lọc theo trạng thái: draft, issued.
     * @queryParam task_assignment_type_id integer Lọc theo loại văn bản. Example: 1
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Import văn bản giao việc từ Excel
     *
     * Cột bắt buộc: name. Cột không bắt buộc: summary, issue_date, task_assignment_type_id, status (mặc định "draft").
     *
     * @bodyParam file file required File Excel (xlsx, xls, csv). Cột theo chuẩn export.
     *
     * @response 200 {"success": true, "message": "Import văn bản giao việc thành công."}
     */
    public function import(ImportTaskAssignmentDocumentRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import văn bản giao việc thành công.');
    }

    /**
     * Thêm tệp đính kèm cho văn bản
     *
     * Upload một hoặc nhiều tệp đính kèm vào văn bản giao việc. Tệp được lưu qua MediaLibrary.
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     *
     * @bodyParam files[] file required Danh sách tệp đính kèm (pdf, doc, docx, xls, xlsx, ppt, pptx, tối đa 20MB/tệp).
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDocumentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDocument
     *
     * @apiResourceAdditional success=true message="Tệp đính kèm đã được thêm thành công!"
     */
    public function addAttachments(AddAttachmentsTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $document = $this->service->addAttachments($taskAssignmentDocument, $request->file('files', []));

        return $this->successResource(new TaskAssignmentDocumentResource($document), 'Tệp đính kèm đã được thêm thành công!');
    }

    /**
     * Gỡ tệp đính kèm khỏi văn bản
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     * @urlParam attachment integer required ID tệp đính kèm cần gỡ. Example: 1
     *
     * @response 200 {"success": true, "message": "Tệp đính kèm đã được gỡ thành công!"}
     */
    public function removeAttachment(TaskAssignmentDocument $taskAssignmentDocument, TaskAssignmentDocumentAttachment $attachment)
    {
        $this->service->removeAttachment($taskAssignmentDocument, $attachment);

        return $this->success(null, 'Tệp đính kèm đã được gỡ thành công!');
    }

    /**
     * Cập nhật thứ tự tệp đính kèm
     *
     * Sắp xếp lại thứ tự hiển thị của các tệp đính kèm theo danh sách ID truyền vào.
     *
     * @urlParam taskAssignmentDocument integer required ID văn bản giao việc. Example: 1
     *
     * @bodyParam ids array required Danh sách ID tệp đính kèm theo thứ tự mong muốn. Example: [3,1,2]
     * @bodyParam ids[] integer required ID tệp đính kèm. Example: 3
     *
     * @response 200 {"success": true, "message": "Cập nhật thứ tự tệp đính kèm thành công!"}
     */
    public function sortAttachments(SortAttachmentsTaskAssignmentDocumentRequest $request, TaskAssignmentDocument $taskAssignmentDocument)
    {
        $this->service->sortAttachments($taskAssignmentDocument, $request->ids);

        return $this->success(null, 'Cập nhật thứ tự tệp đính kèm thành công!');
    }
}
