<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Html;

class DashboardController extends BaseAdminController
{
    /**
     * Mengarahkan folder view ke views/pages/dashboard/
     */
    public function getViewPath(): string
    {
        return Yii::getAlias('@app/views/pages/dashboard');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Endpoint API AJAX: Data Indikator KPI & Grafik
     */
    public function actionData(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->asJson([
            'status'    => 'success',
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'kpis' => [
                ['id' => 'orders', 'title' => 'Pesanan Baru', 'value' => (string) rand(120, 250), 'icon' => 'fas fa-shopping-bag', 'bg_class' => 'bg-info', 'link_url' => '#'],
                ['id' => 'conversion', 'title' => 'Rasio Konversi', 'value' => rand(40, 65) . '%', 'icon' => 'fas fa-chart-line', 'bg_class' => 'bg-success', 'link_url' => '#'],
                ['id' => 'users', 'title' => 'User Terdaftar', 'value' => (string) rand(30, 99), 'icon' => 'fas fa-user-plus', 'bg_class' => 'bg-warning', 'link_url' => '#'],
                ['id' => 'visitors', 'title' => 'Pengunjung Unik', 'value' => (string) rand(500, 1200), 'icon' => 'fas fa-chart-pie', 'bg_class' => 'bg-danger', 'link_url' => '#'],
            ],
            'sales_chart' => [
                'card_title' => 'Grafik Penjualan Bulanan (Juta Rp)',
                'labels'     => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
                'datasets'   => [
                    [
                        'label'           => 'Total Omset',
                        'data'            => [rand(10, 20), rand(15, 30), rand(20, 40), rand(25, 45), rand(30, 50), rand(35, 60), rand(40, 65), rand(45, 70), rand(50, 85)],
                        'backgroundColor' => 'rgba(60, 141, 188, 0.85)',
                        'borderColor'     => 'rgba(60, 141, 188, 1)',
                        'borderWidth'     => 1
                    ]
                ]
            ],
            'device_chart' => [
                'card_title' => 'Distribusi Perangkat Pengguna (%)',
                'labels'     => ['Mobile', 'Desktop', 'Tablet'],
                'datasets'   => [
                    [
                        'data'            => [rand(50, 70), rand(20, 35), rand(5, 15)],
                        'backgroundColor' => ['#f56954', '#00a65a', '#f39c12']
                    ]
                ]
            ]
        ]);
    }

    /**
     * Endpoint API AJAX: DataTables Server-Side untuk Tabel Pesanan Dashboard
     */
    public function actionOrdersDatatable(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 5);
        $search = $request->get('search')['value'] ?? '';
        $order  = $request->get('order', []);

        $allOrders = $this->getDummyOrdersPool();
        $totalRecords = count($allOrders);

        // 1. Server-Side Filter Search
        if (!empty($search)) {
            $allOrders = array_values(array_filter($allOrders, function ($row) use ($search) {
                return stripos($row['id'], $search) !== false ||
                       stripos($row['item'], $search) !== false ||
                       stripos($row['status'], $search) !== false ||
                       stripos($row['price'], $search) !== false ||
                       stripos($row['created_at'], $search) !== false;
            }));
        }
        $filteredRecords = count($allOrders);

        // 2. Server-Side Sorting
        if (!empty($order)) {
            $colIndex = (int) ($order[0]['column'] ?? 0);
            $colDir   = $order[0]['dir'] ?? 'asc';
            $columns  = ['id', 'item', 'status', 'price', 'created_at'];
            $sortKey  = $columns[$colIndex] ?? 'id';

            usort($allOrders, function ($a, $b) use ($sortKey, $colDir) {
                $res = strcmp((string) $a[$sortKey], (string) $b[$sortKey]);
                return $colDir === 'asc' ? $res : -$res;
            });
        }

        // 3. Server-Side Pagination
        $pagedOrders = array_slice($allOrders, $start, $length);

        // 4. Format Output Data
        $data = [];
        foreach ($pagedOrders as $item) {
            $data[] = [
                'id'         => '<a href="#" class="font-weight-bold">' . Html::encode($item['id']) . '</a>',
                'item'       => Html::encode($item['item']),
                'status'     => '<span class="badge ' . Html::encode($item['badge']) . '">' . Html::encode($item['status']) . '</span>',
                'price'      => '<strong>' . Html::encode($item['price']) . '</strong>',
                'created_at' => $item['created_at'],
            ];
        }

        return $this->asJson([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    /**
     * Sumber data dummy tabel pesanan
     */
    private function getDummyOrdersPool(): array
    {
        return [
            ['id' => 'OR-9001', 'item' => 'PlayStation 5 Slim 1TB', 'badge' => 'badge-success', 'status' => 'Shipped', 'price' => 'Rp 8.500.000', 'created_at' => '2026-03-01T02:15:30Z'],
            ['id' => 'OR-9002', 'item' => 'Samsung Smart TV 55"', 'badge' => 'badge-warning', 'status' => 'Pending', 'price' => 'Rp 6.200.000', 'created_at' => '2026-03-02T08:45:00Z'],
            ['id' => 'OR-9003', 'item' => 'Apple iPhone 15 Pro Max', 'badge' => 'badge-danger', 'status' => 'Delivered', 'price' => 'Rp 21.000.000', 'created_at' => '2026-03-03T11:20:15Z'],
            ['id' => 'OR-9004', 'item' => 'Mechanical Keyboard RGB', 'badge' => 'badge-info', 'status' => 'Processing', 'price' => 'Rp 1.150.000', 'created_at' => '2026-03-04T14:10:00Z'],
            ['id' => 'OR-9005', 'item' => 'Logitech MX Master 3S', 'badge' => 'badge-success', 'status' => 'Shipped', 'price' => 'Rp 1.450.000', 'created_at' => '2026-03-05T01:05:45Z'],
            ['id' => 'OR-9006', 'item' => 'MacBook Air M2 13"', 'badge' => 'badge-success', 'status' => 'Shipped', 'price' => 'Rp 16.500.000', 'created_at' => '2026-03-06T09:30:20Z'],
            ['id' => 'OR-9007', 'item' => 'Asus ROG Gaming Monitor', 'badge' => 'badge-warning', 'status' => 'Pending', 'price' => 'Rp 4.800.000', 'created_at' => '2026-03-07T16:00:00Z'],
            ['id' => 'OR-9008', 'item' => 'Sony WH-1000XM5', 'badge' => 'badge-info', 'status' => 'Processing', 'price' => 'Rp 5.200.000', 'created_at' => '2026-03-08T06:12:35Z'],
            ['id' => 'OR-9009', 'item' => 'iPad Air 5th Gen', 'badge' => 'badge-danger', 'status' => 'Delivered', 'price' => 'Rp 10.900.000', 'created_at' => '2026-03-09T23:55:00Z'],
            ['id' => 'OR-9010', 'item' => 'Keychron Q1 Pro', 'badge' => 'badge-success', 'status' => 'Shipped', 'price' => 'Rp 2.900.000', 'created_at' => '2026-03-10T12:00:00Z'],
            ['id' => 'OR-9011', 'item' => 'Samsung Galaxy S24 Ultra', 'badge' => 'badge-warning', 'status' => 'Pending', 'price' => 'Rp 20.500.000', 'created_at' => '2026-03-11T04:22:18Z'],
            ['id' => 'OR-9012', 'item' => 'Dell Ultrasharp 27"', 'badge' => 'badge-info', 'status' => 'Processing', 'price' => 'Rp 7.800.000', 'created_at' => '2026-03-12T18:40:10Z'],
        ];
    }
}