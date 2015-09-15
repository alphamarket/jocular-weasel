<?php
namespace vendors\activerecord;

class ARInitializer extends \zinux\kernel\application\baseInitializer
{
    public function Execute()
    {
        # invoking AR's autoloader
        require_once 'lib/ActiveRecord.php';
    }
}
