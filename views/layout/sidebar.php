<?php
$sidebarUser = Auth::user();
$can = $sidebarUser instanceof UserInterface ? fn(string $r) => $sidebarUser->canAccess($r) : fn() => false;
?>
<aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between hidden md:flex">
    <div>
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <i class="fa-solid fa-layer-group text-blue-600 text-xl mr-2"></i>
            <span class="font-bold text-sm tracking-tight text-slate-900">Retail Information System</span>
        </div>
        <nav class="p-4 space-y-1">
            <?php if ($can('dashboard')): ?>
            <a href="index.php?controller=Dashboard&action=index" class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-gauge w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if ($can('products')): ?>
            <a href="index.php?controller=Product&action=index"
                class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-box w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Products</span>
            </a>
            <?php endif; ?>
            <?php if ($can('orders')): ?>
            <a href="index.php?controller=Order&action=index"
                class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-cart-shopping w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Orders</span>
            </a>
            <?php endif; ?>
            <?php if ($can('clients')): ?>
            <a href="index.php?controller=Client&action=index"
                class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-users w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Clients</span>
            </a>
            <?php endif; ?>
            <?php if ($can('employees')): ?>
            <a href="index.php?controller=Employee&action=index"
                class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-id-badge w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Employees</span>
            </a>
            <?php endif; ?>
            <?php if ($can('users')): ?>
            <a href="index.php?controller=User&action=index"
                class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-shield-halved w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Users</span>
            </a>
            <?php endif; ?>
            
            <?php if ($can('audit')): ?>
            <div class="border-t border-gray-100 my-2"></div>
            <a href="index.php?controller=Audit&action=index"
                class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-gray-50 hover:text-slate-900 rounded-md transition-colors">
                <i class="fa-solid fa-clipboard-list w-5 opacity-75"></i>
                <span class="font-medium text-sm ml-2">Audit Logs</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="p-4 border-t border-gray-100">
        <form action="index.php?controller=Auth&action=logout" method="POST">
            <button type="submit"
                class="w-full flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-md transition-colors bg-white border-0">
                <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                <span class="font-medium text-sm ml-2">Logout</span>
            </button>
        </form>
    </div>
</aside>