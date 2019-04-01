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

    public function search($userId)
    {
        return [];
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
