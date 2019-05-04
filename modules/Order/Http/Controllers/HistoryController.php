<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class HistoryController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mHistoryService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mHistoryService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function types()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mHistoryService()->types(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required',
            'type' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'Không xác định được đơn hàng!',
            'type.required' => 'Chưa chọn công việc thực hiện!',
            'content.required' => 'Chưa nhập nội dung thực hiện!'
        ];

        $user = $request->user();
        $input['user_id'] = $user['id'];
        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            // Order
            $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
            if (empty($order)) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }

            $userId = $order['order']['user_id'];
            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $userId]);
            $con_lai = 0;
            $tongTien = 0;
            if ($input['type'] == 5) {
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

                $thanh_toan = empty($order['order']['thanh_toan']) ? 0 : $order['order']['thanh_toan'];
                $con_lai = $tongTien - $thanh_toan;
                if ($con_lai > $debt) {
                    return $this->sendError('Error', ['Dư nợ không đủ để thanh lý!']);
                }
            }

            $create = OrderServiceFactory::mHistoryService()->create($input);
            if (!empty($create)) {
                // Update order status
                $orderInput = array();
                $orderInput['id'] = $order['order']['id'];

                if ($input['type'] == 4) {
                    $orderInput['status'] = 4;
                }
                if ($input['type'] == 5) {
                    $orderInput['status'] = 5;
                    $orderInput['thanh_toan'] = $tongTien;

                    // Transaction
                    $transaction = [
                        'user_id' => $userId,
                        'type' => 6,
                        'code' => $order['order']['id'] . '.H' . $create['id'],
                        'value' => $con_lai,
                        'debt' => $debt - $con_lai,
                        'content' => $input['content']
                    ];
                    CommonServiceFactory::mTransactionService()->create($transaction);
                }
                if ($input['type'] == 6) {
                    $orderInput['status'] = 6;
                }
                if ($input['type'] == 7) {
                    $tiencoc = $order['order']['thanh_toan'];
                    if (!empty($tiencoc) && $tiencoc > 0) {
                        // Hoan tien
                        $orderInput['datcoc_content'] = $input['content'];
                        $orderInput['thanh_toan'] = 0;
                        $orderInput['count_product'] = 0;
                        $orderInput['tien_hang'] = 0;
                        $orderInput['phi_tam_tinh'] = 0;
                        $orderInput['tong'] = 0;

                        // Transaction
                        $transaction = [
                            'user_id' => $userId,
                            'type' => 5,
                            'code' => $order['order']['id'] . '.H' . $create['id'],
                            'value' => $tiencoc,
                            'debt' => $debt + $tiencoc,
                            'content' => $input['content']
                        ];
                        CommonServiceFactory::mTransactionService()->create($transaction);
                    }
                }
                OrderServiceFactory::mOrderService()->update($orderInput);
            }
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
