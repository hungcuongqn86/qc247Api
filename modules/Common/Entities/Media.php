<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Media extends BaseEntity
{
    use Notifiable;

    protected $table = 'media';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'item_id',
        'table',
        'dir',
        'name',
        'url',
        'file_type',
        'size',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}