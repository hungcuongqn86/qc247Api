<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Package extends BaseEntity
{
    use Notifiable;

    protected $table = 'package';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'package_code',
        'contract_code',
        'ship_khach',
        'ship_tt',
        'tra_shop',
        'thanh_toan',
        'status',
        'note_tl',
        'weight',
        'weight_qd',
        'gia_can',
        'tien_can',
        'tien_thanh_ly',
        'bill_id',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function status()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Chờ mua hàng'];
        $res[] = ['id' => 2, 'name' => 'Đã mua hàng'];
        $res[] = ['id' => 3, 'name' => 'Shop đang giao hàng'];
        $res[] = ['id' => 4, 'name' => 'Kho Trung Quốc nhận hàng'];
        $res[] = ['id' => 5, 'name' => 'Đang trên đường về VN'];
        $res[] = ['id' => 6, 'name' => 'Trong kho VN'];
        $res[] = ['id' => 7, 'name' => 'Thanh lý'];
        $res[] = ['id' => 8, 'name' => 'Hủy'];
        return $res;
    }
}
