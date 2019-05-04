<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Notifications\Notifiable;

class Transaction extends BaseEntity
{
    use Notifiable;

    protected $table = 'transaction';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'type',
        'code',
        'value',
        'debt',
        'bank_account',
        'bank_debt',
        'content',
        'is_deleted',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['otype'];

    public function getOtypeAttribute()
    {
        $oType = new \stdClass();
        if (!empty($this->attributes['type'])) {
            $types = self::_type();
            foreach ($types as $type) {
                if ($type->id === $this->attributes['type']) {
                    $oType = $type;
                }
            }
        }
        return $oType;
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function BankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account', 'id');
    }

    public function _type()
    {
        $types = [];

        // Nạp tiền
        $newobj = new \stdClass();
        $newobj->id = 1;
        $newobj->name = 'Nạp tiền';
        $newobj->value = 1;
        $types[] = $newobj;

        // Rút tiền
        $newobj = new \stdClass();
        $newobj->id = 2;
        $newobj->name = 'Rút tiền';
        $newobj->value = -1;
        $types[] = $newobj;

        // Thanh toán
        $newobj = new \stdClass();
        $newobj->id = 3;
        $newobj->name = 'Thanh toán';
        $newobj->value = -1;
        $types[] = $newobj;

        // Đặt cọc
        $newobj = new \stdClass();
        $newobj->id = 4;
        $newobj->name = 'Đặt cọc';
        $newobj->value = -1;
        $types[] = $newobj;

        // Đặt cọc
        $newobj = new \stdClass();
        $newobj->id = 5;
        $newobj->name = 'Hoàn cọc';
        $newobj->value = 1;
        $types[] = $newobj;

        // Thanh lý
        $newobj = new \stdClass();
        $newobj->id = 6;
        $newobj->name = 'Thanh lý';
        $newobj->value = -1;
        $types[] = $newobj;

        return $types;
    }
}