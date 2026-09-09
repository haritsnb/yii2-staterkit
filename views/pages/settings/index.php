<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Pengaturan Sistem';
$getDataUrl = Url::to(['/setting/get-data']);
$saveUrl    = Url::to(['/setting/save-register']);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold">Pengaturan Sistem</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/dashboard/index']) ?>">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-8 col-md-10">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-user-plus text-primary mr-2"></i> Kontrol Halaman Registrasi Publik
                            </h5>
                            <!-- Live Status Badge -->
                            <span id="live-register-status" class="badge badge-secondary px-3 py-2 font-weight-bold">Memuat Status...</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form id="form-settings-register">
                            
                            <!-- Opsi Mode Registrasi -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-2">Pilih Mode Operasional Registrasi:</label>
                                
                                <div class="custom-control custom-radio mb-2">
                                    <input class="custom-control-input" type="radio" id="mode-always-active" name="register_mode" value="always_active" checked>
                                    <label for="mode-always-active" class="custom-control-label font-weight-bold text-dark">
                                        <i class="fas fa-check-circle text-success mr-1"></i> Diaktifkan Selamanya (Always On)
                                    </label>
                                    <small class="text-muted d-block ml-4">Halaman pendaftaran akun publik selalu terbuka untuk siapa saja.</small>
                                </div>

                                <div class="custom-control custom-radio mb-2">
                                    <input class="custom-control-input" type="radio" id="mode-active-schedule" name="register_mode" value="active_schedule">
                                    <label for="mode-active-schedule" class="custom-control-label font-weight-bold text-dark">
                                        <i class="fas fa-clock text-info mr-1"></i> Diaktifkan Berdasarkan Jadwal (Tanggal & Jam Tertentu)
                                    </label>
                                    <small class="text-muted d-block ml-4">Registrasi hanya dibuka pada rentang waktu yang ditentukan (dapat diperpanjang kapan saja).</small>
                                </div>

                                <div class="custom-control custom-radio mb-2">
                                    <input class="custom-control-input" type="radio" id="mode-always-inactive" name="register_mode" value="always_inactive">
                                    <label for="mode-always-inactive" class="custom-control-label font-weight-bold text-dark">
                                        <i class="fas fa-ban text-danger mr-1"></i> Dimatikan Manual (Pendaftaran Ditutup)
                                    </label>
                                    <small class="text-muted d-block ml-4">Halaman pendaftaran ditutup sepenuhnya dan link di halaman login akan disembunyikan.</small>
                                </div>

                                <div class="custom-control custom-radio mb-2">
                                    <input class="custom-control-input" type="radio" id="mode-inactive-schedule" name="register_mode" value="inactive_schedule">
                                    <label for="mode-inactive-schedule" class="custom-control-label font-weight-bold text-dark">
                                        <i class="fas fa-calendar-times text-warning mr-1"></i> Dimatikan Sementara Berdasarkan Jadwal
                                    </label>
                                    <small class="text-muted d-block ml-4">Registrasi dibuka, kecuali pada rentang waktu masa jeda/pemeliharaan yang ditentukan.</small>
                                </div>
                            </div>

                            <!-- Input Tanggal & Jam (Muncul jika mode jadwal dipilih) -->
                            <div id="schedule-input-container" class="card card-outline card-info bg-light p-3 mb-4" style="display: none;">
                                <h6 class="font-weight-bold text-dark mb-3"><i class="far fa-calendar-alt text-info mr-1"></i> Rentang Jadwal (Waktu Lokal Klien):</h6>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="small font-weight-bold">Tanggal & Jam Mulai *</label>
                                        <input type="datetime-local" name="start_at" id="input-start-at" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="small font-weight-bold">Tanggal & Jam Selesai *</label>
                                        <input type="datetime-local" name="end_at" id="input-end-at" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-xs">
                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php
$this->registerJs(<<<JS
    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3500" };

    // Format ISO UTC ke input datetime-local browser
    function formatIsoToDatetimeLocal(isoStr) {
        if (!isoStr) return '';
        const d = new Date(isoStr);
        if (isNaN(d.getTime())) return '';
        const pad = (n) => String(n).padStart(2, '0');
        return `\${d.getFullYear()}-\${pad(d.getMonth() + 1)}-\${pad(d.getDate())}T\${pad(d.getHours())}:\${pad(d.getMinutes())}`;
    }

    function toggleScheduleInputs(mode) {
        if (mode === 'active_schedule' || mode === 'inactive_schedule') {
            $('#schedule-input-container').slideDown(200);
        } else {
            $('#schedule-input-container').slideUp(200);
        }
    }

    function loadSettings() {
        $.ajax({
            url: '{$getDataUrl}',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    const s = res.settings;

                    $(`input[name="register_mode"][value="\${s.register_mode}"]`).prop('checked', true);
                    toggleScheduleInputs(s.register_mode);

                    if (s.register_start_at) $('#input-start-at').val(formatIsoToDatetimeLocal(s.register_start_at));
                    if (s.register_end_at) $('#input-end-at').val(formatIsoToDatetimeLocal(s.register_end_at));

                    // Status Badge
                    if (s.is_active_now) {
                        $('#live-register-status').removeClass('badge-danger badge-secondary').addClass('badge-success').html('<i class="fas fa-check-circle mr-1"></i> STATUS: DIBUKA (AKTIF)');
                    } else {
                        $('#live-register-status').removeClass('badge-success badge-secondary').addClass('badge-danger').html('<i class="fas fa-ban mr-1"></i> STATUS: DITUTUP');
                    }
                }
            }
        });
    }

    $('input[name="register_mode"]').on('change', function() {
        toggleScheduleInputs($(this).val());
    });

    $('#form-settings-register').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{$saveUrl}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    loadSettings();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    $(document).ready(function() {
        loadSettings();
    });
JS
);
?>