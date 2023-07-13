<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require __DIR__.'/../../imports/vendor/autoload.php';

class Mail {

    private $mail;

    public function __construct() {

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'webart3.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'default@webart3.com';
        $mail->Password = 'Default##@1';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('default@webart3.com', 'Default Webart3');

        $this->mail = $mail;

    }

    public function send($email, $name, $subject, $body) {

        $mail = $this->mail;

        $mail->addAddress($email, $name);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        if ($mail->send()) {
            return true;
        } else {
            return false;
        }

    }

}
