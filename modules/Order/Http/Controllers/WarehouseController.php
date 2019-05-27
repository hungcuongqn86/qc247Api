<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

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
        $billinput['employee'] = $user['id'];
        $billinput['tong_can'] = 0;
        $billinput['gia_can_nang'] = 0;
        $billinput['tien_can'] = 0;
        $billinput['tien_thanh_ly'] = 0;
        $billinput['status'] = 1;
        $billinput['so_ma'] = 0;
        try {
            //Lay danh sach kien hang
            $packages = OrderServiceFactory::mPackageService()->findByPkCodes($input['pkcodelist']);
            $tongcan = 0;
            $soma = 0;
            foreach ($packages as $package) {
                $tongcan = $tongcan + $package['weight_qd'];
                $soma = $soma + 1;
            }
            $billinput['tong_can'] = $tongcan;
            $billinput['so_ma'] = $soma;
            $gia_can_nang = 0;
            if ($tongcan < 10) {
                $gia_can_nang = 27000;
            }
            if (($tongcan >= 10) && ($tongcan <= 30)) {
                $gia_can_nang = 23000;
            }
            if ($tongcan > 30) {
                $gia_can_nang = 19000;
            }
            $billinput['gia_can_nang'] = $gia_can_nang;
            $billinput['tien_can'] = $tongcan * $gia_can_nang;
            return $this->sendResponse($billinput, 'Successfully.');
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
}
