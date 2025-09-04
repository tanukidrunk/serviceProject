<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require('Connect.php');
$app->get('/zones', function (Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "select * from zone ";
    $result = $conn->query($sql);
    $data = array();
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/zone/{Zone_Name}', function (Request $request, Response $response, array $args) {
    $zn = $args['Zone_Name'];
    $conn = $GLOBALS['conn'];

    $stmt = $conn->prepare('SELECT * FROM zone WHERE `ZoneName` = ?');

    $stmt->bind_param("s", $zn);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->POST('/zone/add', function (Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();
    $stmt = $conn->prepare("INSERT INTO `zone`(`ZoneName`, `detail`) VALUES(?,?)");
    $stmt->bind_param("ss", $body['ZoneName'], $body['detail']);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result . "");
    return $response->withHeader('Content-Type', 'application/json');
});

$app->POST('/zone/edit', function (Request $request, Response $response, array $args) {

    $body = $request->getParsedBody();
    $zid = $body['ZoneID'];
    $conn = $GLOBALS['conn'];

    $sql = "SELECT * FROM zone WHERE ZoneID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $zid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateSql = "UPDATE zone SET ZoneName = ?, detail = ? WHERE ZoneID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sss", $body['ZoneName'], $body['detail'], $zid);
        $stmt->execute();

        $response->getBody()->write("update completed successfully");
        return $response->withStatus(200);
    } else {
        $response->getBody()->write("update failed");
        return $response->withStatus(404);
    }
});

$app->post('/zone/delete', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $conn = $GLOBALS['conn']; 
    $stmt = $conn->prepare("DELETE FROM zone WHERE ZoneID = ?");
    $stmt->bind_param("s", $body['ZoneID']
    );

    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
});



?>