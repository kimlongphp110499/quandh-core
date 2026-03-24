<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Core\Services\MediaService;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\MeetingDocument;
use Illuminate\Http\UploadedFile;

class MeetingDocumentService
{
    public function __construct(private MediaService $mediaService) {}
    public function index(Meeting $meeting): \Illuminate\Database\Eloquent\Collection
    {
        return $meeting->documents()->with(['uploader', 'media'])->get();
    }

    /**
     * Upload nhiều tài liệu họp.
     * @return \Illuminate\Database\Eloquent\Collection<MeetingDocument>
     */
    public function upload(Meeting $meeting, array $files, ?string $customName = null, ?string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $ids = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $ids[] = $this->uploadOne($meeting, $file, $customName, $type)->id;
        }

        return MeetingDocument::with(['uploader', 'media'])->whereIn('id', $ids)->get();
    }

    private function uploadOne(Meeting $meeting, UploadedFile $file, ?string $customName, ?string $type): MeetingDocument
    {
        $doc = MeetingDocument::create([
            'meeting_id' => $meeting->id,
            'name' => $customName ?: $file->getClientOriginalName(),
            'type' => $type,
        ]);

        $this->mediaService->uploadOne($doc, $file, 'file');

        return $doc;
    }

    public function destroy(MeetingDocument $document): void
    {
        $document->delete(); // Spatie tự xóa media đính kèm khi model bị xóa
    }
}
