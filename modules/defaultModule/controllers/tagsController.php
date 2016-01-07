<?php
namespace modules\defaultModule\controllers;
    
/**
 * The modules\defaultModule\controllers\indexController
 * @by Zinux Generator <b.g.dariush@gmail.com>
 */
class tagsController extends \zinux\kernel\controller\baseController
{
    public function Initiate()
    {
        parent::Initiate();
        $this->layout->AddTitle("Manage Tags | iDisqus");
    }
    
    public function IndexAction()
    {
        $this->view->tags =\modules\defaultModule\models\tag::make_list();
        $this->view->setView("index");
    }
    public function updateAction() {
        \zinux\kernel\security\security::__validate_request($this->request->params);
        \zinux\kernel\security\security::IsSecure($this->request->params, array('tag_name'), array('tag_name' => array('\is_string', '\strlen')));
        switch(true) {
            case isset($this->request->params['op:update']):
                if(isset($this->request->params["tag_id"])) {
                    # updating
                    $tag = \modules\defaultModule\models\tag::find($this->request->params['tag_id']);
                    $old_tag_name = $tag->tag_name;
                    $tag->tag_name = $this->request->params["tag_name"];
                    $tag->save();
                    $this->view->msg[] = "Tag `<b>{$old_tag_name}</b>` renamed to `<b>{$this->request->params["tag_name"]}</b>` successfully!";
                } else {
                    $tag = new \modules\defaultModule\models\tag;
                    $tag->tag_name = $this->request->params["tag_name"];
                    $tag->save();
                    $this->view->msg[] = "Tag `<b>{$this->request->params["tag_name"]}</b>` added successfully!";
                }
                $this->IndexAction();
                return;
            case isset($this->request->params['op:delete']):
                \modules\defaultModule\models\tag::find($this->request->params['tag_id'])->delete();
                $this->view->msg[] = "Tag `<b>{$this->request->params["tag_name"]}</b>` deleted successfully!";
                $this->IndexAction();
                return;
        }
        throw new \zinux\kernel\exceptions\invalidOperationException;
    }
}
