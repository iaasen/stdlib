#!/usr/bin/env php
<?php

require 'SharedMemoryCache.php';
require 'Debug/Timer.php';

use Iaasen\SharedMemoryCache;
use Iaasen\Debug\Timer;

$processes = 1000;
$key = crc32('stress_test_key');
$cache = new SharedMemoryCache($key);

for ($i = 0; $i < $processes; $i++) {
    $pid = pcntl_fork();
    if ($pid === -1) {
        die("Could not fork process\n");
    } elseif ($pid === 0) {
        // Child process
        usleep(rand(1,10000));
        Timer::setStart();
        $cache->store("Process $i", 5);
        echo "Process $i write ".Timer::getElapsed() . PHP_EOL;
        $value = $cache->fetch();
        echo "Process $i read ".Timer::getElapsed() . PHP_EOL;
        exit(0);
    }
}

// Wait for all children
while (pcntl_wait($status) !== -1);
?>
