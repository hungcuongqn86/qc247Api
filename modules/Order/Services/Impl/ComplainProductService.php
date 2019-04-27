<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\ComplainProducts;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IComplainProductService;
use Illuminate\Support\Facades\DB;

class ComplainProductService extends CommonService implements IComplainProductService
{
    protected function getDefaultModel()
    {
        return ComplainProducts::getTableName();
    }

    protected function getDefaultClass()
    {
        return ComplainProducts::class;
    }

    public function search($filter)
    {
        return [];
    }

    public function findById($id)
    {
        $rResult = ComplainProducts::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('complain_products' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $complain = new ComplainProducts($arrInput);
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
            $complain = ComplainProducts::find($id);
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
