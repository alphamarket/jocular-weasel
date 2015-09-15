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
    {
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::viewAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function viewAction()
    {
        
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::deleteAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function deleteAction()
    {
        
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::editAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function editAction()
    {
        
    }

    /**
    * The \modules\defaultModule\controllers\disqusController::newAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function newAction()
    {
        if(!$this->request->IsPOST()) return;
        \zinux\kernel\utilities\debug::_var($this->request->params, 1);
    }
}

