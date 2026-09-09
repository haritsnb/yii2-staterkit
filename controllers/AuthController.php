<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\AuthSession;

class AuthController extends Controller
{
    public $layout = '@app/views/layouts/adminlte/auth';

    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/auth');
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post', 'get'],
                ],
            ],
        ];
    }

    /**
     * Halaman Login
     */
    public function actionLogin()
    {
        // Jika sudah login, langsung ke dashboard
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard/index']);
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // REDIRECT KE DASHBOARD SETELAH LOGIN
            return $this->redirect(['/dashboard/index']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Halaman Registrasi Publik
     */
    public function actionRegister()
    {
        // Blokir akses jika registrasi ditutup
        if (!\app\models\AppSetting::isRegisterAllowed()) {
            Yii::$app->session->setFlash('error', 'Pendaftaran akun baru saat ini sedang ditutup.');
            return $this->redirect(['/auth/login']);
        }

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard/index']);
        }

        $model = new RegisterForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($user = $model->register()) {
                return [
                    'status'   => 'success',
                    'message'  => 'Registrasi berhasil! Silakan masuk dengan akun Anda.',
                    'redirect' => \yii\helpers\Url::to(['/auth/login'])
                ];
            }
            return [
                'status' => 'error',
                'errors' => $model->getErrors()
            ];
        }

        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            Yii::$app->session->setFlash('success', 'Registrasi berhasil! Silakan login.');
            return $this->redirect(['/auth/login']);
        }

        return $this->render('public-register', [
            'model' => $model,
        ]);
    }

    /**
     * LOGOUT: Hapus Sesi di Database & Redirect ke Halaman Login
     */
    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            // Hapus sesi aktif dari tabel database
            $currentSessionId = Yii::$app->session->getId();
            AuthSession::deleteAll(['id' => $currentSessionId]);

            // Hapus sesi di browser / PHP
            Yii::$app->user->logout(true); // destroySession = true
        }

        return $this->redirect(['/auth/login']);
    }
}