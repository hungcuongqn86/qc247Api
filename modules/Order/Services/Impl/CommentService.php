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
}
