<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\Comment;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\ICommentService;
use Illuminate\Support\Facades\DB;

class CommentService extends CommonService implements ICommentService
{
    protected function getDefaultModel()
    {
        return Comment::getTableName();
    }

    protected function getDefaultClass()
    {
        return Comment::class;
    }

    public function search($filter)
    {
        return [];
    }

    public function getByOrderId($orderId)
    {
        $query = Comment::where('is_deleted', '=', 0)->where('order_id', '=', $orderId);
        $query->orderBy('id', 'asc');
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function getWaitByOrderId($orderId, $userid)
    {
        $query = Comment::where('is_deleted', '=', 0)->where('order_id', '=', $orderId);
        $query->whereDoesntHave('CommentUsers', function ($q) use ($userid) {
            $q->where('user_id', '=', $userid);
        });
        $query->orderBy('id', 'asc');
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function create($arrInput)
    {
        $history = new Comment($arrInput);
        DB::beginTransaction();
        try {
            $history->save();
            DB::commit();
            return $history;
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
            $order = Comment::find($id);
            $order->update($arrInput);
            DB::commit();
            return $order;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
