<?php
namespace modules\defaultModule\models;
/**
* The modules\defaultModule\models\disqus
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class disqus extends \ActiveRecord\Model
{
    static $belongs_to = array(
        "user"
    );
}