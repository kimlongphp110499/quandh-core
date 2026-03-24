<?php

namespace App\Modules\Meeting;

use App\Http\Controllers\Controller;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\SpeechRequest;
use App\Modules\Meeting\Requests\StoreSpeechRequest;
use App\Modules\Meeting\Requests\UpdateSpeechStatusRequest;
use App\Modules\Meeting\Resources\SpeechRequestCollection;
use App\Modules\Meeting\Resources\SpeechRequestResource;
use App\Modules\Meeting\Services\SpeechRequestService;
use Illuminate\Http\Request;

/**
 * @group Meeting - Đăng ký phát biểu
 *
 * Đại biểu đăng ký phát biểu; Quản lý duyệt hoặc từ chối.
 */
class SpeechRequestController extends Controller
{
    public function __construct(private SpeechRequestService $speechService) {}

    /**
     * Danh sách đăng ký phát biểu
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @queryParam status string Lọc trạng thái: pending, approved, rejected.
     * @queryParam agenda_id integer Lọc theo mục chương trình. Example: 2
     */
    public function index(Request $request, Meeting $meeting)
    {
        $requests = $this->speechService->index($meeting, $request->only(['status', 'agenda_id']));

        return $this->successCollection(new SpeechRequestCollection($requests));
    }

    /**
     * Đăng ký phát biểu (Đại biểu)
     *
     * Người dùng phải là đại biểu trong cuộc họp.
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @bodyParam agenda_id integer ID mục chương trình (nullable = phát biểu chung). Example: 2
     * @bodyParam content string Nội dung ý kiến dự kiến. Example: Đề xuất tăng ngân sách phòng CNTT.
     */
    public function store(StoreSpeechRequest $request, Meeting $meeting)
    {
        $speechRequest = $this->speechService->store($meeting, $request->validated());

        return $this->successResource(new SpeechRequestResource($speechRequest), 'Đăng ký phát biểu đã được gửi!', 201);
    }

    /**
     * Duyệt hoặc từ chối đăng ký phát biểu (Quản lý / Chủ trì)
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam speechRequest integer required ID đăng ký. Example: 1
     * @bodyParam status string required Trạng thái: approved, rejected. Example: approved
     * @bodyParam rejection_reason string Lý do từ chối (bắt buộc khi rejected).
     */
    public function updateStatus(UpdateSpeechStatusRequest $request, Meeting $meeting, SpeechRequest $speechRequest)
    {
        $speechRequest = $this->speechService->updateStatus($speechRequest, $request->validated());

        return $this->successResource(new SpeechRequestResource($speechRequest), 'Đã cập nhật trạng thái đăng ký phát biểu!');
    }
}
