<?php

namespace App\Modules\TaskAssignment\Notifications;

use App\Modules\Core\Services\SettingService;
use App\Modules\TaskAssignment\Models\TaskAssignmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class TaskAssignmentReminderEmailNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected TaskAssignmentReminder $reminder
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Đọc toàn bộ cấu hình từ SettingService (có cache) rồi áp dụng trước khi gửi
        $emailConfig = $this->getEmailConfig();
        $this->applyMailConfig($emailConfig);

        $item = $this->reminder->item;
        $deadline = $item?->end_at?->format('H:i d/m/Y') ?? 'Chưa xác định';
        $remindAt = $this->reminder->remind_at?->format('H:i d/m/Y') ?? now()->format('H:i d/m/Y');

        $senderAddress = $emailConfig['email_sender_address'] ?? config('mail.from.address');
        $senderName    = $emailConfig['email_sender_name'] ?? config('mail.from.name');

        return (new MailMessage)
            ->from($senderAddress, $senderName)
            ->subject('Nhắc việc công việc được giao')
            ->greeting('Xin chào '.$notifiable->name.',')
            ->line('Bạn có công việc cần theo dõi tiến độ.')
            ->line('Tên công việc: '.($item?->name ?? 'Không có tiêu đề'))
            ->line('Hạn hoàn thành: '.$deadline)
            ->line('Thời điểm nhắc: '.$remindAt)
            ->line('Vui lòng kiểm tra và cập nhật tiến độ công việc đúng hạn.');
    }

    /**
     * Lấy nhóm cấu hình email từ SettingService.
     * Dữ liệu giống hệt response của GET api/settings (nhóm theo group, có cache).
     *
     * @return array<string, mixed>
     */
    protected function getEmailConfig(): array
    {
        /** @var SettingService $settingService */
        $settingService = app(SettingService::class);
        $all = $settingService->getAll();

        // getAll() trả về ['email' => ['email_smtp_host' => ..., ...], ...]
        return $all['email'] ?? [];
    }

    /**
     * Ghi đè cấu hình mailer runtime bằng dữ liệu từ nhóm email trong settings.
     * Chỉ ghi đè các key có giá trị thực sự được cấu hình.
     *
     * @param array<string, mixed> $emailConfig
     */
    protected function applyMailConfig(array $emailConfig): void
    {
        // Mapping key trong settings -> key config Laravel
        $map = [
            'email_smtp_host'       => 'mail.mailers.smtp.host',
            'email_smtp_port'       => 'mail.mailers.smtp.port',
            'email_smtp_username'   => 'mail.mailers.smtp.username',
            'email_smtp_password'   => 'mail.mailers.smtp.password',
            'email_smtp_encryption' => 'mail.mailers.smtp.encryption',
            'email_sender_address'  => 'mail.from.address',
            'email_sender_name'     => 'mail.from.name',
        ];

        foreach ($map as $settingKey => $configKey) {
            $value = $emailConfig[$settingKey] ?? null;
            if ($value !== null && $value !== '') {
                Config::set($configKey, $value);
            }
        }
    }
}
