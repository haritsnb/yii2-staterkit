<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Html;
use app\models\User;
use app\models\UserProfile;

class UserController extends BaseAdminController
{
    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/users');
    }

    public function actionIndex(string $defaultRange = 'all')
    {
        return $this->render('index', [
            'defaultRange' => $defaultRange,
        ]);
    }

    /**
     * Endpoint DataTables Server-Side
     */
    public function actionList(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $draw           = (int) $request->get('draw', 1);
        $start          = (int) $request->get('start', 0);
        $length         = (int) $request->get('length', 10);
        $search         = $request->get('search')['value'] ?? '';
        $order          = $request->get('order', []);
        $startDate      = $request->get('start_date');
        $endDate        = $request->get('end_date');
        $isTrash        = (int) $request->get('is_trash', 0);
        $selectedFilter = $request->get('selected_filter', 'all');
        $selectedIds    = $request->get('selected_ids', []);

        if (!is_array($selectedIds)) {
            $selectedIds = json_decode($selectedIds, true) ?? [];
        }

        $trashCount = User::find()->where(['not', ['deleted_at' => null]])->count();

        $query = (new \yii\db\Query())
            ->select([
                'u.id', 'u.username', 'u.email', 'u.login_mode', 'u.avatar', 'u.status', 'u.registered_at', 'u.deleted_at',
                'p.name as full_name', 'p.phone', 'p.gender'
            ])
            ->from(['u' => User::tableName()])
            ->leftJoin(['p' => UserProfile::tableName()], 'p.user_id = u.id');

        if ($isTrash === 1) {
            $query->where(['not', ['u.deleted_at' => null]]);
        } else {
            $query->where(['u.deleted_at' => null]);
        }

        // Filter Tanggal
        if (!empty($startDate) && !empty($endDate) && $startDate !== 'all') {
            $query->andWhere(['between', 'u.registered_at', $startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Filter Checked / Unchecked
        if ($selectedFilter === 'checked') {
            if (!empty($selectedIds)) {
                $query->andWhere(['in', 'u.id', $selectedIds]);
            } else {
                $query->andWhere('1=0');
            }
        } elseif ($selectedFilter === 'unchecked') {
            if (!empty($selectedIds)) {
                $query->andWhere(['not in', 'u.id', $selectedIds]);
            }
        }

        $totalRecords = (clone $query)->count();

        // Search
        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $search],
                ['like', 'u.email', $search],
                ['like', 'p.name', $search],
                ['like', 'p.phone', $search],
                ['like', 'u.status', $search],
                ['like', 'u.login_mode', $search],
            ]);
        }
        $filteredRecords = (clone $query)->count();

        // Sorting
        if (!empty($order)) {
            $colIndex = (int) ($order[0]['column'] ?? 0);
            $colDir   = strtolower($order[0]['dir'] ?? 'desc') === 'asc' ? SORT_ASC : SORT_DESC;
            $columns  = [0 => 'u.id', 1 => 'u.id', 2 => 'p.name', 3 => 'u.username', 4 => 'u.email', 5 => 'u.login_mode', 6 => 'u.status', 7 => 'u.registered_at'];
            $sortCol  = $columns[$colIndex] ?? 'u.registered_at';
            $query->orderBy([$sortCol => $colDir]);
        } else {
            $query->orderBy(['u.registered_at' => SORT_DESC]);
        }

        $rows = $query->offset($start)->limit($length)->all();

        $data = [];
        foreach ($rows as $row) {
            $statusBadge = match ($row['status']) {
                'active'   => 'badge-success',
                'inactive' => 'badge-secondary',
                'banned'   => 'badge-danger',
                default    => 'badge-light'
            };

            $modeBadge = $row['login_mode'] === 'single_device' ? 'badge-info' : 'badge-primary';
            $isoDate = $row['registered_at'] ? gmdate('Y-m-d\TH:i:s\Z', strtotime($row['registered_at'])) : null;

            // Tombol Aksi dengan Penambahan Tombol Detail (<i class="fas fa-eye"></i>)
            if ($isTrash === 1) {
                $actions = '
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-secondary btn-detail" data-id="' . $row['id'] . '" title="Detail Pengguna">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-success btn-restore" data-id="' . $row['id'] . '" title="Restore Data">
                            <i class="fas fa-trash-restore"></i>
                        </button>
                        <button class="btn btn-danger btn-force-delete" data-id="' . $row['id'] . '" title="Force Delete">
                            <i class="fas fa-skull-crossbones"></i>
                        </button>
                    </div>';
            } else {
                $actions = '
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-secondary btn-detail" data-id="' . $row['id'] . '" title="Detail Pengguna">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-info btn-edit" data-id="' . $row['id'] . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-warning btn-delete" data-id="' . $row['id'] . '" title="Soft Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="btn btn-danger btn-force-delete" data-id="' . $row['id'] . '" title="Hard Delete Permanen">
                            <i class="fas fa-skull-crossbones"></i>
                        </button>
                    </div>';
            }

            $data[] = [
                'checkbox'      => '<div class="text-center"><input type="checkbox" class="row-checkbox" value="' . $row['id'] . '"></div>',
                'id'            => $row['id'],
                'full_name'     => Html::encode($row['full_name'] ?? '-'),
                'username'      => '<strong>' . Html::encode($row['username']) . '</strong>',
                'email'         => Html::encode($row['email']),
                'login_mode'    => '<span class="badge ' . $modeBadge . '">' . str_replace('_', ' ', strtoupper($row['login_mode'])) . '</span>',
                'status'        => '<span class="badge ' . $statusBadge . '">' . ucfirst($row['status']) . '</span>',
                'registered_at' => $isoDate,
                'actions'       => $actions
            ];
        }

        return $this->asJson([
            'draw'            => $draw,
            'recordsTotal'    => (int) $totalRecords,
            'recordsFiltered' => (int) $filteredRecords,
            'trashCount'      => (int) $trashCount,
            'data'            => $data,
        ]);
    }

    /**
     * Endpoint Detail Lengkap User + Profile + Audit Trail
     */
    public function actionViewData($id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::find()->with('profile')->where(['id' => $id])->one();

        if (!$user) {
            return $this->asJson(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        $formatIso = function (?string $date) {
            return $date ? gmdate('Y-m-d\TH:i:s\Z', strtotime($date)) : null;
        };

        return $this->asJson([
            'status' => 'success',
            'data' => [
                'id'            => $user->id,
                'username'      => $user->username,
                'email'         => $user->email,
                'login_mode'    => $user->login_mode,
                'status'        => $user->status,
                'avatar'        => $user->getAvatarUrl(),
                'name'          => $user->profile->name ?? $user->username,
                'gender'        => $user->profile->gender ?? 'other',
                'phone'         => $user->profile->phone ?? '-',
                'birth_place'   => $user->profile->birth_place ?? '-',
                'birth_date'    => $user->profile->birth_date ?? '-',
                'address'       => $user->profile->address ?? '-',

                // Audit Columns (UTC)
                'registered_at' => $formatIso($user->registered_at),
                'registered_by' => $user->registered_by ?? 1,
                'modified_at'   => $formatIso($user->modified_at),
                'modified_by'   => $user->modified_by,
                'deleted_at'    => $formatIso($user->deleted_at),
                'deleted_by'    => $user->deleted_by,
                'restored_at'   => $formatIso($user->restored_at),
                'restored_by'   => $user->restored_by,
            ]
        ]);
    }

    public function actionCreate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $user = new User();
        $user->username      = $req['username'] ?? null;
        $user->email         = $req['email'] ?? null;
        $user->password_hash = $req['password'] ?? '123456';
        $user->login_mode    = $req['login_mode'] ?? 'single_device';
        $user->status        = $req['status'] ?? 'active';

        if ($user->save()) {
            $profile = new UserProfile();
            $profile->user_id     = $user->id;
            $profile->name        = $req['name'] ?? $user->username;
            $profile->gender      = $req['gender'] ?? 'other';
            $profile->phone       = $req['phone'] ?? null;
            $profile->birth_place = $req['birth_place'] ?? null;
            $profile->birth_date  = !empty($req['birth_date']) ? $req['birth_date'] : null;
            $profile->address     = $req['address'] ?? null;
            $profile->save();

            return $this->asJson(['status' => 'success', 'message' => 'User berhasil ditambahkan!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $user->getErrors()]);
    }

    public function actionUpdate($id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request->post();

        $user = User::findOne($id);
        if (!$user) {
            return $this->asJson(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        $user->username   = $req['username'] ?? $user->username;
        $user->email      = $req['email'] ?? $user->email;
        $user->login_mode = $req['login_mode'] ?? $user->login_mode;
        $user->status     = $req['status'] ?? $user->status;

        if (!empty($req['password'])) {
            $user->password_hash = Yii::$app->security->generatePasswordHash($req['password']);
        }

        if ($user->save()) {
            $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);
            $profile->name        = $req['name'] ?? $profile->name;
            $profile->gender      = $req['gender'] ?? $profile->gender;
            $profile->phone       = $req['phone'] ?? $profile->phone;
            $profile->birth_place = $req['birth_place'] ?? $profile->birth_place;
            $profile->birth_date  = !empty($req['birth_date']) ? $req['birth_date'] : null;
            $profile->address     = $req['address'] ?? $profile->address;
            $profile->save();

            return $this->asJson(['status' => 'success', 'message' => 'User berhasil diperbarui!']);
        }

        return $this->asJson(['status' => 'error', 'errors' => $user->getErrors()]);
    }

    public function actionDelete($id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::findOne($id);

        if ($user && $user->softDelete()) {
            return $this->asJson(['status' => 'success', 'message' => 'User dipindahkan ke Recycle Bin!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menghapus user.']);
    }

    public function actionRestore($id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::findOne($id);

        if ($user && $user->restore()) {
            return $this->asJson(['status' => 'success', 'message' => 'User berhasil dipulihkan!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal memulihkan user.']);
    }

    public function actionForceDelete($id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::findOne($id);

        if ($user && $user->delete()) {
            return $this->asJson(['status' => 'success', 'message' => 'User BERHASIL DIHAPUS PERMANEN!']);
        }

        return $this->asJson(['status' => 'error', 'message' => 'Gagal menghapus permanen user.']);
    }

    public function actionBulkDelete(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = Yii::$app->request->post('ids', []);

        if (empty($ids)) {
            return $this->asJson(['status' => 'error', 'message' => 'Tidak ada data yang dipilih']);
        }

        $nowUtc = gmdate('Y-m-d H:i:s');
        $userId = Yii::$app->user->id ?? 1;

        $count = User::updateAll(
            ['deleted_at' => $nowUtc, 'deleted_by' => $userId],
            ['and', ['id' => $ids], ['deleted_at' => null]]
        );

        return $this->asJson([
            'status' => 'success',
            'message' => "{$count} pengguna berhasil dipindahkan ke Recycle Bin!"
        ]);
    }

    public function actionBulkRestore(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = Yii::$app->request->post('ids', []);

        if (empty($ids)) {
            return $this->asJson(['status' => 'error', 'message' => 'Tidak ada data yang dipilih']);
        }

        $nowUtc = gmdate('Y-m-d H:i:s');
        $userId = Yii::$app->user->id ?? 1;

        $count = User::updateAll(
            ['deleted_at' => null, 'deleted_by' => null, 'restored_at' => $nowUtc, 'restored_by' => $userId],
            ['and', ['id' => $ids], ['not', ['deleted_at' => null]]]
        );

        return $this->asJson([
            'status' => 'success',
            'message' => "{$count} pengguna berhasil dipulihkan!"
        ]);
    }

    public function actionBulkForceDelete(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = Yii::$app->request->post('ids', []);

        if (empty($ids)) {
            return $this->asJson(['status' => 'error', 'message' => 'Tidak ada data yang dipilih']);
        }

        $count = User::deleteAll(['id' => $ids]);

        return $this->asJson([
            'status' => 'success',
            'message' => "{$count} pengguna BERHASIL DIHAPUS PERMANEN dari database!"
        ]);
    }
}