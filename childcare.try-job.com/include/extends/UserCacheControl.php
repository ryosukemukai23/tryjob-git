<?php
namespace Websquare\UserCache;

include_once "custom/extends/cacheConf.php";
include_once "include/base/interface/iUserCache.php";

class UserCacheControl{
    /**
     * UserCache クラスを返す
     * @return iUserCache
     */
     static  function getControl(){
        global $CONF_CACHE_FLAG;
        global $CONF_CACHE_ENGINE;
         global $CONF_CACHE_SERVER;
        if(!$CONF_CACHE_FLAG){ $CONF_CACHE_ENGINE= "Null";}
        if(is_null(self::$control)){

            include_once self::$UserCacheControlPath . strtolower( $CONF_CACHE_ENGINE ) . '.php';
            $class_name = "Websquare\\UserCache\\".$CONF_CACHE_ENGINE."UserCache";
            if(!class_exists($class_name)){ d('hoge'); }
            self::$control = new $class_name();
            self::$control->init($CONF_CACHE_SERVER);
        }
        return self::$control;
    }
    private static $control=null;
    private static $UserCacheControlPath="include/extends/UserCache/";

    static function store($key,$value){
        global $CONF_CACHE_FLAG;
        if(!$CONF_CACHE_FLAG){ return null;}
        return self::getControl()->store($key,$value);

    }

    static function get($key){
        global $CONF_CACHE_FLAG;
        if(!$CONF_CACHE_FLAG){ return null;}
        return self::getControl()->get($key);
    }
}

