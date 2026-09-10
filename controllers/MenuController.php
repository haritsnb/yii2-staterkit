<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\Html;
use app\models\Menu;
use app\models\MenuGroup;

class MenuController extends BaseAdminController
{
    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/menus');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Endpoint API: Daftar Rute Internal Aplikasi untuk Select2 Link
     */
    public function actionGetAvailableRoutes(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $routes = [
            ['id' => '/dashboard', 'name' => '/dashboard (Dashboard Utama)'],
            ['id' => '/users', 'name' => '/users (Manajemen Pengguna)'],
            ['id' => '/roles', 'name' => '/roles (Kelola Hak Akses / Roles)'],
            ['id' => '/permissions', 'name' => '/permissions (Kelola Izin / Permissions)'],
            ['id' => '/menus', 'name' => '/menus (Manajemen Navigasi Menu)'],
            ['id' => '/settings', 'name' => '/settings (Pengaturan Sistem)'],
            ['id' => '/profile', 'name' => '/profile (Profil Saya)'],
            ['id' => '#', 'name' => '# (Hanya Menu Induk / Tanpa Link)'],
        ];

        return $this->asJson(['status' => 'success', 'data' => $routes]);
    }

    /**
     * Endpoint API: Daftar Ikon FontAwesome Populer untuk Select2 Visual Symbol
     */
    public function actionGetFontawesomeIcons(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $icons = [
            'fas fa-tachometer-alt' => 'Dashboard (fas fa-tachometer-alt)',
            'fas fa-users'          => 'Users (fas fa-users)',
            'fas fa-user-shield'    => 'Security / Shield (fas fa-user-shield)',
            'fas fa-key'            => 'Key / Permission (fas fa-key)',
            'fas fa-sitemap'        => 'Sitemap / Tree (fas fa-sitemap)',
            'fas fa-bars'           => 'Bars / Navigation (fas fa-bars)',
            'fas fa-cogs'           => 'Settings (fas fa-cogs)',
            'fas fa-user-cog'       => 'Profile / Account (fas fa-user-cog)',
            'fas fa-home'           => 'Home (fas fa-home)',
            'fas fa-shopping-cart'  => 'Shopping Cart (fas fa-shopping-cart)',
            'fas fa-shopping-bag'   => 'Shopping Bag (fas fa-shopping-bag)',
            'fas fa-chart-bar'      => 'Chart Bar (fas fa-chart-bar)',
            'fas fa-chart-pie'      => 'Chart Pie (fas fa-chart-pie)',
            'fas fa-chart-line'     => 'Chart Line (fas fa-chart-line)',
            'fas fa-table'          => 'Table / Data (fas fa-table)',
            'fas fa-cubes'          => 'Cubes / Modules (fas fa-cubes)',
            'fas fa-file-alt'       => 'Document (fas fa-file-alt)',
            'fas fa-folder'         => 'Folder (fas fa-folder)',
            'fas fa-envelope'       => 'Envelope / Message (fas fa-envelope)',
            'fas fa-bell'           => 'Notification / Bell (fas fa-bell)',
            'fas fa-layer-group'    => 'Layer Group (fas fa-layer-group)',
            'fas fa-globe'          => 'Globe / Website (fas fa-globe)',
            'fas fa-database'       => 'Database (fas fa-database)',
            'fas fa-shield-alt'     => 'Shield Protection (fas fa-shield-alt)',
            'fas fa-circle'         => 'Default Circle (fas fa-circle)',
            'fab fa-instagram'      => 'Instagram (fab fa-instagram)',
            'fab fa-facebook'       => 'Facebook (fab fa-facebook)',
            'fab fa-twitter'        => 'Twitter (fab fa-twitter)',
            'fab fa-youtube'        => 'YouTube (fab fa-youtube)',
            'fab fa-whatsapp'       => 'WhatsApp (fab fa-whatsapp)',
            'fab fa-telegram'       => 'Telegram (fab fa-telegram)',
            'fab fa-github'         => 'GitHub (fab fa-github)',
        ];

        $data = [];
        foreach ($icons as $class => $name) {
            $data[] = ['id' => $class, 'name' => $name, 'icon_class' => $class];
        }

        return $this->asJson(['status' => 'success', 'data' => $data]);
    }

    /**
     * Endpoint AJAX: Mengunggah file ikon ke project/storages/icons/
     */
    public function actionUploadIcon(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('icon_file');

        if (!$file) {
            return $this->asJson(['status' => 'error', 'message' => 'Tidak ada file ikon yang diunggah.']);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'ico'];
        $ext = strtolower($file->extension ?: '');

        if (!in_array($ext, $allowedExtensions, true)) {
            return $this->asJson([
                'status'  => 'error',
                'message' => 'Format file ".' . htmlspecialchars($ext) . '" tidak didukung! Format yang diizinkan: JPG, JPEG, PNG, WebP, SVG, GIF, ICO.'
            ]);
        }

        if ($file->size > 2 * 1024 * 1024) {
            return $this->asJson(['status' => 'error', 'message' => 'Ukuran file ikon terlalu besar! Maksimal 2 MB.']);
        }

        try {
            $iconPath = \app\components\StorageManager::executeAtomicUpload(
                $file,
                'icons',
                null,
                fn($newRelPath) => $newRelPath
            );

            return $this->asJson([
                'status'   => 'success',
                'message'  => 'Ikon berhasil diunggah!',
                'iconPath' => $iconPath,
                'fullUrl'  => \app\components\StorageManager::getUrl($iconPath)
            ]);
        } catch (\Throwable $e) {
            return $this->asJson(['status' => 'error', 'message' => 'Gagal mengunggah ikon: ' . $e->getMessage()]);
        }
    }

    public function actionGetGroups(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $groups = MenuGroup::find()->where(['deleted_at' => null])->all();

        $data = [];
        foreach ($groups as $g) {
            $data[] = [
                'id'          => $g->id,
                'name'        => $g->name,
                'code'        => $g->code,
                'type'        => $g->type,
                'description' => $g->description,
            ];
        }

        return $this->asJson(['status' => 'success', 'data' => $data]);
    }

    public function actionGetTree(int $group_id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $menus = Menu::find()
            ->where(['group_id' => $group_id, 'deleted_at' => null])
            ->orderBy(['parent_id' => SORT_ASC, 'order' => SORT_ASC])
            ->all();

        $treeData = [];
        foreach ($menus as $m) {
            $treeData[] = [
                'id'        => (string) $m->id,
                'parent_id' => (string) $m->parent_id,
                'order'     => (int) $m->order,
                'label'     => $m->label,
                'link'      => $m->link,
                'icon'      => $m->icon ?: 'fas fa-circle',
                'type'      => $m->type,
                'status'    => $m->status,
                'bind'      => (bool) $m->bind,
            ];
        }

        return $this->asJson(['status' => 'success', 'data' => $treeData]);
    }

    public function actionSaveOrder(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderData = Yii::$app->request->post('orderData', []);
        $groupId   = (int) Yii::$app->request->post('group_id');

        if (empty($orderData) || !$groupId) {
            return $this->asJson(['status' => 'error', 'message' => 'Data urutan tidak valid.']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $nowUtc = gmdate('Y-m-d H:i:s');
            $userId = Yii::$app->user->id ?? 1;

            foreach ($orderData as $item) {
                Menu::updateAll([
                    'parent_id'   => (int) ($item['parent_id'] ?? 0),
                    'order'       => (int) ($item['order'] ?? 1),
                    'bind'        => isset($item['bind']) ? ($item['bind'] ? 1 : 0) : 1,
                    'modified_at' => $nowUtc,
                    'modified_by' => $userId,
                ], ['id' => $item['id'], 'group_id' => $groupId]);
            }

            $transaction->commit();
            return $this->asJson(['status' => 'success', 'message' => 'Urutan navigasi menu berhasil diperbarui!']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->asJson(['status' => 'error', 'message' => 'Gagal menyimpan urutan: ' . $e->getMessage()]);
        }
    }

    public function actionListDatatable(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $draw    = (int) $request->get('draw', 1);
        $start   = (int) $request->get('start', 0);
        $length  = (int) $request->get('length', 10);
        $search  = $request->get('search')['value'] ?? '';
        $groupId = (int) $request->get('group_id', 0);

        $query = Menu::find()->where(['deleted_at' => null]);
        if ($groupId > 0) {
            $query->andWhere(['group_id' => $groupId]);
        }

        $totalRecords = (clone $query)->count();

        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'label', $search],
                ['like', 'link', $search],
                ['like', 'icon', $search],
                ['like', 'type', $search],
                ['like', 'status', $search],
            ]);
        }
        $filteredRecords = (clone $query)->count();

        $rows = $query->orderBy(['group_id' => SORT_ASC, 'parent_id' => SORT_ASC, 'order' => SORT_ASC])
            ->offset($start)
            ->limit($length)
            ->all();

        $data = [];
        foreach ($rows as $row) {
            $iconTrim = trim($row->icon ?? '');
            $isImage = \app\components\StorageManager::exists($iconTrim)
                       || preg_match('/^(https?:\/\/|\/|uploads\/|data:image\/).*\.(png|jpg|jpeg|svg|webp|gif|ico)$/i', $iconTrim)
                       || preg_match('/^https?:\/\//i', $iconTrim);

            if ($isImage) {
                $imgUrl = \app\components\StorageManager::exists($iconTrim) 
                    ? \app\components\StorageManager::getUrl($iconTrim) 
                    : $iconTrim;

                $iconHtml = '<img src="' . Html::encode($imgUrl) . '" style="width: 20px; height: 20px; object-fit: contain; margin-right: 5px; border-radius: 3px;" alt="icon"> <small class="text-muted text-truncate d-inline-block" style="max-width: 110px;" title="' . Html::encode($iconTrim) . '">' . Html::encode($iconTrim) . '</small>';
            } else {
                $iconHtml = '<i class="' . Html::encode($row->icon ?: 'fas fa-circle') . ' mr-1 text-primary"></i> <small class="font-weight-bold">' . Html::encode($row->icon) . '</small>';
            }

            $isExternal = preg_match('/^(https?:\/\/|mailto:|tel:)/i', $row->link);
            if ($isExternal) {
                $linkHtml = '<a href="' . Html::encode($row->link) . '" target="_blank" rel="noopener noreferrer" class="text-primary font-weight-500"><code>' . Html::encode($row->link) . '</code> <i class="fas fa-external-link-alt text-xs ml-1"></i></a>';
            } else {
                $linkHtml = '<code>' . Html::encode($row->link) . '</code>';
            }

            $statusBadge = $row->status === 'active' 
                ? '<span class="badge badge-success">Active</span>' 
                : '<span class="badge badge-secondary">Inactive</span>';

            $data[] = [
                'id'         => $row->id,
                'group_name' => $row->group ? Html::encode($row->group->name) : '-',
                'label'      => '<strong>' . Html::encode($row->label) . '</strong>',
                'link'       => $linkHtml,
                'icon'       => $iconHtml,
                'parent_id'  => $row->parent_id == 0 ? '<span class="badge badge-light border">Root Menu</span>' : 'Parent #' . $row->parent_id,
                'order'      => $row->order,
                'status'     => $statusBadge,
                'actions'    => '
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-info btn-edit-menu" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-success btn-clone-direct" data-id="' . $row->id . '" title="Clone Menu"><i class="fas fa-copy"></i></button>
                        <button class="btn btn-danger btn-delete-menu" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>'
            ];
        }

        return $this->asJson([
            'draw'            => $draw,
            'recordsTotal'    => (int) $totalRecords,
            'recordsFiltered' => (int) $filteredRecords,
            'data'            => $data,
        ]);
    }

    public function actionCreate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $menu = new Menu();
        $menu->group_id  = (int) ($req['group_id'] ?? 1);
        $menu->parent_id = (int) ($req['parent_id'] ?? 0);
        $menu->label     = trim($req['label'] ?? '');
        $menu->link      = trim($req['link'] ?? '#');
        $menu->icon      = !empty($req['icon']) ? trim($req['icon']) : 'fas fa-circle';
        $menu->type      = $req['type'] ?? 'url';
        $menu->status    = $req['status'] ?? 'active';
        $menu->bind      = isset($req['bind']) ? (int) $req['bind'] : 1;

        if ($menu->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Menu berhasil ditambahkan!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $menu->getErrors()]);
    }

    public function actionViewData(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $menu = Menu::findOne(['id' => $id, 'deleted_at' => null]);

        if (!$menu) {
            return $this->asJson(['status' => 'error', 'message' => 'Menu tidak ditemukan']);
        }

        $isExternal = (bool) preg_match('/^(https?:\/\/|mailto:|tel:)/i', $menu->link);

        return $this->asJson([
            'status' => 'success',
            'data' => [
                'id'          => $menu->id,
                'group_id'    => $menu->group_id,
                'parent_id'   => $menu->parent_id,
                'label'       => $menu->label,
                'link'        => $menu->link,
                'is_external' => $isExternal,
                'icon'        => $menu->icon,
                'type'        => $menu->type,
                'status'      => $menu->status,
                'bind'        => $menu->bind,
            ]
        ]);
    }

    public function actionUpdate(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $menu = Menu::findOne($id);
        if (!$menu) {
            return $this->asJson(['status' => 'error', 'message' => 'Menu tidak ditemukan']);
        }

        $oldIcon = $menu->icon;
        $newIcon = !empty($req['icon']) ? trim($req['icon']) : $oldIcon;

        $menu->group_id  = (int) ($req['group_id'] ?? $menu->group_id);
        $menu->parent_id = (int) ($req['parent_id'] ?? $menu->parent_id);
        $menu->label     = trim($req['label'] ?? $menu->label);
        $menu->link      = trim($req['link'] ?? $menu->link);
        $menu->icon      = $newIcon;
        $menu->type      = $req['type'] ?? $menu->type;
        $menu->status    = $req['status'] ?? $menu->status;
        $menu->bind      = isset($req['bind']) ? (int) $req['bind'] : $menu->bind;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($menu->save()) {
                $transaction->commit();

                // Bersihkan file lama jika icon berubah
                if (!empty($oldIcon) && $oldIcon !== $newIcon && \app\components\StorageManager::exists($oldIcon)) {
                    \app\components\StorageManager::delete($oldIcon);
                }

                return $this->asJson(['status' => 'success', 'message' => 'Menu berhasil diperbarui!']);
            }

            $transaction->rollBack();
            return $this->asJson(['status' => 'error', 'errors' => $menu->getErrors()]);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return $this->asJson(['status' => 'error', 'message' => 'Gagal memperbarui menu: ' . $e->getMessage()]);
        }
    }

    public function actionClone(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $targetMenuId = (int) ($req['source_menu_id'] ?? 0);
        $sourceMenu = Menu::findOne($targetMenuId);

        if (!$sourceMenu) {
            return $this->asJson(['status' => 'error', 'message' => 'Menu sumber yang akan diclone tidak ditemukan.']);
        }

        $newMenu = new Menu();
        $newMenu->group_id  = (int) ($req['group_id'] ?? $sourceMenu->group_id);
        $newMenu->parent_id = (int) ($req['parent_id'] ?? $sourceMenu->parent_id);
        $newMenu->label     = trim($req['label'] ?? ($sourceMenu->label . ' (Copy)'));
        $newMenu->link      = trim($req['link'] ?? $sourceMenu->link);
        $newMenu->icon      = !empty($req['icon']) ? trim($req['icon']) : $sourceMenu->icon;
        $newMenu->type      = $req['type'] ?? $sourceMenu->type;
        $newMenu->status    = $req['status'] ?? $sourceMenu->status;
        $newMenu->bind      = isset($req['bind']) ? (int) $req['bind'] : $sourceMenu->bind;

        $maxOrder = (int) Menu::find()->where(['group_id' => $newMenu->group_id, 'parent_id' => $newMenu->parent_id])->max('`order`');
        $newMenu->order = $maxOrder + 1;

        if ($newMenu->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Menu berhasil diclone di urutan terakhir!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $newMenu->getErrors()]);
    }

    public function actionDelete(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $menu = Menu::findOne($id);

        if ($menu) {
            $menu->deleted_at = gmdate('Y-m-d H:i:s');
            $menu->deleted_by = Yii::$app->user->id ?? 1;
            $menu->save(false);

            return $this->asJson(['status' => 'success', 'message' => 'Menu berhasil dihapus.']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menghapus menu.']);
    }
}