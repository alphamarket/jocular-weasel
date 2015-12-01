<?php
namespace modules\defaultModule\controllers;
    
/**
 * The modules\defaultModule\controllers\indexController
 * @by Zinux Generator <b.g.dariush@gmail.com>
 */
class indexController extends \zinux\kernel\controller\baseController
{
    public function Initiate()
    {
        $this->layout->AddTitle(ucwords(preg_replace("#action$#i", "", $this->request->action->name)). " | iDisqus");
    }
    /**
    * The modules\defaultModule\controllers\indexController::IndexAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function IndexAction()
    {
        $limit = 20;
        $offset = (!isset($this->request->params["page"])? 0 : $this->request->params["page"] - 1) * $limit;
        $ds = new \modules\defaultModule\models\disqus;
        $this->view->query = $ds->get_latest_toptics($offset, $limit, @$this->request->params["tag"]);
        $this->view->current_page = floor($offset / $limit) + 1;
        $this->view->total_pages = ceil($ds->count(array('conditions' => 'parentid IS NULL')) / $limit);
    }
    /**
    * The \modules\defaultModule\controllers\indexController::signinAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function signinAction()
    {
        if(!$this->request->IsPOST()) return;
        try{
            \zinux\kernel\security\security::__validate_request($this->request->params);
            \zinux\kernel\security\security::IsSecure($this->request->params, array("username", "password"));
            $user = \modules\defaultModule\models\user::Fetch($this->request->params["username"], $this->request->params["password"]);
        } catch(\Exception $e) { $user = NULL; }
        if(!$user) { $this->view->errors[] = "Invalid username or password!"; return; }
        if(!$user->activated) { $this->view->errors[] = "This account has not been activated!"; return; }
        $user->signin(isset($this->request->params["remember-me"]));
        header("location: /");
        exit;
    }

    /**
    * The \modules\defaultModule\controllers\indexController::signoutAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function signoutAction()
    {
        \modules\defaultModule\models\user::signout();
        header("location: /");
        exit;
    }
    /**
    * The \modules\defaultModule\controllers\indexController::signupAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function signupAction()
    {
        if(\zinux\kernel\application\config::GetConfig("idisqus.disable.signup"))
            throw new \zinux\kernel\exceptions\accessDeniedException;
        if(!$this->request->IsPOST()) return;
        try{
            \zinux\kernel\security\security::__validate_request($this->request->params);
            \zinux\kernel\security\security::IsSecure($this->request->params, array("username", "password", "email"));
            $user = new \modules\defaultModule\models\user;
            foreach(array("username", "password", "email") as $elem)
                $user->{$elem} = $this->request->params[$elem];
            $user->password = md5($user->password);
            $user->save();
            $user->reload();
            $user->readonly();
            $mail = new \modules\defaultModule\models\mailer("noreply", \zinux\kernel\application\config::GetConfig("idisqus.mail.noreply.password"));
            $mail->CharSet = 'UTF-8';
            # add a subject
            $mail->Subject = "Activate Your iDisqus Account";
            # add the reciever address
            $mail->addAddress($user->email); 
            # start reading the html context of reset mail
            ob_start();
                $this->view->RenderPartial("activate_email",
                        array(
                                'user' => $user,
                                'alink' => "/activate/u/{$user->userid}/h/".  \md5(\sha1($user->userid).__CLASS__).\sha1($user->email.__FILE__).\md5($user->password."dar!ush")));
            # set the html msg and clean the ob's buffer
            $mail->msgHTML(ob_get_clean());
            # msgHTML also sets AltBody, but if you want a custom one, set it afterwards
            $mail->AltBody = "Activate Your iDisqus Account";
            # try to send the email
            if (!$mail->send())
                throw new \RuntimeException("Counld'n send email to `{$this->request->params["email"]}` due to error : `{$mail->ErrorInfo}`");
        } catch(\Exception $e) { $user = NULL; }
        if(!$user) { $this->view->errors[] = "Invalid username or password!"; return; }
        $this->view->success[] = "Congratulations, You have successfully signed up to iDisqus.";
        $this->view->success[] = "An activation link sent to your email address please check your inbox.";
    }
    /**
    * The \modules\defaultModule\controllers\indexController::activateAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function activateAction()
    {
        if(\zinux\kernel\application\config::GetConfig("idisqus.disable.signup"))
            throw new \zinux\kernel\exceptions\accessDeniedException;
        \zinux\kernel\security\security::IsSecure($this->request->params, array("u", "h"));
        try{
            $user =\modules\defaultModule\models\user::find($this->request->params["u"]);
        } catch(\ActiveRecord\RecordNotFound $re) { throw new \zinux\kernel\exceptions\notFoundException("The user# {$this->request->params["u"]} not found!"); }
        if(\md5(\sha1($user->userid).__CLASS__).\sha1($user->email.__FILE__).\md5($user->password."dar!ush") !== $this->request->params["h"])
                throw new \zinux\kernel\exceptions\invalidOperationException("The hash didn't match!");
        $user->activated = 1;
        $user->save();
    }
}
