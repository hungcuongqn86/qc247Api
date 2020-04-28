<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Common\Services\CommonServiceFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends CommonController
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
            return $this->sendResponse(CommonServiceFactory::mUserService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }


    public function custumers(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }
            return $this->sendResponse(CommonServiceFactory::mUserService()->custumer($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mUserService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'c_password' => 'required|same:password',
                'partner_id' => 'required',
                'phone_number' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'name.required',
                'email.required' => 'email.required',
                'email.email' => 'email.email',
                'email.unique' => 'email.unique',
                'password.required' => 'password.required',
                'c_password.required' => 'c_password.required',
                'c_password.same' => 'c_password.same',
                'partner_id.required' => 'partner_id.required',
                'phone_number.required' => 'phone_number.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }

            $input['password'] = bcrypt($input['password']);
            $input['rate'] = 0;
            $input['active'] = 1;
            $create = CommonServiceFactory::mUserService()->create($input);
            if ($create) {
                $role = CommonServiceFactory::mRoleService()->findById($input['role_id']);
                if ($role) {
                    $create->assignRole($role['role']['name']);
                }
            }
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
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $input['id'],
                'c_password' => 'same:password',
                'partner_id' => 'required',
                'phone_number' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'name.required',
                'email.required' => 'email.required',
                'email.email' => 'email.email',
                'email.unique' => 'email.unique',
                'c_password.same' => 'c_password.same',
                'partner_id.required' => 'partner_id.required',
                'phone_number.required' => 'phone_number.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            if (!empty($input['password'])) {
                $input['password'] = bcrypt($input['password']);
            }

            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }

            $update = CommonServiceFactory::mUserService()->update($input);
            if ($update) {
				if(isset($input['role_id'])){
					$role = CommonServiceFactory::mRoleService()->findById($input['role_id']);
					if ($role) {
						$roles = $update->getRoleNames();
						foreach ($roles as $item) {
							$update->removeRole($item);
						}
						$update->assignRole($role['role']['name']);
					}
				}
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $owners = CommonServiceFactory::mUserService()->findByIds($input);
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
                $errData[] = 'User Id ' . $id . ' NotExist';
            }
        }

        if (!empty($errData)) {
            return $this->sendError('Error', $errData);
        }

        try {
            CommonServiceFactory::mUserService()->delete($input);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
