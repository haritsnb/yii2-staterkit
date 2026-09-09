<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;

$userId = Yii::$app->user->id ?? 1;
$currentUser = User::find()->with('profile')->where(['id' => $userId])->one();

$currentAvatar = $currentUser ? $currentUser->getAvatarUrl() : User::generateInitialAvatar('Admin', 1);
$currentName   = $currentUser->profile->name ?? ($currentUser->username ?? 'Administrator');
$currentEmail  = $currentUser->email ?? 'admin@example.com';
$currentMode   = ($currentUser->login_mode ?? 'single_device') === 'single_device' ? 'Single Device' : 'Multi Device';
$registeredIso = $currentUser ? gmdate('Y-m-d\TH:i:s\Z', strtotime($currentUser->registered_at)) : '';

// URL Logout terarah ke AuthController::actionLogout
$logoutUrl = Url::to(['/auth/logout']);
?>

<style>
/* Google Account-style User Dropdown Menu */
.navbar .dropdown-menu-google {
    width: 320px;
    border-radius: 24px;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.16), 0 2px 8px rgba(0, 0, 0, 0.08);
    border: none;
    padding: 0;
    margin-top: 8px;
    background-color: #ffffff;
    overflow: hidden;
}
.google-user-header {
    background-color: #f8f9fa;
    padding: 20px 20px 16px;
    text-align: center;
    border-bottom: 1px solid #e9ecef;
}
.google-user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 3px solid #ffffff;
}
.google-btn-manage {
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 7px 18px;
    border: 1px solid #dadce0;
    color: #1a73e8;
    background-color: #ffffff;
    transition: all 0.2s ease;
}
.google-btn-manage:hover {
    background-color: #f1f3f4;
    color: #174ea6;
    border-color: #dadce0;
}
.google-footer {
    padding: 12px 20px 16px;
    text-align: center;
    background-color: #ffffff;
}
.google-btn-logout {
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 7px 20px;
}
</style>

<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom shadow-xs px-3" style="min-height: 56px;">
    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link text-secondary p-2" data-widget="pushmenu" href="#" role="button" title="Toggle Sidebar">
                <i class="fas fa-bars fa-lg"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block ml-2">
            <a href="<?= Url::to(['/dashboard/index']) ?>" class="nav-link font-weight-bold text-dark">
                <i class="fas fa-home text-primary mr-1"></i> Dashboard
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- Fullscreen Button -->
        <li class="nav-item mr-3">
            <a class="nav-link text-secondary p-2" data-widget="fullscreen" href="#" role="button" title="Layar Penuh">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <!-- USER AVATAR ONLY (GOOGLE ACCOUNT STYLE) -->
        <li class="nav-item dropdown">
            <a href="#" class="nav-link p-0 d-flex align-items-center" data-toggle="dropdown" aria-expanded="false" title="<?= Html::encode($currentName) ?>">
                <img src="<?= $currentAvatar ?>" class="img-circle elevation-1 border" alt="User Avatar" style="width: 38px; height: 38px; object-fit: cover; cursor: pointer;">
            </a>
            
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-google animated fadeIn">
                
                <div class="google-user-header">
                    <small class="text-muted font-weight-bold d-block text-truncate mb-3"><?= Html::encode($currentEmail) ?></small>
                    
                    <div class="position-relative d-inline-block mb-2">
                        <img src="<?= $currentAvatar ?>" class="google-user-avatar" alt="Avatar">
                    </div>
                    
                    <h6 class="font-weight-bold text-dark mb-0 text-truncate px-2"><?= Html::encode($currentName) ?></h6>
                    <small class="badge badge-light border text-secondary mt-1 px-2 py-1"><?= Html::encode($currentMode) ?></small>
                    
                    <div class="mt-3">
                        <a href="<?= Url::to(['/profile/index']) ?>" class="btn google-btn-manage">
                            <i class="fas fa-user-cog mr-1"></i> Kelola Akun Anda
                        </a>
                    </div>
                </div>

                <div class="google-footer">
                    <button type="button" class="btn btn-outline-danger btn-block google-btn-logout btn-logout-navbar">
                        <i class="fas fa-sign-out-alt mr-1"></i> Keluar (Logout)
                    </button>
                    <div class="text-muted mt-2" style="font-size: 0.72rem;">
                        <i class="far fa-calendar-alt mr-1"></i> Bergabung: <span id="navbar-reg-date">-</span>
                    </div>
                </div>

            </div>
        </li>

    </ul>
</nav>

<?php
$this->registerJs(<<<JS
    // Format Waktu Registrasi di Navbar
    const regIso = "{$registeredIso}";
    if (regIso) {
        const d = new Date(regIso);
        if (!isNaN(d.getTime())) {
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            $('#navbar-reg-date').text(`\${day}-\${month}-\${year}`);
        }
    }

    // Konfirmasi Logout dengan SweetAlert2
    $('.btn-logout-navbar').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Keluar?',
            text: 'Sesi login Anda saat ini akan dihentikan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Ya, Keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{$logoutUrl}";
            }
        });
    });
JS
);
?>