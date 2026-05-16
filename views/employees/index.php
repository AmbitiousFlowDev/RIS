<?php
/** @var array $employees */
/** @var array $roles */
/** @var object $user */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Dashboard</title>
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
                <h1 class="text-xl font-semibold text-slate-800">Employees</h1>
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

                        <!-- Add Employee Button -->
                        <button onclick="openCreateModal()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm font-medium transition-colors w-full md:w-auto justify-center">
                            <i class="fa-solid fa-plus"></i> Add Employee
                        </button>

                        <!-- Export PDF Button -->
                        <a href="index.php?controller=Employee&action=exportPDF"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm font-medium transition-colors w-full md:w-auto justify-center">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </a>

                    </div>

                    <!-- Employees Table -->
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-600">ID</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">First Name</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Last Name</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Role</th>
                                <th class="px-6 py-3 font-semibold text-slate-600 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($employees as $employee): ?>
                                <?php
                                $roleCode = $employee['role_code'] ?? 'N/A';
                                $badgeClass = match ($roleCode) {
                                    'USER_HR' => 'bg-purple-100 text-purple-800',
                                    'USER_COMMERCIAL' => 'bg-blue-100 text-blue-800',
                                    'USER_DIRECTION' => 'bg-red-100 text-red-800',
                                    'USER_PURCHASING' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <tr class="table-row-hover transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?= (int)$employee['employee_id'] ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($employee['first_name']) ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($employee['last_name']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                            <?= htmlspecialchars($roleCode) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='openEditModal(<?= htmlspecialchars(json_encode($employee, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)'
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-xs flex items-center gap-1 transition-colors">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <button onclick="openDeleteModal(<?= (int)$employee['employee_id'] ?>, '<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES) ?>')"
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

    <!-- Create Employee Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-indigo-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Add New Employee</h2>
                <button onclick="closeModal('createModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <form action="index.php?controller=Employee&action=create" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter first name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter last name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('createModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Create Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-indigo-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Edit Employee</h2>
                <button onclick="closeModal('editModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <form action="index.php?controller=Employee&action=update" method="POST" class="p-6">
                <input type="hidden" name="employee_id" id="edit_employee_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter first name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter last name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role_id" id="edit_role_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i> Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Employee Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Delete Employee</h2>
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
                            You are about to delete "<span id="delete_employee_name" class="font-semibold"></span>". 
                            This action cannot be undone.
                        </p>
                    </div>
                </div>
                <form action="index.php?controller=Employee&action=delete" method="POST">
                    <input type="hidden" name="employee_id" id="delete_employee_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal('deleteModal')"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            <i class="fa-solid fa-trash mr-1"></i> Delete Employee
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

        function openEditModal(employee) {
            document.getElementById('edit_employee_id').value = employee.employee_id;
            document.getElementById('edit_first_name').value = employee.first_name;
            document.getElementById('edit_last_name').value = employee.last_name;
            document.getElementById('edit_role_id').value = employee.role_id;
            document.getElementById('editModal').classList.add('show');
        }

        function openDeleteModal(employeeId, employeeName) {
            document.getElementById('delete_employee_id').value = employeeId;
            document.getElementById('delete_employee_name').textContent = employeeName;
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
