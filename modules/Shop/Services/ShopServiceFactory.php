<?php

namespace Modules\Shop\Services;

use Modules\Shop\Services\Impl\ShopService;

class ShopServiceFactory
{
    protected static $mShopService;

    public static function mShopService()
    {
        if (self::$mShopService == null) {
            self::$mShopService = new ShopService();
        }
        return self::$mShopService;
    }
}