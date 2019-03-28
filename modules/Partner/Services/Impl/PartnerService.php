<?php

namespace Modules\Partner\Services\Impl;

use Modules\Common\Entities\Partner;
use Modules\Common\Services\Impl\CommonService;
use Modules\Partner\Services\Intf\IPartnerService;
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
        $query = Partner::where('is_deleted', '=', 0);
        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->Where('name', 'like', '%' . $sKeySearch . '%');
            $query->orWhere('phone_number', 'like', '%' . $sKeySearch . '%');
            $query->orWhere('facebook', 'like', '%' . $sKeySearch . '%');
            $query->orWhere('email', 'like', '%' . $sKeySearch . '%');
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Partner::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('partner' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $owner = new Partner($arrInput);
        DB::beginTransaction();
        try {
            $owner->save();
            DB::commit();
            return $owner;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findByIds($ids)
    {
        $rResult = Partner::wherein('id', $ids)->get()->toArray();
        return $rResult;
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $owner = Partner::find($id);
            $owner->update($arrInput);
            DB::commit();
            return $owner;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            Partner::wherein('id', $ids)->update(['is_deleted' => 1]);
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
