<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\StorageManager;

class StorageController extends Controller
{
    /**
     * Menyajikan file gambar/dokumen dari project/storages/{folder}/{filename}
     */
    public function actionFile(string $folder, string $filename)
    {
        // Gabungkan folder dan filename
        $relativePath = $folder . '/' . $filename;
        $absPath = StorageManager::getAbsolutePath($relativePath);

        if (!$absPath || !file_exists($absPath) || !is_file($absPath)) {
            throw new NotFoundHttpException('File gambar tidak ditemukan.');
        }

        // Cache browser selama 30 hari untuk performa tinggi
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=2592000');

        return Yii::$app->response->sendFile($absPath, $filename, [
            'inline' => true, // Ditampilkan langsung di browser
        ]);
    }
}