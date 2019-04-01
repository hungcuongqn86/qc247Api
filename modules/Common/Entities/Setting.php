<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Setting extends BaseEntity
{
    use Notifiable;

    protected $table = 'setting';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'key',
        'title',
        'value',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}