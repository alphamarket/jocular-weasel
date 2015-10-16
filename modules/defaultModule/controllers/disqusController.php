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
        \modules\defaultModule\models\disqus::first(@$this->request->indexed_param[0])->delete();
        header("location: /");
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
        header("location: /disqus/view/". ($is_reply ? $disqus->parentid : $disqus->disqusid));
        exit;
    }
    
    public function draftAction()
    {
        \zinux\kernel\security\security::__validate_request($this->request->params);
        \zinux\kernel\security\security::IsSecure($this->request->params, array("title", "content"));
        if(!@$this->request->params["did"])
            $draft = new \modules\defaultModule\models\draft;
        else
            $draft =\modules\defaultModule\models\draft::first($this->request->params["did"]);
        $draft->title = trim($this->request->params["title"]);
        $draft->context = trim($this->request->params["content"]);
        $draft->save();
        if(!@$this->request->params["did"]) 
            echo "{ 'draft_id': {$draft->draftid} }";
        die;
    }
}

