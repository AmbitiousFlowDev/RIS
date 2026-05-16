<?php

spl_autoload_register(function ($class) {
    $directories = ['controllers/', 'models/', 'utils/', 'traits/', 'interfaces/', 'strategies/', 'adapters/'];

    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/interfaces/UserInterface.php';
require_once __DIR__ . '/interfaces/UserProfile.php';
require_once __DIR__ . '/interfaces/UserFactory.php';
require_once __DIR__ . '/utils/Security.php';

Security::bootstrap();
Security::validateCsrfFromPost();

$controllerKey = $_GET['controller'] ?? 'Auth';
$actionName = $_GET['action'] ?? 'loginForm';

if (!preg_match('/^[A-Za-z]+$/', $controllerKey) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $actionName)) {
    http_response_code(400);
    exit('Invalid route.');
}

$allowedActions = [
    'Auth' => ['loginForm', 'login', 'logout'],
    'Dashboard' => ['index'],
    'Client' => ['index', 'create', 'update', 'delete', 'exportPDF'],
    'Employee' => ['index', 'create', 'update', 'delete', 'exportPDF'],
    'Order' => ['index', 'create', 'update', 'delete', 'exportPDF'],
    'Product' => ['index', 'create', 'update', 'delete', 'exportPDF'],
    'User' => ['index', 'create', 'update', 'delete', 'exportPDF'],
    'Audit' => ['index', 'recent'],
];

if (!isset($allowedActions[$controllerKey]) || !in_array($actionName, $allowedActions[$controllerKey], true)) {
    http_response_code(404);
    exit('Route not found.');
}

$controllerName = ucfirst($controllerKey) . 'Controller';

if (!class_exists($controllerName)) {
    http_response_code(404);
    exit("Error: The controller '$controllerName' was not found.");
}

$controller = new $controllerName();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $controllerKey === 'Auth' && $actionName === 'login') {
    $actionName = 'loginForm';
}

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    exit("Error: The action '$actionName' does not exist.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->$actionName($_POST);
    exit;
}

$controller->$actionName();
