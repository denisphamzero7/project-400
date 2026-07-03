# Hướng dẫn cài đặt Real-time, Queues và Redis trong Laravel

**Ngày tạo:** 2026-06-30
**Mục đích:** Tài liệu hóa các bước cài đặt và cấu hình để triển khai hệ thống xử lý nền (Queues) và cập nhật giao diện tức thì (Real-time) bằng Redis, Laravel Reverb và Horizon. Đây là "skill" nền tảng cho các module có nghiệp vụ phức tạp.

---

## 1. Tổng quan kiến trúc

Để cải thiện trải nghiệm người dùng và hiệu năng hệ thống, chúng ta tách các tác vụ tốn thời gian (gửi mail, xử lý file...) ra khỏi luồng request chính và đẩy chúng vào hàng đợi (Queue). Đồng thời, sử dụng WebSockets để cập nhật giao diện người dùng ngay lập tức khi có sự kiện xảy ra ở phía server.

**Các thành phần chính:**

1.  **Redis:** Một kho lưu trữ dữ liệu key-value trong bộ nhớ, được sử dụng làm "trái tim" cho hệ thống này. Nó đóng vai trò:
    *   **Queue Driver:** Nơi lưu trữ các jobs trong hàng đợi.
    *   **Broadcast Driver:** Kênh trung gian để phát các sự kiện real-time.
    *   **Cache Driver:** Lưu trữ cache của ứng dụng.
    *   **Session Driver:** Quản lý session người dùng.
2.  **Laravel Queues:** Framework của Laravel để làm việc với hàng đợi, cho phép xử lý tác vụ bất đồng bộ.
3.  **Laravel Horizon:** Một dashboard và hệ thống quản lý mạnh mẽ cho các hàng đợi trên Redis, giúp giám sát jobs, cấu hình workers, và xử lý lỗi.
4.  **Laravel Reverb:** Server WebSocket chính chủ từ Laravel, hiệu năng cao, dễ cài đặt, tích hợp sâu với hệ thống Broadcasting của Laravel.
5.  **Laravel Echo:** Thư viện JavaScript giúp lắng nghe các sự kiện được phát ra từ server một cách dễ dàng ở phía frontend.

**Sơ đồ luồng hoạt động:**

```
[User Action] -> [Controller] -> [Service]
                             |
                             +-> (Dispatch Event) -> [Laravel Event Bus]
                                                         |
                  +----------------------------------------+------------------------------------------+
                  | (Implements ShouldQueue)                                                         | (Implements ShouldBroadcast)
                  v                                                                                  v
    [Pushes Job to REDIS QUEUE]                                                          [Pushes Event to REDIS BROADCAST]
                  |                                                                                  |
                  v                                                                                  v
    [HORIZON WORKER picks up Job] -> [Listener/Handler] -> [Executes Task (e.g., Send Email)]        [REVERB SERVER picks up Event] -> [Sends to WebSocket]
                                                                                                     |
                                                                                                     v
                                                                                               [LARAVEL ECHO on Frontend] -> [Updates UI]
```

---

## 2. Các bước cài đặt và cấu hình

### Bước 1: Cài đặt Redis

Redis là yêu cầu bắt buộc. Bạn có thể cài đặt qua Docker (khuyến khích) hoặc trực tiếp trên máy.

1.  **Cài đặt `phpredis` extension:**
    ```bash
    # Ví dụ trên môi trường Sail/Docker
    pecl install redis
    docker-php-ext-enable redis
    ```
2.  **Cài đặt package Laravel:**
    ```bash
    composer require predis/predis
    ```
3.  **Cấu hình file `.env`:**
    Cập nhật các biến môi trường để Laravel sử dụng Redis làm driver mặc định cho các thành phần quan trọng.

    ```env
    # --- Redis ---
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379

    # --- Drivers ---
    BROADCAST_DRIVER=redis
    CACHE_DRIVER=redis
    QUEUE_CONNECTION=redis
    SESSION_DRIVER=redis
    ```

### Bước 2: Cài đặt Laravel Queues & Horizon

1.  **Cài đặt Horizon:**
    ```bash
    composer require laravel/horizon
    php artisan horizon:install
    ```
    Lệnh này sẽ tạo file cấu hình `config/horizon.php` và `HorizonServiceProvider`.

2.  **Cấu hình `config/horizon.php`:**
    Đây là nơi bạn định nghĩa các môi trường và các "supervisor" cho workers. Ví dụ, bạn có thể tạo các queues với độ ưu tiên khác nhau.

    ```php
    // config/horizon.php
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['instant', 'default', 'low'], // Ưu tiên: instant > default > low
                'balance' => 'auto',
                'processes' => 10,
                'tries' => 3,
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['instant', 'default', 'low'],
                'balance' => 'auto',
                'processes' => 3,
                'tries' => 3,
            ],
        ],
    ],
    ```

3.  **Chạy Horizon:**
    Để bắt đầu xử lý jobs, chạy lệnh:
    ```bash
    php artisan horizon
    ```
    Truy cập dashboard tại `/horizon` để giám sát.

### Bước 3: Cài đặt Laravel Reverb (Real-time)

1.  **Cài đặt Reverb:**
    ```bash
    php artisan install:broadcasting
    ```
    Lệnh này sẽ:
    *   Cài đặt `laravel/reverb`.
    *   Tạo file cấu hình `config/reverb.php`.
    *   Thêm các biến môi trường vào `.env`.
    *   Cài đặt `laravel-echo` và `pusher-js` qua NPM.

2.  **Cấu hình file `.env`:**
    Đảm bảo các biến này đã được tạo.
    ```env
    BROADCAST_CONNECTION=reverb

    REVERB_APP_ID=your_app_id
    REVERB_APP_KEY=your_app_key
    REVERB_SECRET=your_secret
    REVERB_HOST="localhost"
    REVERB_PORT=8080
    REVERB_SCHEME=http
    ```
    Bạn có thể tạo key và secret mới bằng lệnh: `php artisan reverb:keys`.

3.  **Chạy Reverb Server:**
    ```bash
    php artisan reverb:start
    ```

### Bước 4: Cấu hình Frontend (Laravel Echo)

File `resources/js/bootstrap.js` sẽ được tự động cấu hình sau khi chạy `install:broadcasting`. Bạn chỉ cần đảm bảo nó được uncomment và cấu hình đúng.

```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

Sau đó, biên dịch tài nguyên frontend:
```bash
npm install
npm run dev
```

---

## 3. Ví dụ sử dụng

Bây giờ bạn đã có thể tạo một Event vừa có thể đẩy vào Queue, vừa có thể phát đi Real-time.

**1. Tạo Event:**

```php
// app/Modules/Jobs/Events/JobUpdatedEvent.php

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobUpdatedEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, SerializesModels;

    // Chỉ định ném vào queue ưu tiên cao
    public $queue = 'instant';

    public function __construct(public string $action, public mixed $data) {}

    // Kênh sẽ phát sự kiện
    public function broadcastOn(): array
    {
        return [new Channel('jobs-realtime-channel')];
    }

    // Tên của sự kiện khi phát đi
    public function broadcastAs(): string
    {
        return 'JobEvent';
    }
}
```

**2. Kích hoạt Event:**

```php
// Trong một Service hoặc Controller

broadcast(new JobUpdatedEvent('job-created', $jobData));
```

**3. Lắng nghe ở Frontend:**

```javascript
// Trong một component Vue.js

onMounted(() => {
  window.Echo.channel('jobs-realtime-channel')
    .listen('.JobEvent', (event) => {
      console.log('Sự kiện mới:', event);
      // Cập nhật UI, hiển thị thông báo...
      if (event.action === 'job-created') {
        alert(`Công việc mới đã được tạo: ${event.data.title}`);
      }
    });
});
```

Với thiết lập này, hệ thống của bạn đã sẵn sàng cho các tính năng phức tạp, đòi hỏi hiệu năng cao và tương tác người dùng tốt.
