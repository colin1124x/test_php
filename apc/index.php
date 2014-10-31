<?php

class Test {
    private $a = 321;
    private $drivers = array();

    public function appendDriver($driver)
    {
        $this->drivers[] = $driver;
    }

    public function __sleep()
    {
        echo __METHOD__;

        return array('a', 'drivers');
    }
    public function __wakeup()
    {
        ++$this->a;
        echo __METHOD__;
    }
}

$o = new Test();

$o->appendDriver(function(){});

var_dump(apcu_store('colin', $o));

var_dump(
    apcu_fetch('colin'),
    apc_fetch('colin'),
    apcu_exists('colin'));
