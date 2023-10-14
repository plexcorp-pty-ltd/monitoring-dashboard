<?php 
namespace Plexcorp\Monitoring;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Wrapper around PHP Mailer. templates can be found in templates/emails
 */
class Mailer {

    private $smtp;

    public function __construct()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();

        $mail->SMTPAuth   = true;
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_TLS'];
        $mail->Port       = (int) $_ENV['SMTP_PORT'];
        $mail->isHTML(true);

        $this->smtp = $mail;
    }

    /**
     * Turn on email debugging output.
     *
     * @return void
     */
    public function enableDebug()
    {
        $this->smtp->SMTPDebug = 4;
    }

    /**
     * Turn off email debugging output.
     *
     * @return void
     */
    public function disableDebug()
    {
        $this->smtp->SMTPDebug = 0;
    }

    /**
     * Takes in a template name as per filename excl .php extension in templates/email/
     * Sends HTML email with phpmailer.
     * 
     * @param string $template
     * @param string $subject
     * @param array $vars
     * 
     * @return void
     */
    public function sendEmail($template, $subject, $vars)
    {
        $vars['subject'] = $subject;
        ob_start();
        require_once('./templates/emails/' . $template.".php");
        $tpl = ob_get_clean();
    
        $this->smtp->setFrom($_ENV['SMTP_DEFAULT_FROM'], "Monitoring Dashboard");

        $tos = explode(",", $_ENV['SMTP_REPORT_EMAILS']);
        foreach($tos as $to) {
            $this->smtp->addAddress($to);
        } 

        $this->smtp->Subject = $subject;
        $this->smtp->Body = $tpl;
        $this->smtp->send();
    }
}