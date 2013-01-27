<?php

require_once('../vendor/autoload.php');

$router = new \ApiSample\ApiSampleRouter();
$response = $router->getResponse();
$response->respond();
exit;
