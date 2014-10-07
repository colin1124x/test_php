<?php

// PHP >= 5.4
class O {
    private $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
}

$closure = function(){
    return $this->name;
};

$closure2 = $closure->bindTo(new O('Colin'), 'O');

var_dump($closure2());