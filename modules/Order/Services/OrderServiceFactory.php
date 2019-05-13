<?php

namespace Modules\Order\Services;

use Modules\Order\Services\Impl\OrderService;
use Modules\Order\Services\Impl\HistoryService;
use Modules\Order\Services\Impl\PackageService;
use Modules\Order\Services\Impl\ComplainService;
use Modules\Order\Services\Impl\ComplainProductService;
use Modules\Order\Services\Impl\CommentService;

class OrderServiceFactory
{
    protected static $mOrderService;
    protected static $mHistoryService;
    protected static $mCommentService;
    protected static $mPackageService;
    protected static $mComplainService;
    protected static $mComplainProductService;

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

    public static function mComplainService()
    {
        if (self::$mComplainService == null) {
            self::$mComplainService = new ComplainService();
        }
        return self::$mComplainService;
    }

    public static function mComplainProductService()
    {
        if (self::$mComplainProductService == null) {
            self::$mComplainProductService = new ComplainProductService();
        }
        return self::$mComplainProductService;
    }

    public static function mCommentService()
    {
        if (self::$mCommentService == null) {
            self::$mCommentService = new CommentService();
        }
        return self::$mCommentService;
    }
}