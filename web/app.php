<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

if (preg_match('/\.dev$/', $_SERVER['HTTP_HOST'])) {
    $app['debug'] = true;
}

$app->get('/auth', function (Application $app, Request $request) {
    return $app['twig']->render('auth.html.twig', array(
        'code'  => $request->get('code')
    ));
})->bind('auth');

$app->get('/', function (Application $app, Request $request) {
    return $app['twig']->render('index.html.twig', array(
    ));
})->bind('index');

$app->run();
