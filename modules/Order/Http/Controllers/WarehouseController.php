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

    public function bills(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->search($input), 'Successfully.');
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

    public function billDetail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
