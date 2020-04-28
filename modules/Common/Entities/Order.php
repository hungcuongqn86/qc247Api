<?php

namespace Modules\Common\Entities;

use App\User;
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
        'rate',
        'count_product',
        'count_link',
        'tien_hang',
        'phi_tam_tinh',
        'tong',
        'baogia_content',
        'datcoc_content',
        'thanh_toan',
        'con_thieu',
        'is_deleted',
        'created_at',
        'updated_at',
		'shipping'
    ];

    public function Shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Cart()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }

    public function Package()
    {
        return $this->hasMany(Package::class, 'order_id', 'id');
    }

    public function History()
    {
        return $this->hasMany(History::class, 'order_id', 'id');
    }

    public function status()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Chờ báo giá'];
        $res[] = ['id' => 2, 'name' => 'Chờ đặt cọc'];
        $res[] = ['id' => 3, 'name' => 'Đang mua hàng'];
        $res[] = ['id' => 4, 'name' => 'Đã mua hàng'];
        $res[] = ['id' => 5, 'name' => 'Thanh lý'];
        $res[] = ['id' => 6, 'name' => 'Hủy'];
        return $res;
    }
}