<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Carbon\Carbon;

class VersionController extends CommonController
{
    public function index(Request $request)
    {
        $input = $request->all();
        $signal = $request->header('signal');
        $app = isset($input['app_code']) ? $input['app_code'] : 'vs';
        try {

            if (!empty($signal)) {
                $expired = $request->header('timestamp');
                $expired_on = Carbon::createFromFormat('Y-m-d H:i:s', $expired)->addMinutes(3);
                $day = Carbon::now();
                if ($day->gt($expired_on)) {
                    return $this->sendError('Error', 'Signal expired', $code = 403);
                }

                $partners = CommonServiceFactory::mParnerService()->getAll();
                $invalid = false;
                foreach ($partners as $partner) {
                    $eSignal = md5($partner['email'] . $partner['token'] . $expired);
                    // echo '<br>'.$eSignal;
                    if ($signal === $eSignal) {
                        $invalid = true;
                        $version = CommonServiceFactory::mVersionService()->check($app, $partner['id']);
                        if (!empty($version)) {
                            return $this->sendResponse($version, 'Successfully.');
                        }
                    }
                }
                // exit;
                if (!$invalid) {
                    return $this->sendError('Error', 'Signal invalid', $code = 403);
                }
                return $this->sendError('No version exists', null, 200);
            } else {
                $version = CommonServiceFactory::mVersionService()->check($app);
                if (!empty($version)) {
                    return $this->sendResponse($version, 'Successfully.');
                } else {
                    return $this->sendError('No version exists', null, 200);
                }
            }
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mVersionService()->search($input), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mVersionService()->findById($id), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'app_code' => 'required',
                'version_code' => 'required',
                'link_apk' => 'required',
            ];
            $arrMessages = [
                'app_code.required' => 'ERRORS_MS.BAD_REQUEST',
                'version_code.required' => 'ERRORS_MS.BAD_REQUEST',
                'link_apk.required' => 'ERRORS_MS.BAD_REQUEST',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $create = CommonServiceFactory::mVersionService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'app_code' => 'required',
                'version_code' => 'required',
                'link_apk' => 'required',
            ];
            $arrMessages = [
                'app_code.required' => 'ERRORS_MS.BAD_REQUEST',
                'version_code.required' => 'ERRORS_MS.BAD_REQUEST',
                'link_apk.required' => 'ERRORS_MS.BAD_REQUEST',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }
            $update = CommonServiceFactory::mVersionService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function latest($app, $partner)
    {
        try {
            $version = [];
            $partner = CommonServiceFactory::mParnerService()->findByCode($partner);
            if (!empty($partner)) {
                $partner_id = $partner['partner']['id'];
                $version = CommonServiceFactory::mVersionService()->check($app, $partner_id);
            }
            if (!empty($version)) {
                $headers = get_headers($version['link_apk'], true);
                header("Pragma: public", true);
                header("Expires: 0"); // set expiration time
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment; filename=" . basename($version['link_apk']));
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: " . $headers['Content-Length']);
                echo readfile($version['link_apk']); // GET request
                exit;
            }
            return $this->sendError('Error', 'NOT VERSION!');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
