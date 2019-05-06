<?php

namespace Modules\Order\Services\Impl;

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
        return [];
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
