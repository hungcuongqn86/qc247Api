<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class BankAccount extends BaseEntity
{
    use Notifiable;

    protected $table = 'bank_account';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'account_number',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['bank_debt'];

    public function getBankDebtAttribute()
    {
        $query = $this->Transaction()->where('is_deleted', '=', 0);
        $res = $query->orderBy('id', 'desc')->first();
        if (!empty($res)) {
            return $res->bank_debt;
        } else {
            return 0;
        }
    }

    public function Transaction()
    {
        return $this->hasMany(Transaction::class, 'bank_account', 'id');
    }
}