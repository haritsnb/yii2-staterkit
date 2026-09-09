<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;

$controller = Yii::$app->controller->id;

$userId = Yii::$app->user->id ?? 1;
$currentUser = User::find()->with('profile')->where(['id' => $userId])->one();
$sidebarAvatar = $currentUser ? $currentUser->getAvatarUrl() : User::generateInitialAvatar('Admin', 1);
$sidebarName   = $currentUser->profile->name ?? ($currentUser->username ?? 'Administrator');
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Url::to(['/dashboard/index']) ?>" class="brand-link">
        <span class="brand-text font-weight-light pl-3"><b>AdminLTE</b> 3.2</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image pl-2">
                <img src="<?= $sidebarAvatar ?>" class="img-circle elevation-1" alt="User Image" style="width: 35px; height: 35px; object-fit: cover;">
            </div>
            <div class="info">
                <a href="<?= Url::to(['/profile/index']) ?>" class="d-block font-weight-bold"><?= Html::encode($sidebarName) ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="<?= Url::to(['/dashboard/index']) ?>" class="nav-link <?= $controller === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= Url::to(['/user/index']) ?>" class="nav-link <?= $controller === 'user' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Manajemen Pengguna</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= Url::to(['/profile/index']) ?>" class="nav-link <?= $controller === 'profile' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= Url::to(['/setting/index']) ?>" class="nav-link <?= $controller === 'setting' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>Pengaturan Sistem</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>