<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\Complain;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IComplainService;
use Illuminate\Support\Facades\DB;

class ComplainService extends CommonService implements IComplainService
{
    protected function getDefaultModel()
    {
        return Complain::getTableName();
    }

    protected function getDefaultClass()
    {
        return Complain::class;
    }

    public function search($filter)
    {
        $query = Complain::with(array('Order' => function ($query) {
            $query->with(['User'])->orderBy('id');
        }))->where('is_deleted', '=', 0);
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function getByOrder($filter)
    {
        $query = Complain::with(['ComplainProducts'])->where('is_deleted', '=', 0);
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
        $rResult = Complain::with(array('ComplainProducts' => function ($query) {
            $query->with(array('Cart' => function ($query) {
                $query->where('is_deleted', '=', 0)->orderBy('id');
            }))->with(array('Media' => function ($query) {
                $query->where('is_deleted', '=', 0);
            }))->orderBy('id');
        }))->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('complain' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function types()
    {
        $history = new Complain();
        return $history->types();
    }

    public function create($arrInput)
    {
        $complain = new Complain($arrInput);
        DB::beginTransaction();
        try {
            $complain->save();
            DB::commit();
            return $complain;
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
            $complain = Complain::find($id);
            $complain->update($arrInput);
            DB::commit();
            return $complain;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
