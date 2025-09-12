<?php
namespace Websquare\UserCache;

    interface iUserCache
    {
        public function init($conf);
        public function add($key, $value,$ttl=null);
        public function delete($key);
        public function fetch($key);
        public function get($key);
        public function store($key, $value,$ttl=null);
        public function cas($key, $value,$old,$ttl=null);
    }
