<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Carbon\Carbon;

class SysController extends CommonController
{
    public function index(Request $request)
    {

    }

    public function getSysConfig($key = null, Request $request)
    {
        $signal = $request->header('signal');
        // $token = (new Token())->Unique('vs_partner', 'token', 60);
        // echo $token;exit;
        try {
            if (!empty($signal)) {
                $expired = $request->header('timestamp');
                $expired_on = Carbon::createFromFormat('Y-m-d H:i:s', $expired)->addMinutes(3);
                $day = Carbon::now();
                if ($day->gt($expired_on)) {
                    return $this->sendError('Error', 'Signal expired', $code = 403);
                }

                $partners = CommonServiceFactory::mParnerService()->getAll();
                foreach ($partners as $partner) {
                    $eSignal = md5($partner['email'] . $partner['token'] . $expired);
                    // echo '<br>'.$eSignal;
                    if ($signal === $eSignal) {
                        $options = $partner['arr_options'];
                        if (!empty($key)) {
                            return $this->sendResponse($options[$key], 'Successfully.');
                        } else {
                            return $this->sendResponse($options, 'Successfully.');
                        }
                    }
                }
                // exit;
                return $this->sendError('Error', 'Signal invalid', $code = 403);
            } else {
                $values = config('sysconfig.values');
                if (!empty($key)) {
                    return $this->sendResponse($values[$key], 'Successfully.');
                } else {
                    return $this->sendResponse($values, 'Successfully.');
                }
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
