<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class ComplainController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mComplainService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function getByOrder(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mComplainService()->getByOrder($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mComplainService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function types()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mComplainService()->types(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'type' => 'required',
            'money_request' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'type.required' => 'type.required',
            'money_request.required' => 'money_request.required',
            'content.required' => 'content.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $user = $request->user();
            $input['user_id'] = $user['id'];

            $create = OrderServiceFactory::mComplainService()->create($input);
            if (!empty($create)) {
                $arrCart = $input['complain_products'];
                foreach ($arrCart as $product) {
                    $complainProduct = array(
                        'complain_id' => $create['id'],
                        'cart_id' => $product['cart']['id'],
                        'is_deleted' => 0
                    );
                    $complainProductCreate = OrderServiceFactory::mComplainProductService()->create($complainProduct);

                    // Update media
                    if ($complainProductCreate && !empty($product['media'])) {
                        foreach ($product['media'] as $media) {
                            $fileinput = array(
                                'id' => $media['id'],
                                'item_id' => $complainProductCreate['id'],
                                'table' => 'complain_products'
                            );
                            CommonServiceFactory::mMediaService()->update($fileinput);
                        }
                    }
                }
            }
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'type' => 'required',
            'money_request' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'type.required' => 'type.required',
            'money_request.required' => 'money_request.required',
            'content.required' => 'content.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $update = OrderServiceFactory::mComplainService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
