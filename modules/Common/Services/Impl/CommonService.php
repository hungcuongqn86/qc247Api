<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Services\Intf\ICommonService;
use Illuminate\Database\Eloquent\Model;

abstract class CommonService implements ICommonService
{
    private $currentConnection = '';

    public function setCurrentConnection($currentConnection)
    {
        $this->currentConnection = $currentConnection;
    }

    protected abstract function getDefaultModel();

    protected abstract function getDefaultClass();

    public function getAll()
    {
        return call_user_func(array($this->getDefaultClass(), 'all'));
    }
}
