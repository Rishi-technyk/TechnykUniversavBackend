<?php

namespace App\Database;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class ODBCDriver extends Connector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $driver   = $config['odbc_driver'] ?? '{ODBC Driver 17 for SQL Server}';
        $host     = $config['host'];
        $database = $config['database'];

        $dsn = "odbc:Driver={$driver};Server={$host};Database={$database};";

        // DEBUG
        // dump($dsn);

        return $this->createConnection($dsn, $config);
    }
}
