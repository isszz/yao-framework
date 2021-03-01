<?php

namespace Yao\Database\Drivers;

use Yao\Database\Driver;

/**
 * Class Mysql
 * @package Yao\Db\Drivers
 */
class Mysql extends Driver
{
    /**
     * Mysql条数限制
     */
    public function limit(int $limit, ?int $offset = null)
    {
        $this->_setLimit('LIMIT ' . (isset($offset) ? $offset . ',' . $limit : $limit));
        return $this;
    }

    /**
     * Mysql PDO-DSN
     * @return string
     */
    public function dsn(): string
    {
        return 'mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['dbname'] . ';charset=' . $this->config['charset'];
    }

}