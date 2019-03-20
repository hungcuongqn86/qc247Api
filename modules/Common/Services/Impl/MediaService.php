<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\PetMedia;
use Modules\Common\Services\Intf\IMediaService;
use Illuminate\Support\Facades\DB;

class MediaService extends CommonService implements IMediaService
{
    protected function getDefaultModel()
    {
        return PetMedia::getTableName();
    }

    protected function getDefaultClass()
    {
        return PetMedia::class;
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

    public function create($arrInput)
    {
        $petMedia = new PetMedia($arrInput);
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
            $petMedia = PetMedia::find($id);
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
