<?php
/** @var array $audits */
/** @var object $user */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Dashboard</title>
    <link rel="stylesheet" href="assets/css/security-ui.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .table-row-hover:hover td {
            background-color: #f9fafb;
        }

        .action-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>

<body class="bg-gray-100 text-slate-800 font-sans antialiased">

    <div class="flex h-screen w-full">

        <?php include_once("views/layout/sidebar.php") ?>

        <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-100">

            <!-- Header -->
            <header
                class="h-16 bg-white border-b border-gray-200 flex justify-between items-center px-6 shadow-sm z-10">
                <h1 class="text-xl font-semibold text-slate-800">Audit Logs</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600"><?= htmlspecialchars($user->username ?? 'Admin') ?></span>
                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-auto p-6 lg:p-8">

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">

                        <div class="flex items-center gap-3">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fa-solid fa-clipboard-list text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">System Activity Log</h2>
                                <p class="text-sm text-slate-500">All system events and user actions</p>
                            </div>
                        </div>

                        <!-- Filter buttons -->
                        <div class="flex gap-2">
                            <button onclick="filterByType('all')"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-sm transition-colors">
                                <i class="fa-solid fa-list mr-1"></i> All
                            </button>
                            <button onclick="filterByType('USER')"
                                class="px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded text-sm transition-colors">
                                <i class="fa-solid fa-user mr-1"></i> User
                            </button>
                            <button onclick="filterByType('PRODUCT')"
                                class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm transition-colors">
                                <i class="fa-solid fa-box mr-1"></i> Product
                            </button>
                            <button onclick="filterByType('ORDER')"
                                class="px-3 py-1.5 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded text-sm transition-colors">
                                <i class="fa-solid fa-shopping-cart mr-1"></i> Order
                            </button>
                        </div>

                    </div>

                    <!-- Audit Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-semibold text-slate-600">ID</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">Date & Time</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">Action Type</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">User</th>
                                    <th class="px-6 py-3 font-semibold text-slate-600">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100" id="auditTableBody">
                                <?php if (empty($audits)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                                            <p>No audit logs found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($audits as $audit): ?>
                                        <?php
                                        // Determine badge color based on action type
                                        $actionType = strtoupper($audit['action_type'] ?? '');
                                        $category = explode('.', $actionType)[0] ?? 'DEFAULT';
                                        
                                        $badgeClass = match ($category) {
                                            'USER' => 'bg-purple-100 text-purple-800',
                                            'PRODUCT' => 'bg-green-100 text-green-800',
                                            'ORDER' => 'bg-orange-100 text-orange-800',
                                            'CLIENT' => 'bg-blue-100 text-blue-800',
                                            'EMPLOYEE' => 'bg-indigo-100 text-indigo-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        
                                        $icon = match ($category) {
                                            'USER' => 'fa-user',
                                            'PRODUCT' => 'fa-box',
                                            'ORDER' => 'fa-shopping-cart',
                                            'CLIENT' => 'fa-users',
                                            'EMPLOYEE' => 'fa-id-card',
                                            default => 'fa-circle-info'
                                        };
                                        ?>
                                        <tr class="table-row-hover transition-colors" data-action-category="<?= e($category) ?>">
                                            <td class="px-6 py-4 font-medium text-slate-900"><?= (int)$audit['audit_id'] ?></td>
                                            <td class="px-6 py-4 text-slate-600">
                                                <?= e(date('M d, Y H:i:s', strtotime($audit['action_date']))) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="action-badge <?= $badgeClass ?>">
                                                    <i class="fa-solid <?= e($icon) ?> mr-1"></i>
                                                    <?= htmlspecialchars($actionType) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-xs">
                                                        <?= e(strtoupper(substr($audit['username'] ?? 'U', 0, 1))) ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($audit['username'] ?? 'Unknown') ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-700">
                                                <?= htmlspecialchars($audit['description'] ?? 'No description') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Filter audit logs by type
        function filterByType(type) {
            const rows = document.querySelectorAll('#auditTableBody tr[data-action-category]');
            
            rows.forEach(row => {
                const category = row.getAttribute('data-action-category');
                if (type === 'all' || category === type) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
