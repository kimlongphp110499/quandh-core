<?php

namespace App\Modules\Meeting;

use App\Http\Controllers\Controller;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\Participant;
use App\Modules\Meeting\Requests\CheckinRequest;
use App\Modules\Meeting\Requests\StoreParticipantRequest;
use App\Modules\Meeting\Requests\UpdateParticipantRequest;
use App\Modules\Meeting\Resources\ParticipantCollection;
use App\Modules\Meeting\Resources\ParticipantResource;
use App\Modules\Meeting\Services\ParticipantService;

/**
 * @group Meeting - Đại biểu (Participants)
 *
 * Quản lý đại biểu tham dự và điểm danh.
 */
class ParticipantController extends Controller
{
    public function __construct(private ParticipantService $participantService) {}

    /**
     * Danh sách đại biểu
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     */
    public function index(Meeting $meeting)
    {
        $participants = $this->participantService->index($meeting);

        return $this->successCollection(new ParticipantCollection($participants));
    }

    /**
     * Thêm đại biểu vào cuộc họp
     *
     * Thêm nhiều người dùng cùng lúc. Người dùng đã có trong cuộc họp sẽ bị bỏ qua.
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @bodyParam user_ids array required Danh sách ID người dùng. Example: [1, 2, 3]
     * @bodyParam meeting_role string required Vai trò: chair, secretary, delegate. Example: delegate
     * @bodyParam position string Chức vụ (tên chức danh). Example: Trưởng phòng CNTT
     */
    public function store(StoreParticipantRequest $request, Meeting $meeting)
    {
        $participants = $this->participantService->store($meeting, $request->validated());

        return $this->successCollection(new ParticipantCollection($participants), 'Đại biểu đã được thêm vào cuộc họp!');
    }

    /**
     * Cập nhật thông tin đại biểu
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam participant integer required ID đại biểu. Example: 1
     * @bodyParam meeting_role string Vai trò: chair, secretary, delegate.
     * @bodyParam position string Chức vụ.
     */
    public function update(UpdateParticipantRequest $request, Meeting $meeting, Participant $participant)
    {
        $participant = $this->participantService->update($participant, $request->validated());

        return $this->successResource(new ParticipantResource($participant), 'Thông tin đại biểu đã được cập nhật!');
    }

    /**
     * Xóa đại biểu khỏi cuộc họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam participant integer required ID đại biểu. Example: 1
     *
     * @response 200 {"success": true, "message": "Đại biểu đã được xóa khỏi cuộc họp!"}
     */
    public function destroy(Meeting $meeting, Participant $participant)
    {
        $this->participantService->destroy($participant);

        return $this->success(null, 'Đại biểu đã được xóa khỏi cuộc họp!');
    }

    /**
     * Điểm danh đại biểu
     *
     * Đại biểu tự xác nhận có mặt hoặc quản lý cập nhật trạng thái điểm danh.
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam participant integer required ID đại biểu. Example: 1
     * @bodyParam attendance_status string required Trạng thái: not_arrived, present, absent. Example: present
     * @bodyParam absence_reason string Lý do vắng (yêu cầu khi absent).
     */
    public function checkin(CheckinRequest $request, Meeting $meeting, Participant $participant)
    {
        if($participant->attendance_status !== 'not_arrived') {
            return [
                    'ok' => false,
                    'message' => 'Đại biểu đã điểm danh rồi.',
                    'code' => 422,
                    'error_code' => 'CONFLICT',
                ];
        }

        $participant = $this->participantService->checkin(
            $participant,
            $request->attendance_status,
            $request->absence_reason
        );

        return $this->successResource(new ParticipantResource($participant), 'Điểm danh thành công!');
    }
}
