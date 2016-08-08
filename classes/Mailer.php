<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 1:56
 */
class Mailer
{
    public static function sendEmail($to, $subject, $mailHeader, $mailBody)
    {
        require __DIR__ . '/../sendmail/PHPMailerAutoload.php';

        $mail = new PHPMailer;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.yandex.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'noreply@todo.hitrov.com';                 // SMTP username
        $mail->Password = 'hFg5DseT';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->setFrom('noreply@todo.hitrov.com', 'To-Do List Mailer');
        $mail->addAddress($to);     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $subject;

        $mail->Body = $mailHeader;
        $mail->Body .= $mailBody;

        return $mail->send();
    }
}