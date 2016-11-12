<?php
/**
 * Created by PhpStorm.
 * User: avs
 * Date: 11/6/16
 * Time: 1:08 PM
 */

use yii\helpers\Html;
use yii\bootstrap\Alert;
?>
<h3>Ветвь дерева</h3>
<div class="container">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <?php if ( $msg = Yii::$app->session->getFlash('errors') ) :
                      echo Alert::widget([ 'options' => [
                            'class' => 'alert-warning',
                          ],
                          'body' => $msg,
                          ]);
                  endif; ?>
                  <?php if( !empty($tree_model) ): ?>
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
</div>
<?php
$this->registerCssFile(Yii::getAlias('@web') . '/css/easyTree.css');
$this->registerJsFile(Yii::getAlias('@web') . '/js/easyTree.js', ['depends' => ['yii\web\YiiAsset']]);
$this->registerJs('jQuery(function($) {
         $("#tree").EasyTree();
    });');

?>
