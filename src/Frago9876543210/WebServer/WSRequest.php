<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

/**
 * Class WSRequest
 * @package Frago9876543210\WebServer
 * @link https://github.com/ClanCatsStation/PHPWebserver/blob/master/src/Request.php
 */
class WSRequest implements StatusCodes
{
    /** @var string The request method */
    protected $method;
    /** @var string The requested uri */
    protected $uri;
    /** @var string The requested version */
    protected $version;
    /** @var array The request params */
    protected $parameters = [];
    /** @var array The request headers */
    protected $headers = [];

    /**
     * Create new request instance using a string header
     * @param string $header
     * @return self
     */
    public static function fromHeaderString(string $header): self
    {
        $lines = explode("\n", $header);
        // method, uri, version
        $requestInfo = explode(' ', array_shift($lines));
        $method = $requestInfo[0] ?? null;
        $uri = $requestInfo[1] ?? null;
        $version = $requestInfo[2] ?? null;
        // headers
        $headers = [];
        foreach ($lines as $line) {
            // clean the line
            $line = trim($line);
            if (strpos($line, ': ') !== false) {
                [$key, $value] = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        // create new request object
        return new static($method, $uri, $headers, $version);
    }

    public function __construct(string $method = "GET", string $uri = "/", array $headers = [], string $version = "HTTP/1.1")
    {
        $this->headers = $headers;
        $this->method = strtoupper($method);
        $this->version = strtoupper($version);
        // split uri and parameters string
        $explode = explode('?', $uri);
        $this->uri = $explode[0];
        // parse the parmeters
        if (!empty($explode[1])) {
            parse_str($explode[1], $this->parameters);
        }
    }

    /**
     * Return the request method
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Return the request uri
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Return a request header
     * @param string $key
     * @param null|mixed $default
     * @return null|string
     */
    public function getHeader(string $key, $default = null): ?string
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Return a request parameter
     * @param string $key
     * @param null|mixed $default
     * @return null|string
     */
    public function getParam(string $key, $default = null): ?string
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Return the protocol version
     * @return string
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
}