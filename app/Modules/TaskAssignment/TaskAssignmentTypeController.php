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
 * @header X-Organization-Id ID tổ chức cần làm việc (bắt buộc với endpoint yêu cầu auth). Example: 1
 *
 * Quản lý danh mục loại văn bản giao việc: thống kê, danh sách, chi tiết, tạo, cập nhật, xóa, thao tác hàng loạt, xuất/nhập và đổi trạng thái.
 */
class TaskAssignmentTypeController extends Controller
{
    public function __construct(private TaskAssignmentTypeService $service) {}

    /**
     * Thống kê loại văn bản giao việc
     *
     * Trả về tổng số loại văn bản và phân loại theo trạng thái.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": {"total": 5, "active": 4, "inactive": 1}}
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->service->stats($request->all()));
    }

    /**
     * Danh sách loại văn bản giao việc
     *
     * Trả về danh sách loại văn bản có phân trang, hỗ trợ tìm kiếm và lọc theo trạng thái.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, name, created_at, updated_at. Example: created_at
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: desc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentTypeCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentType paginate=15
     *
     * @apiResourceAdditional success=true
     */
    public function index(FilterRequest $request)
    {
        return $this->successCollection(new TaskAssignmentTypeCollection(
            $this->service->index($request->all(), (int) ($request->limit ?? 15))
        ));
    }

    /**
     * Chi tiết loại văn bản giao việc
     *
     * @urlParam taskAssignmentType integer required ID loại văn bản. Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentTypeResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentType
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentType $taskAssignmentType)
    {
        return $this->successResource(new TaskAssignmentTypeResource($this->service->show($taskAssignmentType)));
    }

    /**
     * Tạo loại văn bản giao việc
     *
     * @bodyParam name string required Tên loại văn bản (tối đa 255 ký tự). Example: Chỉ thị
     * @bodyParam description string Mô tả loại văn bản.
     * @bodyParam status string Trạng thái: active, inactive (mặc định active). Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentTypeResource status=201
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentType
     *
     * @apiResourceAdditional success=true message="Loại văn bản đã được tạo thành công!"
     */
    public function store(StoreTaskAssignmentTypeRequest $request)
    {
        $type = $this->service->store($request->validated());

        return $this->successResource(new TaskAssignmentTypeResource($type), 'Loại văn bản đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật loại văn bản giao việc
     *
     * @urlParam taskAssignmentType integer required ID loại văn bản. Example: 1
     *
     * @bodyParam name string Tên loại văn bản (tối đa 255 ký tự). Example: Chỉ thị
     * @bodyParam description string Mô tả loại văn bản.
     * @bodyParam status string Trạng thái: active, inactive. Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentTypeResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentType
     *
     * @apiResourceAdditional success=true message="Loại văn bản đã được cập nhật!"
     */
    public function update(UpdateTaskAssignmentTypeRequest $request, TaskAssignmentType $taskAssignmentType)
    {
        $type = $this->service->update($taskAssignmentType, $request->validated());

        return $this->successResource(new TaskAssignmentTypeResource($type), 'Loại văn bản đã được cập nhật!');
    }

    /**
     * Xóa loại văn bản giao việc
     *
     * @urlParam taskAssignmentType integer required ID loại văn bản. Example: 1
     *
     * @response 200 {"success": true, "message": "Loại văn bản đã được xóa thành công!"}
     */
    public function destroy(TaskAssignmentType $taskAssignmentType)
    {
        $this->service->destroy($taskAssignmentType);

        return $this->success(null, 'Loại văn bản đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt loại văn bản giao việc
     *
     * @bodyParam ids array required Danh sách ID loại văn bản cần xóa. Example: [1,2,3]
     * @bodyParam ids[] integer required ID loại văn bản. Example: 1
     *
     * @response 200 {"success": true, "message": "Đã xóa thành công các loại văn bản được chọn!"}
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentTypeRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các loại văn bản được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt loại văn bản giao việc
     *
     * @bodyParam ids array required Danh sách ID loại văn bản. Example: [1,2,3]
     * @bodyParam ids[] integer required ID loại văn bản. Example: 1
     * @bodyParam status string required Trạng thái mới: active, inactive. Example: inactive
     *
     * @response 200 {"success": true, "message": "Cập nhật trạng thái thành công!"}
     */
    public function bulkUpdateStatus(BulkUpdateStatusTaskAssignmentTypeRequest $request)
    {
        $this->service->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái thành công!');
    }

    /**
     * Thay đổi trạng thái loại văn bản giao việc
     *
     * @urlParam taskAssignmentType integer required ID loại văn bản. Example: 1
     *
     * @bodyParam status string required Trạng thái mới: active, inactive. Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentTypeResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentType
     *
     * @apiResourceAdditional success=true message="Cập nhật trạng thái thành công!"
     */
    public function changeStatus(ChangeStatusTaskAssignmentTypeRequest $request, TaskAssignmentType $taskAssignmentType)
    {
        $type = $this->service->changeStatus($taskAssignmentType, $request->status);

        return $this->successResource(new TaskAssignmentTypeResource($type), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất Excel danh sách loại văn bản giao việc
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
     * Import loại văn bản giao việc từ Excel
     *
     * Cột bắt buộc: name. Cột không bắt buộc: description, status (mặc định "active").
     *
     * @bodyParam file file required File Excel (xlsx, xls, csv). Cột theo chuẩn export.
     *
     * @response 200 {"success": true, "message": "Import loại văn bản thành công."}
     */
    public function import(ImportTaskAssignmentTypeRequest $request)
    {
        $this->service->import($request->file('file'));

        return $this->success(null, 'Import loại văn bản thành công.');
    }

    /**
     * Tải file mẫu import loại văn bản
     *
     * Trả về file Excel mẫu gồm tiêu đề cột và dữ liệu ví dụ.
     * Cột bắt buộc: name. Cột không bắt buộc: description, status (mặc định "active").
     *
     * @response file Trả về file Excel mẫu (mau-import-loai-van-ban.xlsx)
     */
    public function downloadTemplate()
    {
        return $this->service->downloadTemplate();
    }
}
