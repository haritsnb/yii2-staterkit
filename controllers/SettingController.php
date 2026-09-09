<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\models\AppSetting;

class SettingController extends BaseAdminController
{
    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/settings');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Endpoint API AJAX: Mengambil Pengaturan Saat Ini
     */
    public function actionGetData(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $mode    = AppSetting::getValue('register_mode', 'always_active');
        $startAt = AppSetting::getValue('register_start_at');
        $endAt   = AppSetting::getValue('register_end_at');

        return $this->asJson([
            'status' => 'success',
            'settings' => [
                'register_mode'     => $mode,
                'register_start_at' => $startAt ? gmdate('Y-m-d\TH:i:s\Z', strtotime($startAt)) : null,
                'register_end_at'   => $endAt ? gmdate('Y-m-d\TH:i:s\Z', strtotime($endAt)) : null,
                'is_active_now'     => AppSetting::isRegisterAllowed(),
            ]
        ]);
    }

    /**
     * Endpoint Simpan Pengaturan Registrasi Publik
     */
    public function actionSaveRegister(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $mode    = $req['register_mode'] ?? 'always_active';
        $startAt = !empty($req['start_at']) ? gmdate('Y-m-d H:i:s', strtotime($req['start_at'])) : null;
        $endAt   = !empty($req['end_at']) ? gmdate('Y-m-d H:i:s', strtotime($req['end_at'])) : null;

        if (in_array($mode, ['active_schedule', 'inactive_schedule']) && (empty($startAt) || empty($endAt))) {
            return $this->asJson(['status' => 'error', 'message' => 'Tanggal & Jam Mulai dan Selesai wajib diisi untuk mode jadwal.']);
        }

        AppSetting::setValue('register_mode', $mode);
        AppSetting::setValue('register_start_at', $startAt);
        AppSetting::setValue('register_end_at', $endAt);

        return $this->asJson([
            'status'  => 'success',
            'message' => 'Pengaturan registrasi publik berhasil disimpan!'
        ]);
    }
}