<?php
/** @var array $metrics */
/** @var array $recentOrders */
/** @var array $auditLogs */
/** @var object $user */
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Retail System Information</title>
    <link rel="stylesheet" href="assets/css/security-ui.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .table-row-hover:hover td {
            background-color: #f9fafb;
        }
    </style>
</head>

<body class="bg-gray-100 text-slate-800 font-sans antialiased">

    <div class="flex h-screen w-full">

        <?php include_once("views/layout/sidebar.php") ?>

        <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-100">

            <!-- Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex justify-between items-center px-6 shadow-sm z-10">
                <h1 class="text-xl font-semibold text-slate-800">Dashboard</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600">
                        Welcome <strong><?= htmlspecialchars($userFullName ?? 'Admin') ?></strong>
                    </span>
                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <div class="flex-1 overflow-auto p-6 lg:p-8">

                <?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-lock text-amber-600"></i>
                    <span>You do not have permission to access that page.</span>
                </div>
                <?php endif; ?>

                <!-- Metrics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                    <!-- Revenue -->
                    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase">Total Revenue</p>
                            <h3 class="text-2xl font-bold mt-1">
                                $<?= number_format($metrics['revenue'], 2) ?>
                            </h3>
                        </div>
                        <div class="p-2 bg-green-50 text-green-600 rounded-md">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                    </div>

                    <!-- Orders -->
                    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase">Total Orders</p>
                            <h3 class="text-2xl font-bold mt-1">
                                <?= number_format($metrics['orders_count']) ?>
                            </h3>
                        </div>
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-md">
                            <i class="fa-solid fa-shopping-cart"></i>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase">Products</p>
                            <h3 class="text-2xl font-bold mt-1">
                                <?= number_format($metrics['products_count']) ?>
                            </h3>
                        </div>
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-md">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                    </div>

                    <!-- Clients -->
                    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase">Clients</p>
                            <h3 class="text-2xl font-bold mt-1">
                                <?= number_format($metrics['clients_count']) ?>
                            </h3>
                        </div>
                        <div class="p-2 bg-orange-50 text-orange-600 rounded-md">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>

                </div>

                <!-- Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Recent Orders -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <div class="flex justify-between mb-4">
                            <h3 class="font-semibold">Recent Orders</h3>
                            <a href="../orders/index.php" class="text-xs text-blue-600 hover:underline">View All</a>
                        </div>

                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-semibold text-slate-600">ID</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">Client</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">Status</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($recentOrders as $order): ?>
                                    <?php
                                    $badgeClass = match ($order['status'] ?? 'Completed') {
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Shipped' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>
                                    <tr class="table-row-hover transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">#ORD-<?= (int)$order['id'] ?></td>
                                        <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($order['client']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                                <?= e($order['status'] ?? 'Completed') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium">$<?= number_format($order['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Audit Logs -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <div class="flex justify-between mb-4">
                            <h3 class="font-semibold">System Audit Log</h3>
                            <span class="text-xs text-slate-400">Latest Activity</span>
                        </div>

                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-semibold text-slate-600">Action</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">User</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600 text-right">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr class="table-row-hover transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900"><?= e($log['action']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="bg-slate-100 px-2 py-0.5 rounded text-xs"><?= e($log['user']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-xs text-slate-400"><?= e($log['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>

        </main>

    </div>
</body>

</html>
