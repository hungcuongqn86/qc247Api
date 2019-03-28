<?php

namespace Modules\Partner\Services;

use Modules\Partner\Services\Impl\PartnerService;

class PartnerServiceFactory
{
    protected static $mPartnerService;

    public static function mPartnerService()
    {
        if (self::$mPartnerService == null) {
            self::$mPartnerService = new PartnerService();
        }
        return self::$mPartnerService;
    }
}