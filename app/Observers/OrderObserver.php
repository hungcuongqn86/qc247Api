<?php

namespace App\Observers;

use Modules\Common\Entities\Order;
use Modules\Common\Entities\Package;
use Kreait\Firebase\Database;

class OrderObserver
{
	private $database;
	public function __construct(Database $database)
    {
        $this->database = $database;
    }
	
    public function saved(Order $order)
    {
		if($order->isDirty('status') || $order->isDirty('is_deleted')){
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
			$this->database->getReference($refer)->set($data);
		}
    }
    /**
     * Handle the order "created" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        //
    }

    /**
     * Handle the order "updated" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        //
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }

    /**
     * Handle the order "restored" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }
}
