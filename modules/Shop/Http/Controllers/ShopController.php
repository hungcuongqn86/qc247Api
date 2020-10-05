<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Common\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Validator;
use Modules\Shop\Services\ShopServiceFactory;
use PeterPetrus\Auth\PassportToken;

class ShopController extends CommonController
{
    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(ShopServiceFactory::mShopService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(ShopServiceFactory::mShopService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            if (empty($input['tk'])) {
                return $this->sendError('Error', ['Auth'], 401);
            }
            $decoded_token = PassportToken::dirtyDecode(
                $input['tk']
            );
            if ($decoded_token['valid']) {
                // Check if token exists in DB (table 'oauth_access_tokens'), require \Illuminate\Support\Facades\DB class
                $token_exists = PassportToken::existsValidToken(
                    $decoded_token['token_id'],
                    $decoded_token['user_id']
                );

                if (!$token_exists) {
                    return $this->sendError('Error', ['Auth'], 401);
                }
            } else {
                return $this->sendError('Error', ['Auth'], 401);
            }

            $arrRules = [
                'name' => 'required',
                'url' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'name.required',
                'url.required' => 'url.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $shop = ShopServiceFactory::mShopService()->findByUrl($input['url']);
            if ($shop) {
                return $this->sendResponse($shop, 'Successfully.');
            } else {
                $create = ShopServiceFactory::mShopService()->create($input);
                return $this->sendResponse($create, 'Successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
