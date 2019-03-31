<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Common\Services\CommonServiceFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TransactionController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mTransactionService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function types()
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mTransactionService()->types(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'user_id' => 'required',
                'type' => 'required',
                'code' => 'required',
                'value' => 'required'
            ];
            $arrMessages = [
                'user_id.required' => 'user_id.required',
                'type.required' => 'type.required',
                'code.required' => 'code.required',
                'value.required' => 'value.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            $input['created_by'] = $user['id'];
            // Du no
            $duNo = CommonServiceFactory::mTransactionService()->debt($input['user_id']);
            $types = CommonServiceFactory::mTransactionService()->types();
            foreach ($types as $type) {
                if ($type->id == $input['type']) {
                    $duNo = $duNo + ($input['value'] * $type->value);
                }
            }
            $input['debt'] = $duNo;
            $create = CommonServiceFactory::mTransactionService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'user_id' => 'required',
                'type' => 'required',
                'code' => 'required',
                'value' => 'required'
            ];
            $arrMessages = [
                'user_id.required' => 'name.required',
                'type.required' => 'email.required',
                'code.required' => 'email.required',
                'value.required' => 'email.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $update = CommonServiceFactory::mTransactionService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $owners = CommonServiceFactory::mTransactionService()->findByIds($input);
        $deleteData = array();
        $errData = array();
        foreach ($input as $id) {
            $check = false;
            foreach ($owners as $owner) {
                if ($id == $owner['id']) {
                    $check = true;
                    $owner['is_deleted'] = 1;
                    $deleteData[] = $owner;
                }
            }
            if (!$check) {
                $errData[] = 'Transaction Id ' . $id . ' NotExist';
            }
        }

        if (!empty($errData)) {
            return $this->sendError('Error', $errData);
        }

        try {
            CommonServiceFactory::mTransactionService()->delete($input);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
