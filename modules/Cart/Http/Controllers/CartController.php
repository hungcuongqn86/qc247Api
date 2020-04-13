<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Shop\Services\ShopServiceFactory;
use Modules\Order\Services\OrderServiceFactory;
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
                'id' => 'required',
                'amount' => 'required',
                'domain' => 'required',
                'image' => 'required',
                'method' => 'required',
                // 'name' => 'required',
                'pro_link' => 'required',
                'rate' => 'required',
                'price' => 'required',
                'site' => 'required'
            ];
            $arrMessages = [
                'id.required' => 'id.required',
                'amount.required' => 'Phải nhập số lượng!',
                'domain.required' => 'domain.required',
                'image.required' => 'image.required',
                'method.required' => 'method.required',
                // 'name.required' => 'name.required',
                'pro_link.required' => 'pro_link.required',
                'rate.required' => 'rate.required',
                'price.required' => 'Phải nhập đơn giá!',
                'site.required' => 'site.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }
            $user = $request->user();
            $cartI = CartServiceFactory::mCartService()->findById($input['id']);
            if (empty($cartI)) {
                return $this->sendError('Error', ['Không tồn tại sản phẩm!']);
            }

            $order = array();
            if (!empty($input['order_id'])) {
                $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
                if ($order) {
                    $order = $order['order'];
                    if ($order['status'] == 5) {
                        return $this->sendError('Error', ['Đơn đã thanh lý!']);
                    }
                }
            }

            $input['price'] = self::convertPrice($input['price']);
            $update = CartServiceFactory::mCartService()->update($input);
            if (!empty($input['order_id']) && $update) {
                $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
                if ($order) {
                    $order = $order['order'];
                }
                if ($order) {
                    $arrCarts = $order['cart'];
                    $tien_hang_old = $order['tien_hang'];
                    $phi_tt_old = $order['phi_tam_tinh'];
                    $tien_hang = 0;
                    $count_product = 0;
                    foreach ($arrCarts as $cartItem) {
                        $price = self::convertPrice($cartItem['price']);
                        $rate = $cartItem['rate'];
                        $amount = $cartItem['amount'];
                        $tien_hang = $tien_hang + round($price * $rate * $amount);
                        $count_product = $count_product + $cartItem['amount'];
                    }
                    if ($tien_hang_old > 0) {
                        $phi_tt = round(($tien_hang * $phi_tt_old) / $tien_hang_old);
                    } else {
                        if (!empty($order['user']['cost_percent'])) {
                            $tigia = $order['user']['cost_percent'];
                            $phi_tt = round($tien_hang * $tigia / 100);
                        } else {
                            $phi_tt = 0;
                        }
                    }

                    $orderInput = array();
                    $orderInput['id'] = $input['order_id'];
                    $orderInput['tien_hang'] = $tien_hang;
                    $orderInput['phi_tam_tinh'] = $phi_tt;
                    $orderInput['tong'] = $tien_hang + $phi_tt;
                    $orderInput['count_product'] = $count_product;
                    OrderServiceFactory::mOrderService()->update($orderInput);
                }

                // History
                $content = 'Mã ' . $input['id'] . ', Trước khi sửa, SL: ' . $cartI['cart']['amount'] . ', đơn giá: ' . $cartI['cart']['price'] . '¥';
                $content .= ' -> Sau khi sửa, SL: ' . $input['amount'] . ', đơn giá: ' . $input['price'] . '¥';
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $input['order_id'],
                    'type' => 8,
                    'content' => $content
                ];
                OrderServiceFactory::mHistoryService()->create($history);
            }

            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function convertPrice($priceStr)
    {
        $price = str_replace(' ', '', $priceStr);
        $price = explode('-', $price)[0];
        $price = str_replace(',', '.', $price);
        return $price;
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $inputData = self::json_decode_nice($input['cart']);

            //Check rate
            $user = $request->user();
            $rate = 0;
            if (!empty($user)) {
                $userData = CommonServiceFactory::mUserService()->findById($user->id);
                if (!empty($userData) && !empty($userData['user']) && !empty($userData['user']['rate'])) {
                    $rate = (int)$userData['user']['rate'];
                } else {
                    $setting = CommonServiceFactory::mSettingService()->findByKey('rate');
                    $rate = (int)$setting['setting']['value'];
                }
            } else {
                return $this->sendError('Error', ['Auth'], 401);
            }

            foreach ((array)$inputData as $item) {
                $inputCart = (array)$item;
                $inputCart['rate'] = $rate;
                $arrRules = [
                    'amount' => 'required',
                    'domain' => 'required',
                    'image' => 'required',
                    'method' => 'required',
                    //'name' => 'required',
                    'pro_link' => 'required',
                    'rate' => 'required',
                    'site' => 'required'
                ];
                $arrMessages = [
                    'amount.required' => 'amount.required',
                    'domain.required' => 'domain.required',
                    'image.required' => 'image.required',
                    'method.required' => 'method.required',
                    //'name.required' => 'name.required',
                    'pro_link.required' => 'pro_link.required',
                    'rate.required' => 'rate.required',
                    'site.required' => 'site.required'
                ];

                $validator = Validator::make($inputCart, $arrRules, $arrMessages);
                if ($validator->fails()) {
                    return $this->sendError('Error', $validator->errors()->all());
                }

                // Shop
                $shop = ShopServiceFactory::mShopService()->findByUrl($inputCart['shop_link']);
                if (!$shop) {
                    $inputShop = [
                        'name' => $inputCart['shop_nick'],
                        'url' => $inputCart['shop_link']
                    ];
                    $shop = ShopServiceFactory::mShopService()->create($inputShop);
                    // return $this->sendError('Error', 'Shop.' . $inputCart['shop_nick'] . '.NotExit');
                }

                $inputCart['shop_id'] = $shop['id'];
                $inputCart['user_id'] = $user->id;
                $inputCart['price'] = self::convertPrice($inputCart['price']);
                $inputCart['price_arr'] = json_encode($inputCart['price_arr']);
                $create = CartServiceFactory::mCartService()->create($inputCart);
            }

            return $this->sendResponse(1, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function json_decode_nice($json, $assoc = FALSE)
    {
        $json = str_replace(array("\n", "\r"), "", $json);
        //$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);
        //$json = preg_replace('/(,)\s*}$/', '}', $json);
        return json_decode($json, $assoc);
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $arrId = explode(',', $input['ids']);
        $carts = CartServiceFactory::mCartService()->findByIds($arrId);
        $deleteData = array();
        $errData = array();
        foreach ($arrId as $id) {
            $check = false;
            foreach ($carts as $cart) {
                if ($id == $cart['id']) {
                    $check = true;
                    $cart['is_deleted'] = 1;
                    $deleteData[] = $cart;
                }
            }
            if (!$check) {
                $errData[] = 'Cart Id ' . $id . ' NotExist';
            }
        }

        if (!empty($errData)) {
            return $this->sendError('Error', $errData);
        }

        try {
            CartServiceFactory::mCartService()->delete($arrId);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
