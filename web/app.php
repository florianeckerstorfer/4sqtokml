<?php

require_once __DIR__.'/../vendor/autoload.php';

define('FSQTOKML_VERSION', '1.0');

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Yaml\Yaml;

use TheTwelve\Foursquare\HttpClient\SymfonyHttpClient;
use TheTwelve\Foursquare\ApiGatewayFactory;

use FoursquareToKml\FoursquareToKml;
use FoursquareToKml\Generator\KmlGenerator;


$app = new Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

if (preg_match('/\.dev$/', $_SERVER['HTTP_HOST'])) {
    $app['debug'] = true;
}

$container = new \Pimple();
$container['config']          = Yaml::parse(__DIR__.'/../config/foursquare.yml');
$container['http_client']     = function () {
    return new SymfonyHttpClient();
};
$container['gateway_factory'] = $container->share(function ($container) {
    $factory = new ApiGatewayFactory($container['http_client']);
    $factory->setEndpointUri($container['config']['foursquare']['endpoint_uri']);
    $factory->useVersion($container['config']['foursquare']['api_version']);
    return $factory;
});


$fsToKml = new FoursquareToKml($container);
if (null !== $token = $app['session']->get('oauth_token')) {
    $fsToKml->setToken($token);
}

$app->get('/', function (Application $app, Request $request) use ($fsToKml) {
    return $app['twig']->render('index.html.twig', array(
        'user'      => $fsToKml->hasToken() ? $fsToKml->getUser() : null,
        'version'   => FSQTOKML_VERSION
    ));
})->bind('index');

$app->get('/login', function (Application $app, Request $request) use ($fsToKml) {
    $authGateway = $fsToKml->getAuthGateway();
    return $app->redirect($authGateway->getLoginUri());
})->bind('login');

$app->get('/logout', function (Application $app, Request $request) {
    $app['session']->set('oauth_token', null);
    return $app->redirect('/');
})->bind('logout');

$app->get('/auth', function (Application $app, Request $request) use ($fsToKml) {
    $authGateway = $fsToKml->getAuthGateway();
    $token = $authGateway->authenticateUser($request->get('code'));
    $app['session']->set('oauth_token', $token);

    return $app->redirect('/');
})->bind('auth');

$app->get('/generate', function (Application $app, Request $request) use ($fsToKml) {
    if (null === $token = $fsToKml->getToken()) {
        return $app->redirect('login');
    }
    $fsToKml->setToken($token);

    $generator = new KmlGenerator();

    return new Response(
        $generator->generate($fsToKml->getCheckins()),
        200,
        array(
            'Content-type' => 'Content-Type: application/vnd.google-earth.kml+xml',
            'Content-Disposition' => 'attachment; filename=checkins.kml'
        )
    );
})->bind('generate');

$app->run();
