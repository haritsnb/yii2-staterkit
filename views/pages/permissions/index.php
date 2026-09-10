<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Kelola Izin (Permissions)';

$listDtUrl            = Url::to(['/permission/list-datatable']);
$getParentOptsUrl     = Url::to(['/permission/get-parent-options']);
$createUrl            = Url::to(['/permission/create']);
$viewDataUrl          = Url::to(['/permission/view-data']);
$updateUrl            = Url::to(['/permission/update']);
$cloneSubtreeUrl      = Url::to(['/permission/clone-subtree']);
$deleteUrl            = Url::to(['/permission/delete']);
$getPermDetailUrl     = Url::to(['/permission/get-permission-detail-data']);
$addRoleToPermUrl     = Url::to(['/permission/add-role-to-permission']);
$removeRoleFromPermUrl= Url::to(['/permission/remove-role-from-permission']);

$this->registerJsVar('permUrls', [
    'listDt'            => $listDtUrl,
    'getParentOpts'     => $getParentOptsUrl,
    'create'            => $createUrl,
    'viewData'          => $viewDataUrl,
    'update'            => $updateUrl,
    'cloneSubtree'      => $cloneSubtreeUrl,
    'delete'            => $deleteUrl,
    'getPermDetail'     => $getPermDetailUrl,
    'addRoleToPerm'     => $addRoleToPermUrl,
    'removeRoleFromPerm'=> $removeRoleFromPermUrl,
]);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-key text-primary mr-2"></i> Kelola Izin (Permissions)
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-xs" id="btn-open-create-perm">
                    <i class="fas fa-plus mr-1"></i> Tambah Node Permission
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Filter Tipe Permission -->
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="font-weight-bold text-sm text-dark mb-1">Filter Tingkatan Tipe Izin:</label>
                        <select id="filter-perm-type" class="form-control select2" style="width: 100%;">
                            <option value="">-- Semua Tingkatan Tipe --</option>
                            <option value="module">Module (Level 1)</option>
                            <option value="page">Page (Level 2)</option>
                            <option value="widget">Widget (Level 3)</option>
                            <option value="action">Action (Level 4)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-list text-info mr-2"></i> Daftar Node Izin Sistem (4-Level)
                </h5>
            </div>
            <div class="card-body">
                <table id="table-perms-dt" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 25%">Nama Izin</th>
                            <th style="width: 25%">Kode Izin</th>
                            <th style="width: 10%">Tipe</th>
                            <th style="width: 18%">Node Induk (Parent)</th>
                            <th style="width: 12%">Role Pemilik</th>
                            <th style="width: 5%">Status</th>
                            <th style="width: 12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- ================= MODAL 1: DETAIL PERMISSION & ROLE PEMILIK ================= -->
<div class="modal fade" id="modal-perm-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header bg-gradient-info text-white py-3">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-shield-alt mr-2"></i> Detail Izin: <span id="detail-perm-name">-</span>
                    </h5>
                    <small class="text-white-50">Kode: <code class="text-white" id="detail-perm-code">-</code> | Tipe: <span id="detail-perm-type" class="badge badge-light">-</span></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <!-- Form Cepat Tambah Role ke Izin Ini -->
                <div class="card card-outline card-primary shadow-xs p-3 mb-3">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-user-plus text-primary mr-1"></i> Berikan Izin Ini ke Role Baru:</h6>
                    <form id="form-add-role-to-perm" class="row align-items-end">
                        <div class="col-md-9 form-group mb-0">
                            <label class="small font-weight-bold text-muted">Pilih Role Penerima Izin:</label>
                            <select id="select-add-role-target" class="form-control select2" style="width: 100%;" required></select>
                        </div>
                        <div class="col-md-3 form-group mb-0 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-xs">
                                <i class="fas fa-plus mr-1"></i> Berikan Izin
                            </button>
                        </div>
                    </form>
                </div>

                <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Role Pemilik Izin Langsung (Direct Assigned Roles):</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm table-striped bg-white">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 35%">Nama Role / Jabatan</th>
                                <th style="width: 25%">Kode Role</th>
                                <th style="width: 15%">Tingkat</th>
                                <th style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-perm-direct-roles-body"></tbody>
                    </table>
                </div>

                <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-level-down-alt text-info mr-1"></i> Role Atasan yang Mewarisi Izin Ini (Inherited via Hierarchy):</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped bg-white">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50%">Nama Role Atasan</th>
                                <th style="width: 30%">Kode Role</th>
                                <th style="width: 20%">Tingkat</th>
                            </tr>
                        </thead>
                        <tbody id="table-perm-inherited-roles-body"></tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer bg-white py-2">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: TAMBAH / EDIT PERMISSION ================= -->
<div class="modal fade" id="modal-perm-form" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-perm-crud">
                <input type="hidden" name="id" id="perm-form-id">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modal-perm-title">Tambah Permission</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Tingkatan Tipe *</label>
                        <select name="type" id="input-perm-type" class="form-control select2" style="width: 100%;">
                            <option value="action">Action (Aksi Izin Spesifik - Level 4)</option>
                            <option value="widget">Widget (Komponen / Bagian Halaman - Level 3)</option>
                            <option value="page">Page (Halaman Menu - Level 2)</option>
                            <option value="module">Module (Modul Utama - Level 1)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="group-perm-parent">
                        <label class="font-weight-bold small">Node Induk (Parent)</label>
                        <select name="parent_id" id="input-perm-parent" class="form-control select2" style="width: 100%;">
                            <option value="0">-- Root (Tanpa Induk / Modul) --</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Nama Izin *</label>
                        <input type="text" name="name" id="input-perm-name" class="form-control" placeholder="Contoh: Tambah Pengguna" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Kode Unik Permission *</label>
                        <input type="text" name="code" id="input-perm-code" class="form-control" placeholder="Contoh: core:users:datatables:create" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Status</label>
                        <select name="status" id="input-perm-status" class="form-control select2" style="width: 100%;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-xs">Simpan Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: CLONE SUBTREE PERMISSION WIZARD ================= -->
<div class="modal fade" id="modal-clone-subtree" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-clone-subtree-submit">
                <input type="hidden" name="source_id" id="clone-source-id">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-copy mr-1"></i> Clone Subtree Permission</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Node Sumber:</label>
                        <input type="text" id="display-clone-source-name" class="form-control bg-light font-weight-bold" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Prefix Kode yang Diganti *</label>
                        <input type="text" name="prefix_find" id="clone-prefix-find" class="form-control" placeholder="Contoh: core:users" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Prefix Kode Baru *</label>
                        <input type="text" name="prefix_replace" id="clone-prefix-replace" class="form-control" placeholder="Contoh: core:customers" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Target Parent Node</label>
                        <select name="target_parent_id" id="select-clone-target-parent" class="form-control select2" style="width: 100%;"></select>
                    </div>
                    <div class="alert alert-info py-2 small mt-3 mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Seluruh anak aksi di bawah node ini akan <strong>diduplikasi secara otomatis</strong>.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-xs">Clone Subtree</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
    let permsDataTable = null;
    let selectedTypeFilter = '';
    let activeDetailPermId = null;

    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };

    $('.select2').select2({ theme: 'bootstrap4' });
    $('#modal-perm-form, #modal-clone-subtree, #modal-perm-detail').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({ theme: 'bootstrap4', dropdownParent: $(this) });
    });

    function initPermsDataTable() {
        permsDataTable = $('#table-perms-dt').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: permUrls.listDt,
                type: 'GET',
                data: function(d) {
                    d.type = selectedTypeFilter;
                }
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'code' },
                { data: 'type' },
                { data: 'parent_name' },
                { data: 'role_count' },
                { data: 'status' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-2x text-primary"></i> Memuat Data...',
                search: "Cari Permission:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ izin",
                paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
            }
        });
    }

    function loadParentOptions() {
        $.ajax({
            url: permUrls.getParentOpts,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let opts = '<option value="0">-- Root (Tanpa Induk / Modul) --</option>';
                    $.each(res.data, function(i, p) {
                        opts += `<option value="${p.id}">${p.name}</option>`;
                    });
                    $('#input-perm-parent, #select-clone-target-parent').html(opts);
                }
            }
        });
    }

    $('#filter-perm-type').on('change', function() {
        selectedTypeFilter = $(this).val();
        permsDataTable.ajax.reload();
    });

    // ================= DETAIL PERMISSION & ROLE PEMILIK =================
    $(document).on('click', '.btn-detail-perm', function() {
        const id = $(this).data('id');
        activeDetailPermId = id;
        loadPermDetailModal(id);
    });

    function loadPermDetailModal(permId) {
        $.ajax({
            url: `${permUrls.getPermDetail}?permission_id=${permId}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const p = res.permission;
                    $('#detail-perm-name').text(p.name);
                    $('#detail-perm-code').text(p.code);
                    $('#detail-perm-type').text(p.type);

                    // 1. Render Direct Roles
                    let directRows = '';
                    if (res.direct_roles.length === 0) {
                        directRows = '<tr><td colspan="4" class="text-center py-2 text-muted small">Belum ada role yang memiliki izin ini secara langsung.</td></tr>';
                    } else {
                        $.each(res.direct_roles, function(i, r) {
                            directRows += `
                                <tr>
                                    <td class="font-weight-bold text-dark">${r.name}</td>
                                    <td><code>${r.code}</code></td>
                                    <td><span class="badge badge-info">Level ${r.level}</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-xs btn-remove-role-perm" data-role-id="${r.id}" title="Cabut Izin dari Role">
                                            <i class="fas fa-times mr-1"></i> Cabut
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#table-perm-direct-roles-body').html(directRows);

                    // 2. Render Inherited Roles
                    let inheritedRows = '';
                    if (res.inherited_roles.length === 0) {
                        inheritedRows = '<tr><td colspan="3" class="text-center py-2 text-muted small">Tidak ada role atasan yang mewarisi izin ini.</td></tr>';
                    } else {
                        $.each(res.inherited_roles, function(i, r) {
                            inheritedRows += `
                                <tr>
                                    <td class="font-weight-bold text-dark">${r.name}</td>
                                    <td><code>${r.code}</code></td>
                                    <td><span class="badge badge-info">Level ${r.level}</span></td>
                                </tr>
                            `;
                        });
                    }
                    $('#table-perm-inherited-roles-body').html(inheritedRows);

                    // 3. Render Available Roles Dropdown
                    let roleOpts = '<option value="">-- Pilih Role Penerima Izin --</option>';
                    $.each(res.available_roles, function(i, r) {
                        roleOpts += `<option value="${r.id}">${r.name}</option>`;
                    });
                    $('#select-add-role-target').html(roleOpts);

                    $('#modal-perm-detail').modal('show');
                }
            }
        });
    }

    // Tambah Role ke Izin
    $('#form-add-role-to-perm').on('submit', function(e) {
        e.preventDefault();
        const roleId = $('#select-add-role-target').val();
        if (!roleId) {
            toastr.error('Silakan pilih role terlebih dahulu.');
            return;
        }

        $.ajax({
            url: permUrls.addRoleToPerm,
            type: 'POST',
            data: { permission_id: activeDetailPermId, role_id: roleId },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    loadPermDetailModal(activeDetailPermId);
                    permsDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // Cabut Izin dari Role
    $(document).on('click', '.btn-remove-role-perm', function() {
        const roleId = $(this).data('role-id');
        $.ajax({
            url: permUrls.removeRoleFromPerm,
            type: 'POST',
            data: { permission_id: activeDetailPermId, role_id: roleId },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    loadPermDetailModal(activeDetailPermId);
                    permsDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // ================= CREATE / EDIT / CLONE =================
    $('#btn-open-create-perm').on('click', function() {
        $('#form-perm-crud')[0].reset();
        $('#perm-form-id').val('');
        $('#modal-perm-title').text('Tambah Permission Baru');
        $('#input-perm-type').val('action').trigger('change.select2');
        $('#input-perm-parent').val('0').trigger('change.select2');
        $('#input-perm-status').val('active').trigger('change.select2');
        $('#modal-perm-form').modal('show');
    });

    $(document).on('click', '.btn-edit-perm', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `${permUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#perm-form-id').val(d.id);
                    $('#modal-perm-title').text('Edit Permission');
                    $('#input-perm-type').val(d.type).trigger('change.select2');
                    $('#input-perm-parent').val(d.parent_id).trigger('change.select2');
                    $('#input-perm-name').val(d.name);
                    $('#input-perm-code').val(d.code);
                    $('#input-perm-status').val(d.status).trigger('change.select2');
                    $('#modal-perm-form').modal('show');
                }
            }
        });
    });

    $('#form-perm-crud').on('submit', function(e) {
        e.preventDefault();
        const id = $('#perm-form-id').val();
        const url = id ? `${permUrls.update}?id=${id}` : permUrls.create;

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-perm-form').modal('hide');
                    toastr.success(res.message);
                    permsDataTable.ajax.reload(null, false);
                    loadParentOptions();
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    $(document).on('click', '.btn-clone-subtree', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `${permUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#clone-source-id').val(d.id);
                    $('#display-clone-source-name').val(`${d.name} (${d.code})`);
                    $('#clone-prefix-find').val(d.code);
                    $('#clone-prefix-replace').val(d.code + '_copy');
                    $('#select-clone-target-parent').val(d.parent_id).trigger('change.select2');
                    $('#modal-clone-subtree').modal('show');
                }
            }
        });
    });

    $('#form-clone-subtree-submit').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: permUrls.cloneSubtree,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-clone-subtree').modal('hide');
                    toastr.success(res.message);
                    permsDataTable.ajax.reload(null, false);
                    loadParentOptions();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    $(document).on('click', '.btn-delete-perm', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Permission?',
            text: 'Izin ini akan dinonaktifkan dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${permUrls.delete}?id=${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            permsDataTable.ajax.reload(null, false);
                            loadParentOptions();
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        initPermsDataTable();
        loadParentOptions();
    });
JS
);
?>