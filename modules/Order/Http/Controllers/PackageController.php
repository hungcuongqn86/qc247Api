<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class PackageController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function status()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'order_id.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $create = OrderServiceFactory::mPackageService()->create($input);
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
                'order_id' => 'required'
            ];
            $arrMessages = [
                'order_id.required' => 'order_id.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $package = OrderServiceFactory::mPackageService()->findById($input['id']);
            if (empty($package)) {
                return $this->sendError('Error', ['Kiện hàng không tồn tại!']);
            }

            $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
            if (empty($order)) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }
            $user = $request->user();

            $history = array();
            $orderInput = array();

            if (!empty($input['contract_code'])) {
                if ($package['package']['status'] < 2) {
                    $input['status'] = 2;
                }
                if ($order['order']['status'] < 4) {
                    $orderInput['id'] = $order['order']['id'];
                    $orderInput['status'] = 4;
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['order']['id'],
                        'type' => 4,
                        'content' => 'Mã hợp đồng ' . $input['contract_code']
                    ];
                }
            }

            if (!empty($input['package_code'])) {
                if ($package['package']['status'] < 3) {
                    $input['status'] = 3;
                }
                if ($order['order']['status'] < 4) {
                    $orderInput['id'] = $order['order']['id'];
                    $orderInput['status'] = 4;
                }
            }

            // Tien can nang
            if (!empty($input['weight_qd'])) {
                $weight_qd = $input['weight_qd'];
                $gia_can_nang = 0;
                if (!empty($order['order']['user']['weight_price'])) {
                    $gia_can_nang = $order['order']['user']['weight_price'];
                } else {
                    if ($weight_qd < 10) {
                        $gia_can_nang = 27000;
                    }
                    if (($weight_qd >= 10) && ($weight_qd <= 30)) {
                        $gia_can_nang = 23000;
                    }
                    if ($weight_qd > 30) {
                        $gia_can_nang = 19000;
                    }
                }
                $input['gia_can'] = $gia_can_nang;
                $input['tien_can'] = $gia_can_nang * $weight_qd;
            }

            // Tien thanh ly
            $tongTien = $order['order']['tong'];
            $arrPk = $order['order']['package'];
            $tigia = $order['order']['rate'];
            foreach ($arrPk as $pk) {
                if ($pk['ship_khach'] && $pk['ship_khach'] > 0) {
                    $ndt = $pk['ship_khach'];
                    $vnd = $ndt * $tigia;
                    $tongTien = $tongTien + $vnd;
                }
            }
            $thanh_toan = empty($order['thanh_toan']) ? 0 : $order['thanh_toan'];
            $tienthanhly = $tongTien - $thanh_toan;
            $input['tien_thanh_ly'] = $tienthanhly;

            $update = OrderServiceFactory::mPackageService()->update($input);
            if (!empty($update)) {
                if (!empty($orderInput['id'])) {
                    OrderServiceFactory::mOrderService()->update($orderInput);
                }

                if (!empty($history['user_id'])) {
                    OrderServiceFactory::mHistoryService()->create($history);
                }
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
