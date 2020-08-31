<?php

namespace App\Exports;

use Modules\Common\Entities\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;

use Illuminate\Support\Collection;

class OrdersExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:W1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'Đơn hàng',
            'Link sp',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $filter = $this->filter;
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
        $data = $query->get(['id'])->toArray();

        $orders = [];
        foreach ($data as $order) {
            foreach ($order['cart'] as $key => $cart) {
                if (!$key) {
                    $orders[] = array(
                        'id' => $order['id'],
                        'link' => $cart['pro_link']
                    );
                } else {
                    $orders[] = array(
                        'id' => '',
                        'link' => $cart['pro_link']
                    );
                }
            }
        }

        return collect($orders);
    }
}
