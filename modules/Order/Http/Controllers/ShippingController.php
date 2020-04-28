<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Illuminate\Support\Facades\DB;

class ShippingController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mShippingService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
	
    public function myshipping(Request $request)
    {
        $input = $request->all();
        try {
			$user = $request->user();
            $input['user_id'] = $user->id;
            return $this->sendResponse(OrderServiceFactory::mShippingService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
	
	public function status()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mShippingService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
	
	public function countByStatus(Request $request)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mShippingService()->countByStatus(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function getByOrder(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mShippingService()->getByOrder($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mShippingService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'package_count' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'package_count.required' => 'package_count.required',
            'content.required' => 'content.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $user = $request->user();
            $input['user_id'] = $user['id'];
            $input['status'] = 1;

            $create = OrderServiceFactory::mShippingService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'package_count' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'package_count.required' => 'package_count.required',
            'content.required' => 'content.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $update = OrderServiceFactory::mShippingService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
	
    public function approve(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required',
            'status' => 'required',
        ];
        $arrMessages = [
            'id.required' => 'id.required',
            'status.required' => 'status.required',
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

		$user = $request->user();
		if ($user['type'] == 1) {
			return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
		}
		
		$shipping = OrderServiceFactory::mShippingService()->findById($input['id']);
		if(empty($shipping)){
			return $this->sendError('Error', ['Không tồn tại yêu cầu ký gửi!']);
		}

        try {
			DB::beginTransaction();
			$input['approve_id'] = $user['id'];
			$input['approve_at'] = date('Y-m-d H:i:s');
            $update = OrderServiceFactory::mShippingService()->update($input);
			if((!empty($update)) && ($input['status'] == '2')){
				// Tao don hang
				$orderInput = Array(
					'user_id' => $update->user_id,
					'shop_id' => 1,
					'status' => 4,
					'rate' => 1,
					'count_product' => 0,
					'count_link' => 0,
					'tien_hang' => 0,
					'phi_tam_tinh' => 0,
					'tong' => 0,
					'thanh_toan' => 0,
					'con_thieu' => 0,
					'shipping' => 1,
				);
				$order = OrderServiceFactory::mOrderService()->create($orderInput);
				if (!empty($order)) {
					// History
					$history = [
						'user_id' => $user['id'],
						'order_id' => $order['id'],
						'type' => 10
					];
					OrderServiceFactory::mHistoryService()->create($history);
					//Package
					$package = [
						'order_id' => $order['id']
					];
					OrderServiceFactory::mPackageService()->create($package);
				}
				
				$update->order_id = $order['id'];
				$update->save();
			}
			DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
			DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
