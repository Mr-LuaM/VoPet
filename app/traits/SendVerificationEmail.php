<?php
namespace App\Traits;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

trait SendVerificationEmail
{
    protected function _sendVerificationEmail($email,$subject, $body){
    $mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alluasan599@gmail.com';
    $mail->Password = 'oxht neem udqo pvgn';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('alluasan599@gmail.com', 'Mailer');
    $mail->addAddress($email);

    //Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->send();
    return true;
} catch (Exception $e) {
    return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
    }
}