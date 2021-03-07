<?php
declare(strict_types=1);

namespace {

    use Yao\Database\Driver;
    use Yao\Facade\Db;

    if (false === function_exists('db')) {
        /**
         * db类助手函数
         * @param string $tableName
         * @return Driver
         */
        function db(string $tableName)
        {
            return Db::name($tableName);
        }
    }

}

namespace Yao\Database {

    use Yao\Provider\Service;

    class DatabaseService implements Service
    {
        public function boot()
        {

        }

        public function register()
        {
            // TODO: Implement register() method.
        }

    }
}

