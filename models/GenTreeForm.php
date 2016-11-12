<?php
/**
 * Created by PhpStorm.
 * User: avs
 * Date: 11/1/16
 * Time: 11:03 PM
 */

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class GenTreeForm extends Model
{
    public $nodes_cnt;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nodes_cnt'], 'required'],
            [['nodes_cnt'], 'integer'],
            [['nodes_cnt'], 'compare', 'compareValue' => 1, 'operator' => '>=', 'type' => 'number'],
            ];
    }

    public function attributeLabels()
    {
        return [
            'nodes_cnt' => 'Количество узлов',
        ];
    }


}
