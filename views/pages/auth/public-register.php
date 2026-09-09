<?php

/** @var yii\web\View $this */
/** @var app\models\RegisterForm $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Daftar Akun Baru - AdminLTE 3.2';
$registerUrl = Url::to(['/auth/register']);
$loginUrl    = Url::to(['/auth/login']);
?>

<style>
/* Kontainer Responsif Dinamis */
.register-wrapper {
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
}
.register-card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
}
/* Password Strength Indicator */
.strength-meter {
    height: 5px;
    border-radius: 3px;
    background-color: #e9ecef;
    margin-top: 6px;
    overflow: hidden;
}
.strength-meter-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background-color 0.3s ease;
}
</style>

<div class="register-wrapper">

    <!-- Tombol Kembali ke Login di Bagian Atas -->
    <div class="mb-3">
        <a href="<?= $loginUrl ?>" class="btn btn-outline-secondary btn-sm font-weight-bold bg-white shadow-xs" style="border-radius: 20px; padding: 6px 16px;">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Halaman Login
        </a>
    </div>

    <!-- Card Registrasi Utama -->
    <div class="card card-outline card-primary register-card mb-4">
        
        <!-- Header Logo -->
        <div class="card-header text-center bg-white py-4 border-bottom">
            <a href="#" class="h1 font-weight-bold text-dark mb-0"><b>Admin</b>LTE<span class="text-primary">.</span></a>
            <p class="text-muted text-sm mt-1 mb-0 font-weight-500">Formulir Pendaftaran Akun Publik</p>
        </div>

        <div class="card-body p-4 bg-white">

            <?php $form = ActiveForm::begin([
                'id' => 'form-public-register',
                'enableClientValidation' => true,
                'validateOnBlur' => false,
                'validateOnChange' => false,
                'validateOnSubmit' => true,
                'fieldConfig' => [
                    'errorOptions' => ['class' => 'text-danger small font-weight-bold mt-1'],
                ],
            ]); ?>

            <!-- 1. Nama Lengkap -->
            <?= $form->field($model, 'name', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Nama Lengkap *</label>
                    <div class=\"input-group\">
                        {input}
                        <div class=\"input-group-append\">
                            <div class=\"input-group-text bg-light\"><span class=\"fas fa-id-card text-muted\"></span></div>
                        </div>
                    </div>
                    {error}
                "
            ])->textInput([
                'class' => 'form-control',
                'placeholder' => 'Contoh: Budi Santoso',
                'autofocus' => true,
            ])->label(false) ?>

            <!-- 2. Username -->
            <?= $form->field($model, 'username', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Username *</label>
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
                'placeholder' => 'Contoh: budisantoso',
                'autocomplete' => 'off',
            ])->label(false) ?>

            <!-- 3. Alamat Email -->
            <?= $form->field($model, 'email', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Alamat Email *</label>
                    <div class=\"input-group\">
                        {input}
                        <div class=\"input-group-append\">
                            <div class=\"input-group-text bg-light\"><span class=\"fas fa-envelope text-muted\"></span></div>
                        </div>
                    </div>
                    {error}
                "
            ])->textInput([
                'type' => 'email',
                'class' => 'form-control',
                'placeholder' => 'nama@domain.com',
                'autocomplete' => 'email',
            ])->label(false) ?>

            <!-- 4. Password + Strength Meter -->
            <?= $form->field($model, 'password', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Password *</label>
                    <div class=\"input-group\">
                        {input}
                        <div class=\"input-group-append\">
                            <button type=\"button\" class=\"btn btn-outline-secondary bg-light border-left-0 toggle-pass\" data-target=\"#registerform-password\" tabindex=\"-1\">
                                <span class=\"far fa-eye text-muted\"></span>
                            </button>
                        </div>
                    </div>
                    <div class=\"strength-meter\"><div class=\"strength-meter-fill\" id=\"strength-bar\"></div></div>
                    <div class=\"d-flex justify-content-between align-items-center mt-1\">
                        <small class=\"font-weight-bold text-muted\" id=\"strength-label\">Kekuatan: -</small>
                        <small class=\"text-muted\">Min. 6 karakter kombinasi</small>
                    </div>
                    {error}
                "
            ])->passwordInput([
                'class' => 'form-control border-right-0',
                'placeholder' => 'Ketik password Anda',
                'autocomplete' => 'new-password',
            ])->label(false) ?>

            <!-- 5. Ulangi Password -->
            <?= $form->field($model, 'password_repeat', [
                'options' => ['class' => 'form-group mb-3'],
                'template' => "
                    <label class=\"font-weight-bold text-sm text-dark mb-1\">Ulangi Password *</label>
                    <div class=\"input-group\">
                        {input}
                        <div class=\"input-group-append\">
                            <button type=\"button\" class=\"btn btn-outline-secondary bg-light border-left-0 toggle-pass\" data-target=\"#registerform-password_repeat\" tabindex=\"-1\">
                                <span class=\"far fa-eye text-muted\"></span>
                            </button>
                        </div>
                    </div>
                    <small class=\"text-danger font-weight-bold\" id=\"match-feedback\" style=\"display: none;\">Konfirmasi password tidak cocok!</small>
                    {error}
                "
            ])->passwordInput([
                'class' => 'form-control border-right-0',
                'placeholder' => 'Ketik ulang password di atas',
                'autocomplete' => 'new-password',
            ])->label(false) ?>

            <!-- 6. Syarat & Ketentuan -->
            <div class="form-group mb-4">
                <div class="custom-control custom-checkbox">
                    <?= Html::activeCheckbox($model, 'terms', [
                        'custom' => true,
                        'label' => '<span class="text-sm text-dark font-weight-500">Saya menyetujui <a href="javascript:void(0)" class="text-primary font-weight-bold">Syarat & Ketentuan</a> yang berlaku</span>',
                        'encode' => false,
                    ]) ?>
                </div>
                <?= Html::error($model, 'terms', ['class' => 'text-danger small font-weight-bold mt-1']) ?>
            </div>

            <!-- Tombol Submit -->
            <div class="mb-3">
                <?= Html::submitButton('<i class="fas fa-user-plus mr-1"></i> Daftar Akun Sekarang', [
                    'class' => 'btn btn-primary btn-block font-weight-bold py-2 shadow-xs',
                    'style' => 'border-radius: 8px; font-size: 1rem;'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <!-- Link Masuk di Bagian Bawah -->
            <div class="text-center pt-3 border-top">
                <p class="text-muted text-sm mb-0">
                    Sudah memiliki akun? 
                    <a href="<?= $loginUrl ?>" class="text-primary font-weight-bold" tabindex="0">
                        Masuk di sini
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3500" };

    // Toggle Eye Show/Hide Password
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

    // Indikator Kekuatan Password Dinamis
    $('#registerform-password').on('input', function() {
        const val = $(this).val();
        let score = 0;
        if (val.length >= 6) score += 20;
        if (val.length >= 10) score += 20;
        if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score += 20;
        if (/\d/.test(val)) score += 20;
        if (/[^a-zA-Z0-9]/.test(val)) score += 20;

        const bar = $('#strength-bar');
        const label = $('#strength-label');
        bar.css('width', score + '%');

        if (score <= 20) { bar.css('background-color', '#dc3545'); label.text('Kekuatan: Sangat Lemah').css('color', '#dc3545'); }
        else if (score <= 40) { bar.css('background-color', '#fd7e14'); label.text('Kekuatan: Lemah').css('color', '#fd7e14'); }
        else if (score <= 60) { bar.css('background-color', '#ffc107'); label.text('Kekuatan: Sedang').css('color', '#ffc107'); }
        else if (score <= 80) { bar.css('background-color', '#20c997'); label.text('Kekuatan: Kuat').css('color', '#20c997'); }
        else { bar.css('background-color', '#28a745'); label.text('Kekuatan: Sangat Kuat').css('color', '#28a745'); }
    });

    // Validasi Cocok Password Real-time
    $('#registerform-password_repeat, #registerform-password').on('input', function() {
        const p1 = $('#registerform-password').val();
        const p2 = $('#registerform-password_repeat').val();
        if (p2.length > 0 && p1 !== p2) {
            $('#match-feedback').show();
        } else {
            $('#match-feedback').hide();
        }
    });

    // Submit AJAX dengan Notifikasi SweetAlert2
    $('#form-public-register').on('submit', function(e) {
        e.preventDefault();
        const p1 = $('#registerform-password').val();
        const p2 = $('#registerform-password_repeat').val();

        if (p1 !== p2) {
            toastr.error('Password dan ulangi password tidak cocok!');
            return;
        }

        $.ajax({
            url: '{$registerUrl}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pendaftaran Berhasil!',
                        text: res.message,
                        confirmButtonColor: '#007bff',
                        confirmButtonText: '<i class="fas fa-sign-in-alt mr-1"></i> Menuju Halaman Login'
                    }).then(() => {
                        window.location.href = res.redirect;
                    });
                } else {
                    let errMsg = Object.values(res.errors).join('<br>');
                    toastr.error(errMsg, 'Gagal Mendaftar');
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan koneksi server.', 'Error');
            }
        });
    });
JS
);
?>