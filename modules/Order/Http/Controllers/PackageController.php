<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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
                    if ($weight_qd <= 20) {
                        $gia_can_nang = 42000;
                    }
                    if (($weight_qd > 20) && ($weight_qd <= 50)) {
                        $gia_can_nang = 40000;
                    }
                    if (($weight_qd > 50) && ($weight_qd <= 200)) {
                        $gia_can_nang = 36000;
                    }
                    if ($weight_qd > 200) {
                        $gia_can_nang = 35000;
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

    public function import(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required',
            'items' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'order_id.required',
            'items.required' => 'Không có mã vận đơn hợp lệ'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
        }

        $arrPk = $order['order']['package'];
        $itemsArray = array_unique($input['items']);
        $data = [];
        $pkCodeArr = [];
        foreach ($itemsArray as $pk) {
            $arrPkInfo = explode('|', $pk);
            $code = trim($arrPkInfo[0]);
            $weight = 0;
            $weightCd = 0;
            $gia_can_nang = 0;
            $status = 3;

            if(sizeof($arrPkInfo) > 1){
                $weightStr = str_replace(',','.', trim($arrPkInfo[1]));
                $weight = (float)$weightStr;
                if ($weight < 0.5) {
                    $weightCd = 0.5;
                } else {
                    $weightCd = $weight;
                }

                if ($weightCd > 0) {
                    $status = 6;

                    if (!empty($order['order']['user']['weight_price'])) {
                        $gia_can_nang = $order['order']['user']['weight_price'];
                    } else {
                        if ($weightCd <= 20) {
                            $gia_can_nang = 42000;
                        }
                        if (($weightCd > 20) && ($weightCd <= 50)) {
                            $gia_can_nang = 40000;
                        }
                        if (($weightCd > 50) && ($weightCd <= 200)) {
                            $gia_can_nang = 36000;
                        }
                        if ($weightCd > 200) {
                            $gia_can_nang = 35000;
                        }
                    }
                }
            }

            $pkCodeArr[] = $code;

            $data[] = [
                'order_id' => $input['order_id'],
                'package_code' => $code,
                'weight' => $weight,
                'weight_qd' => $weightCd,
                'gia_can' => $gia_can_nang,
                'tien_can' => $gia_can_nang * $weightCd,
                'tien_thanh_ly' => 0,
                'status' => $status
            ];
        }

        $codeCheck = [];
        $checkEx = OrderServiceFactory::mPackageService()->findByPkCodes($pkCodeArr);
        if(!empty($checkEx)){
            foreach ($checkEx as $pk) {
                $codeCheck[] = $pk['package_code'];
            }
        }

        if(sizeof($codeCheck) > 0){
            $msg = 'Mã kiện ' . implode(', ', $codeCheck) . ' đã tồn tại!';
            return $this->sendError('Error', [$msg]);
        }

        DB::beginTransaction();
        try {
            $orderInput = array();
            if ($order['order']['status'] < 4) {
                $orderInput['id'] = $order['order']['id'];
                $orderInput['status'] = 4;
            }

            $importRes = OrderServiceFactory::mPackageService()->import($data);
            if(!empty($importRes)){
                if (!empty($orderInput['id'])) {
                    OrderServiceFactory::mOrderService()->update($orderInput);
                }
            }
            DB::commit();
            return $this->sendResponse(1, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required',
            'items' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'order_id.required',
            'items.required' => 'Không có kiện'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        DB::beginTransaction();
        try {
            $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
            if (empty($order)) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }
            OrderServiceFactory::mPackageService()->remove($input['items']);
            DB::commit();
            return $this->sendResponse(1, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
