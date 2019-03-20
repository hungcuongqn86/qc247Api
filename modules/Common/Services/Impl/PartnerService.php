<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Partner;
use Modules\Common\Services\Intf\IPartnerService;
use Illuminate\Support\Facades\DB;

class PartnerService extends CommonService implements IPartnerService
{
    protected function getDefaultModel()
    {
        return Partner::getTableName();
    }

    protected function getDefaultClass()
    {
        return Partner::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = Partner::where('is_deleted', '=', 0);
        $sKey = isset($filter['key']) ? trim($filter['key']) : '';

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'created_at';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'desc';

        if ($sKey != '') {
            $query->where(function ($query) use ($sKey) {
                $query->where('email', 'LIKE', '%' . $sKey . '%');
                $query->orWhere('options', 'LIKE', '%' . $sKey . '%');
            });
        }

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function getAll()
    {
        $query = Partner::where('is_deleted', '=', 0);
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Partner::where('id', '=', $id)->first();
        return array('partner' => $rResult);
    }

    public function findByCode($code)
    {
        $rResult = Partner::where('code', '=', $code)->first();
        return array('partner' => $rResult);
    }

    public function create($arrInput)
    {
        $partner = new Partner($arrInput);
        DB::beginTransaction();
        try {
            $partner->save();
            DB::commit();
            return $partner;
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
            $partner = Partner::find($id);
            $partner->update($arrInput);
            DB::commit();
            return $partner;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
