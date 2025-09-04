<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require('Connect.php');



$app->get('/event',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $sql = "select * from event ";
    $result = $conn->query($sql);
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
     
});





$app->POST('/event/add',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();
    $stmt = $conn->prepare("INSERT INTO `event`(`eventname`,`startDate`, `endDate`) VALUES(?,?,?)");
    $stmt->bind_param("sss",
    $body['eventname'],
    $body['startDate'],
    $body['endDate'],);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
});

$app->POST('/event/edit',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();
    $stmt = $conn->prepare("INSERT INTO `event`(`eventname`,`startDate`, `endDate`) VALUES(?,?,?)");
    $stmt->bind_param("sss",
    $body['eventname'],
    $body['startDate'],
    $body['endDate'],);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
});
?>