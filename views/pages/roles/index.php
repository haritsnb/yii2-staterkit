<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Kelola Hak Akses (Roles)';

$listDtUrl          = Url::to(['/role/list-datatable']);
$getRolesListUrl    = Url::to(['/role/get-roles-list']);
$createUrl          = Url::to(['/role/create']);
$viewDataUrl        = Url::to(['/role/view-data']);
$updateUrl          = Url::to(['/role/update']);
$cloneUrl           = Url::to(['/role/clone']);
$deleteUrl          = Url::to(['/role/delete']);
$getMatrixUrl       = Url::to(['/role/get-permission-matrix']);
$saveMatrixUrl      = Url::to(['/role/save-permission-matrix']);
$getRoleDetailUrl   = Url::to(['/role/get-role-detail-data']);
$addUserToRoleUrl   = Url::to(['/role/add-user-to-role']);
$removeUserRoleUrl  = Url::to(['/role/remove-user-from-role']);
$addPermToRoleUrl   = Url::to(['/role/add-permission-to-role']);
$removePermRoleUrl  = Url::to(['/role/remove-permission-from-role']);

$this->registerJsVar('roleUrls', [
    'listDt'          => $listDtUrl,
    'getRolesList'    => $getRolesListUrl,
    'create'          => $createUrl,
    'viewData'        => $viewDataUrl,
    'update'          => $updateUrl,
    'clone'           => $cloneUrl,
    'delete'          => $deleteUrl,
    'getMatrix'       => $getMatrixUrl,
    'saveMatrix'      => $saveMatrixUrl,
    'getRoleDetail'   => $getRoleDetailUrl,
    'addUserToRole'   => $addUserToRoleUrl,
    'removeUserRole'  => $removeUserRoleUrl,
    'addPermToRole'   => $addPermToRoleUrl,
    'removePermRole'  => $removePermRoleUrl,
]);
?>

<style>
/* Permission Matrix Treeview Styling */
.perm-module-card {
    border-left: 4px solid #007bff;
    background-color: #f8f9fa;
    border-radius: 8px;
}
.perm-page-box {
    border-left: 3px solid #17a2b8;
    background-color: #ffffff;
    border-radius: 6px;
}
.perm-widget-box {
    border-left: 2px solid #6c757d;
    background-color: #fcfcfc;
}
.perm-action-badge {
    display: inline-flex;
    align-items: center;
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 4px 10px;
    margin: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.perm-action-badge:hover {
    border-color: #007bff;
    background-color: #e8f0fe;
}
.perm-action-badge input {
    cursor: pointer;
    margin-right: 6px;
}
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-user-shield text-primary mr-2"></i> Kelola Hak Akses (Roles)
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-outline-success btn-sm font-weight-bold mr-2 shadow-xs" id="btn-open-clone-role">
                    <i class="fas fa-copy mr-1"></i> Clone Role
                </button>
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-xs" id="btn-open-create-role">
                    <i class="fas fa-plus mr-1"></i> Tambah Role Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-sitemap text-info mr-2"></i> Daftar Hierarki Jabatan & Role
                </h5>
            </div>
            <div class="card-body">
                <table id="table-roles-dt" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 20%">Nama Role</th>
                            <th style="width: 15%">Kode Role</th>
                            <th style="width: 20%">Parent (Atasan)</th>
                            <th style="width: 8%">Tingkat</th>
                            <th style="width: 10%">Hak Akses</th>
                            <th style="width: 10%">Pengguna</th>
                            <th style="width: 5%">Status</th>
                            <th style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- ================= MODAL 1: DETAIL ROLE (PENGGUNA & PERMISSIONS DUA ARAH) ================= -->
<div class="modal fade" id="modal-role-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header bg-gradient-secondary text-white py-3">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-id-badge mr-2"></i> Detail Role: <span id="detail-role-name">-</span>
                    </h5>
                    <small class="text-white-50">Kode: <code class="text-white" id="detail-role-code">-</code> | Atasan: <span id="detail-role-parent">-</span> | Tingkat: <span id="detail-role-level">-</span></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <!-- Nav Tabs -->
                <ul class="nav nav-pills mb-3 border-bottom pb-2" id="role-detail-tabs" role="tablist">
                    <li class="nav-item mr-2">
                        <a class="nav-link active font-weight-bold" id="tab-role-users-link" data-toggle="pill" href="#tab-role-users" role="tab">
                            <i class="fas fa-users mr-1 text-primary"></i> Pengguna Pemilik Role (<span id="badge-total-users">0</span>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-role-perms-link" data-toggle="pill" href="#tab-role-perms" role="tab">
                            <i class="fas fa-key mr-1 text-warning"></i> Hak Akses Role (<span id="badge-total-perms">0</span>)
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    
                    <!-- TAB 1: DAFTAR PENGGUNA ANGGOTA ROLE -->
                    <div class="tab-pane fade show active" id="tab-role-users" role="tabpanel">
                        
                        <!-- Form Cepat Tambah User ke Role Ini -->
                        <div class="card card-outline card-primary shadow-xs p-3 mb-3">
                            <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-user-plus text-primary mr-1"></i> Tambahkan Pengguna ke Role Ini:</h6>
                            <form id="form-add-user-to-role" class="row align-items-end">
                                <div class="col-md-5 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Pilih Pengguna:</label>
                                    <select id="select-add-user-target" class="form-control select2" style="width: 100%;" required></select>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Tipe Penugasan Role:</label>
                                    <select id="select-add-user-type" class="form-control select2" style="width: 100%;">
                                        <option value="primary">👑 Role Utama (Primary Role)</option>
                                        <option value="bypass" selected>🛡️ Role Bypass (Hak Akses Tambahan)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group mb-0 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-xs">
                                        <i class="fas fa-plus mr-1"></i> Tambahkan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tabel Pengguna -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover bg-white mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 5%">Avatar</th>
                                        <th style="width: 25%">Nama Pengguna</th>
                                        <th style="width: 20%">Username / Email</th>
                                        <th style="width: 20%">Status Penugasan</th>
                                        <th style="width: 20%">Waktu Ditugaskan</th>
                                        <th style="width: 10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="table-role-users-body">
                                    <tr><td colspan="6" class="text-center py-3 text-muted">Memuat data pengguna...</td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- TAB 2: DAFTAR PERMISSIONS ROLE -->
                    <div class="tab-pane fade" id="tab-role-perms" role="tabpanel">
                        
                        <!-- Form Cepat Tambah Permission Langsung ke Role Ini -->
                        <div class="card card-outline card-warning shadow-xs p-3 mb-3">
                            <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-plus-circle text-warning mr-1"></i> Tambahkan Izin Langsung ke Role Ini:</h6>
                            <form id="form-add-perm-to-role" class="row align-items-end">
                                <div class="col-md-9 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Pilih Permission / Aksi Izin:</label>
                                    <select id="select-add-perm-target" class="form-control select2" style="width: 100%;" required></select>
                                </div>
                                <div class="col-md-3 form-group mb-0 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-warning btn-block font-weight-bold shadow-xs">
                                        <i class="fas fa-plus mr-1"></i> Berikan Izin
                                    </button>
                                </div>
                            </form>
                        </div>

                        <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Izin Diberikan Langsung (Direct Permissions):</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm table-striped bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 35%">Nama Izin</th>
                                        <th style="width: 35%">Kode Izin</th>
                                        <th style="width: 15%">Tipe</th>
                                        <th style="width: 15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="table-role-direct-perms-body"></tbody>
                            </table>
                        </div>

                        <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-level-down-alt text-info mr-1"></i> Izin Diwarisi dari Jabatan Bawahan (Inherited Permissions):</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-striped bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 30%">Nama Izin</th>
                                        <th style="width: 35%">Kode Izin</th>
                                        <th style="width: 15%">Tipe</th>
                                        <th style="width: 20%">Diwarisi Dari Role</th>
                                    </tr>
                                </thead>
                                <tbody id="table-role-inherited-perms-body"></tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>
            <div class="modal-footer bg-white py-2">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: TAMBAH / EDIT ROLE ================= -->
<div class="modal fade" id="modal-role-form" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-role-crud">
                <input type="hidden" name="id" id="role-form-id">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modal-role-title">Tambah Role Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Parent Role (Atasan / Hierarki Induk)</label>
                        <select name="parent_id" id="input-role-parent" class="form-control select2" style="width: 100%;">
                            <option value="0">-- Top Level / Root Role (Owner) --</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Nama Role / Jabatan *</label>
                        <input type="text" name="name" id="input-role-name" class="form-control" placeholder="Contoh: Manajer Operasional" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Kode Unik Role *</label>
                        <input type="text" name="code" id="input-role-code" class="form-control" placeholder="Contoh: operational-manager" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Deskripsi Tugas</label>
                        <textarea name="description" id="input-role-desc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Status Role</label>
                        <select name="status" id="input-role-status" class="form-control select2" style="width: 100%;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-xs">Simpan Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: CLONE ROLE WIZARD ================= -->
<div class="modal fade" id="modal-clone-role" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-clone-role-submit">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-copy mr-1"></i> Clone Role & Permission</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3 pb-2 border-bottom">
                        <label class="font-weight-bold text-dark small">Pilih Role Sumber yang Akan Diclone *</label>
                        <select name="source_role_id" id="select-clone-source-role" class="form-control select2" style="width: 100%;"></select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Nama Role Baru *</label>
                        <input type="text" name="new_name" id="input-clone-role-name" class="form-control" placeholder="Contoh: Kasir Cabang 2" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Kode Role Baru *</label>
                        <input type="text" name="new_code" id="input-clone-role-code" class="form-control" placeholder="Contoh: cashier-branch-2" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Parent Role (Atasan)</label>
                        <select name="parent_id" id="select-clone-role-parent" class="form-control select2" style="width: 100%;">
                            <option value="0">-- Top Level / Root Role --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-xs">Clone Role Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 4: PERMISSION MATRIX TREEVIEW ================= -->
<div class="modal fade" id="modal-permission-matrix" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header bg-gradient-primary text-white py-3">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-key mr-2"></i> Matriks Hak Akses (Permission Matrix)
                    </h5>
                    <small class="text-white-50">Role: <b id="matrix-role-title" class="text-white">-</b> (<span id="matrix-role-code"></span>)</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <span class="badge badge-success px-2 py-1 mr-1"><i class="fas fa-check-circle mr-1"></i> <span id="count-direct-perm">0</span> Izin Diberikan Langsung</span>
                        <span class="badge badge-info px-2 py-1"><i class="fas fa-level-down-alt mr-1"></i> <span id="count-inherited-perm">0</span> Izin Diwarisi dari Bawahan</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-xs font-weight-bold mr-1" id="btn-check-all-perms">
                            <i class="fas fa-check-double mr-1"></i> Centang Semua
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-xs font-weight-bold" id="btn-uncheck-all-perms">
                            <i class="fas fa-times mr-1"></i> Hapus Semua Centang
                        </button>
                    </div>
                </div>

                <div id="permission-tree-container"></div>

            </div>
            <div class="modal-footer bg-white d-flex justify-content-between py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-xs px-4" id="btn-save-permission-matrix">
                    <i class="fas fa-save mr-1"></i> Simpan Hak Akses Role
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
    let rolesDataTable = null;
    let activeRoleId = null;

    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };

    $('.select2').select2({ theme: 'bootstrap4' });
    $('#modal-role-form, #modal-clone-role, #modal-role-detail').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({ theme: 'bootstrap4', dropdownParent: $(this) });
    });

    function renderDateTime(utcString) {
        if (!utcString) return '-';
        const d = new Date(utcString);
        if (isNaN(d.getTime())) return utcString;
        const pad = n => String(n).padStart(2, '0');
        return `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function initRolesDataTable() {
        rolesDataTable = $('#table-roles-dt').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            ajax: { url: roleUrls.listDt, type: 'GET' },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'code' },
                { data: 'parent_name' },
                { data: 'level' },
                { data: 'perm_count' },
                { data: 'user_count' },
                { data: 'status' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-2x text-primary"></i> Memuat Data...',
                search: "Cari Role:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ role",
                paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
            }
        });
    }

    function loadRoleDropdownOptions() {
        $.ajax({
            url: roleUrls.getRolesList,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let parentOpts = '<option value="0">-- Top Level / Root Role (Owner) --</option>';
                    let cloneOpts = '<option value="">-- Pilih Role Sumber --</option>';

                    $.each(res.data, function(i, r) {
                        parentOpts += `<option value="${r.id}">${r.name}</option>`;
                        cloneOpts += `<option value="${r.id}">${r.name}</option>`;
                    });

                    $('#input-role-parent, #select-clone-role-parent').html(parentOpts);
                    $('#select-clone-source-role').html(cloneOpts);
                }
            }
        });
    }

    // ================= 1. DETAIL ROLE (MANAJEMEN PENGGUNA & PERMISSIONS) =================
    $(document).on('click', '.btn-detail-role', function() {
        const id = $(this).data('id');
        activeRoleId = id;
        loadRoleDetailModal(id);
    });

    function loadRoleDetailModal(roleId) {
        $.ajax({
            url: `${roleUrls.getRoleDetail}?role_id=${roleId}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const r = res.role;
                    $('#detail-role-name').text(r.name);
                    $('#detail-role-code').text(r.code);
                    $('#detail-role-parent').text(r.parent_name);
                    $('#detail-role-level').text(`Level ${r.level}`);

                    // 1. Render Tabel Pengguna
                    $('#badge-total-users').text(res.users.length);
                    let userRows = '';
                    if (res.users.length === 0) {
                        userRows = '<tr><td colspan="6" class="text-center py-3 text-muted">Belum ada pengguna yang memiliki role ini.</td></tr>';
                    } else {
                        $.each(res.users, function(i, u) {
                            const typeBadge = u.user_type === 'primary' 
                                ? '<span class="badge badge-primary px-2 py-1"><i class="fas fa-crown text-warning mr-1"></i> Role Utama</span>'
                                : '<span class="badge badge-warning px-2 py-1"><i class="fas fa-shield-alt mr-1"></i> Role Bypass</span>';

                            userRows += `
                                <tr>
                                    <td class="text-center align-middle">
                                        <img src="${u.avatar}" class="img-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark">${u.name}</td>
                                    <td class="align-middle text-muted">@${u.username}<br><small>${u.email}</small></td>
                                    <td class="align-middle">${typeBadge}</td>
                                    <td class="align-middle small text-muted">${renderDateTime(u.assigned_at)}</td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-outline-danger btn-xs font-weight-bold btn-remove-user-role" data-user-id="${u.user_id}" title="Keluarkan Pengguna dari Role">
                                            <i class="fas fa-user-minus mr-1"></i> Keluarkan
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#table-role-users-body').html(userRows);

                    // 2. Render Dropdown Pengguna yang Tersedia
                    let userOpts = '<option value="">-- Pilih Pengguna --</option>';
                    $.each(res.available_users, function(i, u) {
                        userOpts += `<option value="${u.id}">${u.name}</option>`;
                    });
                    $('#select-add-user-target').html(userOpts);

                    // 3. Render Permissions
                    const totalPerms = res.direct_perms.length + res.inherited_perms.length;
                    $('#badge-total-perms').text(totalPerms);

                    let directRows = '';
                    if (res.direct_perms.length === 0) {
                        directRows = '<tr><td colspan="4" class="text-center py-2 text-muted small">Tidak ada izin langsung.</td></tr>';
                    } else {
                        $.each(res.direct_perms, function(i, p) {
                            directRows += `
                                <tr>
                                    <td class="font-weight-bold text-dark">${p.name}</td>
                                    <td><code>${p.code}</code></td>
                                    <td><span class="badge badge-info">${p.type.toUpperCase()}</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-xs btn-remove-perm-role" data-perm-id="${p.id}" title="Cabut Izin">
                                            <i class="fas fa-times"></i> Cabut
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#table-role-direct-perms-body').html(directRows);

                    let inheritedRows = '';
                    if (res.inherited_perms.length === 0) {
                        inheritedRows = '<tr><td colspan="4" class="text-center py-2 text-muted small">Tidak ada izin yang diwarisi dari jabatan bawahan.</td></tr>';
                    } else {
                        $.each(res.inherited_perms, function(i, p) {
                            inheritedRows += `
                                <tr>
                                    <td class="font-weight-bold text-dark">${p.name}</td>
                                    <td><code>${p.code}</code></td>
                                    <td><span class="badge badge-info">${p.type.toUpperCase()}</span></td>
                                    <td><span class="badge badge-light border text-primary font-weight-bold"><i class="fas fa-level-down-alt mr-1"></i> ${p.inherited_from_role}</span></td>
                                </tr>
                            `;
                        });
                    }
                    $('#table-role-inherited-perms-body').html(inheritedRows);

                    // 4. Dropdown Permission
                    let permOpts = '<option value="">-- Pilih Permission / Izin --</option>';
                    $.each(res.available_perms, function(i, p) {
                        permOpts += `<option value="${p.id}">${p.name}</option>`;
                    });
                    $('#select-add-perm-target').html(permOpts);

                    $('#modal-role-detail').modal('show');
                }
            }
        });
    }

    // Tambah Pengguna ke Role (Submit)
    $('#form-add-user-to-role').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#select-add-user-target').val();
        const userType = $('#select-add-user-type').val();

        if (!userId) {
            toastr.error('Silakan pilih pengguna terlebih dahulu.');
            return;
        }

        $.ajax({
            url: roleUrls.addUserToRole,
            type: 'POST',
            data: { role_id: activeRoleId, user_id: userId, user_type: userType },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    loadRoleDetailModal(activeRoleId);
                    rolesDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // Keluarkan Pengguna dari Role
    $(document).on('click', '.btn-remove-user-role', function() {
        const userId = $(this).data('user-id');
        Swal.fire({
            title: 'Keluarkan Pengguna?',
            text: 'Pengguna akan dicabut hak aksesnya dari role ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Keluarkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: roleUrls.removeUserRole,
                    type: 'POST',
                    data: { role_id: activeRoleId, user_id: userId },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            loadRoleDetailModal(activeRoleId);
                            rolesDataTable.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });

    // Tambah Permission ke Role (Submit)
    $('#form-add-perm-to-role').on('submit', function(e) {
        e.preventDefault();
        const permId = $('#select-add-perm-target').val();
        if (!permId) {
            toastr.error('Silakan pilih permission terlebih dahulu.');
            return;
        }

        $.ajax({
            url: roleUrls.addPermToRole,
            type: 'POST',
            data: { role_id: activeRoleId, permission_id: permId },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    loadRoleDetailModal(activeRoleId);
                    rolesDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // Cabut Permission dari Role
    $(document).on('click', '.btn-remove-perm-role', function() {
        const permId = $(this).data('perm-id');
        $.ajax({
            url: roleUrls.removePermRole,
            type: 'POST',
            data: { role_id: activeRoleId, permission_id: permId },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    loadRoleDetailModal(activeRoleId);
                    rolesDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // ================= 2. TAMBAH / EDIT / CLONE / MATRIX =================
    $('#btn-open-create-role').on('click', function() {
        $('#form-role-crud')[0].reset();
        $('#role-form-id').val('');
        $('#modal-role-title').text('Tambah Role Baru');
        $('#input-role-parent').val('0').trigger('change.select2');
        $('#input-role-status').val('active').trigger('change.select2');
        $('#modal-role-form').modal('show');
    });

    $(document).on('click', '.btn-edit-role', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `${roleUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#role-form-id').val(d.id);
                    $('#modal-role-title').text('Edit Role');
                    $('#input-role-name').val(d.name);
                    $('#input-role-code').val(d.code);
                    $('#input-role-parent').val(d.parent_id).trigger('change.select2');
                    $('#input-role-desc').val(d.description);
                    $('#input-role-status').val(d.status).trigger('change.select2');
                    $('#modal-role-form').modal('show');
                }
            }
        });
    });

    $('#form-role-crud').on('submit', function(e) {
        e.preventDefault();
        const id = $('#role-form-id').val();
        const url = id ? `${roleUrls.update}?id=${id}` : roleUrls.create;

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-role-form').modal('hide');
                    toastr.success(res.message);
                    rolesDataTable.ajax.reload(null, false);
                    loadRoleDropdownOptions();
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    $('#btn-open-clone-role').on('click', function() {
        $('#form-clone-role-submit')[0].reset();
        $('#select-clone-source-role').val('').trigger('change.select2');
        $('#select-clone-role-parent').val('0').trigger('change.select2');
        $('#modal-clone-role').modal('show');
    });

    $(document).on('click', '.btn-clone-role', function() {
        const id = $(this).data('id');
        $('#select-clone-source-role').val(id).trigger('change');
        $('#modal-clone-role').modal('show');
    });

    $('#select-clone-source-role').on('change', function() {
        const id = $(this).val();
        if (!id) return;
        $.ajax({
            url: `${roleUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#input-clone-role-name').val(d.name + ' (Copy)');
                    $('#input-clone-role-code').val(d.code + '-copy');
                    $('#select-clone-role-parent').val(d.parent_id).trigger('change.select2');
                }
            }
        });
    });

    $('#form-clone-role-submit').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: roleUrls.clone,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-clone-role').modal('hide');
                    toastr.success(res.message);
                    rolesDataTable.ajax.reload(null, false);
                    loadRoleDropdownOptions();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    $(document).on('click', '.btn-delete-role', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Role Ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${roleUrls.delete}?id=${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            rolesDataTable.ajax.reload(null, false);
                            loadRoleDropdownOptions();
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });

    // ================= 3. MATRIX PERMISSIONS =================
    $(document).on('click', '.btn-matrix-perm', function() {
        const id = $(this).data('id');
        activeRoleId = id;

        $('#permission-tree-container').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="text-muted mt-2">Memuat matriks izin...</p></div>');
        $('#modal-permission-matrix').modal('show');

        $.ajax({
            url: `${roleUrls.getMatrix}?role_id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#matrix-role-title').text(res.role.name);
                    $('#matrix-role-code').text(res.role.code);
                    $('#count-direct-perm').text(res.direct_count);
                    $('#count-inherited-perm').text(res.inherited_count);

                    renderPermissionMatrixTree(res.tree_map);
                }
            }
        });
    });

    function renderPermissionMatrixTree(map) {
        const rootModules = map[0] || [];
        if (rootModules.length === 0) {
            $('#permission-tree-container').html('<div class="alert alert-light text-center">Belum ada data permission yang terdaftar di database.</div>');
            return;
        }

        let html = '';
        $.each(rootModules, function(i, mod) {
            html += `
                <div class="card perm-module-card mb-3 shadow-xs">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input chk-cascade-module" id="chk-mod-${mod.id}">
                            <label class="custom-control-label font-weight-bold text-primary" for="chk-mod-${mod.id}">
                                <i class="fas fa-cubes mr-1"></i> MODUL: ${mod.name} <code>(${mod.code})</code>
                            </label>
                        </div>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                    <div class="card-body p-3">
            `;

            const pages = map[mod.id] || [];
            $.each(pages, function(j, page) {
                html += `
                    <div class="perm-page-box p-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input chk-cascade-page" id="chk-page-${page.id}">
                                <label class="custom-control-label font-weight-bold text-dark" for="chk-page-${page.id}">
                                    <i class="fas fa-file-alt text-info mr-1"></i> Halaman: ${page.name} <code>(${page.code})</code>
                                </label>
                            </div>
                        </div>
                `;

                const widgets = map[page.id] || [];
                $.each(widgets, function(k, widget) {
                    html += `
                        <div class="perm-widget-box p-2 mb-2 border rounded">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input chk-cascade-widget" id="chk-widget-${widget.id}">
                                <label class="custom-control-label font-weight-bold text-secondary text-sm" for="chk-widget-${widget.id}">
                                    <i class="fas fa-th-large text-muted mr-1"></i> Widget: ${widget.name} <code>(${widget.code})</code>
                                </label>
                            </div>
                            <div class="d-flex flex-wrap pl-3">
                    `;

                    const actions = map[widget.id] || [];
                    $.each(actions, function(l, act) {
                        const isChecked = act.is_direct ? 'checked' : '';
                        const inheritedBadge = act.is_inherited 
                            ? '<span class="badge badge-info ml-1" title="Diwarisi dari bawahan"><i class="fas fa-level-down-alt"></i> Diwarisi</span>' 
                            : '';

                        html += `
                            <label class="perm-action-badge">
                                <input type="checkbox" class="perm-action-checkbox" value="${act.id}" ${isChecked}>
                                <span class="font-weight-500 text-dark small">${act.name}</span>
                                ${inheritedBadge}
                            </label>
                        `;
                    });

                    html += `</div></div>`;
                });

                html += `</div>`;
            });

            html += `</div></div>`;
        });

        $('#permission-tree-container').html(html);
    }

    $(document).on('change', '.chk-cascade-module', function() {
        $(this).closest('.perm-module-card').find('input[type="checkbox"]').prop('checked', $(this).is(':checked'));
    });
    $(document).on('change', '.chk-cascade-page', function() {
        $(this).closest('.perm-page-box').find('input[type="checkbox"]').prop('checked', $(this).is(':checked'));
    });
    $(document).on('change', '.chk-cascade-widget', function() {
        $(this).closest('.perm-widget-box').find('.perm-action-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#btn-check-all-perms').on('click', function() { $('#permission-tree-container input[type="checkbox"]').prop('checked', true); });
    $('#btn-uncheck-all-perms').on('click', function() { $('#permission-tree-container input[type="checkbox"]').prop('checked', false); });

    $('#btn-save-permission-matrix').on('click', function() {
        const selectedPermIds = [];
        $('.perm-action-checkbox:checked').each(function() { selectedPermIds.push($(this).val()); });

        toastr.info('Menyimpan hak akses role...');
        $.ajax({
            url: roleUrls.saveMatrix,
            type: 'POST',
            data: { role_id: activeRoleId, permission_ids: selectedPermIds },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-permission-matrix').modal('hide');
                    toastr.success(res.message);
                    rolesDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    $(document).ready(function() {
        initRolesDataTable();
        loadRoleDropdownOptions();
    });
JS
);
?>