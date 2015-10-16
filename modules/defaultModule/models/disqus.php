<?php
namespace modules\defaultModule\models;
/**
* The modules\defaultModule\models\disqus
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class disqus extends \ActiveRecord\Model
{
    public function user() {
        return user::find($this->created_by, array('select' => 'username', 'readonly' => true));
    }
}