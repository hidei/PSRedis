<?php

namespace PSRedis;

use PSRedis\Client\Adapter\Predis\PredisClientCreator;
use PSRedis\Client\Adapter\PredisClientAdapter;
use PSRedis\Client\ClientAdapter;

/**
 * Class Client
 *
 * Represents one single sentinel or redis node and provides identification if we want to connect to it
 *
 * @package PSRedis
 */
class Client
{
    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var array
     */
    private $parameters;

    private $clientAdapter;

    const TYPE_SENTINEL = 'sentinel';
    const TYPE_REDIS    = 'redis';

    const ROLE_SENTINEL = 'sentinel';
    const ROLE_MASTER   = 'master';
    const ROLE_SLAVE    = 'slave';

    public function __construct(
    	$ipAddress,
    	$port,
    	ClientAdapter $uninitializedClientAdapter = null,
    	$connectionType = self::TYPE_SENTINEL,
    	array $parameters = array())
    {
        $this->ipAddress  = $ipAddress;
        $this->port       = $port;
        $this->parameters = $parameters;

        if (empty($uninitializedClientAdapter)) {
            $uninitializedClientAdapter = new PredisClientAdapter(new PredisClientCreator(), $connectionType);
        }
        $this->clientAdapter = $this->initializeClientAdapter($uninitializedClientAdapter);
    }

    private function initializeClientAdapter(ClientAdapter $clientAdapter)
    {
        $clientAdapter->setIpAddress($this->getIpAddress());
        $clientAdapter->setPort($this->getPort());
        $clientAdapter->setParameters($this->getParameters());

        return $clientAdapter;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
    	return $this->parameters;
    }

    public function connect()
    {
        $this->clientAdapter->connect();
    }

    public function isConnected()
    {

        return (bool) $this->clientAdapter->isConnected();
    }

    public function getClientAdapter()
    {
        return $this->clientAdapter;
    }

    /**
     * @return \Redis\Client
     */
    public function getMaster($nameOfNodeSet)
    {
        return $this->clientAdapter->getMaster($nameOfNodeSet);
    }

    public function getRole()
    {
        return $this->clientAdapter->getRole();
    }

    public function getRoleType()
    {
        $role = $this->getRole();
        return $role[0];
    }

    public function isMaster()
    {
        return $this->getRoleType() === Client::ROLE_MASTER;
    }

    public function isSentinel()
    {
        return $this->getRoleType() === Client::ROLE_SENTINEL;
    }

    public function isSlave()
    {
        return $this->getRoleType() === Client::ROLE_SLAVE;
    }

    public function __call($methodName, array $methodParameters = array())
    {
        return call_user_func_array(array($this->clientAdapter, $methodName), $methodParameters);
    }
}