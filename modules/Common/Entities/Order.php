<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Order extends BaseEntity
{
    use Notifiable;

    protected $table = 'orders';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'shop_id',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function Shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function Cart()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }
}