<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;
use app\models\Menu;
use app\models\MenuGroup;

// 1. Data User Panel
$userId = Yii::$app->user->id ?? 1;
$currentUser   = User::find()->with('profile')->where(['id' => $userId])->one();
$sidebarAvatar = $currentUser ? $currentUser->getAvatarUrl() : User::generateInitialAvatar('Admin', 1);
$sidebarName   = $currentUser->profile->name ?? ($currentUser->username ?? 'Administrator');

// 2. Data Menu Dinamis dari Database (Group: Main Sidebar 'ap-main-sidebar')
$menuGroup = MenuGroup::findOne(['code' => 'ap-main-sidebar', 'deleted_at' => null]);
$groupId = $menuGroup ? $menuGroup->id : 2;

$dbMenus = Menu::find()
    ->where(['group_id' => $groupId, 'status' => 'active', 'deleted_at' => null])
    ->orderBy(['parent_id' => SORT_ASC, 'order' => SORT_ASC])
    ->all();

// 3. Petakan Tree Hierarchy
$treeMap = [];
foreach ($dbMenus as $m) {
    $treeMap[$m->parent_id][] = $m;
}

// 4. Parameter Route Aktif
$currentController = '/' . Yii::$app->controller->id;
$currentAction     = '/' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
$currentPath       = '/' . ltrim(Yii::$app->request->pathInfo, '/');

/**
 * Helper: Deteksi & Render Icon (Gambar URL/SVG vs FontAwesome)
 */
$renderNavIcon = function(?string $icon) {
    if (empty($icon)) {
        return '<i class="nav-icon far fa-circle"></i>';
    }

    $iconTrim = trim($icon);
    // Cek apakah icon berupa URL gambar, file upload, atau SVG
    $isImage = preg_match('/^(https?:\/\/|\/|uploads\/|data:image\/).*\.(png|jpg|jpeg|svg|webp|gif|ico)$/i', $iconTrim) 
               || str_starts_with($iconTrim, 'data:image/') 
               || preg_match('/^https?:\/\//i', $iconTrim);

    if ($isImage) {
        return '<img src="' . Html::encode($iconTrim) . '" class="nav-icon" style="width: 1.25rem; height: 1.25rem; object-fit: contain; margin-right: 0.5rem; border-radius: 4px;" alt="Icon">';
    }

    return '<i class="nav-icon ' . Html::encode($iconTrim) . '"></i>';
};

/**
 * Helper: Cek Menu Aktif
 */
$isItemActive = function($menu) use ($currentController, $currentAction, $currentPath) {
    $link = trim($menu->link);
    if (empty($link) || $link === '#' || preg_match('/^https?:\/\//i', $link)) {
        return false; // Link eksternal tidak pernah ditandai sebagai internal route aktif
    }

    $normalizedLink = '/' . ltrim($link, '/');
    if ($normalizedLink === $currentAction) return true;
    if ($normalizedLink === $currentController) return true;
    if ($normalizedLink === $currentPath) return true;
    if (str_starts_with($currentAction, $normalizedLink . '/')) return true;

    return false;
};

$isTreeActive = function($parentId) use (&$isTreeActive, &$treeMap, $isItemActive) {
    if (!isset($treeMap[$parentId])) return false;
    foreach ($treeMap[$parentId] as $child) {
        if ($isItemActive($child) || $isTreeActive($child->id)) {
            return true;
        }
    }
    return false;
};

/**
 * Renderer Rekursif Menu
 */
$renderMenuTree = function($parentId = 0) use (&$renderMenuTree, &$treeMap, $isItemActive, $isTreeActive, $renderNavIcon) {
    if (!isset($treeMap[$parentId])) return '';

    $html = '';
    foreach ($treeMap[$parentId] as $menu) {
        $hasChildren   = isset($treeMap[$menu->id]) && count($treeMap[$menu->id]) > 0;
        $isSelfActive  = $isItemActive($menu);
        $isChildActive = $hasChildren ? $isTreeActive($menu->id) : false;
        $isParentOpen  = $isSelfActive || $isChildActive;

        // Render Section Header (Tipe Text)
        if ($menu->type === 'text') {
            $html .= '<li class="nav-header text-uppercase font-weight-bold text-xs text-muted pt-3 pb-1 pl-3">' 
                  . Html::encode($menu->label) 
                  . '</li>';
            continue;
        }

        $iconHtml = $renderNavIcon($menu->icon);
        $rawLink  = trim($menu->link);

        if ($hasChildren) {
            // Parent Treeview
            $html .= '<li class="nav-item has-treeview ' . ($isParentOpen ? 'menu-open' : '') . '">';
            $html .= '<a href="#" class="nav-link ' . ($isParentOpen ? 'active' : '') . '">';
            $html .= $iconHtml;
            $html .= '<p>' . Html::encode($menu->label) . '<i class="right fas fa-angle-left"></i></p>';
            $html .= '</a>';
            $html .= '<ul class="nav nav-treeview pl-2">';
            $html .= $renderMenuTree($menu->id);
            $html .= '</ul>';
            $html .= '</li>';
        } else {
            // Single Menu (Internal vs Eksternal)
            $isExternal = preg_match('/^(https?:\/\/|mailto:|tel:)/i', $rawLink);
            
            if ($isExternal) {
                // Link Eksternal -> Buka Tab Baru
                $url = Html::encode($rawLink);
                $linkAttr = 'target="_blank" rel="noopener noreferrer"';
                $externalBadge = ' <i class="fas fa-external-link-alt text-xs ml-1 text-muted" style="font-size: 0.7rem;"></i>';
            } else {
                // Link Internal Yii2
                $url = ($rawLink === '#' || empty($rawLink)) ? '#' : Url::to([$rawLink]);
                $linkAttr = '';
                $externalBadge = '';
            }

            $activeClass = $isSelfActive ? 'active' : '';

            $html .= '<li class="nav-item">';
            $html .= '<a href="' . $url . '" class="nav-link ' . $activeClass . '" ' . $linkAttr . '>';
            $html .= $iconHtml;
            $html .= '<p>' . Html::encode($menu->label) . $externalBadge . '</p>';
            $html .= '</a>';
            $html .= '</li>';
        }
    }
    return $html;
};
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Url::to(['/dashboard/index']) ?>" class="brand-link border-bottom border-secondary d-flex align-items-center">
        <span class="brand-text font-weight-bold pl-3" style="font-size: 1.25rem;">
            <b>Admin</b>LTE<span class="text-primary">.</span>
        </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar px-2">
        
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center border-bottom border-secondary">
            <div class="image pl-1">
                <img src="<?= $sidebarAvatar ?>" class="img-circle elevation-1 border border-secondary" alt="User Image" style="width: 36px; height: 36px; object-fit: cover;">
            </div>
            <div class="info pl-3">
                <a href="<?= Url::to(['/profile/index']) ?>" class="d-block font-weight-bold text-white text-truncate" style="max-width: 155px;" title="<?= Html::encode($sidebarName) ?>">
                    <?= Html::encode($sidebarName) ?>
                </a>
                <small class="text-success font-weight-500"><i class="fas fa-circle text-xs mr-1"></i> Online</small>
            </div>
        </div>

        <!-- Dynamic Menu Navigation -->
        <nav class="mt-2 mb-4">
            <ul class="nav nav-pills nav-sidebar flex-column nav-flat nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <?= $renderMenuTree(0) ?>
            </ul>
        </nav>

    </div>
</aside>