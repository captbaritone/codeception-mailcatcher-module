<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    public function sendEmail($to, $subject, $body)
    {
        $phpmailer = new \PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->Host = '127.0.0.1';
        $phpmailer->Port = 1025;

        $phpmailer->addAddress($to);
        $phpmailer->Subject = $subject;
        $phpmailer->Body = $body;
        $phpmailer->send();
    }
    
    public function sendEmailWithAttachment($to, $subject, $body, $attachment_amount = 1) 
    {
        $phpmailer = new \PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->Host = '127.0.0.1';
        $phpmailer->Port = 1025;

        $phpmailer->addAddress($to);
        $phpmailer->Subject = $subject;
        $phpmailer->Body = $body;
        
        for ($i=0; $i<$attachment_amount; $i++) {
            $mail->addAttachment(codecept_data_dir('image.jpg'), 'attachment'.$i'.jpg');  
        }
        
        $phpmailer->send();
    }
}
