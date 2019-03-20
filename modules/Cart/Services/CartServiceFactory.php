<?php

namespace Modules\Cart\Services;

use Modules\Cart\Services\Impl\CartService;

class CartServiceFactory
{
    protected static $mCartService;

    public static function mCartService()
    {
        if (self::$mCartService == null) {
            self::$mCartService = new CartService();
        }
        return self::$mCartService;
    }
}