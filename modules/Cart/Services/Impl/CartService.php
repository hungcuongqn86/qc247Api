<?php

namespace Modules\Cart\Services\Impl;

use Modules\Common\Entities\Cart;
use Modules\Common\Services\Impl\CommonService;
use Modules\Cart\Services\Intf\ICartService;
use Illuminate\Support\Facades\DB;

class CartService extends CommonService implements ICartService
{
    protected function getDefaultModel()
    {
        return Cart::getTableName();
    }

    protected function getDefaultClass()
    {
        return Cart::class;
    }

    public function search($userId)
    {
        return [];
    }

    public function getDistinctShopCart($userId)
    {
        $query = Cart::distinct('shop_id')->where('is_deleted', '=', 0);
        $query->where('status', '=', 1);
        $query->where('user_id', '=', $userId);
        $rResult = $query->pluck('shop_id');
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Cart::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('cart' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function findByIds($ids)
    {
        $rResult = Cart::wherein('id', $ids)->get();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $owner = new Cart($arrInput);
        DB::beginTransaction();
        try {
            $owner->save();
            DB::commit();
            return $owner;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $owner = Cart::find($id);
            $owner->update($arrInput);
            DB::commit();
            return $owner;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelOrder($orderId)
    {
        DB::beginTransaction();
        try {
            $cart = Cart::where('order_id','=',$orderId)->update(['amount' => 0]);
            DB::commit();
            return $cart;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            Cart::wherein('id', $ids)->update(['is_deleted' => 1]);
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
