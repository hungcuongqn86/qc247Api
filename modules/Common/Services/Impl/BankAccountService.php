<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\BankAccount;
use Modules\Common\Services\Intf\IBankAccountService;
use Illuminate\Support\Facades\DB;

class BankAccountService extends CommonService implements IBankAccountService
{
    protected function getDefaultModel()
    {
        return BankAccount::getTableName();
    }

    protected function getDefaultClass()
    {
        return BankAccount::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $query = BankAccount::where('is_deleted', '=', 0);
        $rResult = $query->get(['id', 'name'])->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = BankAccount::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('bank_account' => $rResult->toArray());
        } else {
            return null;
        }
    }
}
