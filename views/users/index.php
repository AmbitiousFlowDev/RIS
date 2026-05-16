<?php
/** @var array $users */
/** @var array $employees */
/** @var object $currentUser */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Dashboard</title>
    <link rel="stylesheet" href="assets/css/security-ui.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .table-row-hover:hover td {
            background-color: #f9fafb;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.2s ease-in-out;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
                <h1 class="text-xl font-semibold text-slate-800">Users</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600"><?= htmlspecialchars($currentUser->username ?? 'Admin') ?></span>
                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-auto p-6 lg:p-8">

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">

                        <!-- Add User Button -->
                        <button onclick="openCreateModal()"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm font-medium transition-colors w-full md:w-auto justify-center">
                            <i class="fa-solid fa-plus"></i> Add User
                        </button>

                        <!-- Export PDF Button -->
                        <a href="index.php?controller=User&action=exportPDF"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm font-medium transition-colors w-full md:w-auto justify-center">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </a>

                    </div>

                    <!-- Users Table -->
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-600">ID</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Username</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Employee</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Category</th>
                                <th class="px-6 py-3 font-semibold text-slate-600 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($users as $user): ?>
                                <?php
                                $category = $user['user_category'] ?? 'N/A';
                                $badgeClass = match ($category) {
                                    'ADMIN' => 'bg-red-100 text-red-800',
                                    'EMPLOYEE' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <tr class="table-row-hover transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?= (int)$user['user_id'] ?></td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-user-circle text-purple-500"></i>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($user['employee_name'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                            <?= htmlspecialchars($category) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='openEditModal(<?= htmlspecialchars(json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)'
                                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded text-xs flex items-center gap-1 transition-colors">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <button onclick="openDeleteModal(<?= (int)$user['user_id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>')"
                                                class="bg-red-600 hover:bg-red-700 text-white p-1.5 px-2 rounded text-xs transition-colors">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Create User Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-purple-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Add New User</h2>
                <button onclick="closeModal('createModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <form action="index.php?controller=User&action=create" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter username">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter password">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                        <select name="employee_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select an employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?= (int)$employee['employee_id'] ?>">
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User Category</label>
                        <select name="user_category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select category</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="EMPLOYEE">EMPLOYEE</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('createModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-purple-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Edit User</h2>
                <button onclick="closeModal('editModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <form action="index.php?controller=User&action=update" method="POST" class="p-6">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" id="edit_username" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter username">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="edit_password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter new password (optional)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                        <select name="employee_id" id="edit_employee_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select an employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?= (int)$employee['employee_id'] ?>">
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User Category</label>
                        <select name="user_category" id="edit_user_category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select category</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="EMPLOYEE">EMPLOYEE</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Delete User</h2>
                <button onclick="closeModal('deleteModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="flex items-start gap-4 mb-6">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fa-solid fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Are you sure?</h3>
                        <p class="text-sm text-gray-600">
                            You are about to delete user "<span id="delete_user_name" class="font-semibold"></span>". 
                            This action cannot be undone.
                        </p>
                    </div>
                </div>
                <form action="index.php?controller=User&action=delete" method="POST">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal('deleteModal')"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            <i class="fa-solid fa-trash mr-1"></i> Delete User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.add('show');
        }

        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.user_id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_employee_id').value = user.employee_id;
            document.getElementById('edit_user_category').value = user.user_category;
            document.getElementById('editModal').classList.add('show');
        }

        function openDeleteModal(userId, username) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_user_name').textContent = username;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>
