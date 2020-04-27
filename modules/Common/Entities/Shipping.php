<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class Shipping extends BaseEntity
{
    use Notifiable;
	
	const CHO_DUYET = 1;
    const KHONG_DUYET = 2;
    const DA_DUYET = 3;

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
	
	protected $appends = ['statusname'];

    public function getStatusnameAttribute()
    {
        $statusname = '';
        if (!empty($this->attributes['status'])) {
            $statusname = $this->list_of_status[$this->attributes['status']];
        }
        return $statusname;
    }

    public function Order()
    {
        return $this->hasMany(Order::class, 'id', 'order_id');
    }
	
	public $list_of_status = [
        self::CHO_DUYET => "Chờ xác nhận",
        self::KHONG_DUYET => "Từ chối",
        self::DA_DUYET => "Đã nhận"
    ];
}