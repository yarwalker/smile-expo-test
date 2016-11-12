<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Tree;
use app\models\GenTreeForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Отображает форму генерации произвольного дерева и само дерево.
     * По сабмиту формы генерирует новое произвольное дерево и отображает его.
     * @return string
     */
    public function actionIndex()
    {
        $errors = '';
        $form_model = new GenTreeForm();
        $tree_model = Tree::find()->addOrderBy('left')->all();


        if( $form_model->load(Yii::$app->request->post()) && $form_model->validate() ):

            $tree_model = Tree::makeTree($form_model->nodes_cnt - 1);

            return $this->render('index', [ 'form_model' => $form_model, 'tree_model' => $tree_model ]);
        else:
            foreach( $form_model->getErrors() as $err ):
                foreach( $err as $row ):
                    $errors .= $row . '<br>';
                endforeach;
            endforeach;

            empty($errors) || Yii::$app->session->setFlash('errors', $errors);

            return $this->render('index', [ 'form_model' => $form_model, 'tree_model' => $tree_model ]);
        endif;
    }

    /**
     * @return Action
     */
    public function actionThread($ids)
    {
        if( strpos($ids, ',') === false ):
            $branches = Tree::getBranch([(int)$ids]);
        else:
            $arr_ids = explode(',', $ids);
            $branches = Tree::getBranch($arr_ids);
        endif;

        return $this->render('thread', [ 'tree_model' => $branches ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
