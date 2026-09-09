<?php

/** @var yii\web\View $this */
/** @var app\models\User|null $user */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;

$this->title = 'Profil Saya';

$getDataUrl          = Url::to(['/profile/get-data']);
$updateInfoUrl       = Url::to(['/profile/update-info']);
$uploadAvatarUrl     = Url::to(['/profile/upload-avatar']);
$deleteAvatarUrl     = Url::to(['/profile/delete-avatar']);
$changePasswordUrl   = Url::to(['/profile/change-password']);
$updateLoginModeUrl  = Url::to(['/profile/update-login-mode']);
$terminateSessionUrl = Url::to(['/profile/terminate-session']);

// Render avatar awal langsung dari PHP agar tidak glitch / delay saat refresh
$initialAvatar = $user ? $user->getAvatarUrl() : User::generateInitialAvatar('Admin', 1);
$initialName   = $user->profile->name ?? ($user->username ?? 'Administrator');
$initialEmail  = $user->email ?? 'admin@example.com';
?>

<style>
/* Modern Interactive Avatar Container */
.avatar-container {
    position: relative;
    width: 125px;
    height: 125px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    border: 3px solid #007bff;
    background-color: #f4f6f9;
}
.avatar-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.avatar-container:hover img {
    transform: scale(1.08);
}
.avatar-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.avatar-container:hover .avatar-overlay {
    opacity: 1;
}
.avatar-overlay i { font-size: 1.3rem; margin-bottom: 2px; }
.avatar-overlay span { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

/* Password Strength Meter */
.strength-meter {
    height: 6px;
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

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold">Profil & Pengaturan Akun</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/dashboard/index']) ?>">Home</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- ================= SISI KIRI: PROFILE CARD ================= -->
            <div class="col-lg-4 col-md-5">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body box-profile text-center">
                        
                        <!-- Avatar Container (Langsung render src PHP untuk hindari glitch) -->
                        <div class="avatar-container mb-3" id="btn-trigger-avatar-modal" title="Klik untuk mengubah foto profil">
                            <img src="<?= $initialAvatar ?>" alt="Avatar" id="user-avatar-img">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Ubah</span>
                            </div>
                        </div>

                        <h4 class="profile-username font-weight-bold mb-1" id="profile-name-display"><?= Html::encode($initialName) ?></h4>
                        <p class="text-muted mb-2 font-italic" id="profile-email-display"><?= Html::encode($initialEmail) ?></p>

                        <div class="mb-3">
                            <span class="badge badge-info px-2 py-1" id="profile-loginmode-badge">Loading...</span>
                            <span class="badge badge-success px-2 py-1" id="profile-status-badge">Active</span>
                        </div>

                        <!-- Vertical Navigation Tabs -->
                        <div class="nav flex-column nav-pills text-left mt-4" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active py-2 mb-2 font-weight-bold" id="tab-info-link" data-toggle="pill" href="#tab-info" role="tab">
                                <i class="fas fa-user-circle mr-2 text-primary"></i> Informasi Akun
                            </a>
                            <a class="nav-link py-2 mb-2 font-weight-bold" id="tab-password-link" data-toggle="pill" href="#tab-password" role="tab">
                                <i class="fas fa-shield-alt mr-2 text-warning"></i> Keamanan & Password
                            </a>
                            <a class="nav-link py-2 font-weight-bold" id="tab-devices-link" data-toggle="pill" href="#tab-devices" role="tab">
                                <i class="fas fa-laptop-house mr-2 text-info"></i> Perangkat Anda
                            </a>
                            <a class="nav-link py-2 font-weight-bold" id="tab-login-history-link" data-toggle="pill" href="#tab-login-history" role="tab">
                                <i class="fas fa-history mr-2 text-purple"></i> Riwayat Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= SISI KANAN: TAB CONTENT ================= -->
            <div class="col-lg-8 col-md-7">
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-body p-4">
                        <div class="tab-content" id="v-pills-tabContent">

                            <!-- SUB-HALAMAN 1: INFORMASI AKUN -->
                            <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h5 class="font-weight-bold mb-0 text-primary"><i class="fas fa-id-card mr-2"></i> Biodata Lengkap</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-toggle-edit-info">
                                        <i class="fas fa-edit mr-1"></i> Edit Biodata
                                    </button>
                                </div>

                                <!-- Read-Only View -->
                                <div id="info-view-mode">
                                    <div class="row">
                                        <div class="col-sm-6 mb-3">
                                            <label class="text-muted mb-0 small">Nama Lengkap:</label>
                                            <div class="font-weight-bold h6" id="view-name">-</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="text-muted mb-0 small">Username:</label>
                                            <div class="font-weight-bold h6" id="view-username">-</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="text-muted mb-0 small">Email:</label>
                                            <div class="font-weight-bold h6" id="view-email">-</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="text-muted mb-0 small">Jenis Kelamin:</label>
                                            <div class="font-weight-bold h6" id="view-gender">-</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="text-muted mb-0 small">Tempat & Tanggal Lahir:</label>
                                            <div class="font-weight-bold h6" id="view-birth">-</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="text-muted mb-0 small">No. Telepon:</label>
                                            <div class="font-weight-bold h6" id="view-phone">-</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="text-muted mb-0 small">Alamat:</label>
                                            <div class="font-weight-bold h6" id="view-address">-</div>
                                        </div>
                                        <div class="col-12">
                                            <label class="text-muted mb-0 small">Tanggal Registrasi:</label>
                                            <div id="view-registered-at">-</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Edit -->
                                <form id="form-edit-info" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Nama Lengkap *</label>
                                            <input type="text" name="name" id="input-name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Jenis Kelamin *</label>
                                            <select name="gender" id="input-gender" class="form-control select2" style="width: 100%;">
                                                <option value="male">Laki-laki (Male)</option>
                                                <option value="female">Perempuan (Female)</option>
                                                <option value="other">Lainnya (Other)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Username *</label>
                                            <input type="text" name="username" id="input-username" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Email *</label>
                                            <input type="email" name="email" id="input-email" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Tempat Lahir</label>
                                            <input type="text" name="birth_place" id="input-birth-place" class="form-control">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Tanggal Lahir</label>
                                            <input type="date" name="birth_date" id="input-birth-date" class="form-control">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>No. Telepon / WA</label>
                                            <input type="text" name="phone" id="input-phone" class="form-control">
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label>Alamat Lengkap</label>
                                            <textarea name="address" id="input-address" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="text-right mt-2">
                                        <button type="button" class="btn btn-secondary mr-1" id="btn-cancel-edit-info">Batal</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>

                            <!-- SUB-HALAMAN 2: KEAMANAN & PASSWORD -->
                            <div class="tab-pane fade" id="tab-password" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <div>
                                        <h5 class="font-weight-bold mb-0 text-primary"><i class="fas fa-shield-alt mr-2"></i> Keamanan Password</h5>
                                        <small class="text-muted">Kelola kredensial login dan pantau riwayat perubahan password.</small>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold" id="btn-show-pass-form">
                                        <i class="fas fa-key mr-1"></i> Ubah Password
                                    </button>
                                </div>

                                <!-- Form Edit Password -->
                                <div id="change-pass-container" class="card card-outline card-warning p-3 mb-4 shadow-sm" style="display: none;">
                                    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-lock text-warning mr-1"></i> Form Pembaruan Password</h6>
                                    <form id="form-change-password">
                                        <div class="form-group">
                                            <label>Password Saat Ini (Lama) *</label>
                                            <div class="input-group">
                                                <input type="password" name="old_password" id="input-old-pass" class="form-control" required placeholder="Masukkan password saat ini">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#input-old-pass"><i class="far fa-eye"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Password Baru *</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password" id="input-new-pass" class="form-control" required placeholder="Minimal 6 karakter kombinasi">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#input-new-pass"><i class="far fa-eye"></i></button>
                                                </div>
                                            </div>
                                            <div class="strength-meter">
                                                <div class="strength-meter-fill" id="strength-bar"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="font-weight-bold text-muted" id="strength-label">Kekuatan: -</small>
                                                <small class="text-muted">Min. 6 karakter</small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Ulangi Password Baru *</label>
                                            <div class="input-group">
                                                <input type="password" name="confirm_password" id="input-confirm-pass" class="form-control" required placeholder="Ketik ulang password baru">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#input-confirm-pass"><i class="far fa-eye"></i></button>
                                                </div>
                                            </div>
                                            <small class="text-danger font-weight-bold" id="pass-match-feedback" style="display: none;">Password tidak cocok!</small>
                                        </div>

                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="button" class="btn btn-secondary mr-2" id="btn-cancel-pass-form">
                                                <i class="fas fa-times mr-1"></i> Batal / Clear
                                            </button>
                                            <button type="submit" class="btn btn-warning font-weight-bold">
                                                <i class="fas fa-save mr-1"></i> Simpan Password Baru
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Riwayat Detail Perubahan Password -->
                                <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-history mr-1"></i> Riwayat Perubahan Password:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 35%">Perangkat / Browser</th>
                                                <th style="width: 30%">Lokasi & IP</th>
                                                <th style="width: 35%">Waktu Perubahan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-pwd-history-body">
                                            <tr><td colspan="3" class="text-center py-2">Memuat riwayat...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- SUB-HALAMAN 3: PERANGKAT ANDA -->
                            <div class="tab-pane fade" id="tab-devices" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <div>
                                        <h5 class="font-weight-bold mb-0 text-primary"><i class="fas fa-laptop-house mr-2"></i> Sesi & Perangkat Anda</h5>
                                        <small class="text-muted">Kelola sesi aktif dan pilih batasan mode perangkat login.</small>
                                    </div>
                                </div>

                                <div class="card card-outline card-info bg-light p-3 mb-4 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">
                                                <i class="fas fa-toggle-on text-info mr-1"></i> Mode Login Multi-Device
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                Jika <b>Aktif (Multi Device)</b>, Anda dapat login di HP & Komputer bersamaan. Jika <b>Nonaktif (Single Device)</b>, sesi login di perangkat lain otomatis dikeluarkan.
                                            </p>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg ml-3">
                                            <input type="checkbox" class="custom-control-input" id="switch-login-mode">
                                            <label class="custom-control-label font-weight-bold" for="switch-login-mode" id="label-login-mode">Single</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="font-weight-bold mb-3 text-secondary"><i class="fas fa-list mr-1"></i> Daftar Sesi Perangkat Aktif:</h6>
                                <div id="session-list-container"></div>
                            </div>

                            <!-- SUB-HALAMAN 4: RIWAYAT LOGIN TERAKHIR (MAKS 10 RECORD) -->
                            <div class="tab-pane fade" id="tab-login-history" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <div>
                                        <h5 class="font-weight-bold mb-0 text-primary"><i class="fas fa-history mr-2"></i> 10 Riwayat Login Terakhir</h5>
                                        <small class="text-muted">Aktivitas login akun Anda pada berbagai perangkat dan lokasi.</small>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 35%">Perangkat / Browser</th>
                                                <th style="width: 30%">Lokasi & IP</th>
                                                <th style="width: 30%">Waktu Login</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-login-history-body">
                                            <tr><td colspan="4" class="text-center py-2">Memuat riwayat login...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================= MODAL UBAH FOTO PROFIL ================= -->
<div class="modal fade" id="modal-avatar-manager" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title font-weight-bold text-dark w-100">Foto Profil</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body py-3">
                <!-- Preview Foto Besar -->
                <div class="mb-3">
                    <img src="<?= $initialAvatar ?>" id="modal-avatar-preview" class="img-circle elevation-2 border" style="width: 110px; height: 110px; object-fit: cover;">
                </div>
                
                <!-- Action 1: Upload File -->
                <button type="button" class="btn btn-primary btn-block font-weight-bold rounded-pill mb-2 py-2" id="btn-modal-upload">
                    <i class="fas fa-upload mr-1"></i> Upload dari Perangkat
                </button>
                <input type="file" id="avatar-input" accept="image/png, image/jpeg, image/jpg" style="display: none;">

                <!-- Action 2: Hapus Foto Profil -->
                <button type="button" class="btn btn-outline-danger btn-block font-weight-bold rounded-pill py-2" id="btn-modal-delete-avatar" style="display: none;">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus Foto Profil
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3500" };
    $('.select2').select2({ theme: 'bootstrap4' });

    let hasCustomPhoto = false;

    function formatUtcWithRelative(utcString) {
        if (!utcString) return '-';
        const d = new Date(utcString);
        if (isNaN(d.getTime())) return utcString;

        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');

        const now = new Date();
        const diffInSeconds = Math.floor((now - d) / 1000);
        let rel = 'baru saja';
        if (diffInSeconds >= 60 && diffInSeconds < 3600) rel = `\${Math.floor(diffInSeconds / 60)} mnt lalu`;
        else if (diffInSeconds >= 3600 && diffInSeconds < 86400) rel = `\${Math.floor(diffInSeconds / 3600)} jam lalu`;
        else if (diffInSeconds >= 86400) rel = `\${Math.floor(diffInSeconds / 86400)} hari lalu`;

        return `
            <div class="text-nowrap font-weight-bold"><i class="far fa-calendar-alt text-primary mr-1"></i>\${day}-\${month}-\${year}</div>
            <div class="text-nowrap text-muted small mt-1"><i class="far fa-clock text-secondary mr-1"></i>\${hours}:\${minutes} <span class="badge badge-light border">(\${rel})</span></div>
        `;
    }

    function loadProfileData() {
        $.ajax({
            url: '{$getDataUrl}',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const u = res.user;
                    hasCustomPhoto = u.has_custom_avatar;

                    // Update Image Sources
                    $('#user-avatar-img').attr('src', u.avatar);
                    $('#modal-avatar-preview').attr('src', u.avatar);
                    $('.navbar .dropdown img, .sidebar img').attr('src', u.avatar);

                    $('#profile-name-display').text(u.name);
                    $('#profile-email-display').text(u.email);
                    $('#profile-loginmode-badge').text(u.login_mode === 'single_device' ? 'Single Device' : 'Multi Device');
                    $('#profile-status-badge').text(u.status.toUpperCase());

                    // Tombol Hapus Avatar di Modal
                    if (hasCustomPhoto) {
                        $('#btn-modal-delete-avatar').show();
                    } else {
                        $('#btn-modal-delete-avatar').hide();
                    }

                    // Update Read-Only View Biodata
                    $('#view-name').text(u.name);
                    $('#view-username').text('@' + u.username);
                    $('#view-email').text(u.email);
                    $('#view-gender').text(u.gender === 'male' ? 'Laki-laki' : (u.gender === 'female' ? 'Perempuan' : 'Lainnya'));
                    $('#view-birth').text((u.birth_place || '-') + ', ' + (u.birth_date || '-'));
                    $('#view-phone').text(u.phone || '-');
                    $('#view-address').text(u.address || '-');
                    $('#view-registered-at').html(formatUtcWithRelative(u.registered_at));

                    // Form Pre-fill
                    $('#input-name').val(u.name);
                    $('#input-username').val(u.username);
                    $('#input-email').val(u.email);
                    $('#input-gender').val(u.gender).trigger('change');
                    $('#input-birth-place').val(u.birth_place);
                    $('#input-birth-date').val(u.birth_date);
                    $('#input-phone').val(u.phone);
                    $('#input-address').val(u.address);

                    // Switch Mode
                    const isMulti = (u.login_mode === 'multi_device');
                    $('#switch-login-mode').prop('checked', isMulti);
                    $('#label-login-mode').text(isMulti ? 'Multi Device' : 'Single Device');

                    renderPasswordHistories(res.password_histories);
                    renderSessions(res.sessions);
                    renderLoginHistories(res.login_histories);
                }
            }
        });
    }

    function renderPasswordHistories(histories) {
        if (!histories || histories.length === 0) {
            $('#table-pwd-history-body').html('<tr><td colspan="3" class="text-center py-2 text-muted">Belum ada riwayat perubahan password.</td></tr>');
            return;
        }

        let html = '';
        $.each(histories, function(i, h) {
            html += `
                <tr>
                    <td class="align-middle">
                        <i class="\${h.icon} mr-1 text-primary"></i> <strong>\${h.device}</strong>
                    </td>
                    <td class="align-middle">
                        <div><i class="fas fa-map-marker-alt text-danger mr-1"></i> \${h.location}</div>
                        <small class="text-muted">IP: <code>\${h.ip_address}</code></small>
                    </td>
                    <td class="align-middle">
                        \${formatUtcWithRelative(h.created_at)}
                    </td>
                </tr>
            `;
        });
        $('#table-pwd-history-body').html(html);
    }

    function renderSessions(sessions) {
        if (!sessions || sessions.length === 0) {
            $('#session-list-container').html('<div class="alert alert-light text-center">Tidak ada sesi perangkat aktif.</div>');
            return;
        }

        let html = '';
        $.each(sessions, function(idx, s) {
            const currentBadge = s.is_current 
                ? '<span class="badge badge-success ml-2 px-2"><i class="fas fa-check-circle mr-1"></i> Perangkat Ini</span>' 
                : '';

            const revokeBtn = !s.is_current
                ? `<button class="btn btn-outline-danger btn-sm btn-revoke-session" data-id="\${s.id}"><i class="fas fa-sign-out-alt mr-1"></i> Logout</button>`
                : '<span class="badge badge-light border">Aktif</span>';

            html += `
                <div class="card card-outline \${s.is_current ? 'card-success' : 'card-light'} shadow-sm mb-3">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-secondary" style="font-size: 2rem;"><i class="\${s.icon}"></i></div>
                            <div>
                                <h6 class="font-weight-bold mb-0 text-dark">\${s.browser} on \${s.os} \${currentBadge}</h6>
                                <small class="text-muted">IP: <code>\${s.ip_address}</code> | Aktif: \${formatUtcWithRelative(s.last_activity_at)}</small>
                            </div>
                        </div>
                        <div>\${revokeBtn}</div>
                    </div>
                </div>
            `;
        });
        $('#session-list-container').html(html);
    }

    function renderLoginHistories(logs) {
        if (!logs || logs.length === 0) {
            $('#table-login-history-body').html('<tr><td colspan="4" class="text-center py-2 text-muted">Belum ada catatan riwayat login.</td></tr>');
            return;
        }

        let html = '';
        $.each(logs, function(i, log) {
            html += `
                <tr>
                    <td class="text-center align-middle font-weight-bold">\${i + 1}</td>
                    <td class="align-middle">
                        <i class="\${log.icon} mr-1 text-primary"></i> <strong>\${log.device}</strong>
                    </td>
                    <td class="align-middle">
                        <div><i class="fas fa-map-marker-alt text-danger mr-1"></i> \${log.location}</div>
                        <small class="text-muted">IP: <code>\${log.ip_address}</code></small>
                    </td>
                    <td class="align-middle">
                        \${formatUtcWithRelative(log.login_at)}
                    </td>
                </tr>
            `;
        });
        $('#table-login-history-body').html(html);
    }

    // 1. Trigger Buka Modal Ubah Foto Profil
    $('#btn-trigger-avatar-modal').on('click', function() {
        $('#modal-avatar-manager').modal('show');
    });

    // 2. Action: Upload dari Perangkat (via Modal)
    $('#btn-modal-upload').on('click', function() {
        $('#avatar-input').click();
    });

    $('#avatar-input').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('avatar_file', file);
        toastr.info('Mengunggah foto profil...');

        $.ajax({
            url: '{$uploadAvatarUrl}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    $('#modal-avatar-manager').modal('hide');
                    loadProfileData();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // 3. Action: Hapus Foto Profil (via Modal)
    $('#btn-modal-delete-avatar').on('click', function() {
        Swal.fire({
            title: 'Hapus Foto Profil?',
            text: 'Foto profil akan dihapus dan dikembalikan ke avatar inisial nama default.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus Foto!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{$deleteAvatarUrl}',
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            $('#modal-avatar-manager').modal('hide');
                            loadProfileData();
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });

    // Toggle Form Password
    $('#btn-show-pass-form').on('click', function() {
        $('#change-pass-container').slideDown();
        $(this).hide();
    });

    $('#btn-cancel-pass-form').on('click', function() {
        $('#form-change-password')[0].reset();
        $('#strength-bar').css('width', '0%');
        $('#strength-label').text('Kekuatan: -');
        $('#pass-match-feedback').hide();
        $('#change-pass-container').slideUp();
        $('#btn-show-pass-form').show();
    });

    // Password Strength Meter
    $('#input-new-pass').on('input', function() {
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

    $('#input-confirm-pass, #input-new-pass').on('input', function() {
        const newPass = $('#input-new-pass').val();
        const confirmPass = $('#input-confirm-pass').val();
        if (confirmPass.length > 0 && newPass !== confirmPass) $('#pass-match-feedback').show();
        else $('#pass-match-feedback').hide();
    });

    $('#form-change-password').on('submit', function(e) {
        e.preventDefault();
        const newPass = $('#input-new-pass').val();
        const confirmPass = $('#input-confirm-pass').val();

        if (newPass !== confirmPass) {
            toastr.error('Password baru dan konfirmasi tidak cocok!');
            return;
        }

        $.ajax({
            url: '{$changePasswordUrl}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#28a745' });
                    $('#btn-cancel-pass-form').click();
                    loadProfileData();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // Toggle Edit Biodata
    $('#btn-toggle-edit-info').on('click', function() { $('#info-view-mode').hide(); $('#form-edit-info').fadeIn(); $(this).hide(); });
    $('#btn-cancel-edit-info').on('click', function() { $('#form-edit-info').hide(); $('#info-view-mode').fadeIn(); $('#btn-toggle-edit-info').show(); });

    $('#form-edit-info').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{$updateInfoUrl}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    $('#btn-cancel-edit-info').click();
                    loadProfileData();
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    // Switch Login Mode
    $('#switch-login-mode').on('change', function() {
        const isChecked = $(this).is(':checked');
        const newMode = isChecked ? 'multi_device' : 'single_device';

        Swal.fire({
            title: `Ubah ke Mode \${isChecked ? 'Multi Device' : 'Single Device'}?`,
            text: isChecked ? 'Anda dapat login di beberapa perangkat bersamaan.' : 'Semua sesi lain akan dikeluarkan seketika.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Ya, Ubah Mode!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{$updateLoginModeUrl}',
                    type: 'POST',
                    data: { login_mode: newMode },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            loadProfileData();
                        }
                    }
                });
            } else {
                $('#switch-login-mode').prop('checked', !isChecked);
            }
        });
    });

    // Revoke Session
    $(document).on('click', '.btn-revoke-session', function() {
        const sessionId = $(this).data('id');
        Swal.fire({
            title: 'Logout Perangkat Ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{$terminateSessionUrl}?id=' + sessionId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            loadProfileData();
                        }
                    }
                });
            }
        });
    });

    $('.toggle-pass').on('click', function() {
        const target = $($(this).data('target'));
        const icon = $(this).find('i');
        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            target.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $(document).ready(function() {
        loadProfileData();
    });
JS
);
?>