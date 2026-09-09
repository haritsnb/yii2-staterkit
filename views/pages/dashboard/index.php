<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Dashboard Utama';

$dataUrl = Url::to(['/dashboard/data']);
$dtUrl   = Url::to(['/dashboard/orders-datatable']);
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tachometer-alt mr-2 text-primary"></i>Dashboard Real-Time
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <!-- Tombol Reload AJAX -->
                <button type="button" id="btn-reload-dashboard" class="btn btn-primary btn-sm font-weight-bold shadow-xs">
                    <i class="fas fa-sync-alt mr-1"></i> Reload Data (AJAX)
                </button>
                <small class="text-muted ml-2">
                    <i class="far fa-clock mr-1"></i>Update: <span id="dashboard-last-updated" class="font-weight-bold">-</span>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- 1. KPI Small Boxes Container (Di-render 100% dari Controller) -->
        <div class="row" id="kpi-container">
            <div class="col-12 text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="text-muted mt-2 font-weight-500">Memuat data indikator KPI...</p>
            </div>
        </div>

        <!-- 2. Charts Row (Grafik Penjualan & Distribusi Perangkat) -->
        <div class="row">
            <!-- Sales Chart -->
            <div class="col-lg-7">
                <div class="card card-primary card-outline shadow-sm" id="card-sales">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold text-dark" id="sales-card-title">
                            <i class="fas fa-chart-bar text-primary mr-1"></i> Memuat Judul Grafik...
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" style="min-height: 270px; height: 270px; max-height: 270px; max-width: 100%;"></canvas>
                    </div>
                    <div class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin text-primary"></i></div>
                </div>
            </div>

            <!-- Device Donut Chart -->
            <div class="col-lg-5">
                <div class="card card-danger card-outline shadow-sm" id="card-device">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold text-dark" id="device-card-title">
                            <i class="fas fa-chart-pie text-danger mr-1"></i> Memuat Judul Grafik...
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="deviceChart" style="min-height: 270px; height: 270px; max-height: 270px; max-width: 100%;"></canvas>
                    </div>
                    <div class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin text-danger"></i></div>
                </div>
            </div>
        </div>

        <!-- 3. Server-Side DataTables (Pesanan Terbaru) -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-shopping-cart text-info mr-1"></i> Transaksi & Pesanan Terbaru (Server-Side)
                </h3>
            </div>
            <div class="card-body">
                <table id="table-dashboard-orders" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th style="width: 15%">Order ID</th>
                            <th style="width: 32%">Item Produk</th>
                            <th style="width: 15%">Status</th>
                            <th style="width: 18%">Total Harga</th>
                            <th style="width: 20%">Waktu Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Diisi otomatis oleh Server-Side DataTables -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<?php
$this->registerJs(<<<JS
    let salesChartInstance = null;
    let deviceChartInstance = null;
    let dashboardOrdersTable = null;

    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };

    /**
     * Helper: Format Tanggal UTC -> Waktu Lokal Klien (2 Baris + Waktu Relatif)
     */
    function formatUtcWithRelative(utcString) {
        if (!utcString) return '-';
        const clientDate = new Date(utcString);
        if (isNaN(clientDate.getTime())) return utcString;

        // Baris 1: dd-mm-yyyy
        const day = String(clientDate.getDate()).padStart(2, '0');
        const month = String(clientDate.getMonth() + 1).padStart(2, '0');
        const year = clientDate.getFullYear();
        const formattedDate = `\${day}-\${month}-\${year}`;

        // Baris 2: hh:mm
        const hours = String(clientDate.getHours()).padStart(2, '0');
        const minutes = String(clientDate.getMinutes()).padStart(2, '0');
        const formattedTime = `\${hours}:\${minutes}`;

        // Waktu relatif
        const now = new Date();
        const diffInSeconds = Math.floor((now - clientDate) / 1000);
        let relativeTime = 'baru saja';
        if (diffInSeconds >= 60 && diffInSeconds < 3600) relativeTime = `\${Math.floor(diffInSeconds / 60)} menit lalu`;
        else if (diffInSeconds >= 3600 && diffInSeconds < 86400) relativeTime = `\${Math.floor(diffInSeconds / 3600)} jam lalu`;
        else if (diffInSeconds >= 86400) relativeTime = `\${Math.floor(diffInSeconds / 86400)} hari lalu`;

        return `
            <div class="text-nowrap font-weight-bold">
                <i class="far fa-calendar-alt text-primary mr-1"></i>\${formattedDate}
            </div>
            <div class="text-nowrap text-muted small mt-1">
                <i class="far fa-clock text-secondary mr-1"></i>\${formattedTime} <span class="badge badge-light border">(\${relativeTime})</span>
            </div>
        `;
    }

    /**
     * Inisialisasi Kerangka Chart.js
     */
    function initDashboardCharts() {
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        salesChartInstance = new Chart(ctxSales, {
            type: 'bar',
            data: { labels: [], datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true }
                    }]
                }
            }
        });

        const ctxDevice = document.getElementById('deviceChart').getContext('2d');
        deviceChartInstance = new Chart(ctxDevice, {
            type: 'doughnut',
            data: { labels: [], datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
    }

    /**
     * Inisialisasi DataTables Server-Side Dashboard
     */
    function initDashboardOrdersTable() {
        dashboardOrdersTable = $('#table-dashboard-orders').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 5,
            lengthMenu: [[5, 10, 25], [5, 10, 25]],
            order: [[4, 'desc']], // Urut berdasarkan kolom waktu transaksi
            ajax: {
                url: '{$dtUrl}',
                type: 'GET'
            },
            columns: [
                { data: 'id' },
                { data: 'item' },
                { data: 'status' },
                { data: 'price' },
                {
                    data: 'created_at',
                    render: function(data, type, row) {
                        return type === 'display' ? formatUtcWithRelative(data) : data;
                    }
                }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw text-primary"></i> Memuat Data Server...',
                search: "Cari Transaksi:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pesanan",
                paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
            }
        });
    }

    /**
     * Mengambil & Memperbarui Seluruh Data Dashboard via AJAX
     */
    function loadDashboardData() {
        $('.overlay').show();
        $('#btn-reload-dashboard i').addClass('fa-spin');

        $.ajax({
            url: '{$dataUrl}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // 1. Render KPI Small Boxes
                    let kpiHtml = '';
                    $.each(response.kpis, function(index, item) {
                        kpiHtml += `
                            <div class="col-lg-3 col-6">
                                <div class="small-box \${item.bg_class} shadow-sm">
                                    <div class="inner">
                                        <h3>\${item.value}</h3>
                                        <p class="font-weight-500">\${item.title}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="\${item.icon}"></i>
                                    </div>
                                    <a href="\${item.link_url}" class="small-box-footer">
                                        Lihat Detail <i class="fas fa-arrow-circle-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    $('#kpi-container').html(kpiHtml);

                    // 2. Update Sales Bar Chart
                    $('#sales-card-title').html('<i class="fas fa-chart-bar text-primary mr-1"></i> ' + response.sales_chart.card_title);
                    salesChartInstance.data.labels = response.sales_chart.labels;
                    salesChartInstance.data.datasets = response.sales_chart.datasets;
                    salesChartInstance.update();

                    // 3. Update Device Donut Chart
                    $('#device-card-title').html('<i class="fas fa-chart-pie text-danger mr-1"></i> ' + response.device_chart.card_title);
                    deviceChartInstance.data.labels = response.device_chart.labels;
                    deviceChartInstance.data.datasets = response.device_chart.datasets;
                    deviceChartInstance.update();

                    // 4. Update Header Timestamp (Waktu Lokal Klien)
                    const updateDate = new Date(response.timestamp);
                    const hours = String(updateDate.getHours()).padStart(2, '0');
                    const minutes = String(updateDate.getMinutes()).padStart(2, '0');
                    const seconds = String(updateDate.getSeconds()).padStart(2, '0');
                    $('#dashboard-last-updated').text(`\${hours}:\${minutes}:\${seconds}`);

                    toastr.success('Data dashboard berhasil diperbarui!');
                }
            },
            error: function() {
                toastr.error('Gagal mengambil data terbaru dari server.');
            },
            complete: function() {
                $('.overlay').hide();
                $('#btn-reload-dashboard i').removeClass('fa-spin');
            }
        });
    }

    // Inisialisasi saat DOM Ready
    $(document).ready(function() {
        initDashboardCharts();
        initDashboardOrdersTable();
        loadDashboardData();

        // Event Tombol Reload Dashboard
        $('#btn-reload-dashboard').on('click', function() {
            loadDashboardData();
            if (dashboardOrdersTable) {
                // Reload data table tanpa me-reset pagination aktif
                dashboardOrdersTable.ajax.reload(null, false);
            }
        });
    });
JS
);
?>