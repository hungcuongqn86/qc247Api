<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Version;
use Modules\Common\Services\Intf\IVersionService;
use Illuminate\Support\Facades\DB;

class VersionService extends CommonService implements IVersionService
{
    protected function getDefaultModel()
    {
        return Version::getTableName();
    }

    protected function getDefaultClass()
    {
        return Version::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = Version::where('is_deleted', '=', 0);

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'created_at';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'desc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function check($app = 'vs', $partner = '0')
    {
        $rResult = Version::where('is_deleted', '=', 0)->where('partner', '=', $partner)->where('app_code', '=', $app)->orderBy('created_at', 'desc')->first(array('version_code', 'link_apk'));
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Version::where('id', '=', $id)->first();
        return array('sim' => $rResult);
    }

    public function create($arrInput)
    {
        $version = new Version($arrInput);
        DB::beginTransaction();
        try {
            $version->save();
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

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $version = Version::find($id);
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
