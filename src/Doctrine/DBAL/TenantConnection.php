<?php

namespace Alanfzf\MultitenatBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;

class TenantConnection extends Connection
{
    public function switchConnection(array $params): void
    {
        $this->close();
        $this->_conn = $this->driver->connect($params);
    }
}
