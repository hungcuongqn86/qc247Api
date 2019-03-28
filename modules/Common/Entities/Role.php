<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Role extends BaseEntity
{
    use Notifiable;

    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];
}