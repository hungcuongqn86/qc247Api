<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\Model;

class BaseEntity extends Model
{
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    protected static function boot()
    {
		parent::boot();
        static::saving(function ($model) {
            if ($model->id != null && $model->id != 0) {
                $model->updated_at = date('Y-m-d H:i:s');
            } else {
                // create: you can modify attribute value
                $model->created_at = date('Y-m-d H:i:s');
                $model->updated_at = date('Y-m-d H:i:s');
            }
			
            return true;
        });

        static::deleting(function ($model) {
            $model->save();
        });
    }

    public function save(array $options = [])
    {
        $result = parent::save($options);
        if (!$result) {
            throw new \Exception('Saving model fails');
        }
        return $result;
    }
}