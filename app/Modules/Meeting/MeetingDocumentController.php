<?php

namespace App\Modules\Meeting;

use App\Http\Controllers\Controller;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\MeetingDocument;
use App\Modules\Meeting\Requests\UploadMeetingDocumentRequest;
use App\Modules\Meeting\Resources\MeetingDocumentCollection;
use App\Modules\Meeting\Resources\MeetingDocumentResource;
use App\Modules\Meeting\Services\MeetingDocumentService;

/**
 * @group Meeting - Tài liệu họp
 *
 * Upload và quản lý tài liệu đính kèm cuộc họp.
 */
class MeetingDocumentController extends Controller
{
    public function __construct(private MeetingDocumentService $documentService) {}

    /**
     * Danh sách tài liệu của cuộc họp
     *
     * @urlParam meeting integer required ID cuộc họp.
     */
    public function index(Meeting $meeting)
    {
        $docs = $this->documentService->index($meeting);

        return $this->successCollection(new MeetingDocumentCollection($docs));
    }

    /**
     * Upload tài liệu họp
     *
     * @urlParam meeting integer required ID cuộc họp.
     * @bodyParam documents[] file required File tài liệu (pdf, doc, docx, xls, xlsx, ppt, pptx, tối đa 50MB mỗi file).
     * @bodyParam name string Tên hiển thị (áp dụng cho tất cả file upload). Example: Báo cáo tháng 3
     * @bodyParam type string Loại tài liệu do người dùng nhập. Example: Biên bản
     */
    public function store(UploadMeetingDocumentRequest $request, Meeting $meeting)
    {
        $docs = $this->documentService->upload(
            $meeting,
            $request->file('documents', []),
            $request->name,
            $request->type
        );

        return $this->successCollection(new MeetingDocumentCollection($docs), 'Tài liệu đã được tải lên thành công!');
    }

    /**
     * Xóa tài liệu họp
     *
     * @urlParam meeting integer required ID cuộc họp.
     * @urlParam document integer required ID tài liệu.
     *
     * @response 200 {"success": true, "message": "Tài liệu đã được xóa!"}
     */
    public function destroy(Meeting $meeting, MeetingDocument $document)
    {
        $this->documentService->destroy($document);

        return $this->success(null, 'Tài liệu đã được xóa!');
    }
}
