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
 * @header X-Organization-Id ID tổ chức cần làm việc (bắt buộc với endpoint yêu cầu auth). Example: 1
 *
 * Quản lý danh mục loại công việc: thống kê, danh sách, chi tiết, tạo, cập nhật, xóa, thao tác hàng loạt, xuất/nhập và đổi trạng thái.
 */
class TaskAssignmentItemTypeController extends Controller
{
    public function __construct(private TaskAssignmentItemTypeService $service) {}

    /**
     * Thống kê loại công việc
     *
     * Trả về tổng số loại công việc và phân loại theo trạng thái.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": {"total": 3, "active": 3, "inactive": 0}}
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách loại công việc
     *
     * Trả về danh sách loại công việc có phân trang, hỗ trợ tìm kiếm và lọc theo trạng thái.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, name, created_at, updated_at. Example: created_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: desc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemType paginate=15
     *
     * @apiResourceAdditional success=true
     */
    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentItemTypeCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết loại công việc
     *
     * @urlParam taskAssignmentItemType integer required ID loại công việc. Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemType
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentItemType $taskAssignmentItemType)
    {
        return $this->successResource(new TaskAssignmentItemTypeResource($this->service->show($taskAssignmentItemType)));
    }

    /**
     * Tạo loại công việc
     *
     * @bodyParam name string required Tên loại công việc (tối đa 255 ký tự). Example: Công việc chuyên môn
     * @bodyParam description string Mô tả loại công việc.
     * @bodyParam status string Trạng thái: active, inactive (mặc định active). Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeResource status=201
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemType
     *
     * @apiResourceAdditional success=true message="Loại công việc đã được tạo thành công!"
     */
    public function store(StoreTaskAssignmentItemTypeRequest $request)
    {
        $type = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentItemTypeResource($type), 'Loại công việc đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật loại công việc
     *
     * @urlParam taskAssignmentItemType integer required ID loại công việc. Example: 1
     *
     * @bodyParam name string Tên loại công việc (tối đa 255 ký tự). Example: Công việc chuyên môn
     * @bodyParam description string Mô tả loại công việc.
     * @bodyParam status string Trạng thái: active, inactive. Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemType
     *
     * @apiResourceAdditional success=true message="Loại công việc đã được cập nhật!"
     */
    public function update(UpdateTaskAssignmentItemTypeRequest $request, TaskAssignmentItemType $taskAssignmentItemType)
    {
        $type = $this->service->update($taskAssignmentItemType, $request->validated());

        return $this->successResource(new TaskAssignmentItemTypeResource($type), 'Loại công việc đã được cập nhật!');
    }

    /**
     * Xóa loại công việc
     *
     * @urlParam taskAssignmentItemType integer required ID loại công việc. Example: 1
     *
     * @response 200 {"success": true, "message": "Loại công việc đã được xóa thành công!"}
     */
    public function destroy(TaskAssignmentItemType $taskAssignmentItemType)
    {
        $this->service->destroy($taskAssignmentItemType);

        return $this->success(null, 'Loại công việc đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt loại công việc
     *
     * @bodyParam ids array required Danh sách ID loại công việc cần xóa. Example: [1,2,3]
     * @bodyParam ids[] integer required ID loại công việc. Example: 1
     *
     * @response 200 {"success": true, "message": "Đã xóa thành công các loại công việc được chọn!"}
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentItemTypeRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các loại công việc được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt loại công việc
     *
     * @bodyParam ids array required Danh sách ID loại công việc. Example: [1,2,3]
     * @bodyParam ids[] integer required ID loại công việc. Example: 1
     * @bodyParam status string required Trạng thái mới: active, inactive. Example: inactive
     *
     * @response 200 {"success": true, "message": "Cập nhật trạng thái thành công!"}
     */
    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentItemTypeRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    /**
     * Thay đổi trạng thái loại công việc
     *
     * @urlParam taskAssignmentItemType integer required ID loại công việc. Example: 1
     *
     * @bodyParam status string required Trạng thái mới: active, inactive. Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentItemTypeResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentItemType
     *
     * @apiResourceAdditional success=true message="Cập nhật trạng thái thành công!"
     */
    public function changeStatus(ChangeStatusTaskAssignmentItemTypeRequest $request, TaskAssignmentItemType $taskAssignmentItemType)
    {
        $type = $this->service->changeStatus($taskAssignmentItemType, $request->status);

        return $this->successResource(new TaskAssignmentItemTypeResource($type), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất Excel danh sách loại công việc
     *
     * Xuất ra các trường: id, name, description, status, created_by, updated_by, created_at, updated_at.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Import loại công việc từ Excel
     *
     * Cột bắt buộc: name. Cột không bắt buộc: description, status (mặc định "active").
     *
     * @bodyParam file file required File Excel (xlsx, xls, csv). Cột theo chuẩn export.
     *
     * @response 200 {"success": true, "message": "Import loại công việc thành công."}
     */
    public function import(ImportTaskAssignmentItemTypeRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import loại công việc thành công.');
    }
}
