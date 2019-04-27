<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Complain extends BaseEntity
{
    use Notifiable;

    protected $table = 'complain';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'type',
        'money_request',
        'content',
        'user_id',
        'status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['otype'];

    public function getOtypeAttribute()
    {
        $oType = [];
        if (!empty($this->attributes['type'])) {
            $types = self::types();
            foreach ($types as $type) {
                if ($type['id'] === $this->attributes['type']) {
                    $oType = $type;
                }
            }
        }
        return $oType;
    }

    public function types()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Khiếu nại chiết khấu cân nặng'];
        $res[] = ['id' => 2, 'name' => 'Khiếu nại hàng bị vỡ, ướt, bẩn'];
        $res[] = ['id' => 3, 'name' => 'Khiếu nại chất lượng dịch vụ'];
        $res[] = ['id' => 4, 'name' => 'Khiếu nại hàng thiếu, nhầm size'];
        $res[] = ['id' => 5, 'name' => 'Khiếu nại hàng về chậm'];
        return $res;
    }

    public function ComplainProducts()
    {
        return $this->hasMany(ComplainProducts::class, 'complain_id', 'id');
    }

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}