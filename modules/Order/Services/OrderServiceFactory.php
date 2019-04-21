<?php

namespace Modules\Order\Services;

use Modules\Order\Services\Impl\OrderService;
use Modules\Order\Services\Impl\HistoryService;
use Modules\Order\Services\Impl\PackageService;

class OrderServiceFactory
{
    protected static $mOrderService;
    protected static $mHistoryService;
    protected static $mPackageService;

    public static function mOrderService()
    {
        if (self::$mOrderService == null) {
            self::$mOrderService = new OrderService();
        }
        return self::$mOrderService;
    }

    public static function mHistoryService()
    {
        if (self::$mHistoryService == null) {
            self::$mHistoryService = new HistoryService();
        }
        return self::$mHistoryService;
    }

    public static function mPackageService()
    {
        if (self::$mPackageService == null) {
            self::$mPackageService = new PackageService();
        }
        return self::$mPackageService;
    }
}