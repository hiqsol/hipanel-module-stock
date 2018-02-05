<?php

/*
 * Stock Module for Hipanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-stock
 * @package   hipanel-module-stock
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\stock\models;

use hipanel\base\Model as YiiModel;
use hipanel\base\ModelTrait;
use Yii;

/**
 * Class ModelGroup
 *
 * @property int $id
 * @property string $name
 * @property string $descr
 * @property int[] $limits
 * @property int[] $model_ids
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class ModelGroup extends YiiModel
{
    use ModelTrait;

    public static function tableName()
    {
        return 'modelgroup';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'num'], 'integer'],
            [['name', 'descr'], 'string'],
            [['model_ids', 'limits'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return $this->mergeAttributeLabels($this->getSupportedLimitTypes());
    }

    public function getSupportedLimitTypes()
    {
        return [
            'dtg' => Yii::t('hipanel:stock', 'DTG'),
            'sdg' => Yii::t('hipanel:stock', 'SDG'),
            'm3' => Yii::t('hipanel:stock', 'M3'),
            'twr' => Yii::t('hipanel:stock', 'TWR'),
        ];
    }

    public function getModels()
    {
        return $this->hasMany(Model::class, ['id' => 'model_ids']);
    }

    public function getStocks()
    {
        return array_keys($this->limits);
    }
}
