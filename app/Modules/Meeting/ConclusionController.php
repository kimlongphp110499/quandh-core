<?php

namespace App\Modules\Meeting;

use App\Http\Controllers\Controller;
use App\Modules\Meeting\Models\Conclusion;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Requests\StoreConclusionRequest;
use App\Modules\Meeting\Requests\UpdateConclusionRequest;
use App\Modules\Meeting\Resources\ConclusionCollection;
use App\Modules\Meeting\Resources\ConclusionResource;
use App\Modules\Meeting\Services\ConclusionService;

/**
 * @group Meeting - Kết luận
 *
 * Quản lý các kết luận của cuộc họp. Mỗi cuộc họp có thể có nhiều kết luận (1:N).
 * Kết luận có thể gắn với một mục chương trình cụ thể.
 */
class ConclusionController extends Controller
{
    public function __construct(private ConclusionService $conclusionService) {}

    /**
     * Danh sách kết luận của cuộc họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     */
    public function index(Meeting $meeting)
    {
        $conclusions = $this->conclusionService->index($meeting);

        return $this->successCollection(new ConclusionCollection($conclusions));
    }

    /**
     * Tạo kết luận mới
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @bodyParam title string required Tiêu đề kết luận. Example: Kết luận về ngân sách
     * @bodyParam content string required Nội dung chi tiết kết luận.
     * @bodyParam agenda_id integer Gắn với mục chương trình (nullable). Example: 2
     */
    public function store(StoreConclusionRequest $request, Meeting $meeting)
    {
        $conclusion = $this->conclusionService->store($meeting, $request->validated());

        return $this->successResource(new ConclusionResource($conclusion), 'Kết luận đã được ghi nhận!', 201);
    }

    /**
     * Cập nhật kết luận
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam conclusion integer required ID kết luận. Example: 1
     */
    public function update(UpdateConclusionRequest $request, Meeting $meeting, Conclusion $conclusion)
    {
        $conclusion = $this->conclusionService->update($conclusion, $request->validated());

        return $this->successResource(new ConclusionResource($conclusion), 'Kết luận đã được cập nhật!');
    }

    /**
     * Xóa kết luận
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam conclusion integer required ID kết luận. Example: 1
     *
     * @response 200 {"success": true, "message": "Kết luận đã được xóa!"}
     */
    public function destroy(Meeting $meeting, Conclusion $conclusion)
    {
        $this->conclusionService->destroy($conclusion);

        return $this->success(null, 'Kết luận đã được xóa!');
    }
}
