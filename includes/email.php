<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

function enviarEmail($para, $assunto, $mensagem)
{

    $mail = new PHPMailer(true);

    try {
        // 🔥 CONFIG SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'v.helchzzz@gmail.com';
        $mail->Password = 'vnvymzyfmzjlwyyx'; // não é sua senha normal!
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('v.helchzzz@gmail.com', 'Tattoo Studio');
        $mail->addAddress($para);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Erro real: " . $mail->ErrorInfo;
        return false;
    }
}
