<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class WarehouseController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function wait(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->waitMoveOut($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billCreate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'user_id' => 'required',
            'pkidlist' => 'required'
        ];
        $arrMessages = [
            'user_id.required' => 'Thiếu thông tin khách hàng!',
            'pkidlist.required' => 'Thiếu thông tin kiện hàng!'
        ];

        $user = $request->user();
        $input['employee'] = $user['id'];
        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Tạo phiếu xuất không thành công!', $validator->errors()->all());
        }




        $arrCartId = explode(',', $input['cart_ids']);
        $carts = CartServiceFactory::mCartService()->findByIds($arrCartId);
        foreach ($carts as $cart) {
            if (!empty($cart['order_id'])) {
                return $this->sendError('Kết đơn không thành công!', ['Xin vui lòng thực hiện lại!']);
            }
        }

        try {
            $input['status'] = 2;
            $create = OrderServiceFactory::mOrderService()->create($input);
            if (!empty($create)) {
                foreach ($arrCartId as $id) {
                    $cartInput = array(
                        'id' => $id,
                        'order_id' => $create['id'],
                        'status' => 2
                    );
                    CartServiceFactory::mCartService()->update($cartInput);
                }
                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $create['id'],
                    'type' => 1
                ];
                OrderServiceFactory::mHistoryService()->create($history);
                //Package
                $package = [
                    'order_id' => $create['id']
                ];
                OrderServiceFactory::mPackageService()->create($package);
            }
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
