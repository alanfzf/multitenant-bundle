<?php

namespace Alanfzf\MultitenatBundle\Dto;

final class ConnectionParameters
{
    public function __construct(
        public readonly string $dbname,
        public readonly string $user,
        public readonly string $password,
        public readonly string $host,
        public readonly string $port,
    ) {
    }

    public function toArray(): array
    {
        $params = [
            'dbname' => $this->dbname,
            'user' => $this->user,
            'password' => $this->password,
            'host' => $this->host,
            'port' => $this->port,
        ];

        ksort($params);

        return $params;
    }
}
