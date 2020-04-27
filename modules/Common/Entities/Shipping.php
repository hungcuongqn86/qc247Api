<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Shipping extends BaseEntity
{
    use Notifiable;

    protected $table = 'shipping';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'package_count',
        'content',
        'user_id',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function Order()
    {
        return $this->hasMany(Order::class, 'id', 'order_id');
    }
}