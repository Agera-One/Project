<?php
// public/index.php
// ============================================================
// Front Controller — Single Entry Point
// Jalankan dengan: php -S localhost:8000 -t public
// ============================================================

require_once dirname(__DIR__) . '/config/app.php';

// Parse URI — sederhana, tidak bergantung pada subfolder
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD']);

// ============================================================
// ROUTING TABLE
// ============================================================
$routes = [
    ['GET',  '/',                               'ArticleController', 'index'],
    ['GET',  '/articles',                       'ArticleController', 'list'],
    ['GET',  '~^/articles/(\d+)$~',             'ArticleController', 'show'],
    ['GET',  '/auth/login',                     'AuthController',    'showLogin'],
    ['POST', '/auth/login',                     'AuthController',    'login'],
    ['GET',  '/auth/register',                  'AuthController',    'showRegister'],
    ['POST', '/auth/register',                  'AuthController',    'register'],
    ['GET',  '/auth/logout',                    'AuthController',    'logout'],
    ['GET',  '/admin',                          'AdminController',   'dashboard'],
    ['GET',  '/admin/dashboard',                'AdminController',   'dashboard'],
    ['GET',  '/admin/articles',                 'AdminController',   'articles'],
    ['GET',  '/admin/articles/create',          'AdminController',   'createArticle'],
    ['POST', '/admin/articles/store',           'AdminController',   'storeArticle'],
    ['GET',  '~^/admin/articles/(\d+)/edit$~',  'AdminController',   'editArticle'],
    ['POST', '~^/admin/articles/(\d+)/update$~','AdminController',   'updateArticle'],
    ['POST', '~^/admin/articles/(\d+)/delete$~','AdminController',   'deleteArticle'],
    ['GET',  '/admin/users',                    'AdminController',   'users'],
    ['POST', '~^/admin/users/(\d+)/delete$~',   'AdminController',   'deleteUser'],
    ['POST', '~^/admin/users/(\d+)/role$~',     'AdminController',   'updateRole'],
];

// ============================================================
// Dispatch
// ============================================================
$matched = false;

foreach ($routes as [$routeMethod, $pattern, $controller, $action]) {
    if ($pattern[0] === '~') {
        if ($method === $routeMethod && preg_match($pattern, $uri, $matches)) {
            $ctrl = new $controller();
            $ctrl->$action(...array_map('intval', array_slice($matches, 1)));
            $matched = true;
            break;
        }
    } else {
        if ($method === $routeMethod && $uri === $pattern) {
            $ctrl = new $controller();
            $ctrl->$action();
            $matched = true;
            break;
        }
    }
}

if (!$matched) {
    http_response_code(404);
    include dirname(__DIR__) . '/views/errors/404.php';
}
