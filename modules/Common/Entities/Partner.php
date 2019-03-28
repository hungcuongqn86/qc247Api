<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Partner extends BaseEntity
{
    use Notifiable;

    protected $table = 'partner';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'phone_number',
        'facebook',
        'email',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}