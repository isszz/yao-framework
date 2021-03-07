<?php
declare(strict_types=1);

namespace Yao\Http;

use Yao\Tools\Str;

class Session
{

    /**
     * 初始化session
     * Session constructor.
     */
    public function __construct()
    {
        isset($_SESSION) || session_start();
    }


    public function init()
    {
    }

    /**
     * session获取方法
     * @param $name
     * @return array|mixed|string|null
     */
    public function get($name)
    {
        return Str::parse($_SESSION, $name);
    }

    /**
     * session设置方法
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * 判断session是否存在
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return !is_null(Str::parse($_SESSION, $key));
    }

    /**
     * session销毁
     */
    public function destroy()
    {
        $_SESSION = [];
        setcookie(session_name(), '', -1);
        session_destroy();
    }

    /**
     * 检查session闪存
     */
    public function flashCheck()
    {
        if (true === $this->get('yao_session_flash_flag')) {
            $this->set('yao_session_flash_flag', false);
        } else if (false === $this->get('Yao_session_flash_flag')) {
            $this->set('Yao_session_flash_flag', null);
            $this->set($this->get('Yao_session_flash_name'), null);
        }
    }

    /**
     * session闪存设置
     * @param $name
     * @param null $value
     * @return array|mixed|string|null
     */
    public function flash($name, $value = null)
    {
        if (isset($value)) {
            $this->set('Yao_session_flash_flag', true);
            $this->set('Yao_session_flash_name', $name);
            $this->set($name, $value);
        } else {
            return $this->get($name);
        }
    }
}
