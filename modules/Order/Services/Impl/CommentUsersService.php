<?php

namespace Modules\Order\Services\Impl;

use Modules\Common\Entities\CommentUsers;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\ICommentUsersService;
use Illuminate\Support\Facades\DB;

class CommentUsersService extends CommonService implements ICommentUsersService
{
    protected function getDefaultModel()
    {
        return CommentUsers::getTableName();
    }

    protected function getDefaultClass()
    {
        return CommentUsers::class;
    }

    public function search($filter)
    {
        return [];
    }

    public function create($arrInput)
    {
        $model = new CommentUsers($arrInput);
        DB::beginTransaction();
        try {
            $model->save();
            DB::commit();
            return $model;
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
            $order = CommentUsers::find($id);
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
