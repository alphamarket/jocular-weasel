<?php
namespace modules\defaultModule\models;

/**
 * Description of Mailer
 *
 * @author dariush
 */
class mailer //extends \PHPMailer
{
    public function __construct($user_name, $password, $exceptions=true)
    {
        \zinux\kernel\security\security::IsSecure(
                array('u'=>$user_name, 'p' =>$password), 
                array(),
                array('u' => array('is_string', 'strlen'), 'p' => 'is_string'));
        parent::__construct($exceptions);
        $this->isSMTP();
        $this->Host = \zinux\kernel\application\config::GetConfig("idisqus.mail.host");
        $this->SMTPAuth = true;
        $this->Port     = \zinux\kernel\application\config::GetConfig("idisqus.mail.port");
        $this->SMTPSecure = \zinux\kernel\application\config::GetConfig("idisqus.mail.protocol");
        $this->Username = $user_name;
        $this->Password  = $password;
        # add the sender address
        $this->setFrom("$user_name@".\zinux\kernel\application\config::GetConfig("idisqus.domain"), 'iDisqus');
    }
}
