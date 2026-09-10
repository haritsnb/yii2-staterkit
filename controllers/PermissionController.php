<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Html;
use app\models\AuthPermission;
use app\models\AuthRole;
use app\models\AuthRolePermission;

class PermissionController extends BaseAdminController
{
    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/permissions');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * DataTables Server-Side untuk Daftar Permissions
     */
    public function actionListDatatable(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';
        $type   = $request->get('type', '');

        $query = AuthPermission::find()->with(['parent'])->where(['deleted_at' => null]);
        if (!empty($type)) {
            $query->andWhere(['type' => $type]);
        }

        $totalRecords = (clone $query)->count();

        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'code', $search],
                ['like', 'name', $search],
                ['like', 'description', $search],
                ['like', 'type', $search],
            ]);
        }
        $filteredRecords = (clone $query)->count();

        $rows = $query->orderBy(['type' => SORT_ASC, 'code' => SORT_ASC])
            ->offset($start)
            ->limit($length)
            ->all();

        $data = [];
        foreach ($rows as $row) {
            $typeBadges = [
                'module' => '<span class="badge badge-dark">MODULE</span>',
                'page'   => '<span class="badge badge-primary">PAGE</span>',
                'widget' => '<span class="badge badge-info">WIDGET</span>',
                'action' => '<span class="badge badge-success">ACTION</span>',
            ];

            $parentName = $row->parent ? Html::encode($row->parent->name . ' (' . $row->parent->code . ')') : '<span class="badge badge-light border">Root (Module)</span>';
            $roleCount = AuthRolePermission::find()->where(['permission_id' => $row->id])->count();

            $data[] = [
                'id'          => $row->id,
                'name'        => '<strong>' . Html::encode($row->name) . '</strong>',
                'code'        => '<code>' . Html::encode($row->code) . '</code>',
                'type'        => $typeBadges[$row->type] ?? '<span class="badge badge-light">' . $row->type . '</span>',
                'parent_name' => $parentName,
                'role_count'  => '<span class="badge badge-secondary">' . $roleCount . ' Role Pemilik</span>',
                'status'      => $row->status === 'active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>',
                'actions'     => '
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-secondary btn-detail-perm" data-id="' . $row->id . '" title="Detail & Role Pemilik"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-info btn-edit-perm" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-success btn-clone-subtree" data-id="' . $row->id . '" title="Clone Subtree"><i class="fas fa-copy"></i></button>
                        <button class="btn btn-danger btn-delete-perm" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>
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

    public function actionGetParentOptions(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $perms = AuthPermission::find()
            ->where(['in', 'type', ['module', 'page', 'widget']])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['type' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        $data = [];
        foreach ($perms as $p) {
            $data[] = [
                'id'   => $p->id,
                'name' => '[' . strtoupper($p->type) . '] ' . $p->name . ' (' . $p->code . ')',
                'type' => $p->type,
            ];
        }

        return $this->asJson(['status' => 'success', 'data' => $data]);
    }

    public function actionCreate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $perm = new AuthPermission();
        $perm->parent_id   = (int) ($req['parent_id'] ?? 0);
        $perm->code        = trim($req['code'] ?? '');
        $perm->name        = trim($req['name'] ?? '');
        $perm->type        = $req['type'] ?? 'action';
        $perm->description = $req['description'] ?? null;
        $perm->status      = $req['status'] ?? 'active';

        if ($perm->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Permission berhasil ditambahkan!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $perm->getErrors()]);
    }

    public function actionViewData(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $perm = AuthPermission::findOne(['id' => $id, 'deleted_at' => null]);

        if (!$perm) {
            return $this->asJson(['status' => 'error', 'message' => 'Permission tidak ditemukan']);
        }

        return $this->asJson([
            'status' => 'success',
            'data' => [
                'id'          => $perm->id,
                'parent_id'   => $perm->parent_id,
                'code'        => $perm->code,
                'name'        => $perm->name,
                'type'        => $perm->type,
                'description' => $perm->description,
                'status'      => $perm->status,
            ]
        ]);
    }

    public function actionUpdate(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $perm = AuthPermission::findOne($id);
        if (!$perm) {
            return $this->asJson(['status' => 'error', 'message' => 'Permission tidak ditemukan']);
        }

        $perm->parent_id   = (int) ($req['parent_id'] ?? $perm->parent_id);
        $perm->code        = trim($req['code'] ?? $perm->code);
        $perm->name        = trim($req['name'] ?? $perm->name);
        $perm->type        = $req['type'] ?? $perm->type;
        $perm->description = $req['description'] ?? $perm->description;
        $perm->status      = $req['status'] ?? $perm->status;

        if ($perm->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Permission berhasil diperbarui!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $perm->getErrors()]);
    }

    public function actionCloneSubtree(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $sourceId       = (int) ($req['source_id'] ?? 0);
        $targetParentId = (int) ($req['target_parent_id'] ?? 0);
        $prefixFind     = trim($req['prefix_find'] ?? '');
        $prefixReplace  = trim($req['prefix_replace'] ?? '');

        $sourcePerm = AuthPermission::findOne(['id' => $sourceId, 'deleted_at' => null]);
        if (!$sourcePerm) {
            return $this->asJson(['status' => 'error', 'message' => 'Permission sumber tidak ditemukan.']);
        }

        if (empty($prefixFind) || empty($prefixReplace)) {
            return $this->asJson(['status' => 'error', 'message' => 'Prefix Kode Lama dan Prefix Kode Baru wajib diisi.']);
        }

        $cloned = $sourcePerm->cloneSubtree($targetParentId, $prefixFind, $prefixReplace);
        if ($cloned) {
            return $this->asJson(['status' => 'success', 'message' => 'Pohon Permission berhasil diclone secara rekursif!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menduplikasi pohon permission.']);
    }

    public function actionDelete(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $perm = AuthPermission::findOne($id);

        if ($perm) {
            $perm->deleted_at = gmdate('Y-m-d H:i:s');
            $perm->deleted_by = Yii::$app->user->id ?? 1;
            $perm->save(false);

            return $this->asJson(['status' => 'success', 'message' => 'Permission berhasil dihapus.']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menghapus permission.']);
    }

    // =========================================================================
    // ENDPOINT DETAIL PERMISSION: LIHAT & KELOLA ROLE PEMILIK IZIN
    // =========================================================================

    /**
     * Ambil data detail permission dan daftar Role pemilik izin
     */
    public function actionGetPermissionDetailData(int $permission_id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $perm = AuthPermission::find()->with(['parent'])->where(['id' => $permission_id, 'deleted_at' => null])->one();

        if (!$perm) {
            return $this->asJson(['status' => 'error', 'message' => 'Permission tidak ditemukan']);
        }

        // 1. Role yang memiliki permission secara langsung (Direct)
        $directRoleRows = (new \yii\db\Query())
            ->select(['r.id', 'r.name', 'r.code', 'r.level', 'rp.created_at'])
            ->from(['rp' => AuthRolePermission::tableName()])
            ->innerJoin(['r' => AuthRole::tableName()], 'r.id = rp.role_id')
            ->where(['rp.permission_id' => $permission_id, 'r.deleted_at' => null])
            ->all();

        $directRoleIds = array_column($directRoleRows, 'id');

        // 2. Role atasan yang mewarisi permission ini secara otomatis (Inherited)
        $allRoles = AuthRole::find()->where(['deleted_at' => null, 'status' => 'active'])->all();
        $inheritedRoles = [];

        foreach ($allRoles as $r) {
            if (in_array($r->id, $directRoleIds, false)) continue;

            $descendantIds = $r->getDescendantRoleIds();
            $hasInherited = count(array_intersect($descendantIds, $directRoleIds)) > 0;

            if ($hasInherited) {
                $inheritedRoles[] = [
                    'id'    => $r->id,
                    'name'  => $r->name,
                    'code'  => $r->code,
                    'level' => $r->level,
                ];
            }
        }

        // 3. Daftar Role yang tersedia untuk ditambahkan izin langsung
        $availableRoles = [];
        foreach ($allRoles as $r) {
            if (!in_array($r->id, $directRoleIds, false)) {
                $availableRoles[] = [
                    'id'   => $r->id,
                    'name' => $r->name . ' (' . $r->code . ') [Level ' . $r->level . ']',
                ];
            }
        }

        return $this->asJson([
            'status' => 'success',
            'permission' => [
                'id'          => $perm->id,
                'name'        => $perm->name,
                'code'        => $perm->code,
                'type'        => strtoupper($perm->type),
                'parent_name' => $perm->parent ? $perm->parent->name . ' (' . $perm->parent->code . ')' : 'Root (Module)',
                'description' => $perm->description ?: 'Tidak ada deskripsi',
            ],
            'direct_roles'    => $directRoleRows,
            'inherited_roles' => $inheritedRoles,
            'available_roles' => $availableRoles,
        ]);
    }

    /**
     * Berikan Izin ini ke Suatu Role
     */
    public function actionAddRoleToPermission(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $permId = (int) ($req['permission_id'] ?? 0);
        $roleId = (int) ($req['role_id'] ?? 0);

        if (!$permId || !$roleId) {
            return $this->asJson(['status' => 'error', 'message' => 'Permission dan Role wajib dipilih.']);
        }

        $exists = AuthRolePermission::find()->where(['role_id' => $roleId, 'permission_id' => $permId])->exists();
        if ($exists) {
            return $this->asJson(['status' => 'error', 'message' => 'Role sudah memiliki izin ini.']);
        }

        $map = new AuthRolePermission();
        $map->role_id       = $roleId;
        $map->permission_id = $permId;
        if ($map->save()) {
            return $this->asJson(['status' => 'success', 'message' => 'Izin berhasil diberikan ke role!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal memberikan izin ke role.']);
    }

    /**
     * Cabut Izin ini dari Suatu Role
     */
    public function actionRemoveRoleFromPermission(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $permId = (int) ($req['permission_id'] ?? 0);
        $roleId = (int) ($req['role_id'] ?? 0);

        $deleted = AuthRolePermission::deleteAll(['role_id' => $roleId, 'permission_id' => $permId]);
        if ($deleted) {
            return $this->asJson(['status' => 'success', 'message' => 'Izin berhasil dicabut dari role!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal mencabut izin dari role.']);
    }
}