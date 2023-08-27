<?php

namespace App\Observers;

use Kreait\Firebase\Database;
use Modules\Common\Entities\Order;
use Modules\Common\Entities\Package;

class PackageObserver
{
    private $database;
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function saved(Package $package)
    {
        /*$orderId = $package->order_id;
        $order = Order::where('id', '=', $orderId)->first();

        $userid = $order->user_id;
        $rResult1 = Order::where('is_deleted', '=', 0)->where('user_id', '=', $userid)->groupBy('status')->selectRaw('status, count(*) as total, "od" as type')->get();

        $rResult2 = Package::where('is_deleted', '=', 0)->whereHas('Order', function ($q) use ($userid) {
            $q->where('user_id', '=', $userid);
            $q->where('is_deleted', '=', 0);
        })->groupBy('status')->selectRaw('status, count(*) as total, "pk" as type')->get();

        $refer = config('app.name').'/mycount/'.$userid;

        $data1 = [];
        $data2 = [];
        if (!empty($rResult1)) {
            $data1 = $rResult1->toArray();
        }

        if (!empty($rResult2)) {
            $data2 = $rResult2->toArray();
        }

        $data = array_merge($data1, $data2);
        $this->database->getReference($refer)->set($data);*/
    }
}
