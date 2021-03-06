<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use ClassLoader;
use Exception;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\Thread;
use pocketmine\utils\Utils;
use raklib\utils\InternetAddress;

class WebServer extends Thread
{
    /** @var resource $socket */
    protected $socket;
    /** @var callable $handler */
    protected $handler;
    /** @var InternetAddress $bindAddress */
    protected $bindAddress;
    /** @var bool $isRunning */
    protected $isRunning = true;

    /**
     * WebServer constructor.
     * @param InternetAddress $bindAddress
     * @param callable $handler
     * @throws Exception
     */
    public function __construct(InternetAddress $bindAddress, callable $handler)
    {
        $this->bindAddress = $bindAddress;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (@!socket_bind($this->socket, $bindAddress->getIp(), $bindAddress->getPort())) {
            throw new PluginException("Failed to bind to $bindAddress");
        }
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_listen($this->socket);
        Utils::validateCallableSignature(static function (WSConnection $connection, WSRequest $request): void {
        }, $handler);
        $this->handler = $handler;
        /** @noinspection NullPointerExceptionInspection */
        /** @var ClassLoader $cl */
        $cl = Server::getInstance()->getPluginManager()->getPlugin("DEVirion")->getVirionClassLoader();
        #$classLoader = new ServerFileAutoLoader($cl);
        #$this->setClassLoader($classLoader);
        $this->setClassLoader($cl);
    }

    public function run(): void
    {
        $this->registerClassLoader();

        while ($this->isRunning) {
            if (is_resource(($client = socket_accept($this->socket)))) {
                $connection = new WSConnection($client);
                try {
                    call_user_func($this->handler, $connection, WSRequest::fromHeaderString($connection->read()));
                } catch (SocketException $e) {
                    print $e->getMessage();
                    #print $e->getTraceAsString();
                } finally {
                    $connection->close();
                    unset($connection);
                }
            }
        }
    }

    /**
     * @return InternetAddress
     */
    public function getBindAddress(): InternetAddress
    {
        return $this->bindAddress;
    }

    /**
     * Disables socket processing
     */
    public function shutdown(): void
    {
        $this->isRunning = false;
    }

    /**
     * @return callable
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }
}