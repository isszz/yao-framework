<?php
declare(strict_types=1);

namespace Yao\Database;

class Connector
{

    const FETCHTYPE = \PDO::FETCH_ASSOC;

    private $PDOstatement;

    private \PDO $pdo;

    /**
     * 获取pdo实例
     * @return \PDO
     */
    public function get()
    {
        return $this->pdo;
    }

    public function __construct($dsn, $config)
    {
        $this->_connect($dsn, $config);
    }

    /**
     * 数据库连接方法
     * @throws /PDOException
     */
    private function _connect($dsn, $config)
    {
        $this->pdo = new \PDO($dsn, $config['user'], $config['pass'], $config['options']);
    }

    /**
     * 预处理
     * @param $sql
     * @param array $data
     * @return object
     */
    public function prepare(string $sql, array $data = []): \PDOStatement
    {
        $this->PDOstatement = $this->pdo->prepare($sql);
        $this->PDOstatement->execute($data);
        return $this->PDOstatement;
    }

    public function fetchAll($sql, $params, $fetchType = self::FETCHTYPE)
    {
        return $this->prepare($sql, $params)->fetchAll($fetchType);
    }

    public function fetch($sql, $params, $fetchType = self::FETCHTYPE)
    {
        return $this->prepare($sql, $params)->fetch($fetchType);
    }
}