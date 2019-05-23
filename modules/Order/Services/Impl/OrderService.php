<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\Order;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IOrderService;
use Illuminate\Support\Facades\DB;

class OrderService extends CommonService implements IOrderService
{
    protected function getDefaultModel()
    {
        return Order::getTableName();
    }

    protected function getDefaultClass()
    {
        return Order::class;
    }

    public function search($filter)
    {
        $query = Order::with(['User', 'Cart', 'Shop'])->with(array('Package' => function ($query) {
            $query->where('is_deleted', '=', 0)->orderBy('id');
        }))->where('is_deleted', '=', 0);
        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->whereHas('User', function ($q) use ($sKeySearch) {
                $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
            });
        }
        $package_code = isset($filter['package_code']) ? trim($filter['package_code']) : '';
        if (!empty($package_code)) {
            $query->whereHas('Package', function ($q) use ($package_code) {
                $q->where('package_code', '=', $package_code);
            });
        }
        $code = isset($filter['code']) ? trim($filter['code']) : '';
        if (!empty($code)) {
            $query->where('id', '=', $code);
        }
        $iuser = isset($filter['user_id']) ? $filter['user_id'] : 0;
        if ($iuser > 0) {
            $query->where('user_id', '=', $iuser);
        }
        $istatus = isset($filter['status']) ? $filter['status'] : 0;
        if ($istatus > 0) {
            $query->where('status', '=', $istatus);
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function countByStatus()
    {
        $rResult = Order::where('is_deleted', '=', 0)->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function findById($id)
    {
        $rResult = Order::with(['User', 'Cart', 'Shop', 'History'])->with(array('Package' => function ($query) {
            $query->where('is_deleted', '=', 0)->orderBy('id');
        }))->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('order' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function status()
    {
        $order = new Order();
        return $order->status();
    }

    public function create($arrInput)
    {
        $order = new Order($arrInput);
        DB::beginTransaction();
        try {
            $order->save();
            DB::commit();
            return $order;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $order = Order::find($id);
            $order->update($arrInput);
            DB::commit();
            return $order;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
