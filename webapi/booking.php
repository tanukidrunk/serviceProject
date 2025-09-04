<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require('Connect.php');


$app->get('/booking', function (Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "select * from booking AND SELECT * FROM booth WHERE boothID = ?";
    $result = $conn->query($sql);
    $data = array();
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});



$app->post('/booking/formInsert', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $conn = $GLOBALS['conn'];

   
    $checkStmt = $conn->prepare("SELECT COUNT(*) AS boothtotal FROM booking WHERE boothID = ? AND reserve != 'Â¡àÅÔ¡¡ÒÃ¨Í§'");
    $checkStmt->bind_param("s", $body['boothID']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result()->fetch_assoc();

    if ($checkResult['boothtotal'] = 4) {
        
        $stmt = $conn->prepare("INSERT INTO `booking` (`IDRE`, `memberID`, `eventID`,  `boothID`, `product_sold`, `StartDate`, `paydate`,`reserve` )
             VALUES(?,?,?,?,?,?,?,?) ");
        $stmt->bind_param("ssssssss",
        $body['IDRE'],
        $body['memberID'],
        $body['eventID'],
        $body['boothID'],
        $body['product_sold'],
        $body['StartDate'],
        $body['paydate'],
        $body['reserve']
        );
        $stmt->execute();
        $result = $stmt->affected_rows;

        if ($result == 1) {
            $bstatus = $body['IDRE'];
            $status = 'Reserved';
            $stmt = $conn->prepare("UPDATE booking SET reserve = ? WHERE IDRE = ?");
            $stmt->bind_param("ss", $status, $bstatus);
            $stmt->execute();
            $result = $stmt->affected_rows;

            if ($result > 0) {
                $boothId = $body['boothID'];
                $newStatus = 'Check';
                $updateStmt = $conn->prepare("UPDATE booth SET statusbooth = ? WHERE boothID = ?");
                $updateStmt->bind_param("ss", $newStatus, $boothId);
                $updateStmt->execute();
                $updateResult = $updateStmt->affected_rows;
            }
        }
        $response->getBody()->write($result . "");
    } else {
        
        $response->getBody()->write(json_encode(["message" => "äÁèÊÒÁÒÃ¶¨Í§ºÙ¸ä´éà¡Ô¹ 4 ºÙ¸"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/booking/cancel', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $conn = $GLOBALS['conn']; 
    $bookingCode = $body['IDRE'];
    $stmt = $conn->prepare("UPDATE booking SET reserve='Cancel' where IDRE=?");
    $stmt->bind_param("s", $bookingCode   
    );
    $stmt->execute();
    $result = $stmt->affected_rows;
    if($result=1){
        $bstatus = $body['boothID'];
        $status = 'vacant';
        $stmt = $conn->prepare("UPDATE booth SET statusbooth = ? WHERE boothID = ?");
        $stmt->bind_param("ss", $status, $bstatus);
        $stmt->execute();
        $result = $stmt->affected_rows;
        }
    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type', 'application/json');
    
});

$app->post('/booking/Payment', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $conn = $GLOBALS['conn'];
    $stmt_event_date = $conn->prepare("SELECT startDate FROM event WHERE eventID = (SELECT eventID FROM booking WHERE IDRE = ?)");
    $stmt_event_date->bind_param("s", $body['IDRE']);
    $stmt_event_date->execute();
    $result_event_date = $stmt_event_date->get_result();
    $event_date_row = $result_event_date->fetch_assoc();
    if (!$event_date_row) {
        $response->getBody()->write(json_encode(["message" => "No event date information found."]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    $event_date = new DateTime($event_date_row['startDate']);
    $current_date = new DateTime();
    $difference = $current_date->diff($event_date)->days;

    if ($difference < 5) {
        $response->getBody()->write(json_encode(["message" => "Can't payment"]));
        $stmt_cancel = $conn->prepare("UPDATE booking, booth SET booking.reserve = 'Cancel', booth.statusbooth = 'vacant' WHERE booking.IDRE = ?");
        $stmt_cancel->bind_param("s", $body['IDRE']);
        $stmt_cancel->execute();
        
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(["message" => "Can payment"]));
        $stmt_payment = $conn->prepare("UPDATE booking SET paymentstatus= 'Payment made', slip = ?, PayDate = NOW() WHERE IDRE = ?");
        $stmt_payment->bind_param("ss", $body['slip'], $body['IDRE']);
        $stmt_payment->execute();
        $stmt_update_booth = $conn->prepare("UPDATE booth SET statusbooth = 'Already reserved' WHERE boothID = (SELECT boothID FROM booking WHERE IDRE = ?)");
        $stmt_update_booth->bind_param("s", $body['IDRE']);
        $stmt_update_booth->execute();

        
        return $response->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/booking/Confirm', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $conn = $GLOBALS['conn'];
    
    $stmt_check_payment = $conn->prepare("SELECT * FROM booking WHERE IDRE = ? AND paymentstatus = 'Payment made'");
    $stmt_check_payment->bind_param("s", $body['IDRE']);
    $stmt_check_payment->execute();
    $result_check_payment = $stmt_check_payment->get_result();
    
    if ($result_check_payment->num_rows > 0) {
        $stmt_update_booking = $conn->prepare("UPDATE booking SET reserve = 'approve' WHERE IDRE= ?");
        $stmt_update_booking->bind_param("s", $body['IDRE']);
        $stmt_update_booking->execute();
        
        $stmt_update_booth = $conn->prepare("UPDATE booth SET statusbooth = 'Already reserved' WHERE boothID = (SELECT boothID FROM booking WHERE IDRE = ?)");
        $stmt_update_booth->bind_param("s", $body['IDRE']);
        $stmt_update_booth->execute();

        $response->getBody()->write(json_encode(["message" => "Booking approved"]));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(["message" => "Unable to approve booking Because no payment has been made yet."]));
        return $response->withHeader('Content-Type', 'application/json');
    }
});

$app->get('/booking/CheckNotpay', function(Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "SELECT members.FirstName , members.LastName , members.telephone , booth.boothName , zone.ZoneName
            FROM booking
            JOIN members ON members.memberID = booking.memberID 
            JOIN booth ON booth.boothID = booking.boothID
            JOIN zone ON   zone.ZoneName = booth.ZoneName
            WHERE booking.paymentstatus= 'Not yet paid'";
    $result = $conn->query($sql);
    $data = array();
    while($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/booking/CheckPaid', function(Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "SELECT members.FirstName , members.LastName , members.telephone , booth.boothName , zone.ZoneName
            FROM booking
            JOIN members ON members.memberID = booking.memberID 
            JOIN booth ON booth.boothID = booking.boothID
            JOIN zone ON   zone.ZoneName = booth.ZoneName
            WHERE  booking.paymentstatus = 'Payment made'";
    $result = $conn->query($sql);
    $data = array();
    while($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});
$app->get('/booking/Checkwait', function(Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "SELECT members.FirstName , members.LastName , members.telephone , booth.boothName , zone.ZoneName
            FROM booking
            JOIN members ON members.memberID = booking.memberID 
            JOIN booth ON booth.boothID = booking.boothID
            JOIN zone ON   zone.ZoneName = booth.ZoneName
            WHERE booth.statusbooth= 'check'";
    $result = $conn->query($sql);
    $data = array();
    while($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/booking/Checkall', function(Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "SELECT members.FirstName , members.LastName , members.telephone , booth.boothName , zone.ZoneName
    FROM booking
    JOIN members ON members.memberID = booking.memberID 
    JOIN booth ON booth.boothID = booking.boothID
    JOIN zone ON   zone.ZoneName = booth.ZoneName
    WHERE booking.reserve = 'Cancel'";
    
    
    $result = $conn->query($sql);
    $data = array();
    while($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/booking/booth', function(Request $request, Response $response, array $args) {
    $conn = $GLOBALS['conn'];
    $sql = "SELECT members.FirstName , members.LastName , members.telephone , booth.boothName , zone.ZoneName ,booth.prices ,booth.statusbooth
    FROM booking
    JOIN members ON members.memberID = booking.memberID 
    JOIN booth ON booth.boothID = booking.boothID
    JOIN zone ON   zone.ZoneName = booth.ZoneName
    WHERE booking.reserve = 'Reserved'";
    
    
    $result = $conn->query($sql);
    $data = array();
    while($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

?>