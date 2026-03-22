<?php

namespace App\Modules\Meeting;

use App\Http\Controllers\Controller;
use App\Modules\Core\Requests\FilterRequest;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Requests\ChangeMeetingStatusRequest;
use App\Modules\Meeting\Requests\StoreMeetingRequest;
use App\Modules\Meeting\Requests\UpdateMeetingRequest;
use App\Modules\Meeting\Resources\MeetingCollection;
use App\Modules\Meeting\Resources\MeetingResource;
use App\Modules\Meeting\Services\MeetingService;

/**
 * @group Meeting - Cuộc họp
 * @header X-Organization-Id ID tổ chức (bắt buộc). Example: 1
 *
 * Quản lý cuộc họp: tạo, chỉnh sửa, xem, thay đổi trạng thái, xóa.
 */
class MeetingController extends Controller
{
    public function __construct(private MeetingService $meetingService) {}

    /**
     * Thống kê cuộc họp
     *
     * @queryParam search string Từ khóa tìm kiếm. Example: họp quý
     * @queryParam status string Lọc trạng thái: draft, active, in_progress, ended.
     * @queryParam from_date date Từ ngày. Example: 2026-01-01
     * @queryParam to_date date Đến ngày. Example: 2026-12-31
     *
     * @response 200 {"success": true, "data": {"total": 10, "draft": 2, "active": 3, "in_progress": 1, "ended": 4}}
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->meetingService->stats($request->all()));
    }

    /**
     * Danh sách cuộc họp
     *
     * @queryParam search string Từ khóa tìm kiếm.
     * @queryParam status string Lọc trạng thái: draft, active, in_progress, ended.
     * @queryParam from_date date Từ ngày bắt đầu. Example: 2026-01-01
     * @queryParam to_date date Đến ngày bắt đầu. Example: 2026-12-31
     * @queryParam sort_by string Sắp xếp theo: id, title, start_at, end_at, created_at. Example: start_at
     * @queryParam sort_order string Thứ tự: asc, desc. Example: desc
     * @queryParam limit integer Số bản ghi mỗi trang (1-100). Example: 10
     */
    public function index(FilterRequest $request)
    {
        $meetings = $this->meetingService->index($request->all(), (int) ($request->limit ?? 10));

        return $this->successCollection(new MeetingCollection($meetings));
    }

    /**
     * Chi tiết cuộc họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     */
    public function show(Meeting $meeting)
    {
        $meeting = $this->meetingService->show($meeting);

        return $this->successResource(new MeetingResource($meeting));
    }

    /**
     * Tạo cuộc họp mới
     *
     * @bodyParam title string required Tiêu đề cuộc họp. Example: Họp tháng 3/2026
     * @bodyParam description string Mô tả.
     * @bodyParam location string Địa điểm. Example: Phòng họp A
     * @bodyParam start_at datetime Thời gian bắt đầu. Example: 2026-03-25 08:00:00
     * @bodyParam end_at datetime Thời gian kết thúc. Example: 2026-03-25 10:00:00
     */
    public function store(StoreMeetingRequest $request)
    {
        $meeting = $this->meetingService->store($request->validated());

        return $this->successResource(new MeetingResource($meeting), 'Cuộc họp đã được tạo thành công!', 201);
    }

    /**
     * Cập nhật cuộc họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     */
    public function update(UpdateMeetingRequest $request, Meeting $meeting)
    {
        $meeting = $this->meetingService->update($meeting, $request->validated());

        return $this->successResource(new MeetingResource($meeting), 'Cuộc họp đã được cập nhật!');
    }

    /**
     * Xóa cuộc họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     *
     * @response 200 {"success": true, "message": "Cuộc họp đã được xóa thành công!"}
     */
    public function destroy(Meeting $meeting)
    {
        $this->meetingService->destroy($meeting);

        return $this->success(null, 'Cuộc họp đã được xóa thành công!');
    }

    /**
     * Thay đổi trạng thái cuộc họp
     *
     * Luồng trạng thái: draft → active → in_progress → ended.
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @bodyParam status string required Trạng thái mới: draft, active, in_progress, ended. Example: active
     *
     * @response 200 {"success": true, "message": "Trạng thái cuộc họp đã được cập nhật!"}
     */
    public function changeStatus(ChangeMeetingStatusRequest $request, Meeting $meeting)
    {
        $meeting = $this->meetingService->changeStatus($meeting, $request->status);

        return $this->successResource(new MeetingResource($meeting), 'Trạng thái cuộc họp đã được cập nhật!');
    }
}
