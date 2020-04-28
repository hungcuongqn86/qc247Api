<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use App\User;

class History extends BaseEntity
{
    use Notifiable;

    protected $table = 'history';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'content',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['user_name'];

    public function getUserNameAttribute()
    {
        return $this->User()->first()->name;
    }

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function types()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Kết đơn'];
        $res[] = ['id' => 2, 'name' => 'Báo giá'];
        $res[] = ['id' => 3, 'name' => 'Đặt cọc'];
        $res[] = ['id' => 4, 'name' => 'Mua hàng'];
        $res[] = ['id' => 5, 'name' => 'Thanh lý'];
        $res[] = ['id' => 6, 'name' => 'Hủy'];
        $res[] = ['id' => 7, 'name' => 'Hoàn cọc'];
        $res[] = ['id' => 8, 'name' => 'Sửa đơn đặt hàng'];
        $res[] = ['id' => 9, 'name' => 'Xuất kho thanh lý'];
        $res[] = ['id' => 10, 'name' => 'Xác nhận đơn ký gửi'];
        return $res;
    }
}
