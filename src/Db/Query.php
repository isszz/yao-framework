<?php

namespace Yao\Db;

use \PDOException;
use Yao\{
    Facade\Config,
    Traits\SingleInstance
};

class Query
{
    use SingleInstance;

    private ?array $config = [];

    private string $type = '';

    private function __construct()
    {
        Config::load('database');
        $this->type = Config::get('database.type');
        $this->config = Config::get('database.' . $this->type);
        $this->_connect();
    }

    /**
     * 数据库连接方法
     * @throws /PDOException
     */
    private function _connect()
    {
        $dsn = $this->type . ':host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['dbname'] . ';charset=' . $this->config['charset'];
        $this->pdo = new \PDO($dsn, $this->config['user'], $this->config['pass'], $this->config['options']);
    }

    /**
     * 预处理
     * @param $sql
     * @param array $data
     * @return object
     */
    public function prepare(string $sql, array $data = []): \PDOStatement
    {
        $PDOstatement = $this->pdo->prepare($sql);
        empty($data) ? $PDOstatement->execute() : $PDOstatement->execute($data);
        return $PDOstatement;
    }
}