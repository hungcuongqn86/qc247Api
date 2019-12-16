<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;

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

    public function bills(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billStatus()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billCreate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'user_id' => 'required',
            'pkcodelist' => 'required'
        ];
        $arrMessages = [
            'user_id.required' => 'Thiếu thông tin khách hàng!',
            'pkcodelist.required' => 'Thiếu thông tin kiện hàng!'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Tạo phiếu xuất không thành công!', $validator->errors()->all());
        }

        //Bill input
        $user = $request->user();
        $billinput = array();
        $billinput['user_id'] = $input['user_id'];
        $billinput['employee_id'] = $user['id'];
        $billinput['status'] = 1;
        $billinput['so_ma'] = 0;
        try {
            //Lay danh sach kien hang
            $packages = OrderServiceFactory::mPackageService()->findByPkCodes($input['pkcodelist']);
            $soma = 0;
            foreach ($packages as $package) {
                $soma = $soma + 1;
                if (!empty($package['bill_id'])) {
                    return $this->sendError('Error', ['Mã vận đơn đã được tạo ở phiếu xuất khác!']);
                }
            }
            $billinput['so_ma'] = $soma;

            // return $this->sendResponse($billinput, 'Successfully.');
            $create = OrderServiceFactory::mBillService()->create($billinput);
            if (!empty($create)) {
                foreach ($packages as $package) {
                    $packageInput = array(
                        'id' => $package['id'],
                        'bill_id' => $create['id']
                    );
                    OrderServiceFactory::mPackageService()->update($packageInput);
                }
            }
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billDelete(Request $request)
    {
        $input = $request->all();
        $bill = OrderServiceFactory::mBillService()->findById($input['id']);
        if (empty($bill)) {
            return $this->sendError('Error', ['Không tồn tại phiếu xuất!']);
        }
        if ($bill['bill']['status'] == 2) {
            return $this->sendError('Error', ['Không thể xóa phiếu xuất đã xuất kho!']);
        }
        try {
            // Package
            $packages = $bill['bill']['package'];
            foreach ($packages as $package) {
                $packageInput = array(
                    'id' => $package['id'],
                    'bill_id' => null
                );
                OrderServiceFactory::mPackageService()->update($packageInput);
            }
            $billInput = array(
                'id' => $input['id'],
                'is_deleted' => 1
            );
            OrderServiceFactory::mBillService()->update($billInput);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billDetail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billConfirm(Request $request)
    {
        $input = $request->all();
        $user = $request->user();
        $arrRules = [
            'id' => 'required',
        ];
        $arrMessages = [
            'id.required' => 'Không xác định được phiếu xuất!',
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Xuất kho không thành công!', $validator->errors()->all());
        }

        try {
            //Bill
            $billinput = array();
            $billinput['id'] = $input['id'];
            $billinput['status'] = 2;
            $billinput['tong_can'] = 0;
            $billinput['tien_can'] = 0;
            $billinput['tien_thanh_ly'] = 0;
            $bill = OrderServiceFactory::mBillService()->findById($input['id']);
            $packages = $bill['bill']['package'];
            foreach ($packages as $package) {
                $billinput['tong_can'] = $billinput['tong_can'] + $package['weight_qd'];
                $billinput['tien_can'] = $billinput['tien_can'] + $package['tien_can'];
                $billinput['tien_thanh_ly'] = $billinput['tien_thanh_ly'] + $package['tien_thanh_ly'];
            }

            if ($billinput['tien_thanh_ly'] > $bill['bill']['user']['debt']) {
                return $this->sendError('Xuất kho không thành công!', ['Dư nợ không đủ để thực hiện thanh lý!']);
            }

            $update = OrderServiceFactory::mBillService()->update($billinput);
            if (!empty($update)) {
                // Thanh ly package
                foreach ($packages as $package) {
                    $packageInput = array(
                        'id' => $package['id'],
                        'status' => 7
                    );
                    $pkupdate = OrderServiceFactory::mPackageService()->update($packageInput);
                    if (!empty($pkupdate)) {
                        //Thanh ly order
                        $order = OrderServiceFactory::mOrderService()->findById($pkupdate['order_id']);
                        $arrPk = $order['order']['package'];
                        if ((!empty($arrPk)) && ($arrPk[0]['id'] == $pkupdate['id'])) {
                            $tongTien = $order['order']['tong'];
                            $tigia = $order['order']['rate'];
                            foreach ($arrPk as $pk) {
                                if ($pk['ship_khach'] && $pk['ship_khach'] > 0) {
                                    $ndt = $pk['ship_khach'];
                                    $vnd = $ndt * $tigia;
                                    $tongTien = $tongTien + $vnd;
                                }
                            }

                            $orderInput = array();
                            $orderInput['id'] = $order['order']['id'];
                            $orderInput['status'] = 5;
                            $orderInput['thanh_toan'] = $tongTien;
                            OrderServiceFactory::mOrderService()->update($orderInput);
                            // dd($orderInput);
                            // add history
                            $history = [
                                'user_id' => $user['id'],
                                'order_id' => $order['order']['id'],
                                'type' => 9,
                                'content' => 'Xuất kho thanh lý, mã phiếu ' . $update['id']
                            ];
                            OrderServiceFactory::mHistoryService()->create($history);
                        }
                    }
                }

                // Transaction
                $transaction = [
                    'user_id' => $update['user_id'],
                    'type' => 6,
                    'code' => 'XKTL.' . $update['id'],
                    'value' => $update['tien_can'] + $update['tien_thanh_ly'],
                    'debt' => $bill['bill']['user']['debt'] - ($update['tien_can'] + $update['tien_thanh_ly']),
                    'content' => 'Xuất kho thanh lý, mã phiếu ' . $update['id']
                ];
                CommonServiceFactory::mTransactionService()->create($transaction);
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
