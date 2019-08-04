<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use App\User;

class Comment extends BaseEntity
{
    use Notifiable;

    protected $table = 'comment';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'order_id',
        'content',
        'is_admin',
        'is_read',
        'old',
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

    public function CommentUsers()
    {
        return $this->hasMany(CommentUsers::class, 'comment_id', 'id');
    }
}
