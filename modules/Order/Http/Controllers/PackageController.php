<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
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
            $user = $request->user();
            if ($user->type === 1) {
                $input['user_id'] = $user->id;
            }

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
                'order_id' => 'required',
                'package_code' => 'nullable|unique:package,package_code,' . $input['id']
            ];
            $arrMessages = [
                'order_id.required' => 'order_id.required',
                'package_code.unique' => 'Mã vận đơn ' . $input['package_code'] . ' đã tồn tại!'
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

            $arrPk = $order['order']['package'];
            $tienthanhly = 0;
            if ($arrPk[0]['id'] == $input['id']) {
                $tongTien = $order['order']['tong'];
                $tigia = $order['order']['rate'];
                foreach ($arrPk as $pk) {
                    if ($pk['ship_khach'] && $pk['ship_khach'] > 0) {
                        $ndt = $pk['ship_khach'];
                        $vnd = $ndt * $tigia;
                        $tongTien = $tongTien + $vnd;
                    }
                }
                $thanh_toan = empty($order['order']['thanh_toan']) ? 0 : $order['order']['thanh_toan'];
                $tienthanhly = $tongTien - $thanh_toan;
            }
            $input['tien_thanh_ly'] = $tienthanhly;

            $update = OrderServiceFactory::mPackageService()->update($input);
            if (!empty($update)) {
                if ($input['status'] == 8) {
                    // Check huy
                    $check = OrderServiceFactory::mOrderService()->checkCancel($order['order']['id']);
                    if ($check) {
                        $orderInput['id'] = $order['order']['id'];
                        $orderInput['status'] = 6;
                        $tiencoc = $order['order']['thanh_toan'];
                        if (!empty($tiencoc) && $tiencoc > 0) {
                            // Hoan tien
                            $orderInput['datcoc_content'] = "Hủy mã, hoàn tiền cọc.";
                            $orderInput['thanh_toan'] = 0;
                            $orderInput['count_product'] = 0;
                            $orderInput['tien_hang'] = 0;
                            $orderInput['phi_tam_tinh'] = 0;
                            $orderInput['tong'] = 0;

                            $userId = $order['order']['user_id'];
                            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $userId]);

                            // Transaction
                            $transaction = [
                                'user_id' => $userId,
                                'type' => 5,
                                'code' => $order['order']['id'] . '.P' . $update['id'],
                                'value' => $tiencoc,
                                'debt' => $debt + $tiencoc,
                                'content' => "Hủy mã, hoàn tiền cọc."
                            ];
                            CommonServiceFactory::mTransactionService()->create($transaction);

                            // update card
                            CartServiceFactory::mCartService()->cancelOrder($order['order']['id']);
                        }

                        $history = [
                            'user_id' => $user['id'],
                            'order_id' => $order['order']['id'],
                            'type' => 6,
                            'content' => "Hủy mã, hoàn tiền cọc."
                        ];
                    }
                }

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
