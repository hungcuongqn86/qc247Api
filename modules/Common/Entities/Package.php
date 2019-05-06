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
        'status',
        'note_tl',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function status()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => ''];
        $res[] = ['id' => 2, 'name' => 'Đã mua hàng'];
        $res[] = ['id' => 3, 'name' => 'Shop đang giao hàng'];
        $res[] = ['id' => 4, 'name' => 'Kho Trung Quốc nhận hàng'];
        $res[] = ['id' => 5, 'name' => 'Đang trên đường về VN'];
        $res[] = ['id' => 6, 'name' => 'Trong kho VN'];
        $res[] = ['id' => 7, 'name' => 'Thanh lý'];
        return $res;
    }
}