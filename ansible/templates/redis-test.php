<?php

// using php redis to test corvus

// need to test both Redis and Redis Array

const CORVUS_HOST = 'corvus1';
const CORVUS_PORT = '12345';
const KEYS_PER_TEST = 25;
const INCREMENT_TIMES = 100;

$redisFactories = [
  'phpredis' => function($host, $port) {
    $redis = new Redis();
    $redis->connect($host, $port);

    return $redis;
  },
  'phpredisarray' => function ($host, $port) {
    $node = $host . ':' . $port;
    $redis = new RedisArray([$node]);

    return $redis;
  }
];

foreach($redisFactories as $keyPrefix => $factory) {
  if (!is_callable($factory)) {
    continue;
  }

  $redis = $factory(CORVUS_HOST, CORVUS_PORT);

  for ($i = 1; $i <= KEYS_PER_TEST; $i++) {
    $key = sprintf('%s_key_%d', $keyPrefix, $i);
    echo sprintf("incrementing %s %d times\n", $key, INCREMENT_TIMES);
    $redis->delete($key); //reset value

    for ($j = 1; $j <= INCREMENT_TIMES; $j++) {
      $redis->incr($key);
    }

    echo sprintf("%s has a value of %d\n", $key, $redis->get($key));
  }

  unset($redis);
}
