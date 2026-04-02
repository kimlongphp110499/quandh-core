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
 *
 * Quản lý báo cáo kết quả thực hiện công việc
 */
class TaskAssignmentItemReportController extends Controller
{
    public function __construct(private TaskAssignmentItemReportService $service) {}

    /**
     * Danh sách báo cáo công việc
     */
    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemReportCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết báo cáo
     *
     * @urlParam taskAssignmentItemReport integer required ID báo cáo. Example: 1
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
     * @urlParam taskAssignmentItem integer required ID công việc. Example: 1
     */
    public function store(StoreTaskAssignmentItemReportRequest $request, TaskAssignmentItem $taskAssignmentItem)
    {
        $report = $this->service->store($taskAssignmentItem, $request->validated(), $request->file('files', []));

        return $this->successResource(new TaskAssignmentItemReportResource($report), 'Báo cáo đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật báo cáo
     *
     * @urlParam taskAssignmentItemReport integer required ID báo cáo. Example: 1
     */
    public function update(UpdateTaskAssignmentItemReportRequest $request, TaskAssignmentItemReport $taskAssignmentItemReport)
    {
        $report = $this->service->update($taskAssignmentItemReport, $request->validated(), $request->file('files', []));

        return $this->successResource(new TaskAssignmentItemReportResource($report), 'Báo cáo đã được cập nhật!');
    }

    /**
     * Xóa báo cáo
     *
     * @urlParam taskAssignmentItemReport integer required ID báo cáo. Example: 1
     */
    public function destroy(TaskAssignmentItemReport $taskAssignmentItemReport)
    {
        $this->service->destroy($taskAssignmentItemReport);

        return $this->success(null, 'Báo cáo đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt báo cáo
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentItemReportRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các báo cáo được chọn!');
    }
}
