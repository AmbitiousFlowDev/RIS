<?php
/** @var array $products */
/** @var object $user */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Dashboard</title>
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
                <h1 class="text-xl font-semibold text-slate-800">Products</h1>
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

                        <div class="flex gap-2 w-full md:w-auto">
                            <!-- Add Product Button -->
                            <button onclick="openCreateModal()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm font-medium transition-colors flex-1 md:flex-initial justify-center">
                                <i class="fa-solid fa-plus"></i> Add Product
                            </button>

                            <!-- Export PDF Button -->
                            <a href="index.php?controller=Product&action=exportPDF"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm flex items-center gap-2 text-sm font-medium transition-colors flex-1 md:flex-initial justify-center">
                                <i class="fa-solid fa-file-pdf"></i> Export PDF
                            </a>
                        </div>

                    </div>

                    <!-- Products Table -->
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-600">ID</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Name</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Category</th>
                                <th class="px-6 py-3 font-semibold text-slate-600 text-center">Stock</th>
                                <th class="px-6 py-3 font-semibold text-slate-600 text-right">Price</th>
                                <th class="px-6 py-3 font-semibold text-slate-600">Status</th>
                                <th class="px-6 py-3 font-semibold text-slate-600 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($products as $product): ?>
                                <?php
                                // Determine status dynamically
                                $stock = (int) $product['stock_quantity'];
                                $status = $stock === 0 ? 'Out of Stock' : ($stock < 10 ? 'Low Stock' : 'In Stock');
                                $badgeClass = match ($status) {
                                    'In Stock' => 'bg-green-100 text-green-800',
                                    'Low Stock' => 'bg-orange-100 text-orange-800',
                                    'Out of Stock' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <tr class="table-row-hover transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?= (int)$product['product_id'] ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($product['category']) ?></td>
                                    <td class="px-6 py-4 text-slate-900 font-medium text-center"><?= (int)$stock ?></td>
                                    <td class="px-6 py-4 text-slate-900 font-medium text-right">
                                        <?= number_format($product['unit_price'], 2, ',', ' ') ?> €
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                            <?= e($status) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='openEditModal(<?= htmlspecialchars(json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)'
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs flex items-center gap-1 transition-colors">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <button onclick="openDeleteModal(<?= (int)$product['product_id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>')"
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

    <!-- Create Product Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Add New Product</h2>
                <button onclick="closeModal('createModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <form action="index.php?controller=Product&action=create" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter product name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input type="text" name="category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter category">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (€)</label>
                        <input type="number" name="unit_price" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('createModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Edit Product</h2>
                <button onclick="closeModal('editModal')" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <form action="index.php?controller=Product&action=update" method="POST" class="p-6">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" name="name" id="edit_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter product name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input type="text" name="category" id="edit_category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter category">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (€)</label>
                        <input type="number" name="unit_price" id="edit_unit_price" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="edit_stock_quantity" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h2 class="text-lg font-semibold">Delete Product</h2>
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
                            You are about to delete "<span id="delete_product_name" class="font-semibold"></span>". 
                            This action cannot be undone.
                        </p>
                    </div>
                </div>
                <form action="index.php?controller=Product&action=delete" method="POST">
                    <input type="hidden" name="product_id" id="delete_product_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal('deleteModal')"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            <i class="fa-solid fa-trash mr-1"></i> Delete Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Open Create Modal
        function openCreateModal() {
            document.getElementById('createModal').classList.add('show');
        }

        // Open Edit Modal with pre-filled data
        function openEditModal(product) {
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_unit_price').value = product.unit_price;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('editModal').classList.add('show');
        }

        // Open Delete Modal
        function openDeleteModal(productId, productName) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('delete_product_name').textContent = productName;
            document.getElementById('deleteModal').classList.add('show');
        }

        // Close Modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }

        // Close modal with Escape key
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