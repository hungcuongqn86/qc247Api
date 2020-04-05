<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\Order;
use Modules\Common\Entities\Comment;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IOrderService;
use Illuminate\Support\Facades\DB;

class OrderService extends CommonService implements IOrderService
{
    protected function getDefaultModel()
    {
        return Order::getTableName();
    }

    protected function getDefaultClass()
    {
        return Order::class;
    }

    public function search($filter)
    {
        $query = Order::with(['User', 'Cart', 'Shop'])->with(array('Package' => function ($query) {
            $query->where('is_deleted', '=', 0)->orderBy('id');
        }))->where('is_deleted', '=', 0);
        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->whereHas('User', function ($q) use ($sKeySearch) {
                $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
            });
            $query->orWhereHas('Cart', function ($q) use ($sKeySearch) {
                $q->whereHas('Shop', function ($q) use ($sKeySearch) {
                    $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                });
            });
        }

        $iPkStatus = isset($filter['pk_status']) ? $filter['pk_status'] : 0;
        $package_code = isset($filter['package_code']) ? trim($filter['package_code']) : '';
        $contract_code = isset($filter['contract_code']) ? trim($filter['contract_code']) : '';
        if (!empty($package_code) || !empty($contract_code)) {
            if ($package_code === '#') {
                $query->whereHas('Package', function ($q) use ($package_code, $contract_code, $iPkStatus) {
                    $q->whereNull('package_code');
                    if(!empty($contract_code)){
                        $q->where('contract_code', '=', $contract_code);
                    }
                    if ($iPkStatus > 0) {
                        $q->where('status', '=', $iPkStatus);
                    }
                });
            } else {
                $query->whereHas('Package', function ($q) use ($package_code, $contract_code, $iPkStatus) {
                    if(!empty($package_code)){
                        $q->where('package_code', '=', $package_code);
                    }
                    if(!empty($contract_code)){
                        $q->where('contract_code', '=', $contract_code);
                    }
                    if ($iPkStatus > 0) {
                        $q->where('status', '=', $iPkStatus);
                    }
                });
            }
        } else {
            if ($iPkStatus > 0) {
                $query->whereHas('Package', function ($q) use ($iPkStatus) {
                    $q->where('status', '=', $iPkStatus);
                });
            }
        }

        $code = isset($filter['code']) ? trim($filter['code']) : '';
        if (!empty($code)) {
            $query->where('id', '=', $code);
        }
        $iuser = isset($filter['user_id']) ? $filter['user_id'] : 0;
        if ($iuser > 0) {
            $query->where('user_id', '=', $iuser);
        }
        $istatus = isset($filter['status']) ? $filter['status'] : 0;
        if ($istatus > 0) {
            $query->where('status', '=', $istatus);
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }


    public function export($filter)
    {
        $query = Order::with(['Cart'])->where('is_deleted', '=', 0);
        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->whereHas('User', function ($q) use ($sKeySearch) {
                $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
            });
        }
        $package_code = isset($filter['package_code']) ? trim($filter['package_code']) : '';
        if (!empty($package_code)) {
            if ($package_code === '#') {
                $query->whereHas('Package', function ($q) use ($package_code) {
                    $q->whereNull('package_code');
                });
            } else {
                $query->whereHas('Package', function ($q) use ($package_code) {
                    $q->where('package_code', '=', $package_code);
                });
            }
        }
        $code = isset($filter['code']) ? trim($filter['code']) : '';
        if (!empty($code)) {
            $query->where('id', '=', $code);
        }
        $istatus = isset($filter['status']) ? $filter['status'] : 0;
        if ($istatus > 0) {
            $query->where('status', '=', $istatus);
        }
        $query->orderBy('id', 'desc');

        $rResult = $query->get(['id'])->toArray();
        return $rResult;
    }

    public function countByStatus()
    {
        $rResult = Order::where('is_deleted', '=', 0)->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function comments($filter)
    {
        $query = Comment::with(['Order'])->where('is_deleted', '=', 0);
        $userid = $filter['user_id'];
        if ($filter['type'] == 1) {
            $query->whereHas('Order', function ($q) use ($userid) {
                $q->where('user_id', '=', $userid);
            });
        }
        if ($filter['type'] == 0 and !$filter['admin']) {
            $query->where('is_admin', '=', 0);
        }
        $query->whereDoesntHave('CommentUsers', function ($q) use ($userid) {
            $q->where('user_id', '=', $userid);
        });
        $query->where('user_id', '<>', $userid);
        $rResult = $query->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function allcomments($filter)
    {
        $userid = $filter['user_id'];

        $query = Comment::with(['Order'])->with(array('CommentUsers' => function ($q) use ($userid) {
            $q->where('user_id', '=', $userid);
        }))->where('is_deleted', '=', 0);

        $query->where('user_id', '<>', $userid);

        if ($filter['type'] == 1) {
            $query->whereHas('Order', function ($q) use ($userid) {
                $q->where('status', '<', 5);
                $q->where('user_id', '=', $userid);
            });
        } else {
            $query->whereHas('Order', function ($q) use ($userid) {
                $q->where('status', '<', 5);
            });
            if (!$filter['admin']) {
                $query->where('is_admin', '=', 0);
            }
        }
        $query->orderBy('created_at', 'desc')->limit(500);
        $rResult = $query->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function myCountByStatus($userId)
    {
        $rResult = Order::where('is_deleted', '=', 0)->where('user_id', '=', $userId)->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function findById($id)
    {
        $rResult = Order::with(['User', 'Cart', 'Shop', 'History'])->with(array('Package' => function ($query) {
            $query->where('is_deleted', '=', 0)->orderBy('id');
        }))->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('order' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function findByIds($ids)
    {
        $rResult = Order::with(array('Package' => function ($query) {
            $query->where('is_deleted', '=', 0)->orderBy('id');
        }))->wherein('id', $ids)->get();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function status()
    {
        $order = new Order();
        return $order->status();
    }

    public function create($arrInput)
    {
        $order = new Order($arrInput);
        DB::beginTransaction();
        try {
            $order->save();
            DB::commit();
            return $order;
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
            $order = Order::find($id);
            $order->update($arrInput);
            DB::commit();
            return $order;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function checkCancel($id)
    {
        $query = Order::where('id', '=', $id)->where('is_deleted', '=', 0);
        $query->whereDoesntHave('Package', function ($q) {
            $q->where('status', '<>', 8);
        });

        $rResult = $query->first();
        if (!empty($rResult)) {
            return true;
        } else {
            return false;
        }
    }
}
