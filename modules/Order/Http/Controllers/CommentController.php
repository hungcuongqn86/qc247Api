<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;

class CommentController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mCommentService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function getall(Request $request)
    {
        $input = $request->all();
        try {
            if (empty($input['orderId'])) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }
            $user = $request->user();
            $order = OrderServiceFactory::mOrderService()->findById($input['orderId']);
            if ($order && ($user['type'] == 1) && $order['order']['user_id'] != $user['id']) {
                return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
            }

            return $this->sendResponse(OrderServiceFactory::mCommentService()->getByOrderId($input['orderId']), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function isread(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'Không xác định được đơn hàng!'
        ];

        $user = $request->user();
        $input['user_id'] = $user['id'];
        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
        }

        if (($user['type'] == 1) && $order['order']['user_id'] != $user['id']) {
            return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
        }

        $currentUser = Auth::user();

        try {
            // comment
            $comments = OrderServiceFactory::mCommentService()->getWaitByOrderId($input['order_id'], $user['id']);
            foreach ($comments as $comment) {
                if (($user['type'] == 1) && $comment['is_admin'] == 1) {
                    $commentInput = array(
                        'id' => $comment['id'],
                        'is_read' => 1
                    );
                    OrderServiceFactory::mCommentService()->update($commentInput);
                    $commentUserInput = array(
                        'user_id' => $user['id'],
                        'comment_id' => $comment['id']
                    );
                    OrderServiceFactory::mCommentUsersService()->create($commentUserInput);
                }
                if ($user['type'] == 0) {
                    if ($currentUser->hasRole('admin') || ($comment['is_admin'] == 0)) {
                        $commentInput = array(
                            'id' => $comment['id'],
                            'is_read' => 1
                        );
                        OrderServiceFactory::mCommentService()->update($commentInput);
                        $commentUserInput = array(
                            'user_id' => $user['id'],
                            'comment_id' => $comment['id']
                        );
                        OrderServiceFactory::mCommentUsersService()->create($commentUserInput);
                    }
                }
            }

            return $this->sendResponse($comments, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'Không xác định được đơn hàng!',
            'content.required' => 'Chưa nhập nội dung!'
        ];

        $user = $request->user();
        $input['user_id'] = $user['id'];
        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            // Order
            $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
            if (empty($order)) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }

            $create = OrderServiceFactory::mCommentService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
