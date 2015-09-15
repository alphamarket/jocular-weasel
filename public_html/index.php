<?php
    session_name('XID');
    session_start();
    # if we access by shell
    # set HTTP_HOST to the script name
    @$_SERVER['HTTP_HOST'] || $_SERVER['HTTP_HOST'] = \array_shift($argv);
    # if there is any second argument passed by shell we consider it as REQUEST URI
    @$_SERVER['REQUEST_URI'] || $_SERVER['REQUEST_URI'] = count($argv) ? \array_shift($argv) : "/";

    defined('RUNNING_ENV') || define('RUNNING_ENV', 'DEVELOPMENT');

    defined('PUBLIC_HTML') || define('PUBLIC_HTML', dirname(__FILE__));

    defined("__SERVER_NAME__") || define("__SERVER_NAME__", $_SERVER['HTTP_HOST']);
    
    switch(RUNNING_ENV)
    {
        case "TEST":
        case "DEVELOPMENT":
            ini_set('display_errors','On');
            error_reporting(E_ALL);
            break;
        default:
            ini_set('display_errors','off');
            error_reporting(E_ERROR);
            break;
    }

    require_once PUBLIC_HTML.'/../zinux/zinux.php';
try
{
    # create an application with given module directory
    $app = new \zinux\kernel\application\application(PUBLIC_HTML.'/../modules');
    # process the application instance
    $app
            # setting cache directory
            ->SetCacheDirectory(PUBLIC_HTML.'/../cache')

            # setting router's bootstrap which will route /note/:id:/edit => /note/edit/:id:
            #->SetRouterBootstrap(new \application\appRoutes)

            # set application's db bootstrap 
            ->SetBootstrap(new application\dbBootstrap)
            
            # load project basic config initializer
            ->SetConfigIniliazer(new \zinux\kernel\utilities\iniParser(PROJECT_ROOT."/config/default.cfg", RUNNING_ENV))

            # init activerecord as db handler
            ->SetInitializer(new \vendors\activerecord\ARInitializer)

            # init the application's optz.
            ->Startup()
            # run the application
            ->Run()
            # shutdown the application
            ->Shutdown();
}
# catch any thing from application
catch(Exception $e)
{
    /**
     * You can redirect this exception to a controller e.g /error
     */
    echo "<legend>Oops!</legend>";
    echo "<p>Error happened ...</p>";
    echo "<p><b>Message: </b></p><p>{$e->getMessage()}</p>";
    echo "<p><b>Stack Trace: </b></p><pre>".$e->getTraceAsString()."</pre>";
}