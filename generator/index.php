<?php

// php >= 5.5
class Arr
{
    private $generator;

    public function __call($method, $args)
    {
        if (isset($this->generator)) {
            $args =  array_merge(array($this->generator), $args);
        }

        $this->generator = call_user_func_array($method, $args);

        return $this;
    }

    public function get()
    {
        return $this->generator;
    }
}

function make($min, $max, $step = 1) {
    for ($num = $min ; $num < $max; $num += $step) {
        yield $num;
    }
}

function take($arr, $n) {
    $start = 0;
    $n = (int) $n;
    foreach ($arr as $item) {
        yield $item;
        if (++$start >= $n) break;
    }
}

$arr = new Arr();


foreach ($arr->make(100, 200, 2)->take(5)->get() as $item) {
    var_dump($item);
}