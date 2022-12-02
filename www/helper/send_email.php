<?php

use PHPMailer\PHPMailer\PHPMailer;   // using namespace
use PHPMailer\PHPMailer\Exception;

function send_email( $to, $subject, $body ) {
    require_once '../lib/PHPMailer/src/Exception.php';
    require_once '../lib/PHPMailer/src/PHPMailer.php';
    require_once '../lib/PHPMailer/src/SMTP.php';

    include "ini/gmail.php" ;
    if( empty( $smtp ) ) {
        return null ;
    }
    
    $mail = new PHPMailer( true ) ;
    $mail->IsSMTP();                       // enable SMTP
    $mail->SMTPDebug   = 1;                // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth    = true;             // authentication enabled
    $mail->SMTPSecure  = $smtp['secure'];  // secure transfer enabled REQUIRED for Gmail
    $mail->Host        = $smtp['host'];
    $mail->Port        = $smtp['port'];
    $mail->SMTPOptions = $smtp['options'];
    $mail->Username    = $smtp['user'];
    $mail->Password    = $smtp['pass'];

    $mail->SetFrom( $smtp['user'] ) ;

    $mail->IsHTML( true ) ;
    $mail->Subject = $subject ;
    $mail->Body    = $body;
    $mail->AddAddress( $to ) ;
    if(!$mail->Send()) {
        return false ;
    } else {
        return true ;
    }
}