<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap\Alert;

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="container">
        <?php Pjax::begin(); ?>
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <?= $this->render('_gen_tree_form', ['form_model' => $form_model ]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">

                <?php
                if ( $msg = Yii::$app->session->getFlash('errors') ) :
                    echo Alert::widget([ 'options' => [
                        'class' => 'alert-warning',
                    ],
                        'body' => $msg, ]);
                elseif( !is_null($tree_model) ): ?>
                    <div id="tree">
                        <ul>
                            <?php
                            $lvl = 0;
                            foreach( $tree_model as $k => $node ):
                                if( $lvl == $node->level ):
                                    echo ( $k != 0 ? '</li>' : '' ) . '<li>' . $node->name;
                                elseif( $lvl < $node->level ):
                                    $lvl = $node->level;
                                    echo '<ul><li>' . $node->name;
                                elseif( $lvl > $node->level ):
                                    for( $i = $node->level; $i < $lvl; $i++ )
                                        echo '</li></ul>';

                                    echo '<li>' . $node->name;
                                    $lvl = $node->level;
                                endif;
                            endforeach;

                            for( $i = $lvl; $i > 0; $i-- )
                                echo '</li></ul>';
                            echo '</li>';
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php Pjax::end(); ?>
    </div>
    <?php
    $this->registerCssFile(Yii::getAlias('@web') . '/css/easyTree.css');
    $this->registerJsFile(Yii::getAlias('@web') . '/js/easyTree.js', ['depends' => ['yii\web\YiiAsset']]);
    $this->registerJs('jQuery(function($) {
         $("#tree").EasyTree();
         
         $(document).on("pjax:complete", function() {
          $("#tree").EasyTree();
        })
    });');

    ?>

</div>
