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
    /**
     * This is out execption handler
     * @param Exception $exception
     */
    function toratan_exception_handler($exception) {
        $mp = new \zinux\kernel\utilities\pipe("__ERRORS__");
        try{
            $mp->write($exception);
        }catch(\Exception $e) {
            unset($e);
            modules\defaultModule\controllers\errorController::$__EXTERN_ERROR = $exception;
        }
        # we need to use api because httpRequest will contain error(/error will set error header)
        # cannot user `header()` we because of the URI change!! we want the error URI remain same.
        @\zinux\kernel\application\api::call("/error");
        exit;
    }
    # set exception handler
    set_exception_handler('toratan_exception_handler');
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

            # register PHPMailer plugin
            ->SetInitializer(new \vendors\PHPMailer\PHPMailerInitializer)
            
            # init the application's optz.
            ->Startup()
            # run the application
            ->Run()
            # shutdown the application
            ->Shutdown();