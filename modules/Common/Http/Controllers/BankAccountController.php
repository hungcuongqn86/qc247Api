<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Common\Services\CommonServiceFactory;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }
            return $this->sendResponse(CommonServiceFactory::mBankAccountService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mBankAccountService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
