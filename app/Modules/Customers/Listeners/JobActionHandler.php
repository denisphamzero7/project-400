<?php

namespace App\Modules\Jobs\Listeners;

use App\Modules\Jobs\Events\JobUpdatedEvent;
use App\Modules\Jobs\Notifications\JobNotification; // Kéo Notification vào
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

use App\Modules\Core\Models\User as ModelsUser;


class JobActionHandler implements ShouldQueue
{
    use InteractsWithQueue;

    // public $queue = 'instant'; // Xóa dòng này hoặc thay đổi thành tên queue khác (ví dụ: 'emails')

    public function __construct() {}

    public function handle(JobUpdatedEvent $event): void
    {
        $action = $event->action;
        $data = $event->data;

        switch ($action) {
            case 'job-created':
                Log::info("🚀 [Listener] Có một công việc mới: ", (array) $data);

                // Lấy danh sách các User có email
                $usersInOrg = ModelsUser::whereNotNull('email')->get();

                if ($usersInOrg->isEmpty()) {
                    Log::warning("⚠️ Không tìm thấy user nào có email để gửi thông báo cho action '{$action}'.");
                    break;
                }

                $emailsList = $usersInOrg->pluck('email')->toArray();
                Log::info("📧 Chuẩn bị gửi email '{$action}' đến " . count($emailsList) . " địa chỉ.", $emailsList);

                // Kích hoạt Notification gửi cho toàn bộ danh sách User
                Notification::send($usersInOrg, new JobNotification($action, $data));
                break;

            case 'job-updated':
                Log::info("✏️ [Listener] Công việc đã được cập nhật.", (array) $data);

                // Gửi cho người tạo ra công việc đó
                if (isset($data['created_by'])) {
                    $creator = ModelsUser::find($data['created_by']);
                    if ($creator) {
                        Log::info("📧 Chuẩn bị gửi email '{$action}' đến người tạo: " . $creator->email);
                        $creator->notify(new JobNotification($action, $data));
                    } else {
                        Log::warning("⚠️ Không tìm thấy người tạo với ID: " . $data['created_by']);
                    }
                }
                break;

            case 'job-deleted':
                Log::info("🗑️ [Listener] Một công việc vừa bị xóa. ID: " . ($data['id'] ?? 'N/A'));
                // Gửi email báo cho người tạo biết công việc của họ đã bị xóa
                if (isset($data['created_by'])) {
                    $creator = ModelsUser::find($data['created_by']);
                     if ($creator) {
                        Log::info("📧 Chuẩn bị gửi email '{$action}' đến người tạo: " . $creator->email);
                        $creator->notify(new JobNotification($action, $data));
                    } else {
                        Log::warning("⚠️ Không tìm thấy người tạo với ID: " . $data['created_by'] . " cho công việc đã xóa.");
                    }
                }
                break;

            case 'job-bulk-deleted':
                Log::info("🔥 [Listener] Đã xóa hàng loạt công việc. Các ID: ", $data['ids'] ?? []);
                $usersInOrg = ModelsUser::whereNotNull('email')->get();
                if (!$usersInOrg->isEmpty()) {
                    Log::info("📧 Chuẩn bị gửi email '{$action}' đến tất cả người dùng.");
                    Notification::send($usersInOrg, new JobNotification($action, $data));
                }
                break;

            case 'job-bulk-status-updated':
                Log::info("🔄 [Listener] Đã cập nhật trạng thái hàng loạt thành [{$data['status']}]. Các ID: ", $data['ids'] ?? []);
                $usersInOrg = ModelsUser::whereNotNull('email')->get();
                if (!$usersInOrg->isEmpty()) {
                     Log::info("📧 Chuẩn bị gửi email '{$action}' đến tất cả người dùng.");
                    Notification::send($usersInOrg, new JobNotification($action, $data));
                }
                break;

            case 'task-reminder':
                Log::info("⏰ [Listener] Nhắc nhở hạn chót: ", (array) $data);
                // Gửi Notification cho người được giao việc
                 if (isset($data['user_id'])) { // Hoặc assignee_id, tùy thuộc vào cấu trúc data
                    $user = ModelsUser::find($data['user_id']);
                    if ($user) {
                        Log::info("📧 Chuẩn bị gửi email '{$action}' đến người được giao: " . $user->email);
                        $user->notify(new JobNotification($action, $data));
                    } else {
                        Log::warning("⚠️ Không tìm thấy user với ID: " . $data['user_id'] . " cho nhắc nhở.");
                    }
                }
                break;

            default:
                Log::info("⚙️ [Listener] Bắt được hành động không xác định: {$action}");
                $usersInOrg = ModelsUser::whereNotNull('email')->get();
                if (!$usersInOrg->isEmpty()) {
                    Log::info("📧 Chuẩn bị gửi email thông báo về hành động '{$action}' đến tất cả người dùng.");
                    Notification::send($usersInOrg, new JobNotification($action, $data));
                }
                break;
        }
    }
}
