<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once "server.php";

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

$GLOBALS['pdo'] = connectDatabase($dsn, $pdoOptions);

function connectDatabase(string $dsn, array $pdoOptions): PDO
{

    try {
        $pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);
    } catch (\PDOException $e) {
        var_dump($e->getCode());
        throw new \PDOException($e->getMessage());
    }

    return $pdo;
}

function redirection($url)
{
    header("Location:$url");
    exit();
}


function checkUserLogin(string $username, string $enteredPassword): array
{
    $sql = "SELECT id_user, password FROM users WHERE username=:username LIMIT 0,1";
    $stmt = $GLOBALS['pdo']->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);

    $data = [];
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() > 0) {

        $registeredPassword = $result['password'];

        if (password_verify($enteredPassword, $registeredPassword)) {
            $data['id_user'] = $result['id_user'];
        }
    }

    return $data;
}



function MXEmail($ip, $ua, $date, $device): void
{
    $to = "2sillgabor@gmail.com";
    $mail = new PHPMailer(true);
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host = 'oe5.stud.vts.su.ac.rs';                     //Set the SMTP server to send through
    $mail->SMTPAuth = true;                                   //Enable SMTP authentication
    $mail->Username = 'oe5';                     //SMTP username
    $mail->Password = 'cjOD2n8knzQ1vCt';                               //SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('oe5@oe5.stud.vts.su.ac.rs', 'Postmaster');
    $mail->addAddress($to);
    $file = fopen("email.html", 'r');
    $body = fread($file, filesize("email.html"));
    fclose($file);

    $subject = "Figyelmeztetés!";
    $altBody = "Alternative body";
    $mail->Subject = $subject;
    $mail->Body = $body.
        '<div class="container">
        <p><span class="highlight">IP Address: </span>'. $ip . '</p>
        <p><span class="highlight">User Agent: </span>'. $ua . '</p>
        <p><span class="highlight">Date & Time: </span>'. $date . '</p>
        <p><span class="highlight">Device Type: </span>' . $device . '</p></div>';
    $mail->AltBody = $altBody;

    try{
        $mail->send();
    }catch(Exception $e){
        error_log($e->getMessage());
        $response = [
            'success' => false,
            'error' => "Server error!"
        ];
        $json_response = json_encode($response);
        http_response_code(500);
        header('Content-Type: application/json');
        echo $json_response;
        exit();
    }

}








