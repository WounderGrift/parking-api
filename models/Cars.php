<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cars".
 *
 * @property int       $id
 * @property int       $id_client
 * @property string    $maker
 * @property string    $model
 * @property string    $color
 * @property string    $number
 * @property bool|null $in_parking
 *
 * @property Clients $client
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
            [['id_client', 'maker', 'model', 'color', 'number'], 'required'],
            [['id_client'], 'integer'],
            [['in_parking'], 'boolean'],
            [['maker', 'model', 'color', 'number'], 'string', 'max' => 255],
            [['id_client'], 'exist', 'skipOnError' => true, 'targetClass' => Clients::class,
                 'targetAttribute' => ['id_client' => 'id']],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'id_client'  => Yii::t('app', 'Id Client'),
            'maker'      => Yii::t('app', 'Maker'),
            'model'      => Yii::t('app', 'Model'),
            'color'      => Yii::t('app', 'Color'),
            'number'     => Yii::t('app', 'Number'),
            'in_parking' => Yii::t('app', 'In Parking'),
        ];
    }
    
    public function getClient()
    {
        return $this->hasOne(Clients::class, ['id' => 'id_client']);
    }
}