<?php
namespace modules\defaultModule\models;
    
/**
* The modules\defaultModule\models\user
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class user extends \ActiveRecord\Model
{
    /**
     * User object alias in this class' cache registery
     */
    const USER_OBJECT = "USER_OBJECT";
    protected static function getUserObjectHash() { return substr(\zinux\kernel\security\hash::Generate(self::USER_OBJECT), 0, 6); } 
    /**
     * Fetches a user's info by either its username or its email and its password 
     * @param string $username_or_userID The users ID or username 
     * @param string $password the user's email or password if password no set it only will search by username or email
     * @return user
     */
    public static function Fetch($username_or_userID, $password = NULL) {
        # find the user with its username or email and password
        $cond = array("(username = ? OR userid = ?)",
                    $username_or_userID,
                    $username_or_userID);
        if($password)
        {
            $cond[0].= " AND password = MD5(?)";
            $cond[] = $password;
        }
        return parent::find("first", array('conditions' => $cond));
    }
    /**
     * Check if the users is signed in or not
     * @return user NULL if user has not signed in, otherwise the user's instace
     */
    public static function HasSignedin() {
        if(self::GetInstance())
            return TRUE;
        # cryption isntance
        $crpt = new \zinux\kernel\security\cryption();
        # secure cookie instance
        $sec_cookie = new \zinux\kernel\security\secureCookie; 
        # cryption key
        $crpt_key = \zinux\kernel\security\hash::Generate(self::USER_OBJECT);
        # cookie name
        $cookie_name =self::getUserObjectHash();
        # if cookie contains the user's ID
        if($sec_cookie->contains($cookie_name))
        {
            # decrypt the user's ID 
            $user_id = $crpt->decrypt($_COOKIE[$cookie_name], $crpt_key);
            # fetch the user
            $fetched_user = self::find("first", array("conditions"=>array("userid = ?", $user_id)));
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
                $fetched_user->signin();
            }
        }
        return self::GetInstance() != NULL;
    }
    /**
     * Fetches users info from its session
     * @return user NULL if user has not signed in, otherwise the user's instace
     */
    public static function GetInstance() {
        # open up a session cache related to this class
        $sc = new \zinux\kernel\caching\sessionCache(__CLASS__);
        # return the USER_OBJECT stored in the session
        return $sc->fetch(self::USER_OBJECT);
    }
    public function signin($remmeber = 0) {
        # validate if the current user initialized and exists!?
        if(!self::exists($this->userid))
            throw new \zinux\kernel\exceptions\invalidOperationException("The operation not permitted!");
        # open up a session cache related to this class
        $sc = new \zinux\kernel\caching\sessionCache(__CLASS__);
        # return the USER_OBJECT stored in the session
        $sc->save(self::USER_OBJECT, $this);
        # if we don't want to set cookie, just return
        if(!$remmeber) return;
        # cryption isntance
        $crpt = new \zinux\kernel\security\cryption();
        # cryption key
        $crpt_key = \zinux\kernel\security\hash::Generate(self::USER_OBJECT);
        # cookie name
        $cookie_name = self::getUserObjectHash();
        # secure cookie instance
        $sec_cookie = new \zinux\kernel\security\secureCookie; 
        # encrypt the user ID & set the cookie
        $sec_cookie->set($cookie_name, $crpt->encrypt($this->userid, $crpt_key), 31536000, "/", __SERVER_NAME__, 0, 0);
    }
    /**
     * Signout the user from its session
     */
    public static function signout() {        
        # destroy all session data
        \session_destroy();
        # secure cookie instance
        $sec_cookie = new \zinux\kernel\security\secureCookie; 
        # delete the user's ID from cookie, it's invalid either
        $sec_cookie->delete(self::getUserObjectHash(),  "/", __SERVER_NAME__);
        # destroy current session array
        unset($_SESSION);
    }
}