<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\History;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IHistoryService;
use Illuminate\Support\Facades\DB;

class HistoryService extends CommonService implements IHistoryService
{
    protected function getDefaultModel()
    {
        return History::getTableName();
    }

    protected function getDefaultClass()
    {
        return History::class;
    }

    public function search($filter)
    {
        return [];
    }

    public function findById($id)
    {
        $rResult = History::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('history' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function types()
    {
        $history = new History();
        return $history->types();
    }

    public function create($arrInput)
    {
        $history = new History($arrInput);
        DB::beginTransaction();
        try {
            $history->save();
            DB::commit();
            return $history;
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
            $history = History::find($id);
            $history->update($arrInput);
            DB::commit();
            return $history;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
