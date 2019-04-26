<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class ComplainProducts extends BaseEntity
{
    use Notifiable;

    protected $table = 'complain_products';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'complain_id',
        'cart_id',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}