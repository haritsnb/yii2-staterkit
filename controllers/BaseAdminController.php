<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Base Controller untuk SEMUA halaman Admin / Panel
 * Mengamankan akses hanya untuk user yang sudah login dan mencegah browser caching setelah logout.
 */
abstract class BaseAdminController extends Controller
{
    public $layout = '@app/views/layouts/adminlte/main';

    public function behaviors(): array
    {
        return [
            // 1. FILTER ACCESS CONTROL: Hanya izinkan user yang terotentikasi (@)
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // '@' = hanya user yang sudah login
                    ],
                ],
                // Jika belum login / sudah logout, otomatis redirect ke /auth/login
                'denyCallback' => function ($rule, $action) {
                    return Yii::$app->response->redirect(['/auth/login']);
                },
            ],
        ];
    }

    /**
     * 2. ANTI-CACHE HEADERS: Mencegah halaman admin tersimpan di cache browser
     * Saat user klik tombol 'Back' setelah logout, browser dipaksa meminta ulang ke server
     * dan langsung dilempar ke halaman login.
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Set header anti-cache pada semua response halaman admin
        $headers = Yii::$app->response->headers;
        $headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $headers->set('Pragma', 'no-cache');
        $headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        return true;
    }
}