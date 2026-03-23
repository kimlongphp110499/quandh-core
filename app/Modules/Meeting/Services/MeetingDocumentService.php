<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\MeetingDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeetingDocumentService
{
    public function index(Meeting $meeting): \Illuminate\Database\Eloquent\Collection
    {
        return $meeting->documents()->with('uploader')->get();
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

        return MeetingDocument::with('uploader')->whereIn('id', $ids)->get();
    }

    private function uploadOne(Meeting $meeting, UploadedFile $file, ?string $customName, ?string $type): MeetingDocument
    {
        $disk = 'public';
        $directory = "meetings/{$meeting->id}/documents";
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $fileName, $disk);

        return MeetingDocument::create([
            'meeting_id' => $meeting->id,
            'name' => $customName ?: $file->getClientOriginalName(),
            'type' => $type,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'disk' => $disk,
        ]);
    }

    public function destroy(MeetingDocument $document): void
    {
        $document->delete(); // file vật lý xóa trong Model::booted()
    }
}
