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
        return [];
    }

    public function findById($id)
    {
        $rResult = Complain::where('id', '=', $id)->first();
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
