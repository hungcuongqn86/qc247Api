<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Media;
use Modules\Common\Services\Intf\IMediaService;
use Illuminate\Support\Facades\DB;

class MediaService extends CommonService implements IMediaService
{
    protected function getDefaultModel()
    {
        return Media::getTableName();
    }

    protected function getDefaultClass()
    {
        return Media::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {

    }

    public function getAll()
    {

    }

    public function findById($id)
    {

    }

    public function findByIds($ids)
    {
        $rResult = Media::wherein('id', $ids)->get()->toArray();
        return $rResult;
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            Media::wherein('id', $ids)->update(['is_deleted' => 1]);
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

    public function create($arrInput)
    {
        $petMedia = new Media($arrInput);
        DB::beginTransaction();
        try {
            $petMedia->save();
            DB::commit();
            return $petMedia;
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
            $petMedia = Media::find($id);
            $petMedia->update($arrInput);
            DB::commit();
            return $petMedia;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
