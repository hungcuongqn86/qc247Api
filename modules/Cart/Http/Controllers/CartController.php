<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Shop\Services\ShopServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class CartController extends CommonController
{
    public function search(Request $request)
    {
        $user = $request->user();
        try {
            // Lay theo shop
            $shopids = CartServiceFactory::mCartService()->getDistinctShopCart($user->id);
            $shops = ShopServiceFactory::mShopService()->getByIds($shopids, $user->id);
            return $this->sendResponse($shops, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'amount' => 'required',
                'domain' => 'required',
                'image' => 'required',
                'method' => 'required',
                'name' => 'required',
                'pro_link' => 'required',
                'rate' => 'required',
                'site' => 'required'
            ];
            $arrMessages = [
                'amount.required' => 'amount.required',
                'domain.required' => 'domain.required',
                'image.required' => 'image.required',
                'method.required' => 'method.required',
                'name.required' => 'name.required',
                'pro_link.required' => 'pro_link.required',
                'rate.required' => 'rate.required',
                'site.required' => 'site.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $update = CartServiceFactory::mCartService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $inputData = self::json_decode_nice($input['cart']);
        $inputCart = (array)$inputData[0];
        $arrRules = [
            'amount' => 'required',
            'domain' => 'required',
            'image' => 'required',
            'method' => 'required',
            'name' => 'required',
            'pro_link' => 'required',
            'rate' => 'required',
            'site' => 'required'
        ];
        $arrMessages = [
            'amount.required' => 'amount.required',
            'domain.required' => 'domain.required',
            'image.required' => 'image.required',
            'method.required' => 'method.required',
            'name.required' => 'name.required',
            'pro_link.required' => 'pro_link.required',
            'rate.required' => 'rate.required',
            'site.required' => 'site.required'
        ];

        $validator = Validator::make($inputCart, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            // Shop
            $shop = ShopServiceFactory::mShopService()->findByUrl($inputCart['shop_link']);
            if (!$shop) {
                return $this->sendError('Error', 'Shop.' . $inputCart['shop_nick'] . '.NotExit');
            }
            $inputCart['shop_id'] = $shop['id'];
            $user = $request->user();
            $inputCart['user_id'] = $user->id;
            $inputCart['price_arr'] = json_encode($inputCart['price_arr']);
            $create = CartServiceFactory::mCartService()->create($inputCart);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function json_decode_nice($json, $assoc = FALSE)
    {
        $json = str_replace(array("\n", "\r"), "", $json);
        $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);
        $json = preg_replace('/(,)\s*}$/', '}', $json);
        return json_decode($json, $assoc);
    }
}
