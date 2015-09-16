<?php
namespace modules\defaultModule\controllers;
    
/**
 * The modules\defaultModule\controllers\errorController
 * @by Zinux Generator <b.g.dariush@gmail.com>
 */
class errorController extends \zinux\kernel\controller\baseController
{
    public static $__EXTERN_ERROR = NULL;
    public function Initiate()
    {
        parent::Initiate();
        $this->layout->SetLayout("error");
        if(!self::$__EXTERN_ERROR) {
            $mp = new \zinux\kernel\utilities\pipe("__ERRORS__");
            if(!$mp->hasFlow() && strtolower($this->request->action->name) === "index") {
                header("location: /");
                exit;
            }
            # empty the pipe and only keep the last error up
            while($mp->hasFlow())
                $this->view->error =$mp->read();
        } else {
            $this->view->error = self::$__EXTERN_ERROR;
            self::$__EXTERN_ERROR = NULL;
        }
        $this->layout->AddTitle("iDisqus");
    }
    /**
    * The modules\defaultModule\controllers\errorController::IndexAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function IndexAction()
    {
        $last = $this->view->error;
        /** 
         * status codes reference : RFC 2616
         * http://tools.ietf.org/html/rfc2616
         */
        switch(true) {
            case $last instanceof \InvalidArgumentException:
            case $last instanceof \zinux\kernel\exceptions\invalidCookieException:
            case $last instanceof \zinux\kernel\exceptions\invalidArgumentException:
            case $last instanceof \zinux\kernel\exceptions\invalidOperationException:
                $msg = "Bad Request";
                $code = 400;
                break;
            case $last instanceof \zinux\kernel\exceptions\securityException:
            case $last instanceof \zinux\kernel\exceptions\accessDeniedException:
            case $last instanceof \zinux\kernel\exceptions\permissionDeniedException:
                $msg = "Forbidden";
                $code = 403;
                break;
            case $last instanceof \zinux\kernel\exceptions\notFoundException:
            case $last instanceof \core\db\exceptions\dbNotFoundException:
                $msg = "Not Found ";
                $code = 404;
                break;
            default: 
                $msg = "Intername Server Error";
                $code  = 500;
                break;
            case $last instanceof \zinux\kernel\exceptions\notImplementedException: 
                $msg = "Not Implemented";
                $code = 501; 
                break;
        }
        # set proper view file
        $this->view->setView("e$code");
        # if headers has not sent yet
        if(!headers_sent())
            # send the error header
            header("HTTP/1.1 $code $msg");
    }
}
