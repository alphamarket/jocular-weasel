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
        $this->view->disqus = \modules\defaultModule\models\disqus::first(@$this->request->indexed_param[0]);
        \zinux\kernel\utilities\debug::_var($this->view->disqus, 1);
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::deleteAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function deleteAction()
    {
        \zinux\kernel\security\security::__validate_request($this->request->params);
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
        if(!$this->request->IsPOST()) return;
        \zinux\kernel\security\security::__validate_request($this->request->params);
        \zinux\kernel\security\security::IsSecure($this->request->params, array("title", "content"));
        $disqus = new \modules\defaultModule\models\disqus;
        $disqus->title = trim($this->request->params["title"]);
        $disqus->context = trim($this->request->params["content"]);
        $disqus->created_by =\modules\defaultModule\models\user::GetInstance()->userid;
        $disqus->save();
        header("location: /disqus/view/{$disqus->disqusid}");
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

