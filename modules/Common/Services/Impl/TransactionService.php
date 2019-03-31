<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Transaction;
use Modules\Common\Services\Intf\ITransactionService;
use Illuminate\Support\Facades\DB;

class TransactionService extends CommonService implements ITransactionService
{
    protected function getDefaultModel()
    {
        return Transaction::getTableName();
    }

    protected function getDefaultClass()
    {
        return Transaction::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $query = Transaction::where('is_deleted', '=', 0);

        $iUser = isset($filter['user_id']) ? $filter['user_id'] : 0;
        $query->Where('user_id', '=', $iUser);

        $iType = isset($filter['type']) ? $filter['type'] : '';
        if (!empty($iType)) {
            $query->Where('type', '=', $iType);
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function types()
    {
        $otran = new Transaction();
        return $otran->_type();
    }

    public function create($arrInput)
    {
        $transaction = new Transaction($arrInput);
        DB::beginTransaction();
        try {
            $transaction->save();
            DB::commit();
            return $transaction;
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
        $rResult = Transaction::wherein('id', $ids)->get()->toArray();
        return $rResult;
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $transaction = Transaction::find($id);
            $transaction->update($arrInput);
            DB::commit();
            return $transaction;
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
            Transaction::wherein('id', $ids)->update(['is_deleted' => 1]);
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
