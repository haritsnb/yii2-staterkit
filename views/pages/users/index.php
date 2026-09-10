<?php

/** @var yii\web\View $this */
/** @var string $defaultRange */

use yii\helpers\Url;

$this->title = 'Manajemen Pengguna';

$listUrl            = Url::to(['/user/list']);
$createUrl          = Url::to(['/user/create']);
$viewDataUrl        = Url::to(['/user/view-data']);
$updateUrl          = Url::to(['/user/update']);
$deleteUrl          = Url::to(['/user/delete']);
$restoreUrl         = Url::to(['/user/restore']);
$forceDeleteUrl     = Url::to(['/user/force-delete']);
$bulkDeleteUrl      = Url::to(['/user/bulk-delete']);
$bulkRestoreUrl     = Url::to(['/user/bulk-restore']);
$bulkForceDeleteUrl = Url::to(['/user/bulk-force-delete']);
$getUserRolesUrl    = Url::to(['/role/get-user-roles']);
$saveUserRolesUrl   = Url::to(['/role/save-user-roles']);

$defaultRangeConfig = $defaultRange ?? 'all';

$this->registerJsVar('userUrls', [
    'list'            => $listUrl,
    'create'          => $createUrl,
    'viewData'        => $viewDataUrl,
    'update'          => $updateUrl,
    'delete'          => $deleteUrl,
    'restore'         => $restoreUrl,
    'forceDelete'     => $forceDeleteUrl,
    'bulkDelete'      => $bulkDeleteUrl,
    'bulkRestore'     => $bulkRestoreUrl,
    'bulkForceDelete' => $bulkForceDeleteUrl,
    'getUserRoles'    => $getUserRolesUrl,
    'saveUserRoles'   => $saveUserRolesUrl,
    'devDefaultPreset'=> $defaultRangeConfig,
]);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark" id="page-heading">
                    <i class="fas fa-users text-primary mr-2"></i> Manajemen Pengguna
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" id="btn-toggle-trash" class="btn btn-outline-danger btn-sm font-weight-bold mr-2 shadow-xs">
                    <i class="fas fa-trash-alt mr-1"></i> Recycle Bin <span id="trash-badge" class="badge badge-danger ml-1">0</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-xs" data-toggle="modal" data-target="#modal-create" id="btn-add-user">
                    <i class="fas fa-plus mr-1"></i> Tambah Pengguna
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Filter Date Range -->
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="mb-1 font-weight-bold text-dark text-sm"><i class="far fa-calendar-alt text-primary mr-1"></i> Rentang Tanggal Registrasi:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-primary"></i></span>
                            </div>
                            <input type="text" class="form-control float-right font-weight-bold bg-white" id="filter-daterange" readonly style="cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Action Toolbar -->
        <div class="card card-warning card-outline mb-3 shadow-sm" id="bulk-action-bar" style="display: none;">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                        <span class="badge badge-warning p-2 font-weight-bold shadow-xs" style="font-size: 0.92rem;">
                            <i class="fas fa-check-square mr-1"></i> <span id="selected-count">0</span> Data Terpilih
                        </span>
                        <button type="button" id="btn-clear-selection" class="btn btn-link btn-sm text-danger ml-1 font-weight-bold" title="Batalkan semua pilihan">
                            <i class="fas fa-times-circle"></i> Batal Pilih
                        </button>
                    </div>

                    <div class="col-lg-4 col-md-4 mb-2 mb-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light font-weight-bold text-xs">Tampilkan:</span>
                            </div>
                            <select id="filter-selection-mode" class="form-control select2" style="width: 60%;">
                                <option value="all">Semua Data (Biasa)</option>
                                <option value="checked">Hanya Yang Dicentang (Checked)</option>
                                <option value="unchecked">Hanya Yang Belum Dicentang (Unchecked)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-4 text-md-right">
                        <div id="bulk-active-actions" style="display: none;">
                            <button type="button" id="btn-bulk-delete" class="btn btn-warning btn-sm font-weight-bold mr-1 shadow-xs">
                                <i class="fas fa-trash mr-1"></i> Bulk Soft-Delete
                            </button>
                            <button type="button" id="btn-bulk-force-delete" class="btn btn-danger btn-sm font-weight-bold shadow-xs">
                                <i class="fas fa-skull-crossbones mr-1"></i> Bulk Destroy
                            </button>
                        </div>
                        <div id="bulk-trash-actions" style="display: none;">
                            <button type="button" id="btn-bulk-restore" class="btn btn-success btn-sm font-weight-bold mr-1 shadow-xs">
                                <i class="fas fa-trash-restore mr-1"></i> Bulk Restore
                            </button>
                            <button type="button" id="btn-bulk-force-delete-trash" class="btn btn-danger btn-sm font-weight-bold shadow-xs">
                                <i class="fas fa-skull-crossbones mr-1"></i> Bulk Destroy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel DataTables Server-Side -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark mb-0" id="table-card-title">
                    <i class="fas fa-users text-primary mr-1"></i> Data Pengguna Aktif
                </h3>
            </div>
            <div class="card-body">
                <table id="table-users" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th style="width: 3%;" class="text-center">
                                <input type="checkbox" id="check-all-page" title="Pilih Semua di Halaman Ini">
                            </th>
                            <th style="width: 5%">ID</th>
                            <th style="width: 18%">Nama Lengkap</th>
                            <th style="width: 13%">Username</th>
                            <th style="width: 14%">Email</th>
                            <th style="width: 10%">Mode Login</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 14%">Tanggal Registrasi</th>
                            <th style="width: 13%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- ================= MODAL 1: DETAIL LENGKAP PENGGUNA (BIODATA, ROLE & AUDIT) ================= -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-gradient-primary text-white py-3">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-user-circle mr-2"></i> Detail Lengkap Pengguna
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card card-outline card-primary shadow-xs mb-3">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <img src="" id="detail-avatar" class="img-circle elevation-2 border" style="width: 75px; height: 75px; object-fit: cover;" alt="Avatar">
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="font-weight-bold mb-0 text-dark" id="detail-name">-</h4>
                                <div class="text-muted font-italic mb-1" id="detail-username">-</div>
                                <div>
                                    <span class="badge badge-info mr-1" id="detail-login-mode">-</span>
                                    <span class="badge badge-success mr-1" id="detail-status">-</span>
                                    <span class="badge badge-light border" id="detail-gender">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Informasi Pribadi & Kontak -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-xs h-100 mb-0">
                            <div class="card-header bg-white py-2">
                                <h6 class="font-weight-bold mb-0 text-secondary"><i class="fas fa-address-card mr-1 text-primary"></i> Informasi Pribadi</h6>
                            </div>
                            <div class="card-body p-3">
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="text-muted">Email:</span>
                                        <strong class="text-dark" id="detail-email">-</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="text-muted">No. Telepon:</span>
                                        <strong class="text-dark" id="detail-phone">-</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="text-muted">Tempat, Tgl Lahir:</span>
                                        <strong class="text-dark" id="detail-birth">-</strong>
                                    </li>
                                    <li class="list-group-item px-0 py-2">
                                        <span class="text-muted d-block mb-1">Alamat:</span>
                                        <div class="text-dark font-weight-bold" id="detail-address">-</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Hak Akses Role & Audit Trail -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-xs h-100 mb-0">
                            <div class="card-header bg-white py-2">
                                <h6 class="font-weight-bold mb-0 text-secondary"><i class="fas fa-user-shield mr-1 text-warning"></i> Role & Audit Trail</h6>
                            </div>
                            <div class="card-body p-3">
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item px-0 py-2">
                                        <span class="text-muted d-block mb-1 font-weight-bold">Role Utama (Primary):</span>
                                        <div id="detail-primary-role"><span class="badge badge-primary px-2 py-1"><i class="fas fa-crown text-warning mr-1"></i> Memuat...</span></div>
                                    </li>
                                    <li class="list-group-item px-0 py-2">
                                        <span class="text-muted d-block mb-1 font-weight-bold">Role Bypass / Tambahan:</span>
                                        <div id="detail-bypass-roles"><span class="text-muted">-</span></div>
                                    </li>
                                    <li class="list-group-item px-0 py-2">
                                        <span class="text-muted d-block">Terdaftar Pada:</span>
                                        <div id="detail-registered-at">-</div>
                                    </li>
                                    <li class="list-group-item px-0 py-2">
                                        <span class="text-muted d-block">Terakhir Diubah:</span>
                                        <div id="detail-modified-at">-</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-white d-flex justify-content-between py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold" data-dismiss="modal">Tutup</button>
                <div>
                    <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-xs mr-1" id="btn-roles-from-detail">
                        <i class="fas fa-user-shield mr-1"></i> Kelola Role
                    </button>
                    <button type="button" class="btn btn-info btn-sm font-weight-bold shadow-xs" id="btn-edit-from-detail">
                        <i class="fas fa-edit mr-1"></i> Edit Pengguna
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: PENUGASAN ROLE USER (1 PRIMARY + UNLIMITED BYPASS) ================= -->
<div class="modal fade" id="modal-user-roles" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-user-roles-submit">
                <input type="hidden" name="user_id" id="assign-user-id">
                <div class="modal-header bg-gradient-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-shield mr-2"></i> Penugasan Role Pengguna (RBAC)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 py-2">
                        <span class="text-muted small d-block">Target Pengguna:</span>
                        <strong class="text-dark h6 mb-0" id="assign-user-display">-</strong>
                    </div>

                    <!-- 1. ROLE UTAMA (HANYA BOLEH 1) -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small d-block">
                            <i class="fas fa-crown text-warning mr-1"></i> Role Utama (Primary Role) *
                        </label>
                        <select name="primary_role_id" id="select-primary-role" class="form-control select2" style="width: 100%;" required></select>
                        <small class="text-muted">Setiap pengguna wajib memiliki <b>tepat 1 role jabatan utama</b>.</small>
                    </div>

                    <!-- 2. ROLE BYPASS (TIDAK TERBATAS) -->
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small d-block">
                            <i class="fas fa-shield-alt text-info mr-1"></i> Role Bypass / Tambahan (Opsional)
                        </label>
                        <select name="bypass_role_ids[]" id="select-bypass-roles" class="form-control select2" multiple="multiple" style="width: 100%;"></select>
                        <small class="text-muted">Memberikan hak akses tambahan tanpa mengubah jabatan utama.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-xs">
                        <i class="fas fa-save mr-1"></i> Simpan Penugasan Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: TAMBAH PENGGUNA BARU ================= -->
<div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-create-user">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-1"></i> Tambah Pengguna Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Nama Lengkap *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Jenis Kelamin *</label>
                            <select name="gender" class="form-control select2" style="width: 100%;">
                                <option value="male">Laki-laki (Male)</option>
                                <option value="female">Perempuan (Female)</option>
                                <option value="other">Lainnya (Other)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Username *</label>
                            <input type="text" name="username" class="form-control" required placeholder="Contoh: budisantoso">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Email *</label>
                            <input type="email" name="email" class="form-control" required placeholder="nama@domain.com">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Password *</label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Mode Login *</label>
                            <select name="login_mode" class="form-control select2" style="width: 100%;">
                                <option value="single_device">Single Device</option>
                                <option value="multi_device">Multi Device</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Status Akun *</label>
                            <select name="status" class="form-control select2" style="width: 100%;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">No. Telepon / WA</label>
                            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label class="small font-weight-bold">Alamat Lengkap</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Alamat domisili..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-xs">
                        <i class="fas fa-save mr-1"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 4: EDIT PENGGUNA ================= -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-edit-user">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header bg-info text-white py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-1"></i> Edit Data Pengguna</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Nama Lengkap *</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Jenis Kelamin *</label>
                            <select name="gender" id="edit-gender" class="form-control select2" style="width: 100%;">
                                <option value="male">Laki-laki (Male)</option>
                                <option value="female">Perempuan (Female)</option>
                                <option value="other">Lainnya (Other)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Username *</label>
                            <input type="text" name="username" id="edit-username" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Email *</label>
                            <input type="email" name="email" id="edit-email" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Password Baru <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Mode Login *</label>
                            <select name="login_mode" id="edit-login-mode" class="form-control select2" style="width: 100%;">
                                <option value="single_device">Single Device</option>
                                <option value="multi_device">Multi Device</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Status Akun *</label>
                            <select name="status" id="edit-status" class="form-control select2" style="width: 100%;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">No. Telepon / WA</label>
                            <input type="text" name="phone" id="edit-phone" class="form-control">
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label class="small font-weight-bold">Alamat Lengkap</label>
                            <textarea name="address" id="edit-address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-info btn-sm font-weight-bold shadow-xs text-white">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
    let isTrashMode = 0;
    let startDateParam = 'all';
    let endDateParam = 'all';
    let selectedFilterMode = 'all';
    let currentDetailUserId = null;
    let selectedRowIds = new Set();

    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };

    $('.select2').select2({ theme: 'bootstrap4' });
    $('#modal-create, #modal-edit, #modal-user-roles').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({ theme: 'bootstrap4', dropdownParent: $(this) });
    });

    function renderDateTimeWithRelative(utcString) {
        if (!utcString) return '-';
        const clientDate = new Date(utcString);
        if (isNaN(clientDate.getTime())) return utcString;

        const day = String(clientDate.getDate()).padStart(2, '0');
        const month = String(clientDate.getMonth() + 1).padStart(2, '0');
        const year = clientDate.getFullYear();
        const formattedDate = `${day}-${month}-${year}`;

        const hours = String(clientDate.getHours()).padStart(2, '0');
        const minutes = String(clientDate.getMinutes()).padStart(2, '0');
        const formattedTime = `${hours}:${minutes}`;

        const now = new Date();
        const diffInSeconds = Math.floor((now - clientDate) / 1000);
        let rel = 'baru saja';
        if (diffInSeconds >= 60 && diffInSeconds < 3600) rel = `${Math.floor(diffInSeconds / 60)} menit lalu`;
        else if (diffInSeconds >= 3600 && diffInSeconds < 86400) rel = `${Math.floor(diffInSeconds / 3600)} jam lalu`;
        else if (diffInSeconds >= 86400) rel = `${Math.floor(diffInSeconds / 86400)} hari lalu`;

        return `
            <div class="text-nowrap font-weight-bold"><i class="far fa-calendar-alt text-primary mr-1"></i>${formattedDate}</div>
            <div class="text-nowrap text-muted small mt-1"><i class="far fa-clock text-secondary mr-1"></i>${formattedTime} <span class="badge badge-light border">(${rel})</span></div>
        `;
    }

    function updateBulkToolbar() {
        const totalSelected = selectedRowIds.size;
        $('#selected-count').text(totalSelected);

        if (totalSelected > 0) {
            $('#bulk-action-bar').slideDown(200);
            if (isTrashMode === 1) {
                $('#bulk-active-actions').hide();
                $('#bulk-trash-actions').show();
            } else {
                $('#bulk-active-actions').show();
                $('#bulk-trash-actions').hide();
            }
        } else {
            $('#bulk-action-bar').slideUp(200);
            if (selectedFilterMode !== 'all') {
                selectedFilterMode = 'all';
                $('#filter-selection-mode').val('all').trigger('change.select2');
            }
        }

        let allPageChecked = true;
        let anyPageRow = false;
        $('.row-checkbox').each(function() {
            anyPageRow = true;
            if (!selectedRowIds.has($(this).val())) {
                allPageChecked = false;
            }
        });
        $('#check-all-page').prop('checked', anyPageRow && allPageChecked);
    }

    // 1. DataTables Server-Side
    const usersTable = $('#table-users').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[7, 'desc']],
        ajax: {
            url: userUrls.list,
            type: 'GET',
            data: function(d) {
                d.start_date      = startDateParam;
                d.end_date        = endDateParam;
                d.is_trash        = isTrashMode;
                d.selected_filter = selectedFilterMode;
                d.selected_ids    = Array.from(selectedRowIds);
            },
            dataSrc: function(json) {
                $('#trash-badge').text(json.trashCount || 0);
                return json.data;
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'id' },
            { data: 'full_name' },
            { data: 'username' },
            { data: 'email' },
            { data: 'login_mode' },
            { data: 'status' },
            { 
                data: 'registered_at',
                render: function(data, type, row) {
                    return type === 'display' ? renderDateTimeWithRelative(data) : data;
                }
            },
            { data: 'actions', orderable: false, searchable: false }
        ],
        drawCallback: function() {
            $('.row-checkbox').each(function() {
                const id = $(this).val();
                if (selectedRowIds.has(id)) {
                    $(this).prop('checked', true);
                    $(this).closest('tr').addClass('table-warning');
                } else {
                    $(this).prop('checked', false);
                    $(this).closest('tr').removeClass('table-warning');
                }
            });
            updateBulkToolbar();
        },
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x text-primary"></i> Memproses Data Server...',
            search: "Cari Pengguna:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ entri",
            paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
        }
    });

    $(document).on('change', '.row-checkbox', function() {
        const id = $(this).val();
        if ($(this).is(':checked')) {
            selectedRowIds.add(id);
            $(this).closest('tr').addClass('table-warning');
        } else {
            selectedRowIds.delete(id);
            $(this).closest('tr').removeClass('table-warning');
        }
        updateBulkToolbar();
        if (selectedFilterMode !== 'all') {
            usersTable.ajax.reload(null, false);
        }
    });

    $('#check-all-page').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').each(function() {
            const id = $(this).val();
            $(this).prop('checked', isChecked);
            if (isChecked) {
                selectedRowIds.add(id);
                $(this).closest('tr').addClass('table-warning');
            } else {
                selectedRowIds.delete(id);
                $(this).closest('tr').removeClass('table-warning');
            }
        });
        updateBulkToolbar();
        if (selectedFilterMode !== 'all') {
            usersTable.ajax.reload(null, false);
        }
    });

    $('#btn-clear-selection').on('click', function() {
        selectedRowIds.clear();
        $('.row-checkbox').prop('checked', false).closest('tr').removeClass('table-warning');
        $('#check-all-page').prop('checked', false);
        selectedFilterMode = 'all';
        $('#filter-selection-mode').val('all').trigger('change.select2');
        updateBulkToolbar();
        usersTable.ajax.reload(null, false);
        toastr.info('Semua pilihan dibatalkan');
    });

    $('#filter-selection-mode').on('change', function() {
        selectedFilterMode = $(this).val();
        usersTable.ajax.reload();
    });

    // DateRangePicker
    const dateRangesConfig = {
        'Semua Tanggal (All)': [moment('1970-01-01'), moment('2099-12-31')],
        'Hari Ini (Today)': [moment(), moment()],
        'Kemarin (Yesterday)': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        '7 Hari Terakhir (7 Days Latest)': [moment().subtract(6, 'days'), moment()],
        '30 Hari Terakhir (30 Days Latest)': [moment().subtract(29, 'days'), moment()],
        'Bulan Ini (This Month)': [moment().startOf('month'), moment().endOf('month')],
        'Bulan Lalu (Last Month)': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    };

    $('#filter-daterange').daterangepicker({
        alwaysShowCalendars: true,
        ranges: dateRangesConfig,
        locale: { format: 'YYYY-MM-DD', customRangeLabel: 'Custom Rentang Tanggal' }
    }, function(start, end, label) {
        if (label === 'Semua Tanggal (All)') {
            startDateParam = 'all';
            endDateParam = 'all';
            $('#filter-daterange').val('Semua Tanggal (All)');
        } else {
            startDateParam = start.format('YYYY-MM-DD');
            endDateParam = end.format('YYYY-MM-DD');
            $('#filter-daterange').val(`${startDateParam} s/d ${endDateParam}`);
        }
        usersTable.ajax.reload();
    });

    function setDefaultDateFilter(preset) {
        if (preset === 'today') {
            startDateParam = moment().format('YYYY-MM-DD');
            endDateParam = moment().format('YYYY-MM-DD');
            $('#filter-daterange').val(`${startDateParam} s/d ${endDateParam}`);
        } else if (preset === '7_days') {
            startDateParam = moment().subtract(6, 'days').format('YYYY-MM-DD');
            endDateParam = moment().format('YYYY-MM-DD');
            $('#filter-daterange').val(`${startDateParam} s/d ${endDateParam}`);
        } else if (preset === '30_days') {
            startDateParam = moment().subtract(29, 'days').format('YYYY-MM-DD');
            endDateParam = moment().format('YYYY-MM-DD');
            $('#filter-daterange').val(`${startDateParam} s/d ${endDateParam}`);
        } else if (preset === 'this_month') {
            startDateParam = moment().startOf('month').format('YYYY-MM-DD');
            endDateParam = moment().endOf('month').format('YYYY-MM-DD');
            $('#filter-daterange').val(`${startDateParam} s/d ${endDateParam}`);
        } else if (preset === 'last_month') {
            startDateParam = moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
            endDateParam = moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
            $('#filter-daterange').val(`${startDateParam} s/d ${endDateParam}`);
        } else {
            startDateParam = 'all';
            endDateParam = 'all';
            $('#filter-daterange').val('Semua Tanggal (All)');
        }
    }
    setDefaultDateFilter(userUrls.devDefaultPreset);

    // Toggle Recycle Bin
    $('#btn-toggle-trash').on('click', function() {
        selectedRowIds.clear();

        if (isTrashMode === 0) {
            isTrashMode = 1;
            $(this).removeClass('btn-outline-danger').addClass('btn-danger').html('<i class="fas fa-arrow-left mr-1"></i> Kembali ke Data Aktif');
            $('#page-heading').html('<i class="fas fa-trash-alt text-danger mr-2"></i> Recycle Bin (Data Pengguna Terhapus)');
            $('#table-card-title').html('<i class="fas fa-trash-alt mr-1 text-danger"></i> Data Pengguna di Recycle Bin');
            $('#btn-add-user').hide();
            toastr.info('Mode Recycle Bin Aktif');
        } else {
            isTrashMode = 0;
            $(this).removeClass('btn-danger').addClass('btn-outline-danger').html('<i class="fas fa-trash-alt mr-1"></i> Recycle Bin <span id="trash-badge" class="badge badge-danger ml-1">0</span>');
            $('#page-heading').html('<i class="fas fa-users text-primary mr-2"></i> Manajemen Pengguna');
            $('#table-card-title').html('<i class="fas fa-users mr-1"></i> Data Pengguna Aktif');
            $('#btn-add-user').show();
            toastr.info('Kembali ke Data Aktif');
        }

        updateBulkToolbar();
        usersTable.ajax.reload();
    });

    // ================= 2. DETAIL PENGGUNA =================
    $(document).on('click', '.btn-detail', function() {
        const id = $(this).data('id');
        currentDetailUserId = id;

        $.ajax({
            url: `${userUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;

                    $('#detail-avatar').attr('src', d.avatar);
                    $('#detail-name').text(d.name);
                    $('#detail-username').text(`@${d.username}`);
                    $('#detail-email').text(d.email);
                    $('#detail-phone').text(d.phone);
                    $('#detail-gender').text(d.gender === 'male' ? 'Laki-laki' : (d.gender === 'female' ? 'Perempuan' : 'Lainnya'));
                    $('#detail-birth').text(`${d.birth_place}, ${d.birth_date}`);
                    $('#detail-address').text(d.address);

                    $('#detail-login-mode').text(d.login_mode === 'single_device' ? 'Single Device' : 'Multi Device');
                    $('#detail-status').text(d.status.toUpperCase())
                        .removeClass('badge-success badge-secondary badge-danger')
                        .addClass(d.status === 'active' ? 'badge-success' : (d.status === 'inactive' ? 'badge-secondary' : 'badge-danger'));

                    $('#detail-registered-at').html(renderDateTimeWithRelative(d.registered_at));
                    $('#detail-modified-at').html(d.modified_at ? renderDateTimeWithRelative(d.modified_at) : '<span class="text-muted font-italic">- Belum pernah diubah -</span>');

                    // Ambil Role User Saat Ini
                    $.ajax({
                        url: `${userUrls.getUserRoles}?user_id=${id}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(roleRes) {
                            if (roleRes.status === 'success') {
                                const primaryRole = roleRes.all_roles.find(r => r.id === roleRes.primary_role_id);
                                if (primaryRole) {
                                    $('#detail-primary-role').html(`<span class="badge badge-primary px-2 py-1"><i class="fas fa-crown text-warning mr-1"></i> ${primaryRole.name}</span>`);
                                } else {
                                    $('#detail-primary-role').html('<span class="badge badge-secondary px-2 py-1">Belum Ada Role Utama</span>');
                                }

                                if (roleRes.bypass_role_ids && roleRes.bypass_role_ids.length > 0) {
                                    let bBadges = '';
                                    $.each(roleRes.bypass_role_ids, function(idx, bId) {
                                        const rObj = roleRes.all_roles.find(r => r.id === bId);
                                        if (rObj) {
                                            bBadges += `<span class="badge badge-warning px-2 py-1 mr-1 mb-1"><i class="fas fa-shield-alt mr-1"></i> ${rObj.name}</span>`;
                                        }
                                    });
                                    $('#detail-bypass-roles').html(bBadges);
                                } else {
                                    $('#detail-bypass-roles').html('<span class="text-muted font-italic">- Tidak ada role bypass -</span>');
                                }
                            }
                        }
                    });

                    $('#modal-detail').modal('show');
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    $('#btn-edit-from-detail').on('click', function() {
        $('#modal-detail').modal('hide');
        if (currentDetailUserId) {
            $(`.btn-edit[data-id="${currentDetailUserId}"]`).click();
        }
    });

    $('#btn-roles-from-detail').on('click', function() {
        $('#modal-detail').modal('hide');
        if (currentDetailUserId) {
            $(`.btn-manage-roles[data-id="${currentDetailUserId}"]`).click();
        }
    });

    // ================= 3. PENUGASAN ROLE RBAC =================
    $(document).on('click', '.btn-manage-roles', function() {
        const id = $(this).data('id');
        $('#assign-user-id').val(id);

        $.ajax({
            url: `${userUrls.getUserRoles}?user_id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#assign-user-display').text(`${res.user.name} (@${res.user.username})`);

                    let primaryOpts = '<option value="">-- Pilih Role Utama --</option>';
                    let bypassOpts = '';

                    $.each(res.all_roles, function(i, r) {
                        primaryOpts += `<option value="${r.id}">${r.name}</option>`;
                        bypassOpts += `<option value="${r.id}">${r.name}</option>`;
                    });

                    $('#select-primary-role').html(primaryOpts).val(res.primary_role_id).trigger('change.select2');
                    $('#select-bypass-roles').html(bypassOpts).val(res.bypass_role_ids).trigger('change.select2');

                    $('#modal-user-roles').modal('show');
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    $('#form-user-roles-submit').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: userUrls.saveUserRoles,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-user-roles').modal('hide');
                    toastr.success(res.message);
                    usersTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // ================= 4. CRUD USER =================
    $('#form-create-user').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: userUrls.create,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-create').modal('hide');
                    $('#form-create-user')[0].reset();
                    $('#modal-create .select2').val('single_device').trigger('change');
                    usersTable.ajax.reload(null, false);
                    toastr.success(res.message);
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `${userUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const u = res.data;
                    $('#edit-id').val(u.id);
                    $('#edit-name').val(u.name);
                    $('#edit-username').val(u.username);
                    $('#edit-email').val(u.email);
                    $('#edit-gender').val(u.gender).trigger('change');
                    $('#edit-login-mode').val(u.login_mode).trigger('change');
                    $('#edit-status').val(u.status).trigger('change');
                    $('#edit-phone').val(u.phone);
                    $('#edit-address').val(u.address);
                    $('#modal-edit').modal('show');
                }
            }
        });
    });

    $('#form-edit-user').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        $.ajax({
            url: `${userUrls.update}?id=${id}`,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-edit').modal('hide');
                    usersTable.ajax.reload(null, false);
                    toastr.success(res.message);
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Pindahkan ke Recycle Bin?',
            text: 'Pengguna ini akan dinonaktifkan sementara.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Ya, Pindahkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${userUrls.delete}?id=${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            selectedRowIds.delete(String(id));
                            updateBulkToolbar();
                            usersTable.ajax.reload(null, false);
                            toastr.warning(res.message);
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-restore', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Pulihkan Pengguna?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Pulihkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${userUrls.restore}?id=${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            selectedRowIds.delete(String(id));
                            updateBulkToolbar();
                            usersTable.ajax.reload(null, false);
                            toastr.success(res.message);
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-force-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Permanen?',
            html: 'PERINGATAN! Data dan file terkait akan <b>DIHAPUS TOTAL</b> dari database & server!',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus Permanen!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${userUrls.forceDelete}?id=${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            selectedRowIds.delete(String(id));
                            updateBulkToolbar();
                            usersTable.ajax.reload(null, false);
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });

    // Bulk Handlers
    $('#btn-bulk-delete').on('click', function() {
        const ids = Array.from(selectedRowIds);
        if (ids.length === 0) return;

        Swal.fire({
            title: `Pindahkan ${ids.length} User ke Recycle Bin?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: `Ya, Pindahkan ${ids.length} Data!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: userUrls.bulkDelete,
                    type: 'POST',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            selectedRowIds.clear();
                            updateBulkToolbar();
                            usersTable.ajax.reload(null, false);
                            toastr.warning(res.message);
                        }
                    }
                });
            }
        });
    });

    $('#btn-bulk-restore').on('click', function() {
        const ids = Array.from(selectedRowIds);
        if (ids.length === 0) return;

        Swal.fire({
            title: `Pulihkan ${ids.length} User?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: `Ya, Pulihkan!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: userUrls.bulkRestore,
                    type: 'POST',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            selectedRowIds.clear();
                            updateBulkToolbar();
                            usersTable.ajax.reload(null, false);
                            toastr.success(res.message);
                        }
                    }
                });
            }
        });
    });

    $('#btn-bulk-force-delete, #btn-bulk-force-delete-trash').on('click', function() {
        const ids = Array.from(selectedRowIds);
        if (ids.length === 0) return;

        Swal.fire({
            title: `Hapus Permanen ${ids.length} User?`,
            html: `PERINGATAN KERAS! ${ids.length} data akan <b>DIHAPUS TOTAL</b>!`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: `Hapus Permanen ${ids.length} Data!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: userUrls.bulkForceDelete,
                    type: 'POST',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            selectedRowIds.clear();
                            updateBulkToolbar();
                            usersTable.ajax.reload(null, false);
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });
JS
);
?>