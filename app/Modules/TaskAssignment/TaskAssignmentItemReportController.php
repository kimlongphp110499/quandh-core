<?php

namespace App\Modules\TaskAssignment;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Models\TaskAssignmentItemReport;
use App\Modules\TaskAssignment\Requests\BulkDestroyTaskAssignmentItemReportRequest;
use App\Modules\TaskAssignment\Requests\StoreTaskAssignmentItemReportRequest;
use App\Modules\TaskAssignment\Requests\UpdateTaskAssignmentItemReportRequest;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemReportCollection;
use App\Modules\TaskAssignment\Resources\TaskAssignmentItemReportResource;
use App\Modules\TaskAssignment\Services\TaskAssignmentItemReportService;

/**
 * @group TaskAssignment - Báo cáo công việc
 * @header X-Organization-Id ID tổ chức cần làm việc (bắt buộc với endpoint yêu cầu auth). Example: 1
 *
 * Quản lý báo cáo kết quả thực hiện công việc: danh sách, chi tiết, tạo, cập nhật, xóa và xóa hàng loạt. Mỗi báo cáo gắn với một công việc cụ thể và có thể đính kèm tệp minh chứng.
 */
class TaskAssignmentItemReportController extends Controller
{
    public function __construct(private TaskAssignmentItemReportService $service) {}

    /**
     * Danh sách báo cáo công việc
     *
     * Trả về danh sách báo cáo kết quả thực hiện công việc, có thể lọc theo công việc hoặc người báo cáo.
     *
     * @queryParam task_assignment_item_id integer Lọc theo ID công việc. Example: 1
     * @queryParam reporter_user_id integer Lọc theo ID người báo cáo. Example: 5
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, completed_at, created_at, updated_at. Example: created_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: desc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentItemReportCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemReport paginate=15
     *
     * @apiResourceAdditional success=true
     */
    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemReportCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết báo cáo công việc
     *
     * Trả về thông tin đầy đủ của báo cáo bao gồm nội dung, số văn bản tham chiếu và danh sách tệp đính kèm.
     *
     * @urlParam taskAssignmentItemReport integer required ID báo cáo. Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemReportResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemReport
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentItemReport $taskAssignmentItemReport)
    {
        return $this->successResource(new TaskAssignmentItemReportResource(
            $this->service->show($taskAssignmentItemReport)
        ));
    }

    /**
     * Tạo báo cáo cho công việc
     *
     * Tạo mới một báo cáo kết quả thực hiện gắn với công việc cụ thể. Có thể đính kèm tệp minh chứng.
     *
     * @urlParam taskAssignmentItem integer required ID công việc cần báo cáo. Example: 1
     *
     * @bodyParam completed_at date Ngày hoàn thành thực tế (Y-m-d). Example: 2026-04-30
     * @bodyParam report_document_number string Số văn bản tham chiếu kết quả (tối đa 100 ký tự). Example: 15/QĐ-UBND
     * @bodyParam report_document_excerpt string Trích yếu nội dung văn bản tham chiếu (tối đa 500 ký tự).
     * @bodyParam report_document_content string Nội dung chi tiết kết quả thực hiện.
     * @bodyParam files[] file Tệp đính kèm minh chứng (pdf, doc, docx, xls, xlsx, ppt, pptx, tối đa 20MB/tệp).
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemReportResource status=201
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemReport
     *
     * @apiResourceAdditional success=true message="Báo cáo đã được tạo thành công!"
     */
    public function store(StoreTaskAssignmentItemReportRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        $report = $this->service->store($taskAssignmentItem, $request->validated(), $request->file('files', []));

        return $this->successResource(new TaskAssignmentItemReportResource($report), 'Báo cáo đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật báo cáo công việc
     *
     * Cập nhật nội dung báo cáo và/hoặc bổ sung thêm tệp đính kèm minh chứng.
     *
     * @urlParam taskAssignmentItemReport integer required ID báo cáo cần cập nhật. Example: 1
     *
     * @bodyParam completed_at date Ngày hoàn thành thực tế (Y-m-d). Example: 2026-04-30
     * @bodyParam report_document_number string Số văn bản tham chiếu kết quả (tối đa 100 ký tự). Example: 15/QĐ-UBND
     * @bodyParam report_document_excerpt string Trích yếu nội dung văn bản tham chiếu (tối đa 500 ký tự).
     * @bodyParam report_document_content string Nội dung chi tiết kết quả thực hiện.
     * @bodyParam files[] file Tệp đính kèm bổ sung (pdf, doc, docx, xls, xlsx, ppt, pptx, tối đa 20MB/tệp).
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemReportResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemReport
     *
     * @apiResourceAdditional success=true message="Báo cáo đã được cập nhật!"
     */
    public function update(UpdateTaskAssignmentItemReportRequest $request, TaskAssignmentItemReport $taskAssignmentItemReport)
    {
        $report = $this->service->update($taskAssignmentItemReport, $request->validated(), $request->file('files', []));

        return $this->successResource(new TaskAssignmentItemReportResource($report), 'Báo cáo đã được cập nhật!');
    }

    /**
     * Xóa báo cáo công việc
     *
     * @urlParam taskAssignmentItemReport integer required ID báo cáo cần xóa. Example: 1
     *
     * @response 200 {"success": true, "message": "Báo cáo đã được xóa thành công!"}
     */
    public function destroy(TaskAssignmentItemReport $taskAssignmentItemReport)
    {
        $this->service->destroy($taskAssignmentItemReport);

        return $this->success(null, 'Báo cáo đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt báo cáo công việc
     *
     * @bodyParam ids array required Danh sách ID báo cáo cần xóa. Example: [1,2,3]
     * @bodyParam ids[] integer required ID báo cáo. Example: 1
     *
     * @response 200 {"success": true, "message": "Đã xóa thành công các báo cáo được chọn!"}
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentItemReportRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các báo cáo được chọn!');
    }
}
