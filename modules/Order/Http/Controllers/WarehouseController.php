<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

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

    public function billExport(Request $request)
    {
        $input = $request->all();
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('admin')) {
            return $this->sendError('Kết xuất không thành công!', ['Auth'], 401);
        }
        $arrRules = [
            'id' => 'required',
        ];
        $arrMessages = [
            'id.required' => 'Không xác định được phiếu xuất!',
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Kết xuất không thành công!', $validator->errors()->all());
        }

        try {
            //Bill
            $bill = OrderServiceFactory::mBillService()->findById($input['id']);
            $report = ["tong_can_nang" => 0, "tong_thanh_ly" => 0, "tong_tien_can" => 0];
            $cartData = [];
            foreach ($bill['bill']['package'] as $package) {
                $report['tong_can_nang'] += $package['weight_qd'];
                $report['tong_tien_can'] += $package['tien_can'];
                $report['tong_thanh_ly'] += $package['tien_thanh_ly'];

                $arrCart = $package['order']['cart'];
                foreach ($arrCart as $cart) {
                    if (($index = array_search($cart['id'], array_column($cartData, 'id'))) !== false) {

                    } else {
                        $cartData[] = $cart;
                    }
                }
            }

            $reportname = time() . '_pet_invoice.xlsx';
            $template = public_path('template/bill.xlsx');
            self::_export($reportname, $template, $bill['bill'], $report, $cartData);
            $url = url('/download/' . $reportname);
            return $this->sendResponse($url, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function _export($reportname, $template, $bill, $report, $cartData)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $worksheet = $spreadsheet->getActiveSheet();

            // Page setup
            $pageSetup = $worksheet->getPageSetup();
            // $pageSetup->addPrintAreaByColumnAndRow(1, 0, 4, 30);
            $pageSetup->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
            $pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
            $pageSetup->setFitToPage(1);
            // $pageSetup->setScale(400);
            $pageSetup->setFitToWidth(1);
            $pageSetup->setFitToHeight(1);
            $worksheet->setShowGridlines(false);

            // Page margins
            $pageMargins = $worksheet->getPageMargins();
            $pageMargins->setTop(0.5);
            $pageMargins->setRight(0.25);
            $pageMargins->setLeft(0.25);
            $pageMargins->setBottom(0.15);
            // bill id
            $billcode = '(Mã phiếu ' . $bill['id'] . ') Hà Nội';
            $worksheet->getCell('B5')->setValue($billcode);

            // bill.user
            $worksheet->getCell('D8')->setValue($bill['user']['name']);
            $worksheet->getCell('D9')->setValue($bill['user']['email']);
            $worksheet->getCell('D10')->setValue($bill['user']['phone_number']);

            // report
            $worksheet->getCell('K8')->setValue($report['tong_tien_can'] + $report['tong_thanh_ly']);
            $worksheet->getCell('K9')->setValue($bill['user']['debt']);
            $worksheet->getCell('K10')->setValue($bill['user']['debt'] - $report['tong_tien_can'] - $report['tong_thanh_ly']);

            $worksheet->getCell('B14')->setValue($report['tong_can_nang']);
            $worksheet->getCell('D14')->setValue($report['tong_tien_can']);
            $worksheet->getCell('K14')->setValue($report['tong_thanh_ly']);

            // package
            $worksheet->getCell('E19')->setValue($report['tong_thanh_ly']);
            $worksheet->getCell('J19')->setValue($report['tong_tien_can']);

            //Footer
            $worksheet->getCell('B29')->setValue($bill['user']['name']);
            $worksheet->getCell('I29')->setValue($bill['employee']['name']);
            $dateStr = 'Hà Nội, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y') . '.';;
            $worksheet->getCell('I25')->setValue($dateStr);

            $baseRow = 18;
            $count = sizeof($bill['package']);
            if ($count > 1) {
                $worksheet->insertNewRowBefore($baseRow, $count - 1);
            }
            for ($i = 0; $i < $count; $i++) {
                $index = $baseRow + $i;
                $item = $bill['package'][$i];

                $worksheet->getCell('B' . $index)->setValue($i + 1);
                $worksheet->getCell('C' . $index)->setValueExplicit($item['package_code'], DataType::TYPE_STRING);
                $worksheet->getCell('D' . $index)->setValue($item['order_id']);
                $worksheet->getCell('E' . $index)->setValue($item['tien_thanh_ly']);
                $worksheet->getCell('G' . $index)->setValue($item['weight']);
                $worksheet->getCell('H' . $index)->setValue($item['weight_qd']);
                $worksheet->getCell('I' . $index)->setValue($item['gia_can']);
                $worksheet->getCell('J' . $index)->setValue($item['tien_can']);
                $worksheet->getCell('K' . $index)->setValue(0);
                $worksheet->getCell('L' . $index)->setValue(0);
                $worksheet->getRowDimension($index)->setRowHeight(-1);
            }
            //
            $baseRow = 23 + $count - 1;
            $count = sizeof($cartData);
            if ($count > 1) {
                $worksheet->insertNewRowBefore($baseRow, $count - 1);
                for ($i = 0; $i < $count; $i++) {
                    $index = $baseRow + $i;
                    $worksheet->mergeCells('B' . $index . ':C' . $index);
                }
            }
            for ($i = 0; $i < $count; $i++) {
                $index = $baseRow + $i;
                $item = $cartData[$i];

                $worksheet->getCell('B' . $index)->setValue($item['order_id']);
                $worksheet->getCell('D' . $index)->setValue($item['id']);
                $worksheet->getCell('E' . $index)->setValue($item['colortxt']);
                $worksheet->getCell('F' . $index)->setValue($item['sizetxt']);
                $worksheet->getCell('G' . $index)->setValue($item['note']);
                $worksheet->getCell('H' . $index)->setValue($item['price']);
                $worksheet->getCell('I' . $index)->setValue($item['amount']);
                $worksheet->getCell('J' . $index)->setValue(0);
                $worksheet->getCell('K' . $index)->setValue($item['amount']);
                $worksheet->getRowDimension($index)->setRowHeight(-1);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save(storage_path('app/exports/' . $reportname));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
