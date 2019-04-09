<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Setting;
use Modules\Common\Services\Intf\ISettingService;
use Illuminate\Support\Facades\DB;

class SettingService extends CommonService implements ISettingService
{
    protected function getDefaultModel()
    {
        return Setting::getTableName();
    }

    protected function getDefaultClass()
    {
        return Setting::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = Setting::where('is_deleted', '=', 0);

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'created_at';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'desc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Setting::where('id', '=', $id)->first();
        return array('setting' => $rResult);
    }

    public function findByKey($key)
    {
        $rResult = Setting::where('key', '=', $key)->first();
        return array('setting' => $rResult);
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $version = Setting::find($id);
            $version->update($arrInput);
            DB::commit();
            return $version;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
