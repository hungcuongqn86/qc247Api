<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Notifications\Notifiable;

class Transaction extends BaseEntity
{
    use Notifiable;

    protected $table = 'transaction';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'type',
        'code',
        'value',
        'debt',
        'content',
        'is_deleted',
        'created_by',
        'created_at',
        'updated_at'
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}