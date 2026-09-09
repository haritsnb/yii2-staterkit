<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    // Gunakan layout auth (layout bersih tanpa sidebar) untuk halaman error
    public $layout = '@app/views/layouts/adminlte/auth';

    public function actions(): array
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
                'view'  => 'error', // views/site/error.php
            ],
        ];
    }

    /**
     * Redirect jika ada yang membuka /site atau /site/index
     */
    public function actionIndex()
    {
        return $this->redirect(['/dashboard/index']);
    }
}