<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Common\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Validator;
use Modules\Shop\Services\ShopServiceFactory;

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
