<?php

namespace Modules\Common\Services;

use Modules\Common\Services\Impl\VersionService;
use Modules\Common\Services\Impl\PartnerService;
use Modules\Common\Services\Impl\MediaService;

class CommonServiceFactory
{
    protected static $mVersionService;
    protected static $mPartnerService;
    protected static $mMediaService;

    public static function mVersionService()
    {
        if (self::$mVersionService == null) {
            self::$mVersionService = new VersionService();
        }
        return self::$mVersionService;
    }

    public static function mParnerService()
    {
        if (self::$mPartnerService == null) {
            self::$mPartnerService = new PartnerService();
        }
        return self::$mPartnerService;
    }

    public static function mMediaService()
    {
        if (self::$mMediaService == null) {
            self::$mMediaService = new MediaService();
        }
        return self::$mMediaService;
    }
}