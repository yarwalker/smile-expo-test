<?php

namespace app\controllers;

use app\models\Tree;
use Yii;
use app\models\GenTreeForm;


class TreeController extends \yii\web\Controller
{
    public function actionTest() {
        $brackets_arr = $result = [];
        for( $i = 0; $i < 5; $i++ ):
            $brackets_arr[] = '(';
            $brackets_arr[] = ')';
        endfor;

        foreach( array_slice( $brackets_arr, 3, 4 ) as $val )
            array_push( $result, $val );

        var_dump($result);
    }

    public function actionIndex()
    {
        $form_model = new GenTreeForm();
        $tree_model = Tree::find()->addOrderBy('left')->all();

        if( $form_model->load(Yii::$app->request->post()) && $form_model->validate() ):

            // сгненрируем случайную скобочную последовательность
            $nodes_cnt = $form_model->nodes_cnt;
            $brackets_arr = [];
            for( $i = 0; $i < $nodes_cnt; $i++ ):
                $brackets_arr[] = '(';
                $brackets_arr[] = ')';
            endfor;

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

            array_unshift($result, '(');
            array_push($result, ')');

            //echo 'result: ' . implode(' ', $result).'<br>';

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

            //echo '<pre>'.print_r($tree,true).'</pre>';

            // очищаем таблицу дерева
            Tree::deleteAll();
            $errors = '';
            foreach( $tree as $k => $node ):
                //echo '<pre>'.print_r($node,true).'</pre><hr>';
                $model = new Tree();
                $model->name = $k == 0 ? 'root' : (string)$k;
                $model->level = $node['level'];
                $model->left = $node['left'];
                $model->right = $node['right'];
                $model->par_id = 0;
                if( !$model->save() ):
                    foreach( $model->getErrors() as $err ):
                        foreach( $err as $row ):
                            $errors .= $row . '<br>';
                        endforeach;
                    endforeach;
                    //echo '<pre>'.print_r($model->getErrors(), true).'</pre>';
                endif;
            endforeach;

            // если есть ошибки при сохранении узлов дерева, то сохраним их в сессии
            empty($errors) || Yii::$app->session->setFlash('errors', $errors);

            $tree_model = Tree::find()
                ->orderBy('left')
                ->all();

            return $this->render('index', [ 'form_model' => $form_model, 'tree_model' => $tree_model ]);
        else:
            return $this->render('index', [ 'form_model' => $form_model, 'tree_model' => $tree_model ]);
        endif;
    }

}
