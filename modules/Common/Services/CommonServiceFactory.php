<?php

namespace Modules\Common\Services;

use Modules\Common\Services\Impl\VersionService;
use Modules\Common\Services\Impl\MediaService;
use Modules\Common\Services\Impl\UserService;
use Modules\Common\Services\Impl\RoleService;

class CommonServiceFactory
{
    protected static $mVersionService;
    protected static $mMediaService;
    protected static $mUserService;
    protected static $mRoleService;

    public static function mVersionService()
    {
        if (self::$mVersionService == null) {
            self::$mVersionService = new VersionService();
        }
        return self::$mVersionService;
    }

    public static function mUserService()
    {
        if (self::$mUserService == null) {
            self::$mUserService = new UserService();
        }
        return self::$mUserService;
    }

    public static function mMediaService()
    {
        if (self::$mMediaService == null) {
            self::$mMediaService = new MediaService();
        }
        return self::$mMediaService;
    }

    public static function mRoleService()
    {
        if (self::$mRoleService == null) {
            self::$mRoleService = new RoleService();
        }
        return self::$mRoleService;
    }
}