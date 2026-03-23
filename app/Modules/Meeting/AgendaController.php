<?php

namespace App\Modules\Meeting;

use App\Http\Controllers\Controller;
use App\Modules\Meeting\Models\Agenda;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Requests\ReorderAgendasRequest;
use App\Modules\Meeting\Requests\StoreAgendaRequest;
use App\Modules\Meeting\Requests\UpdateAgendaRequest;
use App\Modules\Meeting\Resources\AgendaCollection;
use App\Modules\Meeting\Resources\AgendaResource;
use App\Modules\Meeting\Services\AgendaService;

/**
 * @group Meeting - Chương trình họp (Agenda)
 *
 * Quản lý mục chương trình họp.
 */
class AgendaController extends Controller
{
    public function __construct(private AgendaService $agendaService) {}

    /**
     * Danh sách mục chương trình họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     */
    public function index(Meeting $meeting)
    {
        $agendas = $this->agendaService->index($meeting);

        return $this->successCollection(new AgendaCollection($agendas));
    }

    /**
     * Thêm mục chương trình họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @bodyParam title string required Tiêu đề mục. Example: Báo cáo tháng 2
     * @bodyParam description string Mô tả chi tiết.
     * @bodyParam duration integer Thời lượng (phút). Example: 30
     */
    public function store(StoreAgendaRequest $request, Meeting $meeting)
    {
        $agenda = $this->agendaService->store($meeting, $request->validated());

        return $this->successResource(new AgendaResource($agenda), 'Mục chương trình đã được thêm!', 201);
    }

    /**
     * Cập nhật mục chương trình họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam agenda integer required ID mục chương trình. Example: 1
     */
    public function update(UpdateAgendaRequest $request, Meeting $meeting, Agenda $agenda)
    {
        $agenda = $this->agendaService->update($agenda, $request->validated());

        return $this->successResource(new AgendaResource($agenda), 'Mục chương trình đã được cập nhật!');
    }

    /**
     * Xóa mục chương trình họp
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam agenda integer required ID mục chương trình. Example: 1
     *
     * @response 200 {"success": true, "message": "Mục chương trình đã được xóa!"}
     */
    public function destroy(Meeting $meeting, Agenda $agenda)
    {
        $this->agendaService->destroy($agenda);

        return $this->success(null, 'Mục chương trình đã được xóa!');
    }

    /**
     * Đặt mục đang thảo luận (real-time điều hướng)
     *
     * Đánh dấu agenda đang được thảo luận. Trigger event để đồng bộ màn hình tất cả đại biểu.
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @urlParam agenda integer required ID mục chương trình. Example: 2
     *
     * @response 200 {"success": true, "message": "Đã chuyển sang mục thảo luận!"}
     */
    public function setCurrent(Meeting $meeting, Agenda $agenda)
    {
        $agenda = $this->agendaService->setCurrent($agenda);

        return $this->successResource(new AgendaResource($agenda), 'Đã chuyển sang mục thảo luận!');
    }

    /**
     * Sắp xếp lại thứ tự chương trình họp (Drag-and-drop)
     *
     * @urlParam meeting integer required ID cuộc họp. Example: 1
     * @bodyParam orders array required Danh sách thứ tự. Example: [{"id": 1, "order_index": 0}, {"id": 2, "order_index": 1}]
     *
     * @response 200 {"success": true, "message": "Thứ tự chương trình đã được cập nhật!"}
     */
    public function reorder(ReorderAgendasRequest $request, Meeting $meeting)
    {
        $this->agendaService->reorder($meeting, $request->orders);

        return $this->success(null, 'Thứ tự chương trình đã được cập nhật!');
    }
}
