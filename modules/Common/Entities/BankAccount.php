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
}