<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Shop extends BaseEntity
{
    use Notifiable;

    protected $table = 'shop';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'url',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['rate'];

    public function getRateAttribute()
    {
        $user = auth()->guard('api')->user();
        if (!empty($user) && $user->rate) {
            return (int)$user->rate;
        }
        $setting = new Setting();
        $rowRate = $setting->where('key', '=', 'rate')->first();
        return (int)$rowRate->value;
    }

    public function Cart()
    {
        return $this->hasMany(Cart::class, 'shop_id', 'id');
    }
}