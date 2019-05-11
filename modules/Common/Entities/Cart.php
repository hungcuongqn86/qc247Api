<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Cart extends BaseEntity
{
    use Notifiable;

    protected $table = 'cart';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'shop_id',
        'order_id',
        'amount',
        'begin_amount',
        'color',
        'colortxt',
        'count',
        'domain',
        'image',
        'method',
        'name',
        'note',
        'nv_note',
        'kho_note',
        'price',
        'price_arr',
        'pro_link',
        'pro_properties',
        'rate',
        'site',
        'size',
        'sizetxt',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function Shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}