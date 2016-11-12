<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tree".
 *
 * @property integer $id
 * @property string $name
 * @property integer $level
 * @property integer $left
 * @property integer $right
 * @property integer $par_id
 */
class Tree extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tree';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level', 'left', 'right'], 'integer'],
            [['name'], 'string', 'max' => 5],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'level' => 'Level',
            'left' => 'Left',
            'right' => 'Right',
        ];
    }

    public static function makeTree($nodes_cnt) {
        // сгненрируем случайную скобочную последовательность
        // создаем массив из пар скобок
        $brackets_arr = [];
        for( $i = 0; $i < $nodes_cnt; $i++ ):
            $brackets_arr[] = '(';
            $brackets_arr[] = ')';
        endfor;

        // перемешиваем массив произвольным образом
        shuffle($brackets_arr);

        $stack = $result = [];
        $balance = $prev = 0;

        foreach( $brackets_arr as $k => $val ):
            if( $val == '(' ):
                $balance++;
            else:
                $balance--;
            endif;

            if( $balance == 0 ):
                if( $brackets_arr[$prev] == '(' ):
                    foreach( array_slice( $brackets_arr, $prev, ($k + 1 - $prev) ) as $val )
                        array_push( $result, $val );
                else:
                    array_push( $result, '(' );
                    $arr = [];
                    foreach( array_slice( $brackets_arr, ($prev + 1), ($k - $prev - 1) ) as $val )
                        if( $val == '(' ):
                            array_push($arr, ')');
                        else:
                            array_push($arr, '(');
                        endif;
                    array_push($stack, $arr);
                endif;
                $prev = $k + 1;
            endif;
        endforeach;

        foreach( array_reverse($stack) as $item ):
            array_push($result, ')');
            foreach( $item as $val ){
                array_push($result, $val);
            }
        endforeach;

        // добавляем узел для корня дерева
        array_unshift($result, '(');
        array_push($result, ')');

        // сщоздаем массив узлов для последующей загрузки в базу
        $lvl = 0;
        $left = 1;
        $tree = $parents = [];
        foreach( $result as $v ):
            if( $v == '(' ):
                $tree[] = [
                    'level' => $lvl,
                    'left' => $left,
                    'right' => ($left + 1),
                    //'par_id' =>
                ];
                $lvl++;
                $left ++;
                $parents[] = count($tree) - 1;
            else:
                $lvl--;
                $left++;
                if( !is_null(array_pop($parents)) )
                    $tree[end($parents)]['right'] = $tree[end($parents)]['left'] + ((count($tree)-1) - end($parents)) * 2 + 1;
            endif;
        endforeach;

        $tree[0]['right'] = count($tree) * 2;

        // очищаем таблицу дерева и заносим новые узлы
        //self::deleteAll(); // работает дольше чем truncate и не сбрасывает ключи
        Yii::$app->db->createCommand()->truncateTable(self::tableName())->execute();
        $errors = '';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach( $tree as $k => $node ):
                $model = new Tree();

                $model->setAttributes([
                    'name' => (string)$k,
                    'level' => $node['level'],
                    'left' => $node['left'],
                    'right' => $node['right']
                ]);

                $model->save();
            endforeach;

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            //throw $e;
            $errors = $e->getTraceAsString();
        }


        // если есть ошибки при сохранении узлов дерева, то сохраним их в сессии
        empty($errors) || Yii::$app->session->setFlash('errors', $errors);

        return self::find()
            ->orderBy('left')
            ->all();
    }

    public static function getBranch(array $ids) {
        $branch = null;
        $nodes = [];

        switch( count($ids) ):
            case 1:
                if( !is_null( $branch = self::findOne($ids[0]) ) ):
                    $nodes = self::find()
                        ->where(['>=', 'left', $branch->left])
                        ->andWhere(['<=', 'right', $branch->right])
                        ->all();
                else:
                    Yii::$app->session->setFlash('errors', 'В текущем дереве не найдено такой ветки ID#' . $ids[0] . '!');
                    $nodes = self::find()
                        ->orderBy('left')
                        ->all();
                endif;
                break;

            case 0:
                Yii::$app->session->setFlash('errors', 'Отсутствует необходимый параметр!');
                $nodes = self::find()
                    ->orderBy('left')
                    ->all();
                break;

            default:
                $str = '';
                $nodes[] = new self();
                $nodes[0]->id = 0;
                $nodes[0]->name = '0';
                $nodes[0]->level = 0;
                $nodes[0]->left = 1;
                $nodes[0]->right = 0;

                sort($ids);
                foreach( $ids as $id ):
                    if( is_numeric($id) && strpos($id, '.') === false ):
                        if( !is_null( $branch = self::findOne($id) ) ):

                            $arr = self::find()
                                ->where(['>=', 'left', $branch->left])
                                ->andWhere(['<=', 'right', $branch->right])
                                ->all();

                            $lvl_diff = $branch->level - 1;
                            foreach( $arr as &$node ):
                                $node->level = $node->level - $lvl_diff;
                                $nodes[] = $node;
                            endforeach;
                        else:
                            $str .= 'В текущем дереве не найдено ветки ID#' . $id . '!<br>';
                        endif;
                    else:
                        $str .= 'Параметр "' . $id . '" должен быть целым числом!<br>';
                    endif;
                endforeach;

                if( !empty($str) ):
                    Yii::$app->session->setFlash('errors', $str);
                    $nodes = self::find()
                        ->orderBy('left')
                        ->all();
                endif;
        endswitch;

        return $nodes;
    }
}
