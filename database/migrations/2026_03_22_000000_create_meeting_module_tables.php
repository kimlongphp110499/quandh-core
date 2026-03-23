<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng cuộc họp
        Schema::create('m_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('status')->default('draft'); // draft, active, in_progress, ended
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // 2. Bảng đại biểu (Pivot mở rộng: users <-> m_meetings)
        Schema::create('m_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('m_meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('position')->nullable();           // Chức vụ trong cuộc họp này
            $table->string('meeting_role')->default('delegate'); // chair, secretary, delegate
            $table->string('attendance_status')->default('not_arrived'); // not_arrived, present, absent
            $table->timestamp('checkin_at')->nullable();
            $table->text('absence_reason')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
        });

        // 3. Bảng mục chương trình họp (Agenda)
        Schema::create('m_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('m_meetings')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->unsignedSmallInteger('duration')->nullable(); // phút
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        // 4. Bảng tài liệu họp
        Schema::create('m_meeting_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('m_meetings')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, docx, xlsx, pptx, ...
            $table->string('disk')->default('public');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // 5. Bảng ghi chú cá nhân (private, chỉ owner thấy)
        Schema::create('m_personal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained('m_meetings')->cascadeOnDelete();
            $table->foreignId('meeting_document_id')->nullable()->constrained('m_meeting_documents')->nullOnDelete();
            $table->longText('content');
            $table->timestamps();

            $table->unique(['user_id', 'meeting_id', 'meeting_document_id'], 'm_personal_notes_unique');
        });

        // 6. Bảng đăng ký phát biểu
        Schema::create('m_speech_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('m_participants')->cascadeOnDelete();
            $table->foreignId('agenda_id')->nullable()->constrained('m_agendas')->nullOnDelete();
            $table->text('content')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // 7. Bảng phiên biểu quyết
        Schema::create('m_votings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('m_meetings')->cascadeOnDelete();
            $table->foreignId('agenda_id')->nullable()->constrained('m_agendas')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('public'); // public, anonymous
            $table->string('status')->default('pending'); // pending, active, closed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 8. Bảng kết quả biểu quyết
        Schema::create('m_vote_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_id')->constrained('m_votings')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('m_participants')->nullOnDelete(); // null nếu ẩn danh
            $table->string('vote_option'); // agree, disagree, abstain
            $table->timestamps();

            // Mỗi participant chỉ vote 1 lần mỗi voting (áp dụng cho public voting)
            $table->unique(['voting_id', 'participant_id'], 'm_vote_results_unique');
        });

        // 9. Bảng kết luận (1:N với meeting, N:M với agenda)
        Schema::create('m_conclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('m_meetings')->cascadeOnDelete();
            $table->foreignId('agenda_id')->nullable()->constrained('m_agendas')->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_conclusions');
        Schema::dropIfExists('m_vote_results');
        Schema::dropIfExists('m_votings');
        Schema::dropIfExists('m_speech_requests');
        Schema::dropIfExists('m_personal_notes');
        Schema::dropIfExists('m_meeting_documents');
        Schema::dropIfExists('m_agendas');
        Schema::dropIfExists('m_participants');
        Schema::dropIfExists('m_meetings');
    }
};
