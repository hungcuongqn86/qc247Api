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
        $query = Shipping::with(['User', 'Order'])->where('is_deleted', '=', 0);
		$iUser = isset($filter['user_id']) ? $filter['user_id'] : '';
		if (!empty($iUser)) {
			$query->where('user_id', '=', $iUser);
		}
		
		$iCode = isset($filter['code']) ? $filter['code'] : '';
		if (!empty($iCode)) {
			$query->where('id', '=', $iCode);
		}
		
		$iStatus = isset($filter['status']) ? $filter['status'] : '';
		if (!empty($iStatus)) {
			$query->where('status', '=', $iStatus);
		}
		
		$sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
			$query->where(function ($q) use ($sKeySearch){
				$q->whereHas('User', function ($q) use ($sKeySearch) {
					$q->where('name', 'LIKE', '%' . $sKeySearch . '%');
					$q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
					$q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
				});
				$q->orWhere('content', 'LIKE', '%' . $sKeySearch . '%');
			});
        }
		
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }
	
	public function status()
    {
        $shipping = new Shipping();
        return $shipping->status();
    }
	
	public function countByStatus()
    {
        $rResult = Shipping::where('is_deleted', '=', 0)->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
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
