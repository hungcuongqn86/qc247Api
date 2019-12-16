<?php

namespace Modules\Order\Services\Impl;

use App\User;
use Modules\Common\Entities\Order;
use Modules\Common\Entities\Package;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IPackageService;
use Illuminate\Support\Facades\DB;

class PackageService extends CommonService implements IPackageService
{
    protected function getDefaultModel()
    {
        return Package::getTableName();
    }

    protected function getDefaultClass()
    {
        return Package::class;
    }

    public function search($filter)
    {
        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        $query = Package::with(array('Order' => function ($query) {
            $query->with(['User', 'Cart', 'Shop']);
            $query->where('is_deleted', '=', 0)->orderBy('id');
        }))->where('is_deleted', '=', 0);

        $sOrderCode = isset($filter['code']) ? $filter['code'] : '';
        $iuserId = isset($filter['user_id']) ? $filter['user_id'] : null;
        $query->whereHas('Order', function ($q) use ($sOrderCode, $sKeySearch, $iuserId) {
            if (!empty($sOrderCode)) {
                $q->where('id', '=', $sOrderCode);
            }

            if (!empty($iuserId)) {
                $q->where('user_id', '=', $iuserId);
            }

            if (!empty($sKeySearch)) {
                $q->whereHas('User', function ($q) use ($sKeySearch) {
                    $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                    $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                    $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
                });
            }
            $q->where('is_deleted', '=', 0);
        });

        $sPackageCode = isset($filter['package_code']) ? $filter['package_code'] : '';
        if (!empty($sPackageCode)) {
            $query->where('package_code', '=', $sPackageCode);
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

    public function myOrderCountByStatus($userId)
    {
        $rResult = Package::where('is_deleted', '=', 0)->whereHas('Order', function ($q) use ($userId) {
            $q->where('user_id', '=', $userId);
            $q->where('is_deleted', '=', 0);
        })->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function waitMoveOut($filter)
    {
        $query = User::with(array('Order' => function ($query) {
            $query->whereHas('Package', function ($q) {
                $q->where('status', '=', 6);
                $q->where('is_deleted', '=', 0);
            });
            $query->where('is_deleted', '=', 0)->orderBy('id');
            $query->with(array('Package' => function ($query) {
                $query->where('status', '=', 6);
                $query->whereNull('bill_id');
                $query->whereNotNull('weight_qd');
                $query->where('is_deleted', '=', 0)->orderBy('id');
            }));
        }))->where('is_deleted', '=', 0);

        $sOrderCode = isset($filter['code']) ? $filter['code'] : '';
        $sPackageCode = isset($filter['package_code']) ? $filter['package_code'] : '';
        $query->whereHas('Order', function ($q) use ($sOrderCode, $sPackageCode) {
            if (!empty($sOrderCode)) {
                $q->where('id', '=', $sOrderCode);
            }
            $q->where('is_deleted', '=', 0);
            $q->whereHas('Package', function ($q) use ($sPackageCode) {
                if (!empty($sPackageCode)) {
                    $q->where('package_code', '=', $sPackageCode);
                }
                $q->where('is_deleted', '=', 0);
                $q->whereNull('bill_id');
                $q->whereNotNull('weight_qd');
                $q->where('status', '=', 6);
            });
        });

        $email = isset($filter['email']) ? trim($filter['email']) : '';
        if (!empty($email)) {
            $query->where('email', '=', $email);
        }
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Package::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('package' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function findByPkCodes($ids)
    {
        $rResult = Package::wherein('package_code', $ids)->get();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function status()
    {
        $package = new Package();
        return $package->status();
    }

    public function create($arrInput)
    {
        $package = new Package($arrInput);
        DB::beginTransaction();
        try {
            $package->save();
            DB::commit();
            return $package;
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
            $package = Package::find($id);
            $package->update($arrInput);
            DB::commit();
            return $package;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
