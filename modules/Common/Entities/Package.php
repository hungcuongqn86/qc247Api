<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Package extends BaseEntity
{
    use Notifiable;

    protected $table = 'package';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'package_code',
        'contract_code',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}