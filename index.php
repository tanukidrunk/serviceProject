<?php
use Slim\Factory\AppFactory;
require __DIR__ .'/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/serviceProject0');

require __DIR__ .'/webapi/booking.php';
require __DIR__ .'/webapi/booth.php';
require __DIR__ .'/webapi/members.php';
require __DIR__ .'/webapi/zones.php';
require __DIR__ .'/webapi/Addmin.php';
require __DIR__ .'/webapi/event.php';

$app->run();
?>