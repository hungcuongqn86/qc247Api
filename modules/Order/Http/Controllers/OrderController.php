<?php

namespace Modules\Order\Http\Controllers;

use App\Exports\OrdersExport;
use Excel;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Entities\Order;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Order\Services\OrderServiceFactory;

class OrderController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    private function convertPrice($priceStr)
    {
        $price = str_replace(' ', '', $priceStr);
        $price = explode('-', $price)[0];
        $price = str_replace(',', '.', $price);
        return $price;
    }

    public function fixbug(Request $request)
    {
        $input = $request->all();
        try {
            $query = Order::with(['Cart'])->where('is_deleted', '=', 0)
                ->where('status', '<', 5)->get()->toArray();

            $count = 0;
            foreach ($query as $key => $order) {
                $tien_hang = 0;
                $arrCarts = $order['cart'];
                if(!empty($arrCarts) && ($arrCarts[0]['rate'] != $order['rate'])){
                    $count ++;
                    foreach ($arrCarts as $cartItem) {
                        $price = self::convertPrice($cartItem['price']);
                        $rate = $order['rate'];
                        $amount = $cartItem['amount'];
                        $tien_hang = $tien_hang + round($price * $rate * $amount);
                    }

                    $phi_tt = round(($tien_hang * $order['phi_tam_tinh']) / $order['tien_hang']);

                    echo $key . "--" . $order['id'] . " -- " . $tien_hang . " -- " . $phi_tt;
                    echo '<br>';

                    $orderInput = array();
                    $orderInput['id'] = $order['id'];
                    $orderInput['tien_hang'] = $tien_hang;
                    $orderInput['phi_tam_tinh'] = $phi_tt;
                    $orderInput['tong'] = $tien_hang + $phi_tt;
                    // dd($orderInput);
                    OrderServiceFactory::mOrderService()->update($orderInput);
                }
            }
            echo '<br>';
            echo $count;
            exit;
            return $this->sendResponse($query, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mOrderService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $input = $request->all();
        try {
            $fileName = time() . '.orders.xlsx';
            $file = \Maatwebsite\Excel\Facades\Excel::store(new OrdersExport($input), $fileName);
            return $this->sendResponse($fileName, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function download(Request $request, $filename)
    {
        try {
            return response()->download(storage_path("app/{$filename}"));
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function countByStatus(Request $request)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mOrderService()->countByStatus(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function comments(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            $input['type'] = $user->type;
            $input['admin'] = false;
            $currentUser = Auth::user();
            if ($currentUser->hasRole('admin')) {
                $input['admin'] = true;
            }
            return $this->sendResponse(OrderServiceFactory::mOrderService()->comments($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function allcomments(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            $input['type'] = $user->type;
            $input['admin'] = false;
            $currentUser = Auth::user();
            if ($currentUser->hasRole('admin')) {
                $input['admin'] = true;
            }
            return $this->sendResponse(OrderServiceFactory::mOrderService()->allcomments($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function myOrder(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            return $this->sendResponse(OrderServiceFactory::mOrderService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function myCountByStatus(Request $request)
    {
        try {
            $user = $request->user();
            $retn = array();

            $arrCountOrder = OrderServiceFactory::mOrderService()->myCountByStatus($user->id);
            foreach ($arrCountOrder as $item) {
                $item['type'] = 'od';
                $retn[] = $item;
            }

            $arrCountPk = OrderServiceFactory::mPackageService()->myOrderCountByStatus($user->id);
            foreach ($arrCountPk as $item) {
                $item['type'] = 'pk';
                $retn[] = $item;
            }

            return $this->sendResponse($retn, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id, Request $request)
    {
        try {
            $user = $request->user();
            $order = OrderServiceFactory::mOrderService()->findById($id);
            if ($order && ($user['type'] == 1) && $order['order']['user_id'] != $user['id']) {
                return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
            }
            return $this->sendResponse($order, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function status()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mOrderService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function historyTypes()
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
            'shop_id' => 'required',
            'cart_ids' => 'required'
        ];
        $arrMessages = [
            'shop_id.required' => 'Không xác định được shop!',
            'cart_ids.required' => 'Không có sản phẩm!'
        ];

        $user = $request->user();
        $input['user_id'] = $user['id'];
        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Kết đơn không thành công!', $validator->errors()->all());
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

    public function update(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $order = OrderServiceFactory::mOrderService()->findById($input['id']);
        if (!empty($order) && ($order['order']['status'] > 2)) {
            return $this->sendError('Error', ['Không thể xóa đơn đã đặt cọc!']);
        }

        try {
            $update = OrderServiceFactory::mOrderService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function baogia(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required',
            'content.required' => 'content.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $input['status'] = 2;
            $input['baogia_content'] = $input['content'];
            $update = OrderServiceFactory::mOrderService()->update($input);
            if (!empty($update)) {
                // History
                $user = $request->user();
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $input['id'],
                    'type' => 2,
                    'content' => $input['content']
                ];
                OrderServiceFactory::mHistoryService()->create($history);
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function datcoc(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required',
            'dc_value' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required',
            'dc_value.required' => 'dc_value.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $order = OrderServiceFactory::mOrderService()->findById($input['id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        if (!empty($order) && ($order['order']['status'] > 2)) {
            return $this->sendError('Error', ['Đơn đã đặt cọc!']);
        }

        try {
            $user = $request->user();
            // Transaction
            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $user['id']]);
            if ($debt < $input['dc_value']) {
                return $this->sendError('Dư nợ không đủ để thực hiện đặt cọc!');
            }

            $input['status'] = 3;
            $input['datcoc_content'] = $input['content'];
            $input['thanh_toan'] = $input['dc_value'];
            $update = OrderServiceFactory::mOrderService()->update($input);
            if (!empty($update)) {
                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $input['id'],
                    'type' => 3,
                    'content' => $input['content']
                ];
                $historyRs = OrderServiceFactory::mHistoryService()->create($history);

                // Transaction
                $transaction = [
                    'user_id' => $user['id'],
                    'type' => 4,
                    'code' => $input['id'] . '.H' . $historyRs['id'],
                    'value' => $input['dc_value'],
                    'debt' => $debt - $input['dc_value'],
                    'content' => $input['content']
                ];
                CommonServiceFactory::mTransactionService()->create($transaction);
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
