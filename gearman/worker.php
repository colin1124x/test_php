<?php

$worker = new GearmanWorker();
$worker->addServer(); // 預設為 localhost
$worker->addFunction('say_hello', function($job){
    $date = unserialize($job->workload());
    print_r($date);
    sleep(3);
    echo "get msg \n\n";
});
while($worker->work()) {
    sleep(1); // 無限迴圈，並讓 CPU 休息一下
}

