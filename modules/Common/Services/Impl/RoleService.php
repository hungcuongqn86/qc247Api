<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Role;
use Modules\Common\Services\Intf\IRoleService;
use Illuminate\Support\Facades\DB;

class RoleService extends CommonService implements IRoleService
{
    protected function getDefaultModel()
    {
        return Role::getTableName();
    }

    protected function getDefaultClass()
    {
        return Role::class;
    }

    public function search($filter)
    {
        $rResult = Role::orderBy('id', 'asc')->get(['id', 'name'])->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Role::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('role' => $rResult->toArray());
        } else {
            return null;
        }
    }
}
