<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cars".
 *
 * @property int $id
 * @property string $maker
 * @property string $model
 * @property string $color
 * @property string $number
 * @property bool|null $in_parking
 */

class Cars extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'cars';
    }
    
    public function rules()
    {
        return [
            [['maker', 'model', 'color', 'number'], 'required'],
            [['in_parking'], 'boolean'],
            [['maker', 'model', 'color', 'number'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'maker'      => Yii::t('app', 'Maker'),
            'model'      => Yii::t('app', 'Model'),
            'color'      => Yii::t('app', 'Color'),
            'number'     => Yii::t('app', 'Number'),
            'in_parking' => Yii::t('app', 'In Parking'),
        ];
    }
    
    public function getClients()
    {
        return $this->hasOne(Clients::class, ['id' => 'id_client'])
            ->viaTable('client_car_ref', ['id_car' => 'id']);
    }
}