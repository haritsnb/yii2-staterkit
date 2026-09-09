<?php

namespace app\assets;

use yii\web\AssetBundle;

class AdminLteAsset extends AssetBundle
{
    public $baseUrl = '@web/vendors';
    public $basePath = '@webroot/vendors';

    public $css = [
        'adminlte/plugins/fontawesome-free/css/all.min.css',
        'adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css',
        'adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css',
        'adminlte/plugins/daterangepicker/daterangepicker.css',
        'adminlte/plugins/select2/css/select2.min.css',
        'adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css',
        'adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
        'adminlte/plugins/toastr/toastr.min.css',
        
        // Plugin DragSort CSS
        'plugins/dragsort/dist/css/dragsort.min.css',

        // Core CSS AdminLTE
        'adminlte/dist/css/adminlte.min.css',
    ];

    public $js = [
        'adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js',
        'adminlte/plugins/moment/moment.min.js',
        'adminlte/plugins/daterangepicker/daterangepicker.js',
        'adminlte/plugins/datatables/jquery.dataTables.min.js',
        'adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
        'adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js',
        'adminlte/plugins/select2/js/select2.full.min.js',
        'adminlte/plugins/sweetalert2/sweetalert2.min.js',
        'adminlte/plugins/toastr/toastr.min.js',
        'adminlte/plugins/chart.js/Chart.min.js',

        // Plugin DragSort JS
        'plugins/dragsort/dist/js/dragsort.min.js',

        // Core JS AdminLTE
        'adminlte/dist/js/adminlte.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}