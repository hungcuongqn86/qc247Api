<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use App\User;

class CommentUsers extends BaseEntity
{
    use Notifiable;

    protected $table = 'comment_users';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'comment_id',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['user_name'];

    public function getUserNameAttribute()
    {
        return $this->User()->first()->name;
    }

    public function Comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
