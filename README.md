This is a redis cluster playground that includes corvus (https://github.com/eleme/corvus) which is a server-side redis 3 cluster proxy.

You can use this to test redis cluster with 6 nodes (3 master and 3 slaves) with or without corvus.

I made this to test corvus as a replacement for twemproxy and all client side key hashing. Instead of using client side hashing like PHP's RedisArray, all codebases would use the regular redis protocol and all sharding/clustering is handled server side. This way we don't have to worry about redis hashing in any programming language and we can use simple redis libraries.

You also get the benefits of redis cluster which offers higher availability.

The demo sets up 6 redis instances, 1 corvus instance and 1 instance for testing.

You will need Ansible 2.x.

Two vagrant plugins are needed: `auto_network` and `vai`.

1. Install plugins
------------------

```
vagrant plugin install vagrant-auto_network
vagrant plugin install vagrant-vai
```

2. Create VMs

```
vagrant up
```

3. Provision redis cluster nodes
--------------------------------

```
cd ansible
ansible-playbook provision_redis_cluster_nodes.yml
```

4. Create redis cluster
-----------------------

Now we need to create the redis cluster

```
ansible-playbook create_redis_cluster_config.yml
vagrant ssh redis1
bash ./create_cluster
```

You will be asked to confirm the cluster config, answer "yes". Note you only need to run the create cluster command on 1 node, do not run it on every node.

5. Provision corvus
-------------------

```
ansible-playbook provision_corvus.yml
```

6. Test corvus/redis cluster
--------------

```
ansible -m setup corvus1 -a 'filter=ansible_eth1'
```

Locate the ipv4 address of corvus.

```
"ipv4": {
    "address": "10.55.0.8",
    "broadcast": "10.55.0.255",
    "netmask": "255.255.255.0",
    "network": "10.55.0.0"
},
```

Now you can use any redis test tool such as redis-benchmark:

```
redis-benchmark -h 10.55.0.8 -p 12345 -t set,get -n 100000 -c 20
```

Note corvus runs on port 12345 by default, it can be changed.

Note that not all redis commands are supported by corvus, their readme has more information.

There is also a very simple test script available for PHP that tests phpredis against corvus:

```
ansible-playbook provision_redis_test.yml
vagrant ssh redistest
php redis-test.php
```

example output:

```
vagrant@redistest:~$ php redis-test.php
incrementing phpredis_key_1
phpredis_key_1: 100
incrementing phpredis_key_2
phpredis_key_2: 100
incrementing phpredis_key_3
phpredis_key_3: 100
incrementing phpredis_key_4
phpredis_key_4: 100
incrementing phpredis_key_5
phpredis_key_5: 100
```

From redistest, you can check the contents of your redis masters (redis1, redis2, redis3) or slaves (redis4, redis5, redis6)

```
vagrant@redistest:~$ redis-cli -h redis1
redis1:6379> keys *
 1) "phpredis_key_7"
 2) "phpredisarray_key_9"
 3) "phpredis_key_20"
 4) "phpredisarray_key_5"
 5) "phpredis_key_3"
 6) "phpredis_key_19"
 7) "phpredis_key_15"
 8) "phpredisarray_key_23"
 9) "phpredisarray_key_13"
10) "phpredis_key_25"
11) "phpredisarray_key_12"
12) "phpredis_key_14"
13) "phpredis_key_24"
14) "phpredis_key_10"
15) "phpredis_key_21"
16) "phpredis_key_18"
17) "phpredisarray_key_1"
18) "phpredisarray_key_16"
19) "phpredis_key_11"
20) "phpredisarray_key_22"
redis1:6379>
```

If you need to need to reset your cluster, have a look at http://redis.io/commands/cluster-reset.
If you need to add nodes, remove nodes etc, start here: http://redis.io/topics/cluster-tutorial

Each redis node has the `redis-trib.rb` executeable.
