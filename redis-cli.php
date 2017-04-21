#!/bin/php
<?php

/*
 * ./redis-cli.php -h <host> -p <port> -a <password> -n <dbnum> SET myKey myValue
 * ./redis-cli.php -h 127.0.0.1 -p 6379 -a myPassword -n 1 SET myKey myValue
 * ./redis-cli.php -h 127.0.0.1 -p 6379 SET myKey myValue
 * ./redis-cli.php SET myKey myValue
 */


class redisCli
{
    /*
     * Add local redis-cli path if it's not here
     */
    private $redisClis = array(
        '/usr/bin/redis-cli',
        '/usr/local/bin/redis-cli',
    );

    private $redisCli = null;

    private $serverHost = '127.0.0.1';
    
    private $serverPort = 6379;


    public function __construct()
    {
        foreach ($this->redisClis as $cli) {
            if (file_exists($cli)) {
                $this->redisCli = $cli;
            }
        }
        if ($this->redisCli === null) {
            throw new Exception('Can not find redis-cli');
        }
    }

    public function setServerHost($host)
    {
        $this->serverHost = $host;
    }

    public function setServerPort($port)
    {
        $this->serverPort = (int) $port;
    }

    public function request($command)
    {
        $fullCommand = $this->redisCli . 
                ' -h ' . $this->serverHost . 
                ' -p ' . $this->serverPort . 
                ' ' . $command;
        exec($fullCommand, $output, $return_var);
        if ($return_var != 0) {
            throw new Exception('Error');
        }
        if (preg_match(
                '/^MOVED [0-9]* ([0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}):([0-9]{4})$/',
                $output[0],
                $matches
        )) {
            $this->setServerHost($matches[1]);
            $this->setServerPort($matches[2]);
            return $this->request($command);
        } else {
            return $output[0];
        }
    }
}


 






$redisCli = new redisCli();
$command = '';
if (count($argv > 1)) {
    for ($i = 1 ; $i < count($argv) ; $i++) {
        switch ($argv[$i]) {
            case '-h':
                $redisCli->setServerHost($argv[++$i]);
                break;
            case '-p':
                $redisCli->setServerPort($argv[++$i]);
                break;
            default:
                $command .= $argv[$i] . ' ';
        }
    }
    $command = substr($command, 0, -1);
}
$res = $redisCli->request($command);
echo $res . PHP_EOL;
