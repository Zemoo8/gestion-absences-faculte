<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // 🔥 CHANGE THIS
    $mail->Username   = 'farouk.zemoo@gmail.com'; // sender
    $mail->Password   = 'kibh ehzs ofxg zpem'; // Gmail app password ONLY

    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('farouk.zemoo@gmail.com', 'Absence System Test');
    $mail->addAddress('ahmed.baghouli@sesame.com.tn');

    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = ' t3adech?mmm';

    $mail->send();
    echo 'Mail sent successfully!';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
