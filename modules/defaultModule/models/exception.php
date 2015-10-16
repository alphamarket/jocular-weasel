<?php
namespace modules\defaultModule\models;
    
/**
* The modules\defaultModule\models\exception
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class exception extends \ActiveRecord\Model
{
    public static function register(\Exception $e) {
        $ee = new self;
        $ee->exception =serialize($e);
        $ee->save();
        $ee->reload();
        $ee->exception_hash = sha1(md5($ee->exceptionid.__CLASS__).".".sha1($ee->exceptionid.__METHOD__));
        $ee->save();
        return $ee->exception_hash;
    }
}