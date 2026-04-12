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
 * Quản lý danh mục phòng ban nội bộ module giao việc: thống kê, danh sách, chi tiết, tạo, cập nhật, xóa, thao tác hàng loạt, xuất/nhập và đổi trạng thái.
 */
class TaskAssignmentDepartmentController extends Controller
{
    public function __construct(private TaskAssignmentDepartmentService $service) {}

    /**
     * Thống kê phòng ban
     *
     * Trả về tổng số phòng ban và phân loại theo trạng thái.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên hoặc mã phòng ban.
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
     * Danh sách phòng ban
     *
     * Trả về danh sách phòng ban có phân trang, hỗ trợ tìm kiếm và lọc theo trạng thái.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên hoặc mã phòng ban.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, code, name, sort_order, created_at, updated_at. Example: sort_order
     * @queryParam sort_order string Thứ tự sắp xếp: asc, desc. Example: asc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 15
     *
     * @apiResourceCollection App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentCollection
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDepartment paginate=15
     *
     * @apiResourceAdditional success=true
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
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDepartment
     *
     * @apiResourceAdditional success=true
     */
    public function show(TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        return $this->successResource(new TaskAssignmentDepartmentResource($this->service->show($taskAssignmentDepartment)));
    }

    /**
     * Tạo phòng ban mới
     *
     * @bodyParam code string required Mã phòng ban (duy nhất, tối đa 50 ký tự). Example: PB001
     * @bodyParam name string required Tên phòng ban (tối đa 255 ký tự). Example: Phòng Kế hoạch
     * @bodyParam description string Mô tả phòng ban.
     * @bodyParam status string Trạng thái: active, inactive (mặc định active). Example: active
     * @bodyParam sort_order integer Thứ tự hiển thị (số nguyên không âm). Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentResource status=201
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDepartment
     *
     * @apiResourceAdditional success=true message="Phòng ban đã được tạo thành công!"
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
     *
     * @bodyParam code string Mã phòng ban (duy nhất, tối đa 50 ký tự). Example: PB001
     * @bodyParam name string Tên phòng ban (tối đa 255 ký tự). Example: Phòng Kế hoạch
     * @bodyParam description string Mô tả phòng ban.
     * @bodyParam status string Trạng thái: active, inactive. Example: active
     * @bodyParam sort_order integer Thứ tự hiển thị (số nguyên không âm). Example: 1
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDepartment
     *
     * @apiResourceAdditional success=true message="Phòng ban đã được cập nhật!"
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
     *
     * @response 200 {"success": true, "message": "Phòng ban đã được xóa thành công!"}
     */
    public function destroy(TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        $this->service->destroy($taskAssignmentDepartment);

        return $this->success(null, 'Phòng ban đã được xóa thành công!');
    }

    /**
     * Xóa hàng loạt phòng ban
     *
     * @bodyParam ids array required Danh sách ID phòng ban cần xóa. Example: [1,2,3]
     * @bodyParam ids[] integer required ID phòng ban. Example: 1
     *
     * @response 200 {"success": true, "message": "Đã xóa thành công các phòng ban được chọn!"}
     */
    public function bulkDestroy(BulkDestroyTaskAssignmentDepartmentRequest $request)
    {
        $this->service->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các phòng ban được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt phòng ban
     *
     * @bodyParam ids array required Danh sách ID phòng ban. Example: [1,2,3]
     * @bodyParam ids[] integer required ID phòng ban. Example: 1
     * @bodyParam status string required Trạng thái mới: active, inactive. Example: inactive
     *
     * @response 200 {"success": true, "message": "Cập nhật trạng thái thành công!"}
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
     *
     * @bodyParam status string required Trạng thái mới: active, inactive. Example: active
     *
     * @apiResource App\Modules\TaskAssignment\Resources\TaskAssignmentDepartmentResource
     *
     * @apiResourceModel App\Modules\TaskAssignment\Models\TaskAssignmentDepartment
     *
     * @apiResourceAdditional success=true message="Cập nhật trạng thái thành công!"
     */
    public function changeStatus(ChangeStatusTaskAssignmentDepartmentRequest $request, TaskAssignmentDepartment $taskAssignmentDepartment)
    {
        $department = $this->service->changeStatus($taskAssignmentDepartment, $request->status);

        return $this->successResource(new TaskAssignmentDepartmentResource($department), 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xuất Excel danh sách phòng ban
     *
     * Xuất ra các trường: id, code, name, description, status, sort_order, created_by, updated_by, created_at, updated_at.
     *
     * @queryParam search string Từ khóa tìm kiếm theo tên hoặc mã.
     * @queryParam status string Lọc theo trạng thái: active, inactive.
     * @queryParam from_date date Lọc từ ngày tạo (Y-m-d). Example: 2026-01-01
     * @queryParam to_date date Lọc đến ngày tạo (Y-m-d). Example: 2026-12-31
     */
    public function export(FilterRequest $request)
    {
        return $this->service->export($request->all());
    }

    /**
     * Tải file mẫu import phòng ban
     *
     * Trả về file Excel mẫu gồm tiêu đề cột và dữ liệu ví dụ.
     * Người dùng tải về, điền dữ liệu phòng ban vào rồi upload qua API import.
     * Cột bắt buộc: Mã phòng ban, Tên phòng ban.
     * Cột không bắt buộc: Mô tả, Trạng thái (active/inactive), Thứ tự sắp xếp.
     *
     * @response file Trả về file Excel mẫu (mau-import-phong-ban.xlsx)
     */
    public function downloadTemplate()
    {
        return $this->service->downloadTemplate();
    }

    /**
     * Import phòng ban từ Excel
     *
     * Cột bắt buộc: code, name. Cột không bắt buộc: description, status (mặc định "active"), sort_order.
     *
     * @bodyParam file file required File Excel (xlsx, xls, csv). Cột theo chuẩn export.
     *
     * @response 200 {"success": true, "message": "Import phòng ban thành công."}
     */
    public function import(ImportTaskAssignmentDepartmentRequest $request)
    {
        $result = $this->service->import($request->file('file'));

        if (! empty($result['errors'])) {
            return $this->success($result, 'Import hoàn tất nhưng có một số dòng lỗi.');
        }

        return $this->success($result, 'Import phòng ban thành công.');
    }
}
