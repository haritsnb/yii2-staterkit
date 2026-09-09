<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Manajemen Navigasi Menu';

$getGroupsUrl  = Url::to(['/menu/get-groups']);
$getTreeUrl    = Url::to(['/menu/get-tree']);
$saveOrderUrl  = Url::to(['/menu/save-order']);
$listDtUrl     = Url::to(['/menu/list-datatable']);
$createUrl     = Url::to(['/menu/create']);
$viewDataUrl   = Url::to(['/menu/view-data']);
$updateUrl     = Url::to(['/menu/update']);
$cloneUrl      = Url::to(['/menu/clone']);
$deleteUrl     = Url::to(['/menu/delete']);
$uploadIconUrl = Url::to(['/menu/upload-icon']);

// Mendaftarkan URL ke JavaScript Variable (Bebas Deprecation Warning PHP 8.2+)
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
]);
?>

<style>
/* Styling Icon DragSort */
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
/* Live Icon Preview Box */
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
.icon-type-btn-group .btn {
    font-size: 0.82rem;
    padding: 6px 12px;
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

        <!-- 1. Filter Pilih Menu Group -->
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

        <!-- 2. CONTAINER DRAGSORT (Tree View) -->
        <div id="container-dragsort-view">
            <div class="card card-outline card-secondary shadow-sm">
                
                <!-- Card Header Rapi & Responsif -->
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

        <!-- 3. CONTAINER DATATABLE (Grid View) -->
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
                <input type="hidden" name="icon" id="final-crud-icon" value="fas fa-circle">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modal-menu-title">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Menu Baru
                    </h5>
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
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Label / Nama Menu *</label>
                            <input type="text" name="label" id="input-menu-label" class="form-control" placeholder="Contoh: Dashboard" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Link / URL *</label>
                            <input type="text" name="link" id="input-menu-link" class="form-control" placeholder="Contoh: /dashboard atau https://instagram.com" required>
                        </div>

                        <!-- ================= AREA IKON MENU ================= -->
                        <div class="col-md-12 mb-3">
                            
                            <!-- 1. Tampilan Ikon Saat Ini (Mode Edit) -->
                            <div id="container-current-icon" class="current-icon-card p-3 mb-0" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-preview-box mr-3 shadow-xs bg-white" id="display-current-icon-box" style="width: 44px; height: 44px;">
                                            <i class="fas fa-circle"></i>
                                        </div>
                                        <div>
                                            <label class="small text-muted mb-0 d-block font-weight-bold">Ikon Menu Saat Ini:</label>
                                            <span class="font-weight-bold text-dark text-truncate d-inline-block" id="display-current-icon-text" style="max-width: 320px;">fas fa-circle</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 mt-sm-0">
                                        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold shadow-xs px-3" id="btn-toggle-change-icon">
                                            <i class="fas fa-edit mr-1"></i> Ubah / Ganti Ikon
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Form Pemilih Ikon Baru -->
                            <div id="container-icon-picker" class="card card-outline card-info p-3 mb-0 shadow-xs" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold small text-dark mb-0">
                                        <i class="fas fa-icons text-primary mr-1"></i> Pilih Sumber / Jenis Ikon Baru:
                                    </label>
                                    <button type="button" class="btn btn-link btn-xs text-danger font-weight-bold p-0" id="btn-cancel-change-icon" style="display: none;">
                                        <i class="fas fa-times-circle mr-1"></i> Batal / Tutup (Gunakan Ikon Semula)
                                    </button>
                                </div>

                                <div class="btn-group btn-group-toggle w-100 icon-type-btn-group shadow-xs mb-3" data-toggle="buttons">
                                    <label class="btn btn-outline-primary active font-weight-bold" id="lbl-crud-font">
                                        <input type="radio" name="crud_icon_type" value="font_icon" checked>
                                        <i class="fas fa-font mr-1"></i> Simbol (FontAwesome)
                                    </label>
                                    <label class="btn btn-outline-primary font-weight-bold" id="lbl-crud-url">
                                        <input type="radio" name="crud_icon_type" value="image_url">
                                        <i class="fas fa-link mr-1"></i> Tautan Gambar
                                    </label>
                                    <label class="btn btn-outline-primary font-weight-bold" id="lbl-crud-upload">
                                        <input type="radio" name="crud_icon_type" value="upload_file">
                                        <i class="fas fa-upload mr-1"></i> Unggah File
                                    </label>
                                </div>

                                <!-- Panel 1: FontAwesome -->
                                <div class="crud-icon-panel" id="panel-crud-font">
                                    <label class="font-weight-bold small text-secondary">Kelas Ikon FontAwesome</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="icon-preview-box" id="preview-crud-font"><i class="fas fa-circle"></i></div>
                                        </div>
                                        <input type="text" id="input-crud-font-class" class="form-control ml-1" placeholder="Contoh: fas fa-tachometer-alt, fab fa-instagram">
                                    </div>
                                    <small class="text-muted mt-1 d-block">Ketik kelas FontAwesome 5.</small>
                                </div>

                                <!-- Panel 2: URL Gambar -->
                                <div class="crud-icon-panel" id="panel-crud-url" style="display: none;">
                                    <label class="font-weight-bold small text-secondary">URL Gambar Online (PNG / SVG / JPG)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="icon-preview-box" id="preview-crud-url"><i class="fas fa-image"></i></div>
                                        </div>
                                        <input type="text" id="input-crud-image-url" class="form-control ml-1" placeholder="Contoh: https://example.com/icons/logo.png">
                                    </div>
                                    <small class="text-muted mt-1 d-block">Masukkan tautan langsung gambar.</small>
                                </div>

                                <!-- Panel 3: Unggah File Baru (Clean & Kosong Saat Dibuka) -->
                                <div class="crud-icon-panel" id="panel-crud-upload" style="display: none;">
                                    <label class="font-weight-bold small text-secondary">Pilih File Ikon Baru (PNG, SVG, JPG, WebP, GIF)</label>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-preview-box mr-2" id="preview-crud-upload"><i class="fas fa-file-image"></i></div>
                                        <div class="custom-file flex-grow-1">
                                            <input type="file" class="custom-file-input" id="file-crud-upload" accept="image/png, image/svg+xml, image/jpeg, image/webp, image/gif, image/x-icon">
                                            <label class="custom-file-label text-truncate" id="label-crud-upload" for="file-crud-upload">Pilih file ikon baru...</label>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">File baru akan otomatis diunggah ke folder penyimpanan.</small>
                                </div>
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
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Label Baru *</label>
                            <input type="text" name="label" id="clone-input-label" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Link / URL *</label>
                            <input type="text" name="link" id="clone-input-link" class="form-control" required>
                        </div>

                        <!-- Ikon pada Modal Clone -->
                        <div class="col-md-12 form-group mb-2">
                            <label class="font-weight-bold small text-dark d-block">
                                <i class="fas fa-icons text-success mr-1"></i> Sumber Ikon Menu Clone:
                            </label>
                            <div class="btn-group btn-group-toggle w-100 icon-type-btn-group shadow-xs" data-toggle="buttons">
                                <label class="btn btn-outline-success active font-weight-bold btn-sm" id="lbl-clone-font">
                                    <input type="radio" name="clone_icon_type" value="font_icon" checked>
                                    <i class="fas fa-font mr-1"></i> Simbol (FontAwesome)
                                </label>
                                <label class="btn btn-outline-success font-weight-bold btn-sm" id="lbl-clone-url">
                                    <input type="radio" name="clone_icon_type" value="image_url">
                                    <i class="fas fa-link mr-1"></i> Tautan Gambar
                                </label>
                                <label class="btn btn-outline-success font-weight-bold btn-sm" id="lbl-clone-upload">
                                    <input type="radio" name="clone_icon_type" value="upload_file">
                                    <i class="fas fa-upload mr-1"></i> Unggah File
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 form-group clone-icon-panel" id="panel-clone-font">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="icon-preview-box" id="preview-clone-font"><i class="fas fa-circle"></i></div>
                                </div>
                                <input type="text" id="input-clone-font-class" class="form-control ml-1" placeholder="Contoh: fas fa-circle">
                            </div>
                        </div>

                        <div class="col-md-12 form-group clone-icon-panel" id="panel-clone-url" style="display: none;">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="icon-preview-box" id="preview-clone-url"><i class="fas fa-image"></i></div>
                                </div>
                                <input type="text" id="input-clone-image-url" class="form-control ml-1" placeholder="Contoh: https://example.com/icon.png">
                            </div>
                        </div>

                        <div class="col-md-12 form-group clone-icon-panel" id="panel-clone-upload" style="display: none;">
                            <div class="d-flex align-items-center">
                                <div class="icon-preview-box mr-2" id="preview-clone-upload"><i class="fas fa-file-image"></i></div>
                                <div class="custom-file flex-grow-1">
                                    <input type="file" class="custom-file-input" id="file-clone-upload" accept="image/png, image/svg+xml, image/jpeg, image/webp, image/gif, image/x-icon">
                                    <label class="custom-file-label text-truncate" id="label-clone-upload" for="file-clone-upload">Pilih file ikon...</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 form-group mt-2">
                            <label class="font-weight-bold small">Tipe</label>
                            <select name="type" id="clone-input-type" class="form-control select2" style="width: 100%;">
                                <option value="url">URL</option>
                                <option value="text">Text</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mt-2">
                            <label class="font-weight-bold small">Status</label>
                            <select name="status" id="clone-input-status" class="form-control select2" style="width: 100%;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Menu baru hasil clone akan otomatis ditempatkan pada <strong>urutan paling akhir</strong>.
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
    
    // Variabel penyimpan nilai ikon asli saat modal edit dibuka
    let originalEditIcon = 'fas fa-circle';

    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };

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

    // ================= SAKELAR OPSI IKON (CRUD MODAL) =================
    $('input[name="crud_icon_type"]').on('change', function() {
        const type = $(this).val();
        $('.crud-icon-panel').hide();

        if (type === 'font_icon') {
            $('#panel-crud-font').fadeIn(150);
            const val = $('#input-crud-font-class').val().trim();
            if (val) $('#final-crud-icon').val(val);
        } else if (type === 'image_url') {
            $('#panel-crud-url').fadeIn(150);
            const val = $('#input-crud-image-url').val().trim();
            if (val) $('#final-crud-icon').val(val);
        } else if (type === 'upload_file') {
            $('#panel-crud-upload').fadeIn(150);
        }
    });

    $('#input-crud-font-class').on('input', function() {
        const val = $(this).val().trim();
        if (val) {
            $('#preview-crud-font').html(`<i class="${val}"></i>`);
            $('#final-crud-icon').val(val);
        } else {
            $('#preview-crud-font').html('<i class="fas fa-circle"></i>');
            $('#final-crud-icon').val(originalEditIcon);
        }
    });

    $('#input-crud-image-url').on('input', function() {
        const val = $(this).val().trim();
        if (val) {
            renderPreviewHtml('#preview-crud-url', val);
            $('#final-crud-icon').val(val);
        } else {
            $('#preview-crud-url').html('<i class="fas fa-image"></i>');
            $('#final-crud-icon').val(originalEditIcon);
        }
    });

    $('#file-crud-upload').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        if (!validateIconFile(file)) {
            $(this).val('');
            $('#label-crud-upload').text('Pilih file ikon baru...');
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
                    $('#file-crud-upload').val('');
                    $('#label-crud-upload').text('Pilih file ikon baru...');
                }
            },
            error: function() {
                toastr.error('Gagal mengunggah file ikon.');
                $('#file-crud-upload').val('');
                $('#label-crud-upload').text('Pilih file ikon baru...');
            }
        });
    });

    // ================= INTERAKSI TOGGLE / UBAH IKON & BATAL =================
    
    // Klik "Ubah / Ganti Ikon" -> Buka form picker yang BERSIH
    $('#btn-toggle-change-icon').on('click', function() {
        $('#container-current-icon').slideUp(150);
        $('#container-icon-picker').slideDown(200);
        $('#btn-cancel-change-icon').show();

        // Bersihkan seluruh input picker agar bersih & siap dipilih
        $('#input-crud-font-class').val('');
        $('#preview-crud-font').html('<i class="fas fa-circle"></i>');
        $('#input-crud-image-url').val('');
        $('#preview-crud-url').html('<i class="fas fa-image"></i>');
        $('#file-crud-upload').val('');
        $('#label-crud-upload').text('Pilih file ikon baru...');
        $('#preview-crud-upload').html('<i class="fas fa-file-image"></i>');

        // Buka panel default (FontAwesome) tanpa mengubah nilai final
        $('#lbl-crud-font').click();
    });

    // Klik "Batal / Tutup" -> Kembalikan ke ikon semula
    $('#btn-cancel-change-icon').on('click', function() {
        $('#final-crud-icon').val(originalEditIcon);
        
        $('#input-crud-font-class').val('');
        $('#input-crud-image-url').val('');
        $('#file-crud-upload').val('');
        $('#label-crud-upload').text('Pilih file ikon baru...');

        $('#container-icon-picker').slideUp(150);
        $('#container-current-icon').slideDown(200);

        toastr.info('Perubahan ikon dibatalkan. Menggunakan ikon semula.');
    });

    // ================= SAKELAR OPSI IKON (CLONE MODAL) =================
    $('input[name="clone_icon_type"]').on('change', function() {
        const type = $(this).val();
        $('.clone-icon-panel').hide();

        if (type === 'font_icon') {
            $('#panel-clone-font').fadeIn(150);
            $('#final-clone-icon').val($('#input-clone-font-class').val().trim() || 'fas fa-circle');
        } else if (type === 'image_url') {
            $('#panel-clone-url').fadeIn(150);
            $('#final-clone-icon').val($('#input-clone-image-url').val().trim());
        } else if (type === 'upload_file') {
            $('#panel-clone-upload').fadeIn(150);
        }
    });

    $('#input-clone-font-class').on('input', function() {
        const val = $(this).val().trim() || 'fas fa-circle';
        $('#preview-clone-font').html(`<i class="${val}"></i>`);
        $('#final-clone-icon').val(val);
    });

    $('#input-clone-image-url').on('input', function() {
        const val = $(this).val().trim();
        renderPreviewHtml('#preview-clone-url', val);
        $('#final-clone-icon').val(val);
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
                    $('#file-clone-upload').val('');
                    $('#label-clone-upload').text('Pilih file ikon...');
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

    // 2. Inisialisasi & Render Plugin DragSort
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

    // 3. Load Data Tree dari Server
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

    // 4. Simpan Urutan DragSort ke Database (AJAX)
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
            },
            error: function() {
                toastr.error('Gagal menyimpan urutan menu ke server.');
            }
        });
    }

    // 5. Inisialisasi DataTables Server-Side
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

    // Ganti Group Aktif
    $('#select-menu-group').on('change', function() {
        currentSelectedGroupId = $(this).val();
        loadTreeData();
        if (menusDataTable) menusDataTable.ajax.reload();
    });

    // Toggle View Mode
    $('#view-mode-dragsort').on('click', function() {
        $('#container-datatable-view').hide();
        $('#container-dragsort-view').fadeIn(200);
        formatDragSortIcons();
    });

    $('#view-mode-datatable').on('click', function() {
        $('#container-dragsort-view').hide();
        $('#container-datatable-view').fadeIn(200);
        if (!menusDataTable) {
            initDataTable();
        } else {
            menusDataTable.ajax.reload();
        }
    });

    // Controls DragSort
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
        $('#modal-menu-title').html('<i class="fas fa-plus-circle mr-1"></i> Tambah Menu Baru');

        $('#container-current-icon').hide();
        $('#container-icon-picker').show();
        $('#btn-cancel-change-icon').hide();

        $('#lbl-crud-font').click();
        $('#input-crud-font-class').val('fas fa-circle');
        $('#preview-crud-font').html('<i class="fas fa-circle"></i>');
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
                    $('#modal-menu-title').html('<i class="fas fa-edit mr-1"></i> Edit Menu Navigasi');
                    
                    $('#input-menu-group').val(d.group_id).trigger('change.select2');
                    $('#input-menu-parent').val(d.parent_id).trigger('change.select2');
                    $('#input-menu-label').val(d.label);
                    $('#input-menu-link').val(d.link);
                    $('#input-menu-type').val(d.type).trigger('change.select2');
                    $('#input-menu-status').val(d.status).trigger('change.select2');
                    $('#input-menu-bind').prop('checked', d.bind == 1);

                    // SIMPAN NILAI ASLI & PASANG KE FINAL ICON
                    originalEditIcon = (d.icon || 'fas fa-circle').trim();
                    $('#final-crud-icon').val(originalEditIcon);

                    // TAMPILKAN KARTU RINGKASAN IKON SAAT INI
                    renderPreviewHtml('#display-current-icon-box', originalEditIcon);
                    $('#display-current-icon-text').text(originalEditIcon).attr('title', originalEditIcon);
                    
                    $('#container-current-icon').show();
                    $('#container-icon-picker').hide();

                    $('#modal-menu-form').modal('show');
                }
            }
        });
    });

    // Submit CRUD Form
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

    // ================= CLONE MENU WIZARD =================
    $('#btn-open-clone-wizard').on('click', function() {
        $('#form-clone-submit')[0].reset();
        $('#select-clone-source').val('').trigger('change.select2');
        $('#clone-target-group').val(currentSelectedGroupId).trigger('change.select2');
        $('#lbl-clone-font').click();
        $('#input-clone-font-class').val('fas fa-circle');
        $('#preview-clone-font').html('<i class="fas fa-circle"></i>');
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
                    $('#clone-input-link').val(d.link);
                    $('#clone-input-type').val(d.type).trigger('change.select2');
                    $('#clone-input-status').val(d.status).trigger('change.select2');

                    const cloneIcon = (d.icon || 'fas fa-circle').trim();
                    $('#final-clone-icon').val(cloneIcon);

                    if (isImageString(cloneIcon)) {
                        if (cloneIcon.startsWith('http://') || cloneIcon.startsWith('https://')) {
                            $('#lbl-clone-url').click();
                            $('#input-clone-image-url').val(cloneIcon);
                            renderPreviewHtml('#preview-clone-url', cloneIcon);
                        } else {
                            $('#lbl-clone-upload').click();
                            $('#label-clone-upload').text(cloneIcon);
                            renderPreviewHtml('#preview-clone-upload', cloneIcon);
                        }
                    } else {
                        $('#lbl-clone-font').click();
                        $('#input-clone-font-class').val(cloneIcon);
                        $('#preview-clone-font').html(`<i class="${cloneIcon}"></i>`);
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

    // Delete Menu
    $(document).on('click', '.ds-action-delete, .btn-delete-menu', function(e) {
        e.stopPropagation();
        const id = $(this).attr('data-ds-id') || $(this).data('id');

        Swal.fire({
            title: 'Hapus Menu Navigasi?',
            text: 'Menu ini akan dihapus dari daftar navigasi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
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
        loadTreeData();
    });
JS
);
?>