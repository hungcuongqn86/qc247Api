<?php

namespace Modules\Order\Services;

use Modules\Order\Services\Impl\OrderService;

class OrderServiceFactory
{
    protected static $mOrderService;

    public static function mOrderService()
    {
        if (self::$mOrderService == null) {
            self::$mOrderService = new OrderService();
        }
        return self::$mOrderService;
    }
}