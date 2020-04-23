<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Modules\Common\Entities\Partner;
use Modules\Common\Entities\Transaction;
use Modules\Common\Entities\Order;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'partner_id', 'phone_number', 'type', 'cost_percent', 'deposit', 'rate', 'weight_price', 'active', 'activation_token', 'is_deleted'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'activation_token'
    ];

    protected $appends = ['debt'];

    public function getDebtAttribute()
    {
        $query = $this->Transaction()->where('is_deleted', '=', 0);
        $res = $query->orderBy('id', 'desc')->first();
        if (!empty($res)) {
            return $res->debt;
        } else {
            return 0;
        }
    }

    public function Partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    public function Transaction()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }

    public function Order()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }
}
