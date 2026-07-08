<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Order Expiration Hours
    |--------------------------------------------------------------------------
    |
    | The number of hours a pending order remains active before it is marked
    | as expired by the orders:expire command.
    |
    */
    'expire_hours' => (int) env('ORDER_EXPIRE_HOURS', 48),
];
