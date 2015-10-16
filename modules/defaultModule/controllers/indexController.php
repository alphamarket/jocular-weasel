<?php
namespace modules\defaultModule\controllers;
    
/**
 * The modules\defaultModule\controllers\indexController
 * @by Zinux Generator <b.g.dariush@gmail.com>
 */
class indexController extends \zinux\kernel\controller\baseController
{
    /**
    * The modules\defaultModule\controllers\indexController::IndexAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function IndexAction()
    {
        $limit = 20;
        $offset = (!isset($this->request->params["page"])? 0 : $this->request->params["page"] - 1) * $limit;
        $ds = new \modules\defaultModule\models\disqus;
        $qb = new \ActiveRecord\SQLBuilder($ds->connection(), $ds->table_name());
        $qb
                ->select("disqusid, title, context, username, disquses.updated_at")
                ->where("parentid IS NULL")
                ->joins("INNER JOIN users ON users.userid= disquses.created_by")
                ->offset($offset)
                ->limit($limit)
                ->order("updated_at desc");
        $this->view->current_page = floor($offset / $limit) + 1;
        $this->view->total_pages = ceil($ds->count(array('conditions' => 'parentid IS NULL')) / $limit);
        $this->view->query = $ds->find_by_sql($qb->to_s(), $qb->bind_values());
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
}
