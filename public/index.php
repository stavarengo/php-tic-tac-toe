<?php

declare(strict_types=1);

chdir(dirname(__DIR__));

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $msg = 'Did you forgot to run `composer install`?' . PHP_EOL . 'Unable to load the "./vendor/autoload.php".';
    http_response_code(500);
    echo "<pre>$msg</pre>";
    throw new RuntimeException($msg);
}
require __DIR__ . '/../vendor/autoload.php';

/**
 * I'm using a self-called anonymous function to create its own scope and keep the the variables created here away from
 * the global scope.
 */
(function () {
    $currentDirectory = dirname(__FILE__);
    $basePath = (new \TicTacToe\WebUi\BasePathDetector())->detect($_SERVER['DOCUMENT_ROOT'], $currentDirectory);
    $view = new \TicTacToe\WebUi\View($basePath, __DIR__ . '/../src/WebUi/views');

    $requestUrlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestRoute = str_replace($basePath, '', rtrim($requestUrlPath, '/'));
    $requestRoute = preg_replace('~^(.*?)index.php$~', '$1', $requestRoute);
    $requestRoute = $requestRoute == '' ? '/' : $requestRoute;

    $viewName = '404.phtml';
    $viewVariables = [];
    try {
        if ($requestRoute == '/') {
            $viewName = 'index.phtml';
        }

        $content = $view->render($viewName, $viewVariables, '_template.phtml');
    } catch (\Throwable $e) {
        http_response_code(500);
        $viewName = '500.phtml';
        $viewVariables = [
            'exception' => $e,
        ];
        $content = $view->render($viewName, $viewVariables, '_template.phtml');
    }

    if ($viewName == '404.phtml') {
        http_response_code(404);
    }

    header('Content-Type: text/html; charset=utf-8');
    echo $content;
})();
