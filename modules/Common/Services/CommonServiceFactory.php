<?php

namespace Modules\Common\Services;

use Modules\Common\Services\Impl\VersionService;
use Modules\Common\Services\Impl\SettingService;
use Modules\Common\Services\Impl\MediaService;
use Modules\Common\Services\Impl\UserService;
use Modules\Common\Services\Impl\RoleService;
use Modules\Common\Services\Impl\TransactionService;
use Modules\Common\Services\Impl\BankAccountService;

class CommonServiceFactory
{
    protected static $mVersionService;
    protected static $mSettingService;
    protected static $mMediaService;
    protected static $mUserService;
    protected static $mBankAccountService;
    protected static $mRoleService;
    protected static $mTransactionService;

    public static function mVersionService()
    {
        if (self::$mVersionService == null) {
            self::$mVersionService = new VersionService();
        }
        return self::$mVersionService;
    }

    public static function mSettingService()
    {
        if (self::$mSettingService == null) {
            self::$mSettingService = new SettingService();
        }
        return self::$mSettingService;
    }

    public static function mUserService()
    {
        if (self::$mUserService == null) {
            self::$mUserService = new UserService();
        }
        return self::$mUserService;
    }

    public static function mBankAccountService()
    {
        if (self::$mBankAccountService == null) {
            self::$mBankAccountService = new BankAccountService();
        }
        return self::$mBankAccountService;
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

    public static function mTransactionService()
    {
        if (self::$mTransactionService == null) {
            self::$mTransactionService = new TransactionService();
        }
        return self::$mTransactionService;
    }
}