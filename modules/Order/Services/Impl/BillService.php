<?php

namespace Modules\Order\Services\Impl;

use App\User;
use Modules\Common\Entities\Bill;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IBillService;
use Illuminate\Support\Facades\DB;

class BillService extends CommonService implements IBillService
{
    protected function getDefaultModel()
    {
        return Bill::getTableName();
    }

    protected function getDefaultClass()
    {
        return Bill::class;
    }

    public function search($filter)
    {
        $query = Bill::with(['User', 'Employee', 'Package'])->where('is_deleted', '=', 0);

        $Code = isset($filter['code']) ? $filter['code'] : '';
        if (!empty($Code)) {
            $query->where('id', '=', $Code);
        }

        $istatus = isset($filter['status']) ? $filter['status'] : 0;
        if ($istatus > 0) {
            $query->where('status', '=', $istatus);
        }

        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->whereHas('User', function ($q) use ($sKeySearch) {
                $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
            });
        }

        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Bill::with(['User', 'Employee'])->with(array('Package' => function ($query) {
            $query->where('is_deleted', '=', 0)->orderBy('id');
            $query->with(array('Order' => function ($query) {
                $query->with(array('Cart' => function ($query) {
                    $query->where('is_deleted', '=', 0)->orderBy('id');
                }));
                $query->where('is_deleted', '=', 0)->orderBy('id');
            }));
        }))->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('bill' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function status()
    {
        $bill = new Bill();
        return $bill->status();
    }

    public function create($arrInput)
    {
        $bill = new Bill($arrInput);
        DB::beginTransaction();
        try {
            $bill->save();
            DB::commit();
            return $bill;
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
            $bill = Bill::find($id);
            $bill->update($arrInput);
            DB::commit();
            return $bill;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
