<?php

namespace app\core;

use app\core\PHPMailer\PHPMailer;
use app\models\Settings;

class Mailer
{
    private $senderData = [];
    private $mail;
    public  $status = [];
    public function __construct($array = [])
    {
        if (count($array) > 0) {
            $this->senderData = [
                'name'  => $array['name'],
                'email' => $array['email'],
            ];
        }
        $this->prepMailer();

        if (!empty($_FILES['files']['name'][0])) {
            $this->addFiles($_FILES['files']);
        }
    }
    public function prepMailer()
    {
        $this->mail = new PHPMailer();
        
        $this->mail->isSMTP();
        $this->mail->CharSet = "UTF-8";
        $this->mail->SMTPAuth   = true;
        $this->mail->SMTPDebug = 4;
        $this->mail->Debugoutput = function ($str, $level) {
            $GLOBALS['status'][] = $str;
        };
            
        $settings = Settings::load('email');

        $this->mail->Host       = $settings['host']['value'];
        $this->mail->Username   = $settings['username']['value'];
        $this->mail->Password   = $settings['password']['value'];
        $this->mail->SMTPSecure   = $settings['secure']['value'];
        $this->mail->Port   = (int) $settings['port']['value'];

        if (isset($this->senderData['email']))
            $this->mail->setFrom($this->senderData['email'], $this->senderData['name']);
        else
            $this->mail->setFrom($this->mail->Username, MAFCLUB_NAME);
        $this->mail->isHTML(true);
    }
    public function prepMessage($array)
    {
        $this->mail->Subject    = $array['title'];
        $this->mail->Body       = $array['body'];
    }
    public function addFiles($files)
    {
        for ($ct = 0; $ct < count($files['tmp_name']); $ct++) {
            $uploadfile = tempnam(sys_get_temp_dir(), sha1($files['name'][$ct]));
            $filename = $files['name'][$ct];
            if (move_uploaded_file($files['tmp_name'][$ct], $uploadfile)) {
                $this->mail->addAttachment($uploadfile, $filename);
                $this->status['fileResult'] = "Файл $filename прикреплён";
            } else {
                $this->status['fileResult'] = "Не удалось прикрепить файл $filename";
            }
        }
    }
    public function send($emails = '')
    {

        if ($emails === '') return false;

        if (!is_array($emails))
            $this->mail->addAddress($emails);
        else {
            for ($x = 0; $x < count($emails); $x++)
                $this->mail->addAddress($emails[$x]);
        }

        return $this->mail->send();
    }
}
