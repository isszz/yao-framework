<?php
declare(strict_types=1);

namespace Yao\Database;

use Yao\App;

/**
 * 数据库驱动基类
 * Class Driver
 * @package Yao\Db
 */
abstract class Driver
{
    /**
     * 表名
     * @var string
     */
    protected string $name;

    /**
     * 默认字段列表为*
     * @var string
     */
    protected string $field = '*';

    /**
     * PDO预处理绑定参数列表
     * @var array
     */
    protected array $bindParam = [];

    /**
     * 数据集对象
     * @var Collection
     */
    protected Collection $collection;

    /**
     * 存放条件排序的数组
     * @var array|string[]
     */
    protected array $construction = [
        'where' => '',
        'group' => '',
        'order' => '',
        'limit' => ''
    ];

    /**
     * 数据库类型
     * @var
     */
    public $type;

    /**
     * 数据库连接实例
     * @var mixed|object|Connector
     */
    public Connector $query;

    /**
     * 数据库配置
     * @var array|mixed|null
     */
    protected ?array $config = null;

    /**
     * 不能被覆盖的构造方法
     * Driver constructor.
     * @param App $app
     * @param $database
     * @throws \Exception
     */
    final public function __construct(App $app, $database)
    {
        $this->config = $app->config->get('database.' . $database);
        if (empty($this->config)) {
            throw new \Exception('没有找到数据库配置文件');
        }
        //实例化数据库连接类
        $this->query = $app->make(Connector::class, [$this->dsn(), $this->config]);
        $this->collection = new Collection();
    }

    /**
     * PDO连接DSN，待实现
     * @return string
     */
    abstract public function dsn(): string;

    /**
     * 表明设置方法，不包含前缀
     * @param string $table_name
     * @return $this
     */
    final public function name(string $table_name)
    {
        $this->name = $this->config['prefix'] . $table_name . ' ';
        return $this;
    }

    /**
     * 带前缀的表名
     * @param string $table_name
     * @return $this
     */
    final public function table(string $table_name)
    {
        $this->name = $table_name;
        return $this;
    }

    /**
     * @param string $sql
     * @param array|null $data
     * @param bool $all
     * @return mixed
     */
    final public function query(string $sql, ?array $data = [], bool $all = true)
    {
        return $all ? $this->query->fetchAll($sql, $data) : $this->query->fetch($sql, $data);
    }

    /**
     * 执行一条操作语句
     * @param string $sql
     * @param array $data
     * @return int
     */
    final public function exec(string $sql, array $data = []): int
    {
        return $this->query
            ->prepare($sql, $data)
            ->rowCount();
    }

    /** 多条查询
     * @return mixed
     */
    public function select()
    {
        $query = 'SELECT ' . $this->field . ' FROM ' . $this->name . $this->_condition();
        $this->collection($this->query->fetchAll($query, $this->bindParam), $query);
        return $this->collection;
    }

    /**
     * 获取某一个值的查询
     * @param $value
     * @return |null
     */
    public function value($value)
    {
        $query = 'SELECT ' . $value . ' FROM ' . $this->name . $this->_condition();
        $data = $this->query->fetch($query, $this->bindParam);
        return !empty($data) ? $data[$value] : null;
    }

    public function count($field)
    {
        $query = 'SELECT COUNT(' . $field . ')AS `count` FROM ' . $this->name . $this->_condition();
        $data = $this->query->fetch($query, $this->bindParam);
        return !empty($data) ? $data['count'] : null;
    }

    /**
     * 查询单条
     * @return mixed
     */
    public function find()
    {
        $query = 'SELECT ' . $this->field . ' FROM ' . $this->name . $this->_condition();
        $this->collection($this->query->fetch($query, $this->bindParam), $query);
        return $this->collection;
    }

    /**
     * 设置数据集数据
     * @param $data
     * @param $query
     */
    public function collection($data, $query)
    {
        $this->collection->data = $data;
        $this->collection->query = $query;
    }

    /**
     * 更新
     * @param array $data
     * @return int
     */
    public function update(array $data)
    {
        $set = '';
        foreach ($data as $field => $value) {
            $set .= $field . ' = ? , ';
            $params[] = $value;
        }
        $set = substr($set, 0, -3);
        //将绑定参数从头部加入到静态属性中
        array_unshift($this->bindParam, ...$params);
        $sql = 'UPDATE ' . $this->name . ' SET ' . $set . $this->_condition();
        return $this->query
            ->prepare($sql, $this->bindParam)
            ->rowCount();
    }

    /**
     * 插入
     * @param array $data
     * @return string
     */
    public function insert(array $data)
    {
        $fields = '(' . implode(',', array_keys($data)) . ')';
        $params = '(' . rtrim(str_repeat('?,', count($data)), ',') . ')';
        foreach ($data as $value) {
            $this->bindParam[] = $value;
        }
        $sql = 'INSERT INTO ' . $this->name . $fields . ' ' . 'VALUES ' . $params;
        $this->query->prepare($sql, $this->bindParam);
        return $this->query->get()->lastinsertid();
    }

    /**
     * 删除数据
     * @return mixed
     */
    public function delete()
    {
        $sql = 'DELETE FROM ' . $this->name . $this->construction['where'];
        unset($this->construction['where']);
        return $this->query
            ->prepare($sql, $this->bindParam)
            ->rowCount();
    }

    /**
     * 设置查询字段
     * @param string|array $field
     * @return $this
     */
    public function field($field)
    {
        if (is_array($field)) {
            $field = implode(',', $field);
        }
        $this->field = $field;
        return $this;
    }

    protected function _checkWhereEmpty(\Closure $closure)
    {
        if (!isset($this->construction['where']) || empty($this->construction['where'])) {
            $this->construction['where'] = ' WHERE ';
        } else {
            $this->construction['where'] .= ' AND ';
        }
        $closure();
        return $this;
    }

    /**
     * where条件表达式，使用数组参数的时候会自动使用预处理
     * @param string|array $where
     * where条件表达式
     * @return $this
     */
    public function where($where)
    {
        return $this->_checkWhereEmpty(function () use ($where) {
            if (!empty($where)) {
                if (is_string($where)) {
                    $this->construction['where'] = $this->construction['where'] . $where;
                } else if (is_array($where)) {
                    foreach ($where as $key => $value) {
                        $this->bindParam[] = $value;
                        $this->construction['where'] .= $key . '=? and ';
                    }
                    $this->construction['where'] = substr($this->construction['where'], 0, -5);
                }
            }
        });
    }


    /**
     * 模糊查询
     * @param array $like
     * @return $this
     */
    public function whereLike(array $like)
    {
        return $this->_checkWhereEmpty(function () use ($like) {
            foreach ($like as $key => $value) {
                $this->construction['where'] .= $key . ' LIKE ? AND ';
                $this->bindParam[] = $value;
            }
            $this->construction['where'] = substr($this->construction['where'], 0, -5);
        });
    }

    /**
     * 判断字段是null
     * @param array $field
     * @return $this
     */
    public function whereNull(array $field)
    {
        return $this->_checkWhereEmpty(function () use ($field) {
            foreach ($field as $key) {
                $this->construction['where'] .= $key . ' IS NULL AND ';
            }
            $this->construction['where'] = substr($this->construction['where'], 0, -5);
        });
    }

    /**
     * 判断字段不是null
     * @param array $field
     * @return $this
     */
    public function whereNotNull(array $field)
    {
        return $this->_checkWhereEmpty(function () use ($field) {
            foreach ($field as $key) {
                $this->construction['where'] .= $key . ' IS NOT NULL AND ';
            }
            $this->construction['where'] = substr($this->construction['where'], 0, -5);
        });
    }

    /**
     * 存在于数组
     * @param array $whereIn
     * @return $this
     */
    public function whereIn(array $whereIn = [])
    {
        return $this->_checkWhereEmpty(function () use ($whereIn) {
            $condition = '';
            foreach ($whereIn as $column => $range) {
                $bindStr = rtrim(str_repeat('?,', count($range)), ',');
                $condition .= $column . ' in (' . $bindStr . ') AND ';
                array_push($this->bindParam, ...$range);
            }
            $this->construction['where'] .= substr($condition, 0, -5);
        });
    }

    /**
     * limit待实现
     * @param int $limit
     * @param int|null $offset
     * @return $this
     */
    abstract public function limit(int $limit, ?int $offset = null);

    /**
     * order排序操作，支持多字段排序
     * @param array $order
     * 传入数组形式的排序字段，例如['id' => 'desc','name' => 'asc']
     * @return $this
     */
    public function order(array $order = [])
    {
        if (!empty($order)) {
            $this->construction['order'] = ' ORDER BY ';
            foreach ($order as $ord => $by) {
                $this->construction['order'] .= $ord . ' ' . strtoupper($by) . ',';
            }
            $this->construction['order'] = rtrim($this->construction['order'], ',');
        }
        return $this;
    }

    /**
     * group by ... having 可以传入最多两个参数
     * @param mixed ...$group
     * 第一个参数为group字段，第二个为having
     * @return $this
     */
    public function group(...$group)
    {
        if (count($group) > 2) {
            throw new \Exception('group传入参数数量不正确');
        }
        if (count($group) == 2) {
            $this->construction['group'] = ' group by ' . $group[0] . ' having ' . $group[1];
        } else {
            $this->construction['group'] = ' group by ' . $group[0];
        }
        return $this;
    }

    protected function _setLimit(string $limit)
    {
        $this->construction['limit'] = $limit;
    }

    /**
     * 根据$this->construction数组生成查询语句
     * @return string
     */
    protected function _condition(): string
    {
        return implode(' ', array_filter($this->construction));
    }



    // public function transaction(array $transaction)
    // {
    //     $this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
    //     try {
    //         $this->pdo->beginTransaction(); //开启事务
    //         foreach (func_get_args() as $key => $sql) {
    //             $this->pdo->exec($sql);
    //         }
    //         $this->pdo->commit();
    //     } catch (\PDOException $e) {
    //         $this->pdo->rollback();
    //         $this->message = $e->getMessage();
    //         return FALSE;
    //     }
    //     $this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
    //     return TRUE;
    // }


}