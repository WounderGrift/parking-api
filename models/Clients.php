<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clients".
 *
 * @property int         $id
 * @property string      $full_name
 * @property string      $gender
 * @property string      $phone
 * @property string|null $address
 *
 * @property Cars[] $cars
 */
class Clients extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'clients';
    }
    
    public function rules()
    {
        return [
            [['full_name', 'gender', 'phone'], 'required'],
            [['gender'], 'string'],
            [['full_name', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'unique'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'full_name' => Yii::t('app', 'Full Name'),
            'gender'    => Yii::t('app', 'Gender'),
            'phone'     => Yii::t('app', 'Phone'),
            'address'   => Yii::t('app', 'Address'),
        ];
    }
    
    public function getCars()
    {
        return $this->hasMany(Cars::class, ['id_client' => 'id']);
    }
}