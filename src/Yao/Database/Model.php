<?php
declare(strict_types=1);

namespace Yao\Database;

use Yao\Facade\Db;

/**
 * @method Driver where(array $where);
 * @method Driver whereIn(array $whereIn);
 * @method Driver whereLike(array $whereLike);
 * @method Driver whereNull(array $whereNull);
 * @method Driver whereNotNull(array $whereNotNull);
 * @method Driver limit(int $limit, ?int $offset);
 * @method insert(array $data);
 * @method Driver field(string|array $fields)
 * Class Model
 * @package Yao
 */
class Model
{

    /**
     * 表名
     * @var $name string|null
     */
    protected ?string $name = null;

    /**
     * 默认主键
     * @var $key string
     */
    protected string $key = 'id';

    /**
     * 初始化表名
     * Model constructor.
     */
    final public function __construct()
    {
        $this->name = $this->name ?? strtolower(ltrim(strrchr(get_called_class(), '\\'), '\\'));
    }

    /**
     * 模型初始化方法
     * 不要再使用__construct
     */
    public function init()
    {

    }

    /**
     * @param $function_name
     * @param $arguments
     * @return \Yao\Database\Driver
     */
    final public function __call($functionName, $arguments)
    {
        return Db::name($this->name)->$functionName(...$arguments);
    }
}
