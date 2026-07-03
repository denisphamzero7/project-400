<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kênh private 'admin'
Broadcast::channel('admin', function ($user) {
    // Ở đây, bạn nên có logic để kiểm tra xem user có phải là admin hay không
    // Ví dụ: return $user->isAdmin();
    // Để đơn giản, chúng ta sẽ cho phép mọi user đã đăng nhập
    return $user != null;
});

// Kênh chung cho các sự kiện của module Customers
Broadcast::channel('customers-channel', function ($user) {
    // Cho phép mọi user đã đăng nhập nghe kênh này
    // Trong thực tế, bạn có thể thêm logic kiểm tra quyền tại đây
    return $user != null;
});

// Kênh chung cho các sự kiện của module Orders
Broadcast::channel('orders-channel', function ($user) {
    return $user != null;
});
