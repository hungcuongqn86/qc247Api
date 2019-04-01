<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class OrderController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'user_id' => 'required',
            'shop_id' => 'required',
            'cart_ids' => 'required'
        ];
        $arrMessages = [
            'user_id.required' => 'user_id.required',
            'shop_id.required' => 'shop_id.required',
            'cart_ids.required' => 'cart_ids.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $create = OrderServiceFactory::mOrderService()->create($input);
            if (!empty($create)) {
                $arrCartId = explode(',', $input['cart_ids']);
                foreach ($arrCartId as $id) {
                    $cartInput = array(
                        'id' => $id,
                        'order_id' => $create['id'],
                        'status' => 2
                    );
                    CartServiceFactory::mCartService()->update($cartInput);
                }
            }
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
