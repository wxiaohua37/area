<?php

namespace XiaoHua\Area;


/**
 * 区域操作门面类
 * @author: 小花
 * @email: once_emails@163.com
 * @date: 2025/7/5
 * @createtime: 12:40
 * @method static array|null get(int $id) 获取区域信息
 * @method static array|null parse(string $path, int $rootId = AreaUtils::ID_CHINA) 解析路径
 * @method static string path(array $areas) 获取完整路径
 * @method static string|null format(int $id, string $separator = ' ') 格式化地址
 * @method static array byType(int $type, callable $func) 按类型获取区域
 * @method static int|null parentOfType(int $id, int $type) 获取指定类型的上级区域ID
 * @see AreaUtils
 */
class AreaFacade
{
    private static ?AreaUtils $instance = null;

    public static function __callStatic(string $method, array $args)
    {
        self::$instance ??= AreaUtils::instance();

        if (!method_exists(self::$instance, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist");
        }

        return self::$instance->$method(...$args);
    }

    public static function init(array $config = []): void
    {
        self::$instance = AreaUtils::instance($config);
    }
}