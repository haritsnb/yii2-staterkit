<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Manajemen Navigasi Menu';

$getGroupsUrl   = Url::to(['/menu/get-groups']);
$getTreeUrl     = Url::to(['/menu/get-tree']);
$saveOrderUrl   = Url::to(['/menu/save-order']);
$listDtUrl      = Url::to(['/menu/list-datatable']);
$createUrl      = Url::to(['/menu/create']);
$viewDataUrl    = Url::to(['/menu/view-data']);
$updateUrl      = Url::to(['/menu/update']);
$cloneUrl       = Url::to(['/menu/clone']);
$deleteUrl      = Url::to(['/menu/delete']);
$uploadIconUrl  = Url::to(['/menu/upload-icon']);
$getRoutesUrl   = Url::to(['/menu/get-available-routes']);
$getFaIconsUrl  = Url::to(['/menu/get-fontawesome-icons']);

$this->registerJsVar('menuUrls', [
    'getGroups'  => $getGroupsUrl,
    'getTree'    => $getTreeUrl,
    'saveOrder'  => $saveOrderUrl,
    'listDt'     => $listDtUrl,
    'create'     => $createUrl,
    'viewData'   => $viewDataUrl,
    'update'     => $updateUrl,
    'clone'      => $cloneUrl,
    'delete'     => $deleteUrl,
    'uploadIcon' => $uploadIconUrl,
    'getRoutes'  => $getRoutesUrl,
    'getFaIcons' => $getFaIconsUrl,
]);
?>

<style>
/* DragSort List Styles */
.ds-list .ds-icon, 
.ds-list .ds-item-icon {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background-color: #e8f0fe;
    color: #1a73e8;
    border-radius: 6px;
    font-size: 0.95rem;
    margin-right: 8px;
    transition: all 0.2s ease;
}
.ds-list .ds-item:hover .ds-icon,
.ds-list .ds-item:hover .ds-item-icon {
    background-color: #007bff;
    color: #ffffff;
}
.icon-preview-box {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1.15rem;
    color: #007bff;
    overflow: hidden;
}
.icon-preview-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.current-icon-card {
    border: 1px dashed #b8daff;
    background-color: #f4f8fd;
    border-radius: 8px;
}
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-bars text-primary mr-2"></i> Manajemen Navigasi Menu
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-outline-success btn-sm font-weight-bold mr-2 shadow-xs" id="btn-open-clone-wizard">
                    <i class="fas fa-copy mr-1"></i> Clone Menu
                </button>
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-xs" id="btn-open-create-modal">
                    <i class="fas fa-plus mr-1"></i> Tambah Menu Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Filter Pilih Menu Group -->
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="font-weight-bold text-sm text-dark mb-1">
                            <i class="fas fa-layer-group text-primary mr-1"></i> Pilih Lingkungan / Group Menu:
                        </label>
                        <select id="select-menu-group" class="form-control select2" style="width: 100%;"></select>
                    </div>
                    <div class="col-md-7 text-md-right mt-3 mt-md-0">
                        <div class="btn-group btn-group-toggle shadow-xs" data-toggle="buttons">
                            <label class="btn btn-outline-primary btn-sm active font-weight-bold" id="view-mode-dragsort">
                                <input type="radio" name="view_mode" checked><i class="fas fa-stream mr-1"></i> Susun Urutan (DragSort)
                            </label>
                            <label class="btn btn-outline-primary btn-sm font-weight-bold" id="view-mode-datatable">
                                <input type="radio" name="view_mode"><i class="fas fa-table mr-1"></i> Pencarian (DataTable)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTAINER DRAGSORT (Tree View) -->
        <div id="container-dragsort-view">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-5 mb-2 mb-md-0">
                            <h5 class="font-weight-bold text-dark mb-1" style="font-size: 1.15rem;">
                                <i class="fas fa-sort-amount-down text-info mr-2"></i>Susunan Menu Navigasi
                            </h5>
                            <small class="text-muted d-block font-weight-500" style="font-size: 0.82rem;">
                                <i class="fas fa-info-circle text-primary mr-1"></i>Seret handle menu untuk memindahkan urutan & hierarki (parent-child).
                            </small>
                        </div>
                        <div class="col-lg-6 col-md-7 text-md-right">
                            <div class="d-inline-flex align-items-center justify-content-md-end flex-wrap">
                                <div class="btn-group mr-2 mb-1 mb-sm-0 shadow-xs">
                                    <button type="button" class="btn btn-default btn-xs font-weight-bold px-2 py-1" id="btn-collapse-all" title="Tutup Semua Sub-menu">
                                        <i class="fas fa-compress-alt mr-1"></i> Collapse All
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs font-weight-bold px-2 py-1" id="btn-expand-all" title="Buka Semua Sub-menu">
                                        <i class="fas fa-expand-alt mr-1"></i> Expand All
                                    </button>
                                </div>
                                <div class="d-inline-flex align-items-center bg-light px-2 py-1 border rounded shadow-xs">
                                    <label for="indent-slider" class="small mb-0 mr-1 font-weight-bold text-muted" style="font-size: 0.75rem;">Indent:</label>
                                    <input type="range" id="indent-slider" min="20" max="60" value="40" step="5" style="width: 75px; height: 14px; cursor: pointer;">
                                    <span id="indent-val" class="small font-weight-bold text-primary ml-1" style="font-size: 0.8rem; min-width: 32px;">40px</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <ul id="menu-list" class="ds-list">
                        <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i> Memuat susunan menu...</div>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTAINER DATATABLE (Grid View) -->
        <div id="container-datatable-view" style="display: none;">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-search text-primary mr-2"></i> Pencarian & Daftar Data Menu
                    </h5>
                </div>
                <div class="card-body">
                    <table id="table-menus-dt" class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 15%">Group</th>
                                <th style="width: 20%">Nama Label</th>
                                <th style="width: 20%">Link Path</th>
                                <th style="width: 15%">Icon</th>
                                <th style="width: 10%">Induk</th>
                                <th style="width: 5%">Order</th>
                                <th style="width: 5%">Status</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ================= MODAL 1: FORM TAMBAH / EDIT MENU ================= -->
<div class="modal fade" id="modal-menu-form" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-menu-crud">
                <input type="hidden" name="id" id="menu-form-id">
                <input type="hidden" name="link" id="final-crud-link" value="#">
                <input type="hidden" name="icon" id="final-crud-icon" value="fas fa-circle">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modal-menu-title">Tambah Menu Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Group Menu *</label>
                            <select name="group_id" id="input-menu-group" class="form-control select2" style="width: 100%;"></select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Menu Induk (Parent)</label>
                            <select name="parent_id" id="input-menu-parent" class="form-control select2" style="width: 100%;">
                                <option value="0">-- Root Level (Tanpa Induk) --</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small">Label / Nama Menu *</label>
                            <input type="text" name="label" id="input-menu-label" class="form-control" placeholder="Contoh: Dashboard" required>
                        </div>

                        <!-- ================= 1. INPUT LINK * {select2(routes, url)}{<input>} ================= -->
                        <div class="col-md-12 form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Link *</label>
                            <div class="input-group">
                                <div class="input-group-prepend" style="width: 32%;">
                                    <select id="select-crud-link-type" class="form-control select2" style="width: 100%;">
                                        <option value="routes">Routes (Internal)</option>
                                        <option value="url">URL (Eksternal)</option>
                                    </select>
                                </div>
                                
                                <!-- Input Mode Routes -->
                                <div id="wrapper-crud-link-routes" class="flex-grow-1 ml-1" style="width: 65%;">
                                    <select id="select-crud-link-routes" class="form-control select2" style="width: 100%;"></select>
                                </div>
                                
                                <!-- Input Mode URL Eksternal -->
                                <div id="wrapper-crud-link-url" class="flex-grow-1 ml-1" style="display: none; width: 65%;">
                                    <input type="text" id="input-crud-link-url" class="form-control" placeholder="https://instagram.com/profil_anda">
                                </div>
                            </div>
                            <small class="text-muted" id="hint-crud-link">Pilih rute internal aplikasi yang tersedia.</small>
                        </div>

                        <!-- ================= 2. INPUT ICON * {select2(symbol, url, upload)}{<input>} ================= -->
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Icon *</label>

                            <!-- Kotak Ikon Saat Ini (Mode Edit) -->
                            <div id="container-current-icon" class="current-icon-card p-3 mb-0" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-preview-box mr-3 shadow-xs bg-white" id="display-current-icon-box">
                                            <i class="fas fa-circle"></i>
                                        </div>
                                        <div>
                                            <label class="small text-muted mb-0 d-block font-weight-bold">Ikon Menu Saat Ini:</label>
                                            <span class="font-weight-bold text-dark text-truncate d-inline-block" id="display-current-icon-text" style="max-width: 320px;">fas fa-circle</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 mt-sm-0">
                                        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold shadow-xs px-3" id="btn-toggle-change-icon">
                                            <i class="fas fa-edit mr-1"></i> Ganti Icon
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Pemilih Ikon Baru {select2(symbol, url, upload)}{<input>} -->
                            <div id="container-icon-picker" class="card card-outline card-info p-3 mb-0 shadow-xs" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold small text-dark mb-0">Pilih Format Ikon Baru:</label>
                                    <button type="button" class="btn btn-link btn-xs text-danger font-weight-bold p-0" id="btn-cancel-change-icon" style="display: none;">
                                        <i class="fas fa-times-circle mr-1"></i> Batal Mengganti Icon
                                    </button>
                                </div>

                                <div class="input-group">
                                    <div class="input-group-prepend" style="width: 32%;">
                                        <select id="select-crud-icon-type" class="form-control select2" style="width: 100%;">
                                            <option value="symbol">Symbol (FontAwesome)</option>
                                            <option value="url">URL (Gambar Online)</option>
                                            <option value="upload">Upload (Dari Komputer)</option>
                                        </select>
                                    </div>

                                    <!-- Panel 1: Select2 Symbol FontAwesome Visual -->
                                    <div id="wrapper-crud-icon-symbol" class="flex-grow-1 ml-1" style="width: 65%;">
                                        <select id="select-crud-icon-symbol" class="form-control select2" style="width: 100%;"></select>
                                    </div>

                                    <!-- Panel 2: Input URL Gambar Online -->
                                    <div id="wrapper-crud-icon-url" class="flex-grow-1 ml-1" style="display: none; width: 65%;">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-preview-box mr-1" id="preview-crud-url"><i class="fas fa-image"></i></div>
                                            <input type="text" id="input-crud-icon-url" class="form-control" placeholder="https://example.com/logo.png">
                                        </div>
                                    </div>

                                    <!-- Panel 3: Upload File -->
                                    <div id="wrapper-crud-icon-upload" class="flex-grow-1 ml-1" style="display: none; width: 65%;">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-preview-box mr-2" id="preview-crud-upload"><i class="fas fa-file-image"></i></div>
                                            <div class="custom-file flex-grow-1">
                                                <input type="file" class="custom-file-input" id="file-crud-upload" accept="image/png, image/svg+xml, image/jpeg, image/webp, image/gif, image/x-icon">
                                                <label class="custom-file-label text-truncate" id="label-crud-upload" for="file-crud-upload">Pilih file ikon...</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted mt-1" id="hint-crud-icon">Pilih simbol grafis FontAwesome dari daftar visual.</small>
                            </div>
                        </div>

                        <!-- Tipe & Status -->
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small">Tipe Menu</label>
                            <select name="type" id="input-menu-type" class="form-control select2" style="width: 100%;">
                                <option value="url">URL (Link)</option>
                                <option value="text">Text / Header</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small">Status</label>
                            <select name="status" id="input-menu-status" class="form-control select2" style="width: 100%;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="input-menu-bind" name="bind" value="1" checked>
                                <label class="custom-control-label font-weight-bold small text-dark" for="input-menu-bind">
                                    Force Bind Mode (Sub-menu anak ikut bergerak saat parent digeser pada DragSort)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-xs">
                        <i class="fas fa-save mr-1"></i> Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: CLONE WIZARD ================= -->
<div class="modal fade" id="modal-clone-menu" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="form-clone-submit">
                <input type="hidden" name="link" id="final-clone-link" value="#">
                <input type="hidden" name="icon" id="final-clone-icon" value="fas fa-circle">

                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-copy mr-1"></i> Clone Menu Navigasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-4 pb-3 border-bottom">
                        <label class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-hand-pointer text-success mr-1"></i> Langkah 1: Pilih Menu yang Ingin Diclone:
                        </label>
                        <select id="select-clone-source" name="source_menu_id" class="form-control select2" style="width: 100%;"></select>
                    </div>

                    <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-edit mr-1"></i> Langkah 2: Sesuaikan Data Menu Baru:</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Target Group Menu *</label>
                            <select name="group_id" id="clone-target-group" class="form-control select2" style="width: 100%;"></select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Menu Induk (Parent)</label>
                            <select name="parent_id" id="clone-target-parent" class="form-control select2" style="width: 100%;">
                                <option value="0">-- Root Level --</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small">Label Baru *</label>
                            <input type="text" name="label" id="clone-input-label" class="form-control" required>
                        </div>

                        <!-- Link Clone {select2(routes, url)}{<input>} -->
                        <div class="col-md-12 form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Link *</label>
                            <div class="input-group">
                                <div class="input-group-prepend" style="width: 32%;">
                                    <select id="select-clone-link-type" class="form-control select2" style="width: 100%;">
                                        <option value="routes">Routes (Internal)</option>
                                        <option value="url">URL (Eksternal)</option>
                                    </select>
                                </div>
                                <div id="wrapper-clone-link-routes" class="flex-grow-1 ml-1" style="width: 65%;">
                                    <select id="select-clone-link-routes" class="form-control select2" style="width: 100%;"></select>
                                </div>
                                <div id="wrapper-clone-link-url" class="flex-grow-1 ml-1" style="display: none; width: 65%;">
                                    <input type="text" id="input-clone-link-url" class="form-control" placeholder="https://example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Icon Clone {select2(symbol, url, upload)}{<input>} -->
                        <div class="col-md-12 form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Icon *</label>
                            <div class="input-group">
                                <div class="input-group-prepend" style="width: 32%;">
                                    <select id="select-clone-icon-type" class="form-control select2" style="width: 100%;">
                                        <option value="symbol">Symbol (FontAwesome)</option>
                                        <option value="url">URL (Gambar Online)</option>
                                        <option value="upload">Upload (Dari Komputer)</option>
                                    </select>
                                </div>
                                <div id="wrapper-clone-icon-symbol" class="flex-grow-1 ml-1" style="width: 65%;">
                                    <select id="select-clone-icon-symbol" class="form-control select2" style="width: 100%;"></select>
                                </div>
                                <div id="wrapper-clone-icon-url" class="flex-grow-1 ml-1" style="display: none; width: 65%;">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-preview-box mr-1" id="preview-clone-url"><i class="fas fa-image"></i></div>
                                        <input type="text" id="input-clone-icon-url" class="form-control" placeholder="https://example.com/logo.png">
                                    </div>
                                </div>
                                <div id="wrapper-clone-icon-upload" class="flex-grow-1 ml-1" style="display: none; width: 65%;">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-preview-box mr-2" id="preview-clone-upload"><i class="fas fa-file-image"></i></div>
                                        <div class="custom-file flex-grow-1">
                                            <input type="file" class="custom-file-input" id="file-clone-upload" accept="image/png, image/svg+xml, image/jpeg, image/webp, image/gif, image/x-icon">
                                            <label class="custom-file-label text-truncate" id="label-clone-upload" for="file-clone-upload">Pilih file ikon...</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small">Tipe</label>
                            <select name="type" id="clone-input-type" class="form-control select2" style="width: 100%;">
                                <option value="url">URL</option>
                                <option value="text">Text</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small">Status</label>
                            <select name="status" id="clone-input-status" class="form-control select2" style="width: 100%;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Menu hasil clone akan otomatis ditempatkan pada <strong>urutan paling akhir</strong>.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-xs">
                        <i class="fas fa-copy mr-1"></i> Simpan Menu Hasil Clone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Nowdoc (<<<'JS') murni aman di PHP 8.2+
$this->registerJs(<<<'JS'
    let dsInstance = null;
    let menusDataTable = null;
    let currentSelectedGroupId = 2;
    let cachedFlatMenus = [];
    let originalEditIcon = 'fas fa-circle';

    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };

    // Custom Template FontAwesome Visual Dropdown Select2
    function formatFaOption(opt) {
        if (!opt.id) return opt.text;
        return $(`<span><i class="${opt.id} mr-2 text-primary" style="width: 22px; text-align: center; font-size: 1.05rem;"></i> ${opt.text}</span>`);
    }

    function formatFaSelection(opt) {
        if (!opt.id) return opt.text;
        return $(`<span><i class="${opt.id} mr-2 text-primary"></i> ${opt.text}</span>`);
    }

    $('.select2').select2({ theme: 'bootstrap4' });
    $('#modal-menu-form, #modal-clone-menu').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({ theme: 'bootstrap4', dropdownParent: $(this) });
    });

    function isImageString(str) {
        if (!str) return false;
        const s = str.trim();
        return /^(https?:\/\/|\/|uploads\/|icons\/|data:image\/).*\.(png|jpg|jpeg|svg|webp|gif|ico)$/i.test(s) 
               || /^https?:\/\//i.test(s) 
               || s.startsWith('data:image/')
               || s.startsWith('uploads/')
               || s.startsWith('icons/')
               || /\.(png|jpg|jpeg|svg|webp|gif|ico)$/i.test(s);
    }

    function resolveImageUrl(iconPath) {
        if (!iconPath) return '';
        const s = iconPath.trim();
        if (s.startsWith('http://') || s.startsWith('https://') || s.startsWith('data:image/')) {
            return s;
        }
        return `${menuUrls.getGroups.replace('/menu/get-groups', '')}/storages/${s.replace(/^\//, '')}`;
    }

    function formatDragSortIcons() {
        $('#menu-list .ds-item').each(function() {
            const $item = $(this);
            const id = $item.attr('data-id') || $item.attr('data-ds-id');
            const itemData = cachedFlatMenus.find(m => String(m.id) === String(id));

            if (itemData && itemData.icon) {
                const iconTrim = itemData.icon.trim();
                let $iconEl = $item.find('.ds-icon, .ds-item-icon, .ds-handle-icon, [data-ds-icon]');
                if ($iconEl.length === 0) {
                    $iconEl = $item.find('span, div').filter(function() {
                        return $(this).text().trim() === itemData.icon;
                    });
                }

                if ($iconEl.length > 0) {
                    if (isImageString(iconTrim)) {
                        const imgUrl = resolveImageUrl(iconTrim);
                        $iconEl.html(`<img src="${imgUrl}" style="width: 18px; height: 18px; object-fit: contain; border-radius: 3px;" alt="icon">`);
                    } else {
                        $iconEl.html(`<i class="${iconTrim}"></i>`);
                    }
                    $iconEl.attr('title', iconTrim);
                }
            }
        });
    }

    function renderPreviewHtml(targetSelector, iconValue) {
        const val = (iconValue || '').trim();
        if (!val) {
            $(targetSelector).html('<i class="fas fa-circle"></i>');
            return;
        }
        if (isImageString(val)) {
            const imgUrl = resolveImageUrl(val);
            $(targetSelector).html(`<img src="${imgUrl}" alt="icon">`);
        } else {
            $(targetSelector).html(`<i class="${val}"></i>`);
        }
    }

    function validateIconFile(file) {
        if (!file) return false;
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'ico'];
        const ext = (file.name || '').split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(ext)) {
            toastr.error(`Format file <b>.${ext}</b> tidak didukung!<br>Gunakan: <b>JPG, JPEG, PNG, WebP, SVG, GIF</b>.`, 'Validasi Gagal');
            return false;
        }
        if (file.size > 2 * 1024 * 1024) {
            toastr.error('Ukuran file ikon terlalu besar! Maksimal <b>2 MB</b>.', 'Validasi Gagal');
            return false;
        }
        return true;
    }

    // Load Routes & FontAwesome Dropdown Options
    function loadRoutesAndIconsOptions() {
        $.ajax({
            url: menuUrls.getRoutes,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let opts = '';
                    $.each(res.data, function(i, r) {
                        opts += `<option value="${r.id}">${r.name}</option>`;
                    });
                    $('#select-crud-link-routes, #select-clone-link-routes').html(opts);
                }
            }
        });

        $.ajax({
            url: menuUrls.getFaIcons,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let opts = '';
                    $.each(res.data, function(i, fa) {
                        opts += `<option value="${fa.id}">${fa.name}</option>`;
                    });
                    $('#select-crud-icon-symbol, #select-clone-icon-symbol').html(opts).select2({
                        theme: 'bootstrap4',
                        templateResult: formatFaOption,
                        templateSelection: formatFaSelection
                    });
                }
            }
        });
    }

    // ================= SAKELAR INPUT LINK {select2(routes, url)}{<input>} =================
    $('#select-crud-link-type').on('change', function() {
        const type = $(this).val();
        if (type === 'routes') {
            $('#wrapper-crud-link-routes').show();
            $('#wrapper-crud-link-url').hide();
            $('#final-crud-link').val($('#select-crud-link-routes').val() || '/dashboard');
            $('#hint-crud-link').text('Pilih rute internal aplikasi yang tersedia.');
        } else {
            $('#wrapper-crud-link-routes').hide();
            $('#wrapper-crud-link-url').show();
            $('#final-crud-link').val($('#input-crud-link-url').val().trim() || 'https://');
            $('#hint-crud-link').text('Masukkan tautan URL eksternal (contoh: https://instagram.com/akun).');
        }
    });

    $('#select-crud-link-routes').on('change', function() {
        if ($('#select-crud-link-type').val() === 'routes') {
            $('#final-crud-link').val($(this).val());
        }
    });

    $('#input-crud-link-url').on('input', function() {
        if ($('#select-crud-link-type').val() === 'url') {
            $('#final-crud-link').val($(this).val().trim());
        }
    });

    // Sakelar Link pada Clone Modal
    $('#select-clone-link-type').on('change', function() {
        const type = $(this).val();
        if (type === 'routes') {
            $('#wrapper-clone-link-routes').show();
            $('#wrapper-clone-link-url').hide();
            $('#final-clone-link').val($('#select-clone-link-routes').val() || '/dashboard');
        } else {
            $('#wrapper-clone-link-routes').hide();
            $('#wrapper-clone-link-url').show();
            $('#final-clone-link').val($('#input-clone-link-url').val().trim() || 'https://');
        }
    });

    $('#select-clone-link-routes').on('change', function() {
        if ($('#select-clone-link-type').val() === 'routes') {
            $('#final-clone-link').val($(this).val());
        }
    });

    $('#input-clone-link-url').on('input', function() {
        if ($('#select-clone-link-type').val() === 'url') {
            $('#final-clone-link').val($(this).val().trim());
        }
    });

    // ================= SAKELAR INPUT ICON {select2(symbol, url, upload)}{<input>} =================
    $('#select-crud-icon-type').on('change', function() {
        const type = $(this).val();
        $('#wrapper-crud-icon-symbol, #wrapper-crud-icon-url, #wrapper-crud-icon-upload').hide();

        if (type === 'symbol') {
            $('#wrapper-crud-icon-symbol').show();
            $('#final-crud-icon').val($('#select-crud-icon-symbol').val() || 'fas fa-circle');
            $('#hint-crud-icon').text('Pilih simbol grafis FontAwesome dari daftar visual.');
        } else if (type === 'url') {
            $('#wrapper-crud-icon-url').show();
            $('#final-crud-icon').val($('#input-crud-icon-url').val().trim());
            $('#hint-crud-icon').text('Masukkan tautan langsung gambar/logo online.');
        } else if (type === 'upload') {
            $('#wrapper-crud-icon-upload').show();
            $('#hint-crud-icon').text('Unggah file gambar dari komputer (PNG, SVG, JPG, WebP).');
        }
    });

    $('#select-crud-icon-symbol').on('change', function() {
        if ($('#select-crud-icon-type').val() === 'symbol') {
            $('#final-crud-icon').val($(this).val());
        }
    });

    $('#input-crud-icon-url').on('input', function() {
        const val = $(this).val().trim();
        renderPreviewHtml('#preview-crud-url', val);
        if ($('#select-crud-icon-type').val() === 'url') {
            $('#final-crud-icon').val(val || 'fas fa-circle');
        }
    });

    $('#file-crud-upload').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        if (!validateIconFile(file)) {
            $(this).val('');
            $('#label-crud-upload').text('Pilih file ikon...');
            return;
        }

        $('#label-crud-upload').text(file.name);
        const formData = new FormData();
        formData.append('icon_file', file);

        toastr.info('Mengunggah file ikon...');
        $.ajax({
            url: menuUrls.uploadIcon,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    $('#final-crud-icon').val(res.iconPath);
                    $('#preview-crud-upload').html(`<img src="${res.fullUrl}" alt="icon">`);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // Toggle Ganti Icon & Batal Mengganti Icon pada Mode Edit
    $('#btn-toggle-change-icon').on('click', function() {
        $('#container-current-icon').slideUp(150);
        $('#container-icon-picker').slideDown(200);
        $('#btn-cancel-change-icon').show();

        // Bersihkan input picker saat dibuka
        $('#select-crud-icon-type').val('symbol').trigger('change');
        $('#input-crud-icon-url').val('');
        $('#preview-crud-url').html('<i class="fas fa-image"></i>');
        $('#file-crud-upload').val('');
        $('#label-crud-upload').text('Pilih file ikon baru...');
        $('#preview-crud-upload').html('<i class="fas fa-file-image"></i>');
    });

    $('#btn-cancel-change-icon').on('click', function() {
        $('#final-crud-icon').val(originalEditIcon);
        $('#container-icon-picker').slideUp(150);
        $('#container-current-icon').slideDown(200);
        toastr.info('Perubahan ikon dibatalkan. Menggunakan ikon semula.');
    });

    // Sakelar Icon pada Modal Clone
    $('#select-clone-icon-type').on('change', function() {
        const type = $(this).val();
        $('#wrapper-clone-icon-symbol, #wrapper-clone-icon-url, #wrapper-clone-icon-upload').hide();

        if (type === 'symbol') {
            $('#wrapper-clone-icon-symbol').show();
            $('#final-clone-icon').val($('#select-clone-icon-symbol').val() || 'fas fa-circle');
        } else if (type === 'url') {
            $('#wrapper-clone-icon-url').show();
            $('#final-clone-icon').val($('#input-clone-icon-url').val().trim());
        } else if (type === 'upload') {
            $('#wrapper-clone-icon-upload').show();
        }
    });

    $('#select-clone-icon-symbol').on('change', function() {
        if ($('#select-clone-icon-type').val() === 'symbol') {
            $('#final-clone-icon').val($(this).val());
        }
    });

    $('#input-clone-icon-url').on('input', function() {
        const val = $(this).val().trim();
        renderPreviewHtml('#preview-clone-url', val);
        if ($('#select-clone-icon-type').val() === 'url') {
            $('#final-clone-icon').val(val || 'fas fa-circle');
        }
    });

    $('#file-clone-upload').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        if (!validateIconFile(file)) {
            $(this).val('');
            $('#label-clone-upload').text('Pilih file ikon...');
            return;
        }

        $('#label-clone-upload').text(file.name);
        const formData = new FormData();
        formData.append('icon_file', file);

        toastr.info('Mengunggah file ikon...');
        $.ajax({
            url: menuUrls.uploadIcon,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    $('#final-clone-icon').val(res.iconPath);
                    $('#preview-clone-upload').html(`<img src="${res.fullUrl}" alt="icon">`);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // 1. Ambil List Group Menu
    function loadMenuGroups() {
        $.ajax({
            url: menuUrls.getGroups,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let opts = '';
                    $.each(res.data, function(i, g) {
                        opts += `<option value="${g.id}">${g.name} (${g.code}) - [${g.type}]</option>`;
                    });
                    $('#select-menu-group').html(opts);
                    $('#input-menu-group, #clone-target-group').html(opts);
                    $('#select-menu-group').val(currentSelectedGroupId).trigger('change.select2');
                }
            }
        });
    }

    // 2. Inisialisasi Plugin DragSort
    function initDragSortTree(treeData) {
        cachedFlatMenus = treeData;

        let parentOpts = '<option value="0">-- Root Level (Tanpa Induk) --</option>';
        $.each(treeData, function(i, m) {
            parentOpts += `<option value="${m.id}">${m.label} (ID: ${m.id})</option>`;
        });
        $('#input-menu-parent, #clone-target-parent').html(parentOpts);

        let cloneSourceOpts = '<option value="">-- Pilih Menu yang Ingin Diclone --</option>';
        $.each(treeData, function(i, m) {
            cloneSourceOpts += `<option value="${m.id}">${m.label} (${m.link})</option>`;
        });
        $('#select-clone-source').html(cloneSourceOpts);

        dsInstance = $('#menu-list').dragsort({
            data: treeData,
            indent: parseInt($('#indent-slider').val()) || 40,
            indentSensitivity: 0.5,

            onDrop: function(itemId, newDepth, orderData, isBinded) {
                dsInstance.render();
                formatDragSortIcons();
                saveMenuOrderAjax(orderData);
            },

            onToggle: function(itemId, isNowCollapsed) {
                formatDragSortIcons();
            }
        });

        formatDragSortIcons();
    }

    function loadTreeData() {
        $('#menu-list').html('<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i> Memuat susunan menu...</div>');

        $.ajax({
            url: `${menuUrls.getTree}?group_id=${currentSelectedGroupId}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    initDragSortTree(res.data);
                }
            }
        });
    }

    function saveMenuOrderAjax(orderData) {
        toastr.info('Menyimpan urutan menu...');
        $.ajax({
            url: menuUrls.saveOrder,
            type: 'POST',
            data: {
                group_id: currentSelectedGroupId,
                orderData: orderData
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    if (menusDataTable) menusDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    }

    function initDataTable() {
        menusDataTable = $('#table-menus-dt').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: menuUrls.listDt,
                type: 'GET',
                data: function(d) {
                    d.group_id = currentSelectedGroupId;
                }
            },
            columns: [
                { data: 'id' },
                { data: 'group_name' },
                { data: 'label' },
                { data: 'link' },
                { data: 'icon' },
                { data: 'parent_id' },
                { data: 'order' },
                { data: 'status' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-2x text-primary"></i> Memuat Data...',
                search: "Cari Menu:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ menu",
                paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
            }
        });
    }

    $('#select-menu-group').on('change', function() {
        currentSelectedGroupId = $(this).val();
        loadTreeData();
        if (menusDataTable) menusDataTable.ajax.reload();
    });

    $('#view-mode-dragsort').on('click', function() {
        $('#container-datatable-view').hide();
        $('#container-dragsort-view').fadeIn(200);
        formatDragSortIcons();
    });

    $('#view-mode-datatable').on('click', function() {
        $('#container-dragsort-view').hide();
        $('#container-datatable-view').fadeIn(200);
        if (!menusDataTable) initDataTable();
        else menusDataTable.ajax.reload();
    });

    $('#btn-collapse-all').on('click', function() { if (dsInstance) { dsInstance.collapseAll(); formatDragSortIcons(); } });
    $('#btn-expand-all').on('click', function() { if (dsInstance) { dsInstance.expandAll(); formatDragSortIcons(); } });
    $('#indent-slider').on('input', function() {
        const val = parseInt($(this).val());
        $('#indent-val').text(val + 'px');
        if (dsInstance) { dsInstance.set('indent', val); formatDragSortIcons(); }
    });

    // ================= BUKA MODAL TAMBAH MENU BARU =================
    $('#btn-open-create-modal').on('click', function() {
        $('#form-menu-crud')[0].reset();
        $('#menu-form-id').val('');
        $('#modal-menu-title').text('Tambah Menu Baru');

        // Form Link Reset
        $('#select-crud-link-type').val('routes').trigger('change');
        $('#select-crud-link-routes').val('/dashboard').trigger('change.select2');
        $('#final-crud-link').val('/dashboard');

        // Form Icon Reset (Mode Tambah: Langsung Tampilkan Picker Bersih)
        $('#container-current-icon').hide();
        $('#container-icon-picker').show();
        $('#btn-cancel-change-icon').hide();

        $('#select-crud-icon-type').val('symbol').trigger('change');
        $('#select-crud-icon-symbol').val('fas fa-circle').trigger('change.select2');
        $('#final-crud-icon').val('fas fa-circle');

        $('#input-menu-group').val(currentSelectedGroupId).trigger('change.select2');
        $('#input-menu-parent').val('0').trigger('change.select2');
        $('#input-menu-type').val('url').trigger('change.select2');
        $('#input-menu-status').val('active').trigger('change.select2');
        $('#modal-menu-form').modal('show');
    });

    // ================= BUKA MODAL EDIT MENU =================
    $(document).on('click', '.ds-action-edit, .btn-edit-menu', function(e) {
        e.stopPropagation();
        const id = $(this).attr('data-ds-id') || $(this).data('id');

        $.ajax({
            url: `${menuUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#menu-form-id').val(d.id);
                    $('#modal-menu-title').text('Edit Menu Navigasi');
                    
                    $('#input-menu-group').val(d.group_id).trigger('change.select2');
                    $('#input-menu-parent').val(d.parent_id).trigger('change.select2');
                    $('#input-menu-label').val(d.label);
                    $('#input-menu-type').val(d.type).trigger('change.select2');
                    $('#input-menu-status').val(d.status).trigger('change.select2');
                    $('#input-menu-bind').prop('checked', d.bind == 1);

                    // 1. Sinkronisasi Link (Routes vs URL Eksternal)
                    if (d.is_external) {
                        $('#select-crud-link-type').val('url').trigger('change');
                        $('#input-crud-link-url').val(d.link);
                        $('#final-crud-link').val(d.link);
                    } else {
                        $('#select-crud-link-type').val('routes').trigger('change');
                        $('#select-crud-link-routes').val(d.link).trigger('change.select2');
                        $('#final-crud-link').val(d.link);
                    }

                    // 2. Sinkronisasi Icon Saat Ini & Simpan Original
                    originalEditIcon = (d.icon || 'fas fa-circle').trim();
                    $('#final-crud-icon').val(originalEditIcon);

                    renderPreviewHtml('#display-current-icon-box', originalEditIcon);
                    $('#display-current-icon-text').text(originalEditIcon).attr('title', originalEditIcon);
                    
                    $('#container-current-icon').show();
                    $('#container-icon-picker').hide();

                    $('#modal-menu-form').modal('show');
                }
            }
        });
    });

    $('#form-menu-crud').on('submit', function(e) {
        e.preventDefault();
        const id = $('#menu-form-id').val();
        const url = id ? `${menuUrls.update}?id=${id}` : menuUrls.create;

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-menu-form').modal('hide');
                    toastr.success(res.message);
                    loadTreeData();
                    if (menusDataTable) menusDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    // ================= CLONE WIZARD =================
    $('#btn-open-clone-wizard').on('click', function() {
        $('#form-clone-submit')[0].reset();
        $('#select-clone-source').val('').trigger('change.select2');
        $('#clone-target-group').val(currentSelectedGroupId).trigger('change.select2');
        $('#select-clone-link-type').val('routes').trigger('change');
        $('#select-clone-icon-type').val('symbol').trigger('change');
        $('#final-clone-link').val('#');
        $('#final-clone-icon').val('fas fa-circle');
        $('#modal-clone-menu').modal('show');
    });

    $(document).on('click', '.btn-clone-direct', function() {
        const id = $(this).data('id');
        $('#select-clone-source').val(id).trigger('change');
        $('#modal-clone-menu').modal('show');
    });

    $('#select-clone-source').on('change', function() {
        const id = $(this).val();
        if (!id) return;

        $.ajax({
            url: `${menuUrls.viewData}?id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#clone-target-group').val(d.group_id).trigger('change.select2');
                    $('#clone-target-parent').val(d.parent_id).trigger('change.select2');
                    $('#clone-input-label').val(d.label + ' (Copy)');
                    $('#clone-input-type').val(d.type).trigger('change.select2');
                    $('#clone-input-status').val(d.status).trigger('change.select2');

                    // Link Clone
                    if (d.is_external) {
                        $('#select-clone-link-type').val('url').trigger('change');
                        $('#input-clone-link-url').val(d.link);
                        $('#final-clone-link').val(d.link);
                    } else {
                        $('#select-clone-link-type').val('routes').trigger('change');
                        $('#select-clone-link-routes').val(d.link).trigger('change.select2');
                        $('#final-clone-link').val(d.link);
                    }

                    // Icon Clone
                    const cloneIcon = (d.icon || 'fas fa-circle').trim();
                    $('#final-clone-icon').val(cloneIcon);

                    if (isImageString(cloneIcon)) {
                        if (cloneIcon.startsWith('http://') || cloneIcon.startsWith('https://')) {
                            $('#select-clone-icon-type').val('url').trigger('change');
                            $('#input-clone-icon-url').val(cloneIcon);
                            renderPreviewHtml('#preview-clone-url', cloneIcon);
                        } else {
                            $('#select-clone-icon-type').val('upload').trigger('change');
                            $('#label-clone-upload').text(cloneIcon);
                            renderPreviewHtml('#preview-clone-upload', cloneIcon);
                        }
                    } else {
                        $('#select-clone-icon-type').val('symbol').trigger('change');
                        $('#select-clone-icon-symbol').val(cloneIcon).trigger('change.select2');
                    }
                }
            }
        });
    });

    $('#form-clone-submit').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: menuUrls.clone,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modal-clone-menu').modal('hide');
                    toastr.success(res.message);
                    loadTreeData();
                    if (menusDataTable) menusDataTable.ajax.reload(null, false);
                } else {
                    toastr.error(Object.values(res.errors).join('<br>'));
                }
            }
        });
    });

    $(document).on('click', '.ds-action-delete, .btn-delete-menu', function(e) {
        e.stopPropagation();
        const id = $(this).attr('data-ds-id') || $(this).data('id');

        Swal.fire({
            title: 'Hapus Menu Navigasi?',
            text: 'Menu ini akan dihapus dari daftar navigasi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${menuUrls.delete}?id=${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            loadTreeData();
                            if (menusDataTable) menusDataTable.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        loadMenuGroups();
        loadRoutesAndIconsOptions();
        loadTreeData();
    });
JS
);
?>