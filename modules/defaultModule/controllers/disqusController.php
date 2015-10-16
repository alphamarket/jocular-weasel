<?php
namespace modules\defaultModule\controllers;
    
/**
 * The modules\defaultModule\controllers\disqusController
 * @by Zinux Generator <b.g.dariush@gmail.com>
 */
class disqusController extends \zinux\kernel\controller\baseController
{
    /**
    * The modules\defaultModule\controllers\disqusController::IndexAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function IndexAction()
    { throw new \zinux\kernel\exceptions\accessDeniedException; }

    /**
    * The \modules\defaultModule\controllers\disqusController::viewAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function viewAction()
    {
        $disqus_id = @$this->request->indexed_param[0];
        $limit = 10;
        $offset = (!isset($this->request->params["page"])? 0 : $this->request->params["page"] - 1) * $limit;
        $ds = new \modules\defaultModule\models\disqus;
        $qb = new \ActiveRecord\SQLBuilder($ds->connection(), $ds->table_name());
        $qb
                ->select("*")
                ->where("parentid = ? OR disqusid = ?", $disqus_id, $disqus_id)
                ->offset($offset)
                ->limit($limit)
                ->order("created_at desc");
        $this->view->current_page = floor($offset / $limit) + 1;
        $this->view->total_pages = ceil($ds->count(array('conditions' => 'parentid IS NULL')) / $limit);
        $this->view->query = $ds->find_by_sql($qb->to_s(), $qb->bind_values());
        $this->view->disqus = \modules\defaultModule\models\disqus::first($disqus_id);
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::deleteAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function deleteAction()
    {
        \zinux\kernel\security\security::__validate_request($this->request->params, array(@$this->request->indexed_param[0]));
        $disqus = \modules\defaultModule\models\disqus::first(@$this->request->indexed_param[0]);
        if(!$disqus->parentid) {
            $next_loc = "/";
            $next = \modules\defaultModule\models\disqus::first(array('order' => 'created_at asc', 'conditions' => array('parentid = ?', $disqus->disqusid)));
            if($next) {
                $next->title = $disqus->title;
                $next->parentid = NULL;
                $next->save();
                $next->readonly();
                $next_loc = "/disqus/view/$next->disqusid";
                \modules\defaultModule\models\disqus::update_all(array('set' => array('parentid' => $next->disqusid), 'conditions' => array('parentid = ?', $disqus->disqusid)));
            }
        } else $next_loc = "/disqus/view/$disqus->parentid";
        $disqus->delete();
        header("location: $next_loc");
        exit;
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::editAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function editAction()
    {
        \zinux\kernel\security\security::__validate_request($this->request->params);
        $this->view->disqus = \modules\defaultModule\models\disqus::first(@$this->request->indexed_param[0]);
        if(!$this->request->IsPOST()) return;
        \zinux\kernel\security\security::IsSecure($this->request->params, array("title", "content"));
        $this->view->disqus->title = trim($this->request->params["title"]);
        $this->view->disqus->context = trim($this->request->params["content"]);
        $this->view->disqus->save();
        header("location: /disqus/view/{$this->view->disqus->disqusid}");
        exit;
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::newAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function newAction()
    {
        if(isset($this->request->params["ajax"])) $this->layout->SuppressLayout();
        if(!$this->request->IsPOST()) return;
        \zinux\kernel\security\security::__validate_request($this->request->params);
        $is_reply = isset($this->request->params["pid"]);
        $essential_data = array('content');
        if($is_reply)
            $essential_data[] = "pid";
        else 
            $essential_data[] = "title";
        \zinux\kernel\security\security::IsSecure($this->request->params, $essential_data);
        $disqus = new \modules\defaultModule\models\disqus;
        if(!$is_reply) $disqus->title = trim($this->request->params["title"]);
        $disqus->context = trim($this->request->params["content"]);
        $disqus->created_by =\modules\defaultModule\models\user::GetInstance()->userid;
        if($is_reply)
            $disqus->parentid = $this->request->params["pid"];
        $disqus->save();
        
        $return_uri = "/disqus/view/". ($is_reply ? $disqus->parentid : $disqus->disqusid);
        
        $users = \modules\defaultModule\models\user::all(array('select' => 'email,username', 'readonly' => true, 'conditions' => array('userid <> ?', \modules\defaultModule\models\user::GetInstance()->userid)));
        
        foreach($users as $user) {
            # factor an instance of php mailer
            $mail = new \modules\defaultModule\models\mailer("noreply", \zinux\kernel\application\config::GetConfig("idisqus.mail.noreply.password"));
            $mail->CharSet = 'UTF-8';
            # add a subject
            $mail->Subject = $disqus->title;
            if($is_reply)
                $mail->Subject = "Re: ".  \modules\defaultModule\models\disqus::find($disqus->parentid, array('select' => 'title'))->title;
            # add the reciever address
            $mail->addAddress($user->email); 
            # start reading the html context of reset mail
            ob_start();
                $this->view->RenderPartial("notify_disqus",
                        array(
                                'user' => $user, 
                                'poster' => \modules\defaultModule\models\user::GetInstance(),
                                'is_reply' => $is_reply,
                                'title' => preg_replace("#^Re: #i", "", $mail->Subject),
                                'disqus' => $disqus,
                                'return_uri' => $return_uri));
            # set the html msg and clean the ob's buffer
            $mail->msgHTML(ob_get_clean());
            # msgHTML also sets AltBody, but if you want a custom one, set it afterwards
            $mail->AltBody = 'New '.($is_reply ? 'reply' : 'post').' from '.  \modules\defaultModule\models\user::GetInstance()->username;
            # try to send the email
            if (!$mail->send())
                die("ERROR EMAILING");
                ; # LOG THE FAILURE
        }
        header("location: $return_uri");
        exit;
    }
}

