<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle utama untuk AdminLTE 3.2.0, Plugin Vendor, dan Custom Theme CSS.
 */
class AdminLteAsset extends AssetBundle
{
    // Menggunakan root @web agar dapat memanggil folder vendors/ dan css/ secara fleksibel
    public $baseUrl = '@web';
    public $basePath = '@webroot';

    public $css = [
        // Font Awesome Icons
        'vendors/adminlte/plugins/fontawesome-free/css/all.min.css',

        // DataTables Bootstrap 4 & Responsive
        'vendors/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css',
        'vendors/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css',

        // DateRangePicker
        'vendors/adminlte/plugins/daterangepicker/daterangepicker.css',

        // Select2 & Bootstrap 4 Theme
        'vendors/adminlte/plugins/select2/css/select2.min.css',
        'vendors/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css',

        // SweetAlert2 (Bootstrap 4 Theme) & Toastr
        'vendors/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
        'vendors/adminlte/plugins/toastr/toastr.min.css',
        
        // Plugin DragSort CSS
        'vendors/plugins/dragsort/dist/css/dragsort.min.css',

        // Core CSS AdminLTE 3.2.0
        'vendors/adminlte/dist/css/adminlte.min.css',

        // CUSTOM CSS THEME ADMINLTE (Diletakkan paling akhir untuk override)
        'css/themes/adminlte/adminlte.css',
    ];

    public $js = [
        // Bootstrap 4 Bundle (termasuk Popper.js)
        'vendors/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js',

        // Moment.js & DateRangePicker
        'vendors/adminlte/plugins/moment/moment.min.js',
        'vendors/adminlte/plugins/daterangepicker/daterangepicker.js',

        // DataTables Core, BS4 Adapter, & Responsive
        'vendors/adminlte/plugins/datatables/jquery.dataTables.min.js',
        'vendors/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
        'vendors/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js',

        // Select2 Full
        'vendors/adminlte/plugins/select2/js/select2.full.min.js',

        // SweetAlert2 & Toastr
        'vendors/adminlte/plugins/sweetalert2/sweetalert2.min.js',
        'vendors/adminlte/plugins/toastr/toastr.min.js',

        // Chart.js
        'vendors/adminlte/plugins/chart.js/Chart.min.js',

        // Plugin DragSort JS
        'vendors/plugins/dragsort/dist/js/dragsort.min.js',

        // Core JS AdminLTE 3.2.0
        'vendors/adminlte/dist/js/adminlte.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}