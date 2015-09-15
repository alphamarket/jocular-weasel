<?php
namespace application;
/**
* The project's bootstrapper
*/
class dbBootstrap extends \zinux\kernel\application\applicationBootstrap
{
    const MODE_TORATAN = RUNNING_ENV;
    /**
     * setups db using activerecord
     */
    public static function PRE_init_db()
    {
        # init activerecord configs
        \ActiveRecord\Config::initialize(function($cfg)
        {
            # fetching db related configurations
            $dbcfg = \zinux\kernel\application\config::GetConfig("idisqus.db");
            # setting connection string
            $cfg->set_connections(array(
                    \application\dbBootstrap::MODE_TORATAN =>
                            "{$dbcfg["type"]}://{$dbcfg["username"]}:{$dbcfg["password"]}@{$dbcfg["host"]}/{$dbcfg["name"]}?charset=utf8")
            );
           # enable the connection string as to \application\dbBootstrap::MODE_TORATAN
            $cfg->set_default_connection(\application\dbBootstrap::MODE_TORATAN);
        });
        # set default datetime format
        \ActiveRecord\DateTime::$DEFAULT_FORMAT = "iso8601";
        # testing db connection
        \ActiveRecord\Connection::instance();
        # if we reach here we are all OK
    }
}