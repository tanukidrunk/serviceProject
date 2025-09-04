<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require('Connect.php');
$app->get('/booth',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $sql = "select * from booth ";
    $result = $conn->query($sql);
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/booth/{Zone_Name}',function (Request $request, Response $response, array $args){
    $zn = $args['Zone_Name'];
    $conn = $GLOBALS['conn'];
    
    $stmt = $conn->prepare('SELECT * FROM booth WHERE `Zone Name` = ?');

    $stmt->bind_param("s",$zn);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});
$app->get('/booth/{Zone_Name}/{boothName}',function (Request $request, Response $response, array $args){
    $zn = $args['Zone_Name'];
    $bn = $args['boothName'];
    $conn = $GLOBALS['conn'];
    
    $stmt = $conn->prepare('SELECT * FROM booth WHERE `Zone Name` = ? and`boothName` = ?');

    $stmt->bind_param("ss",$zn,$bn);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/booth/{Zone_Name}/{boothName}/{sizebooth}',function (Request $request, Response $response, array $args){
    $zn = $args['Zone_Name'];
    $bn = $args['boothName'];
    $sb = $args['sizebooth'];
    $conn = $GLOBALS['conn'];
    
    $stmt = $conn->prepare('SELECT * FROM booth WHERE `Zone Name` = ? and`boothName` = ? and`sizebooth` =? ');

    $stmt->bind_param("sss",$zn,$bn,$sb,);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});
$app->get('/booth/{Zone_Name}/{boothName}/{sizebooth}/{status}',function (Request $request, Response $response, array $args){
    $zn = $args['Zone_Name'];
    $bn = $args['boothName'];
    $sb = $args['sizebooth'];
    $st = $args['status'];
    $conn = $GLOBALS['conn'];
    
    $stmt = $conn->prepare('SELECT * FROM booth WHERE `Zone Name` = ? and`boothName` = ? and`sizebooth` =? and `status` = ?');

    $stmt->bind_param("ssss",$zn,$bn,$sb,$st);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/booth/{Zone_Name}/{boothName}/{sizebooth}/{status}/{price}',function (Request $request, Response $response, array $args){
    $zn = $args['Zone_Name'];
    $bn = $args['boothName'];
    $sb = $args['sizebooth'];
    $st = $args['status'];
    $pr = $args['price'];
    $conn = $GLOBALS['conn'];
    
    $stmt = $conn->prepare('SELECT * FROM booth WHERE `Zone Name` = ? and`boothName` = ? and`sizebooth` =? and `status` = ? abd price = ?');

    $stmt->bind_param("sssss",$zn,$bn,$sb,$st,$pr);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});


////////////////////////////////////////////////////////////////
$app->POST('/booth/add',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();
    $stmt = $conn->prepare("INSERT INTO `booth`(`ZoneName`,
     `boothName`, `sizebooth`, `statusbooth`, `prices`) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssss",
    $body['ZoneName'],
    $body['boothName'],
    $body['sizebooth'],
    $body['statusbooth'],
    $body['prices']);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
});

$app->POST('/booth/edit', function (Request $request, Response $response, array $args) {

    $body = $request->getParsedBody();
    $bid = $body['boothID'];
    $conn = $GLOBALS['conn'];

    $sql = "SELECT * FROM booth WHERE boothID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateSql = "UPDATE booth SET ZoneName = ?, boothName = ?, sizebooth = ?, statusbooth = ?, prices = ? WHERE boothID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssssss", 
        $body['ZoneName'],
        $body['boothName'],
        $body['sizebooth'],
        $body['statusbooth'],
        $body['prices'], $bid);
        $stmt->execute();

        $response->getBody()->write("update completed successfully");
        return $response->withStatus(200);
    } else {
        $response->getBody()->write("update failed");
        return $response->withStatus(404);
    }
});

////////////////////////////////////////////////////////////////////////
$app->post('/booth/delete', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $conn = $GLOBALS['conn']; 
    $stmt = $conn->prepare("DELETE FROM booth WHERE boothID = ?");
    $stmt->bind_param("s", $body['boothID']
    );

    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
});


?>