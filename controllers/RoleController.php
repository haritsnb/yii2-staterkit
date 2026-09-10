<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Html;
use app\models\AuthRole;
use app\models\AuthPermission;
use app\models\AuthRolePermission;
use app\models\AuthUserRole;
use app\models\User;

class RoleController extends BaseAdminController
{
    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/roles');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * DataTables Server-Side untuk Daftar Roles
     */
    public function actionListDatatable(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';

        $query = AuthRole::find()->with(['parent'])->where(['deleted_at' => null]);
        $totalRecords = (clone $query)->count();

        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'name', $search],
                ['like', 'code', $search],
                ['like', 'description', $search],
                ['like', 'status', $search],
            ]);
        }
        $filteredRecords = (clone $query)->count();

        $rows = $query->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])
            ->offset($start)
            ->limit($length)
            ->all();

        $data = [];
        foreach ($rows as $row) {
            $parentName = $row->parent ? Html::encode($row->parent->name) : '<span class="badge badge-light border">Top Level / Root</span>';
            $levelBadge = '<span class="badge badge-info">Level ' . $row->level . '</span>';
            $statusBadge = $row->status === 'active' 
                ? '<span class="badge badge-success">Active</span>' 
                : '<span class="badge badge-secondary">Inactive</span>';

            $permCount = AuthRolePermission::find()->where(['role_id' => $row->id])->count();
            $userCount = AuthUserRole::find()->where(['role_id' => $row->id])->count();

            $data[] = [
                'id'          => $row->id,
                'name'        => '<strong>' . Html::encode($row->name) . '</strong>',
                'code'        => '<code>' . Html::encode($row->code) . '</code>',
                'parent_name' => $parentName,
                'level'       => $levelBadge,
                'perm_count'  => '<span class="badge badge-primary">' . $permCount . ' Izin</span>',
                'user_count'  => '<span class="badge badge-secondary">' . $userCount . ' Pengguna</span>',
                'status'      => $statusBadge,
                'actions'     => '
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-secondary btn-detail-role" data-id="' . $row->id . '" title="Detail Role & Anggota"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-primary btn-matrix-perm" data-id="' . $row->id . '" title="Matriks Hak Akses"><i class="fas fa-key"></i></button>
                        <button class="btn btn-info btn-edit-role" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-success btn-clone-role" data-id="' . $row->id . '" title="Clone Role"><i class="fas fa-copy"></i></button>
                        <button class="btn btn-danger btn-delete-role" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>
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

    public function actionGetRolesList(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $roles = AuthRole::find()->where(['deleted_at' => null])->orderBy(['level' => SORT_ASC, 'name' => SORT_ASC])->all();

        $data = [];
        foreach ($roles as $r) {
            $prefix = str_repeat('— ', max(0, $r->level - 1));
            $data[] = [
                'id'    => $r->id,
                'name'  => $prefix . $r->name . ' (' . $r->code . ')',
                'level' => $r->level,
            ];
        }

        return $this->asJson(['status' => 'success', 'data' => $data]);
    }

    public function actionCreate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $role = new AuthRole();
        $role->name        = $req['name'] ?? '';
        $role->code        = $req['code'] ?? '';
        $role->parent_id   = (int) ($req['parent_id'] ?? 0);
        $role->description = $req['description'] ?? null;
        $role->status      = $req['status'] ?? 'active';

        if ($role->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Role berhasil ditambahkan!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $role->getErrors()]);
    }

    public function actionViewData(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $role = AuthRole::findOne(['id' => $id, 'deleted_at' => null]);

        if (!$role) {
            return $this->asJson(['status' => 'error', 'message' => 'Role tidak ditemukan']);
        }

        return $this->asJson([
            'status' => 'success',
            'data' => [
                'id'          => $role->id,
                'parent_id'   => $role->parent_id,
                'name'        => $role->name,
                'code'        => $role->code,
                'description' => $role->description,
                'status'      => $role->status,
                'level'       => $role->level,
            ]
        ]);
    }

    public function actionUpdate(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $role = AuthRole::findOne($id);
        if (!$role) {
            return $this->asJson(['status' => 'error', 'message' => 'Role tidak ditemukan']);
        }

        $newParentId = (int) ($req['parent_id'] ?? $role->parent_id);
        if ($newParentId === $role->id) {
            return $this->asJson(['status' => 'error', 'message' => 'Role tidak dapat menjadi parent untuk dirinya sendiri.']);
        }

        $role->name        = $req['name'] ?? $role->name;
        $role->code        = $req['code'] ?? $role->code;
        $role->parent_id   = $newParentId;
        $role->description = $req['description'] ?? $role->description;
        $role->status      = $req['status'] ?? $role->status;

        if ($role->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Role berhasil diperbarui!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $role->getErrors()]);
    }

    public function actionClone(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $sourceId = (int) ($req['source_role_id'] ?? 0);
        $newCode  = trim($req['new_code'] ?? '');
        $newName  = trim($req['new_name'] ?? '');
        $parentId = isset($req['parent_id']) && $req['parent_id'] !== '' ? (int) $req['parent_id'] : null;

        $sourceRole = AuthRole::findOne(['id' => $sourceId, 'deleted_at' => null]);
        if (!$sourceRole) {
            return $this->asJson(['status' => 'error', 'message' => 'Role sumber tidak ditemukan.']);
        }

        if (empty($newCode) || empty($newName)) {
            return $this->asJson(['status' => 'error', 'message' => 'Kode Role Baru dan Nama Role Baru wajib diisi.']);
        }

        $cloned = $sourceRole->cloneRole($newCode, $newName, $parentId);
        if ($cloned) {
            return $this->asJson(['status' => 'success', 'message' => 'Role berhasil diclone beserta seluruh hak aksesnya!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menduplikasi role. Kode role mungkin sudah digunakan.']);
    }

    public function actionDelete(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $role = AuthRole::findOne($id);

        if ($role) {
            $role->deleted_at = gmdate('Y-m-d H:i:s');
            $role->deleted_by = Yii::$app->user->id ?? 1;
            $role->save(false);

            return $this->asJson(['status' => 'success', 'message' => 'Role berhasil dinonaktifkan.']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menghapus role.']);
    }

    // =========================================================================
    // ENDPOINT DETAIL ROLE: MANAJEMEN PENGGUNA (ROLE UTAMA VS BYPASS)
    // =========================================================================

    /**
     * Ambil detail users dan permissions yang dimiliki role ini
     */
    public function actionGetRoleDetailData(int $role_id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $role = AuthRole::find()->with(['parent'])->where(['id' => $role_id, 'deleted_at' => null])->one();

        if (!$role) {
            return $this->asJson(['status' => 'error', 'message' => 'Role tidak ditemukan']);
        }

        // 1. Ambil Pengguna Pemilik Role
        $userRoles = AuthUserRole::find()
            ->with(['user.profile'])
            ->where(['role_id' => $role_id])
            ->all();

        $users = [];
        $existingUserIds = [];
        foreach ($userRoles as $ur) {
            if (!$ur->user || $ur->user->deleted_at !== null) continue;
            $existingUserIds[] = $ur->user_id;
            $users[] = [
                'user_id'     => $ur->user_id,
                'name'        => $ur->user->profile->name ?? $ur->user->username,
                'username'    => $ur->user->username,
                'email'       => $ur->user->email,
                'user_type'   => $ur->user_type, // 'primary' | 'bypass'
                'avatar'      => $ur->user->getAvatarUrl(),
                'assigned_at' => gmdate('Y-m-d\TH:i:s\Z', strtotime($ur->assigned_at)),
            ];
        }

        // 2. Ambil Permissions Langsung (Direct)
        $directPerms = (new \yii\db\Query())
            ->select(['p.id', 'p.name', 'p.code', 'p.type'])
            ->from(['rp' => AuthRolePermission::tableName()])
            ->innerJoin(['p' => AuthPermission::tableName()], 'p.id = rp.permission_id')
            ->where(['rp.role_id' => $role_id, 'p.deleted_at' => null])
            ->all();

        // 3. Ambil Permissions Diwarisi (Inherited)
        $descendantIds = $role->getDescendantRoleIds();
        $inheritedPerms = [];
        if (!empty($descendantIds)) {
            $inheritedPerms = (new \yii\db\Query())
                ->select(['p.id', 'p.name', 'p.code', 'p.type', 'r.name as inherited_from_role'])
                ->from(['rp' => AuthRolePermission::tableName()])
                ->innerJoin(['p' => AuthPermission::tableName()], 'p.id = rp.permission_id')
                ->innerJoin(['r' => AuthRole::tableName()], 'r.id = rp.role_id')
                ->where(['in', 'rp.role_id', $descendantIds])
                ->andWhere(['p.deleted_at' => null])
                ->all();
        }

        // 4. Daftar User yang Tersedia untuk Ditambahkan
        $availableUsers = User::find()
            ->with(['profile'])
            ->where(['deleted_at' => null, 'status' => 'active'])
            ->all();

        $userOptions = [];
        foreach ($availableUsers as $u) {
            $userOptions[] = [
                'id'   => $u->id,
                'name' => ($u->profile->name ?? $u->username) . ' (@' . $u->username . ')',
            ];
        }

        // 5. Daftar Semua Permission untuk Ditambahkan Langsung
        $allPerms = AuthPermission::find()->where(['deleted_at' => null])->orderBy(['code' => SORT_ASC])->all();
        $permOptions = [];
        foreach ($allPerms as $p) {
            $permOptions[] = [
                'id'   => $p->id,
                'name' => '[' . strtoupper($p->type) . '] ' . $p->name . ' (' . $p->code . ')',
            ];
        }

        return $this->asJson([
            'status' => 'success',
            'role'   => [
                'id'          => $role->id,
                'name'        => $role->name,
                'code'        => $role->code,
                'level'       => $role->level,
                'parent_name' => $role->parent ? $role->parent->name : 'Top Level / Root',
                'description' => $role->description ?: 'Tidak ada deskripsi',
            ],
            'users'            => $users,
            'direct_perms'     => $directPerms,
            'inherited_perms'  => $inheritedPerms,
            'available_users'  => $userOptions,
            'available_perms'  => $permOptions,
        ]);
    }

    /**
     * Tambah Pengguna ke Role (Bisa sebagai Primary atau Bypass)
     */
    public function actionAddUserToRole(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $roleId   = (int) ($req['role_id'] ?? 0);
        $userId   = (int) ($req['user_id'] ?? 0);
        $userType = $req['user_type'] ?? 'bypass'; // 'primary' | 'bypass'

        if (!$roleId || !$userId) {
            return $this->asJson(['status' => 'error', 'message' => 'Role dan Pengguna wajib dipilih.']);
        }

        if ($userType === 'primary') {
            $success = AuthUserRole::assignPrimaryRole($userId, $roleId);
        } else {
            $success = AuthUserRole::addBypassRole($userId, $roleId);
        }

        if ($success) {
            return $this->asJson(['status' => 'success', 'message' => 'Pengguna berhasil ditambahkan ke role!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menambahkan pengguna ke role.']);
    }

    /**
     * Keluarkan Pengguna dari Role
     */
    public function actionRemoveUserFromRole(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $roleId = (int) ($req['role_id'] ?? 0);
        $userId = (int) ($req['user_id'] ?? 0);

        if (!$roleId || !$userId) {
            return $this->asJson(['status' => 'error', 'message' => 'Parameter tidak valid.']);
        }

        $deleted = AuthUserRole::deleteAll(['user_id' => $userId, 'role_id' => $roleId]);
        if ($deleted) {
            return $this->asJson(['status' => 'success', 'message' => 'Pengguna berhasil dikeluarkan dari role!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal mengeluarkan pengguna.']);
    }

    /**
     * Tambah Satu Permission ke Role
     */
    public function actionAddPermissionToRole(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $roleId = (int) ($req['role_id'] ?? 0);
        $permId = (int) ($req['permission_id'] ?? 0);

        if (!$roleId || !$permId) {
            return $this->asJson(['status' => 'error', 'message' => 'Role dan Permission wajib dipilih.']);
        }

        $exists = AuthRolePermission::find()->where(['role_id' => $roleId, 'permission_id' => $permId])->exists();
        if ($exists) {
            return $this->asJson(['status' => 'error', 'message' => 'Permission ini sudah dimiliki role.']);
        }

        $map = new AuthRolePermission();
        $map->role_id       = $roleId;
        $map->permission_id = $permId;
        if ($map->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Izin berhasil ditambahkan ke role!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menambahkan izin.']);
    }

    /**
     * Hapus Satu Permission dari Role
     */
    public function actionRemovePermissionFromRole(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $roleId = (int) ($req['role_id'] ?? 0);
        $permId = (int) ($req['permission_id'] ?? 0);

        $deleted = AuthRolePermission::deleteAll(['role_id' => $roleId, 'permission_id' => $permId]);
        if ($deleted) {
            return $this->asJson(['status' => 'success', 'message' => 'Izin berhasil dicabut dari role!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal mencabut izin.']);
    }

    // =========================================================================
    // ENDPOINT MATRIKS HAK AKSES
    // =========================================================================

    public function actionGetPermissionMatrix(int $role_id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $role = AuthRole::findOne(['id' => $role_id, 'deleted_at' => null]);

        if (!$role) {
            return $this->asJson(['status' => 'error', 'message' => 'Role tidak ditemukan']);
        }

        $directPermIds = AuthRolePermission::find()->select('permission_id')->where(['role_id' => $role_id])->column();
        $descendantRoleIds = $role->getDescendantRoleIds();
        $inheritedPermIds = [];
        if (!empty($descendantRoleIds)) {
            $inheritedPermIds = AuthRolePermission::find()
                ->select('permission_id')
                ->where(['in', 'role_id', $descendantRoleIds])
                ->column();
        }

        $allPermissions = AuthPermission::find()->where(['deleted_at' => null])->orderBy(['type' => SORT_ASC, 'code' => SORT_ASC])->all();

        $pMap = [];
        foreach ($allPermissions as $p) {
            $pMap[$p->parent_id][] = [
                'id'           => $p->id,
                'code'         => $p->code,
                'name'         => $p->name,
                'type'         => $p->type,
                'is_direct'    => in_array($p->id, $directPermIds, false),
                'is_inherited' => in_array($p->id, $inheritedPermIds, false),
            ];
        }

        return $this->asJson([
            'status' => 'success',
            'role'   => [
                'id'   => $role->id,
                'name' => $role->name,
                'code' => $role->code,
            ],
            'direct_count'    => count($directPermIds),
            'inherited_count' => count(array_unique($inheritedPermIds)),
            'tree_map'        => $pMap,
        ]);
    }

    public function actionSavePermissionMatrix(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $roleId        = (int) ($req['role_id'] ?? 0);
        $permissionIds = $req['permission_ids'] ?? [];

        if (!$roleId) {
            return $this->asJson(['status' => 'error', 'message' => 'Role ID tidak valid.']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            AuthRolePermission::deleteAll(['role_id' => $roleId]);

            $nowUtc = gmdate('Y-m-d H:i:s');
            $userId = Yii::$app->user->id ?? 1;

            foreach ($permissionIds as $permId) {
                $map = new AuthRolePermission();
                $map->role_id       = $roleId;
                $map->permission_id = (int) $permId;
                $map->created_at    = $nowUtc;
                $map->created_by    = $userId;
                $map->save(false);
            }

            $transaction->commit();
            return $this->asJson(['status' => 'success', 'message' => 'Hak akses role berhasil diperbarui!']);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return $this->asJson(['status' => 'error', 'message' => 'Gagal menyimpan hak akses: ' . $e->getMessage()]);
        }
    }
}