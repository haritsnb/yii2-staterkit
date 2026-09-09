<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\AppSetting;

$this->title = 'Masuk ke Akun - AdminLTE 3.2';
$isRegisterActive = AppSetting::isRegisterAllowed();
?>

<div class="login-box shadow-lg" style="width: 400px; border-radius: 16px; overflow: hidden;">
    <div class="card card-outline card-primary mb-0 border-0">
        
        <!-- Header Card / Brand -->
        <div class="card-header text-center bg-white py-4 border-bottom">
            <a href="#" class="h1 font-weight-bold text-dark mb-0"><b>Admin</b>LTE<span class="text-primary">.</span></a>
            <p class="text-muted text-sm mt-1 mb-0 font-weight-500">Silakan masuk menggunakan akun Anda</p>
        </div>

        <div class="card-body login-card-body p-4 bg-white">

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'enableClientValidation' => true,
                'validateOnBlur' => false,   // <-- MENCEGAH VALIDASI ERROR MUNCUL SAAT KLIK LINK DAFTAR
                'validateOnChange' => false, // <-- MENCEGAH VALIDASI OTOMATIS SAAT BERPINDAH FOKUS
                'validateOnSubmit' => true,  // <-- HANYA VALIDASI KETIKA TOMBOL MASUK DITEKAN
                'fieldConfig' => [
                    'errorOptions' => ['class' => 'text-danger small font-weight-bold mt-1'],
                ],
            ]); ?>

            <!-- Field 1: Username atau Email -->
            <?= $form->field($model, 'username', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Username atau Email</label>
                    <div class=\"input-group\">
                        {input}
                        <div class=\"input-group-append\">
                            <div class=\"input-group-text bg-light\"><span class=\"fas fa-user text-muted\"></span></div>
                        </div>
                    </div>
                    {error}
                "
            ])->textInput([
                'class' => 'form-control',
                'placeholder' => 'Ketik username atau email',
                'autofocus' => true,
                'autocomplete' => 'username',
            ])->label(false) ?>

            <!-- Field 2: Password + Toggle Eye -->
            <?= $form->field($model, 'password', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Password</label>
                    <div class=\"input-group\">
                        {input}
                        <div class=\"input-group-append\">
                            <button type=\"button\" class=\"btn btn-outline-secondary bg-light border-left-0 toggle-pass\" data-target=\"#loginform-password\" tabindex=\"-1\">
                                <span class=\"far fa-eye text-muted\"></span>
                            </button>
                        </div>
                    </div>
                    {error}
                "
            ])->passwordInput([
                'class' => 'form-control border-right-0',
                'placeholder' => 'Ketik password Anda',
                'autocomplete' => 'current-password',
            ])->label(false) ?>

            <!-- Ingat Saya -->
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <?= Html::activeCheckbox($model, 'rememberMe', [
                            'custom' => true,
                            'label' => '<span class="text-sm text-dark font-weight-500">Ingat Saya di Perangkat Ini</span>',
                            'encode' => false,
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit Masuk -->
            <div class="mb-3">
                <?= Html::submitButton('<i class="fas fa-sign-in-alt mr-1"></i> Masuk ke Dashboard', [
                    'class' => 'btn btn-primary btn-block font-weight-bold py-2 shadow-xs',
                    'style' => 'border-radius: 8px;'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <!-- Link Registrasi Publik (Hanya muncul jika registrasi dibuka) -->
            <?php if ($isRegisterActive): ?>
                <div class="text-center pt-3 border-top">
                    <p class="text-muted text-sm mb-0">
                        Belum memiliki akun? 
                        <a href="<?= Url::to(['/auth/register']) ?>" class="text-primary font-weight-bold" tabindex="0">
                            Daftar Akun Baru
                        </a>
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    // Toggle Show/Hide Password
    $('.toggle-pass').on('click', function() {
        const target = $($(this).data('target'));
        const icon = $(this).find('span');
        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('fa-eye text-muted').addClass('fa-eye-slash text-primary');
        } else {
            target.attr('type', 'password');
            icon.removeClass('fa-eye-slash text-primary').addClass('fa-eye text-muted');
        }
    });
JS
);
?>