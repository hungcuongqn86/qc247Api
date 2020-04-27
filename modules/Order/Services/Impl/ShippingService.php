<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\Shipping;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IShippingService;
use Illuminate\Support\Facades\DB;

class ShippingService extends CommonService implements IShippingService
{
    protected function getDefaultModel()
    {
        return Shipping::getTableName();
    }

    protected function getDefaultClass()
    {
        return Shipping::class;
    }

    public function search($filter)
    {
        $query = Shipping::with(array('Order' => function ($query) {
            $query->with(['User'])->orderBy('id');
        }))->where('is_deleted', '=', 0);
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function getByOrder($filter)
    {
        $query = Shipping::with(['ComplainProducts'])->where('is_deleted', '=', 0);
        $iorder = isset($filter['order_id']) ? $filter['order_id'] : 0;
        if ($iorder > 0) {
            $query->where('order_id', '=', $iorder);
        }
        $query->orderBy('id', 'desc');
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Shipping::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('shipping' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $shipping = new Shipping($arrInput);
        DB::beginTransaction();
        try {
            $shipping->save();
            DB::commit();
            return $shipping;
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
            $shipping = Shipping::find($id);
            $shipping->update($arrInput);
            DB::commit();
            return $shipping;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
