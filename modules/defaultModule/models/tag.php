<?php
namespace modules\defaultModule\models;
    
/**
* The modules\defaultModule\models\exception
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class tag extends \ActiveRecord\Model
{
    static $has_many = array(
            "disqus"
    );
    public static function fetch($name) {
        return self::first(array('conditions' => array('tag_name = ?', $name)));
    }
    public static function make_list() {
        return self::all(array('order' => 'tag_id asc'));
    }
}