<?php
$redis = new Redis();
$redis->connect("172.19.0.4", 6379);
echo "Connected: " . $redis->ping();