<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\Model;

class BaseEntity extends Model
{
    /**
     * @var bool
     */
    public $skip = false;

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
        static::saving(function ($model) {
            if ($model->skip) {
                return true;
            }

            if ($model->id != null && $model->id != 0) {
                $model->updated_at = date('Y-m-d H:i:s');
            } else {
                // create: you can modify attribute value
                $model->created_at = date('Y-m-d H:i:s');
                $model->updated_at = date('Y-m-d H:i:s');
            }

            if($model->getTable()==='spt_sim'){
                if(!empty($model->sim_number_standard)){
                    $model->yinyang = get_yinyang($model->sim_number_standard);
                    $model->phase = get_phase($model->sim_number_standard);
                    $model->scores = get_scores($model->sim_number_standard);
                }
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