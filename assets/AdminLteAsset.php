<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle utama untuk AdminLTE 3.2.0 dan seluruh plugin pendukungnya.
 * Mengacu pada folder lokal: project/web/vendors/adminlte/
 */
class AdminLteAsset extends AssetBundle
{
    public $baseUrl = '@web/vendors/adminlte';
    public $basePath = '@webroot/vendors/adminlte';

    public $css = [
        // 1. Font Awesome Icons
        'plugins/fontawesome-free/css/all.min.css',

        // 2. DataTables Bootstrap 4 & Responsive
        'plugins/datatables-bs4/css/dataTables.bootstrap4.min.css',
        'plugins/datatables-responsive/css/responsive.bootstrap4.min.css',

        // 3. DateRangePicker
        'plugins/daterangepicker/daterangepicker.css',

        // 4. Select2 & Bootstrap 4 Theme
        'plugins/select2/css/select2.min.css',
        'plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css',

        // 5. SweetAlert2 (Bootstrap 4 Theme) & Toastr
        'plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
        'plugins/toastr/toastr.min.css',

        // 6. Core CSS AdminLTE 3.2.0
        'dist/css/adminlte.min.css',
    ];

    public $js = [
        // 1. Bootstrap 4 Bundle (termasuk Popper.js)
        'plugins/bootstrap/js/bootstrap.bundle.min.js',

        // 2. Moment.js & DateRangePicker
        'plugins/moment/moment.min.js',
        'plugins/daterangepicker/daterangepicker.js',

        // 3. DataTables Core, BS4 Adapter, & Responsive
        'plugins/datatables/jquery.dataTables.min.js',
        'plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
        'plugins/datatables-responsive/js/dataTables.responsive.min.js',

        // 4. Select2 Full
        'plugins/select2/js/select2.full.min.js',

        // 5. SweetAlert2 & Toastr
        'plugins/sweetalert2/sweetalert2.min.js',
        'plugins/toastr/toastr.min.js',

        // 6. Chart.js (untuk Grafik Dashboard)
        'plugins/chart.js/Chart.min.js',

        // 7. Core JS AdminLTE 3.2.0
        'dist/js/adminlte.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset', // Menjamin jQuery dimuat sebelum plugin di atas
    ];
}