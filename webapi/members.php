<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require('Connect.php');



    
$app->get('/members',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $sql = "select * from members ";
    $result = $conn->query($sql);
    $data = array();
    while ($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
     
});

$app->POST('/members/sigin',function (Request $request, Response $response, array $args){
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();
    $stmt = $conn->prepare("INSERT INTO `members`(`titleName`, `FirstName`, `LastName`, `telephone`, `email`, `password`) VALUES(?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$body['titlename'],$body['FirstName'],$body['LastName'],$body['telephone'],$body['email'],$body['password']);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
});

$app->POST('/members/edit',function (Request $request, Response $response, array $args){
     
      $body = $request->getParsedBody();
      $mid = $body['memberID'];
      $conn = $GLOBALS['conn'];
  
      $sql = "SELECT * FROM members WHERE memberID = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $mid);
      $stmt->execute();
      $result = $stmt->get_result();
  
      if ($result->num_rows > 0) {
          $updateSql = "UPDATE members SET titleName = ?, FirstName = ?, LastName = ?, telephone = ?, email = ?, password = ? WHERE memberID = ?";
          $stmt = $conn->prepare($updateSql);
          $stmt->bind_param("sssssss", $body['titlename'], $body['FirstName'], $body['LastName'], $body['telephone'], $body['email'], $body['password'], $mid);
          $stmt->execute();
  
          $response->getBody()->write("update completed successfully");
          return $response->withStatus(200);
      } else {
          $response->getBody()->write("update failed");
          return $response->withStatus(404);
      }
  
});

$app->POST('/members/login', function (Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $body = $request->getParsedBody();

    $stmt = $conn->prepare("SELECT * FROM members WHERE email=? AND password=?");
    $stmt->bind_param("ss", $body['email'], $body['password']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $response->getBody()->write(json_encode($result));
    } else {
        
    }
    return $response->withHeader('Content-Type', 'application/json');
});

$app->GET('/members/data', function (Request $request, Response $response) {
    $conn = $GLOBALS['conn'];
    $sql = "SELECT FirstName, LastName, telephone, email FROM members";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $members = array();
        while ($row = $result->fetch_assoc()) {
            $member = array(
                "FirstName" => $row["FirstName"],
                "LastName" => $row["LastName"],
                "telephone" => $row["telephone"],
                "email" => $row["email"]
            );
            array_push($members, $member);
        }
        $response->getBody()->write(json_encode($members));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write("none members");
        return $response->withStatus(404);
    }
});


?>