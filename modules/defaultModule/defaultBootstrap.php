<?php
namespace modules\defaultModule;
/**
* The defaultModule's Bootstrapper
*/
class defaultBootstrap
{
    /**
     * A pre-dispatch function
     * @param \zinux\kernel\routing\request $request
     */
    public function pre_auth_checkpoint(\zinux\kernel\routing\request $request)
    {
        # if user already signed in?
        if(models\user::HasSignedin())
            # no need for check
            return;
        # a list of exception array({controller => array(actions)}) which does not need signin sig.
        $signin_free_ops = array(
            "index"  => array("signin", "signup", "activate"),
            "error"   => "*"
        );
        # the normalized currently requested {conttroller => action} 
        $current_request = array(
            # the pure controller name without the suffix
            "controller" => strtolower($request->controller->name), 
            # the pure action name without the suffix 
            "action"       => strtolower($request->action->name)
        );
        # if current request's matches with an index in the signin free list
        if(isset($signin_free_ops[$current_request["controller"]]) && ($signin_free_ops[$current_request["controller"]] === "*" || in_array($current_request["action"], $signin_free_ops[$current_request["controller"]])))
            return;
        throw new \zinux\kernel\exceptions\accessDeniedException;
    }
}