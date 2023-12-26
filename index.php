<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/vendor/autoload.php';
/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();
$twig = Twig::create('templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));
/**
 * The routing middleware should be added earlier than the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled by the middleware
 */
$app->addRoutingMiddleware();

/**
 * Add Error Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null $logger -> Optional PSR-3 Logger
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);


// Define app routes
$app->get('/api/users', function (Request $request, Response $response, $args) {
    $n = checkLimit($request->getQueryParams());
    setcookie("users_cnt", (string)$n);
    $user_contr = new UserController($n);
    $users = $user_contr->getUser();
    $view = Twig::fromRequest($request);
    $response->getBody()->write(json_encode($users));
    return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
});


$app->get('/users', function (Request $request, Response $response, $args) {
    $n = checkLimit($request->getQueryParams());
    setcookie("users_cnt", $n);
    $user_contr = new UserController($n);
    $users = $user_contr->getUser();
    $view = Twig::fromRequest($request);
    return $view->render($response
        ->withHeader('content-type', 'text/html')
        ->withStatus(200), 'users.html', [
        'items' => $users
    ]);
})->setName('users');


$app->get('/add_user_token', function (Request $request, Response  $response){
    setcookie('access', "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdHQiOiJhY2Nlc3MiLCJleHAiOjE3MDM1MTA3MjQsImlhdCI6MTcwMzUwNzEyNC", 86400);
    return $response->withStatus(200)->withHeader('Location', 'users');
});

$app->get('/api/users/{userId}', function (Request $request, Response $response, $args) {
    $ids = $args['userId'];
    $user_contr = new UserController();
    $lim = $request->getCookieParams()['users_cnt'];
    $users = $user_contr->getConcreteUser($ids, $lim);
    $view = Twig::fromRequest($request);
    if ($users) {
        $response->getBody()->write(json_encode($users));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["message"=>"user not found"]));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(404);
    }
})->setName('user');



$app->get('/users/{userId}', function (Request $request, Response $response, $args) {
    $ids = $args['userId'];
    $user_contr = new UserController();
    $lim = $request->getCookieParams()['users_cnt'];
    $users = $user_contr->getConcreteUser($ids, $lim) ;
    $view = Twig::fromRequest($request);
    if ($users) {
        return $view->render($response
            ->withHeader('content-type', 'text/html')
            ->withStatus(200), 'user.html', [
            'items' => $users
        ]);
    } else {
        return $view->render($response
            ->withHeader('content-type', 'text/html')
            ->withStatus(404), '404.html');
    }
})->setName('user');

$app->delete('/api/users/delete/{userId}', function (Request $request, Response $response, $args) {
    $id = $args['userId'];
    $res["message"] = "User with id=$id was deleted";
    $response->getBody()->write(json_encode($res));
    return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(204);
})->setName('delete users');

$app->get('/welcome', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'start.html');
});


//
//
//$app->get('/all', function (Request $request, Response $response) {
//    $user_contr = new UserController();
//    $customers = $user_contr->getUser();
//
//    if ($customers){
//        $view = Twig::fromRequest($request);
//        return $view->render($response, 'users.html', [
//            'items' => $customers
//        ]);
//    } else {
//        $response->getBody()->write(json_encode($customers));
//        return $response
//            ->withHeader('content-type', 'application/json')
//            ->withStatus(500);
//    }
//})->setName('prof');
//
//$app->post('/users', function (Request $request, Response $response) {
//    $_input = $request->getParsedBody();
//
//    $name = $_input['name'];
//    $email = $_input['email'];
//    $user_contr = new UserController();
//    $users = $user_contr->createUser($name, $email);
//    return $response->withJson($users);
//});
//
//$app->put('/users/{id}', function (Request $request, Response $response, $args) {
//    $_input = $request->getParsedBody();
//
//    $name = $_input['name'];
//    $email = $_input['email'];
//    $user_contr = new UserController();
//    $users = $user_contr->updateUser($name, $email);
//    return $response->withJson($users);
//});


// Run app
$app->run();