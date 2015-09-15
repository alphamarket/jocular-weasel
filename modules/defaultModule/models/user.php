<?php
namespace modules\defaultModule\models;
    
/**
* The modules\defaultModule\models\user
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class user
{
    /**
     * User object alias in this class' cache registery
     */
    const USER_OBJECT = "USER_OBJECT";
    /**
     * Check if the users is signed in or not
     * @return user NULL if user has not signed in, otherwise the user's instace
     */
    public static function HasSignedin()
    {
        if(self::GetInstance())
            return TRUE;
        # cryption isntance
        $crpt = new \zinux\kernel\security\cryption();
        # secure cookie instance
        $sec_cookie = new \zinux\kernel\security\secureCookie; 
        # cryption key
        $crpt_key = \zinux\kernel\security\hash::Generate(self::USER_OBJECT);
        # cookie name
        $cookie_name = \zinux\kernel\security\hash::Generate(self::USER_OBJECT);
        # if cookie contains the user's ID
        if($sec_cookie->contains($cookie_name))
        {
            # decrypt the user's ID 
            $user_id = $crpt->decrypt($_COOKIE[$cookie_name], $crpt_key);
            # fetch the user
            $fetched_user = self::find("first", array("conditions"=>array("user_id = ?", $user_id)));
            # if not found?
            if(!$fetched_user)
            {
                # delete the user's ID from cookie, it's invalid either
                $sec_cookie->delete($cookie_name,  "/", __SERVER_NAME__);
                # not FOUND
                return  FALSE;
            }
            else
            {
                # if we find the user
                # sign in the user
                self::Signin($fetched_user);
            }
        }
        return self::GetInstance() != NULL;
    }
    /**
     * Fetches users info from its session
     * @return user NULL if user has not signed in, otherwise the user's instace
     */
    public static function GetInstance()
    {
        # open up a session cache related to this class
        $sc = new \zinux\kernel\caching\sessionCache(__CLASS__);
        # return the USER_OBJECT stored in the session
        return $sc->fetch(self::USER_OBJECT);
    }
    /**
     * Signout the user from its session
     */
    public static function signout()
    {        
        # destroy all session data
        \session_destroy();
        # destroy current session array
        unset($_SESSION);
    }
}