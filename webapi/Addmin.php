<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require('Connect.php');

$app->POST('/addmin/login', function (Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();

    $stmt = $conn->prepare("SELECT * FROM members WHERE email= ?  AND password= ?"  );
    $stmt->bind_param("ss", $body['email'], $body['password']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $response->getBody()->write(json_encode($result));
    } 
    
    return $response->withHeader('Content-Type', 'application/json');
});

?>