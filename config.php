<?php

ini_set ( 'memory_limit', '2048M' );
error_reporting ( E_ERROR | E_WARNING | E_PARSE );
define ( 'ROOT', str_replace ( "\\", '/', dirname ( __FILE__ ) ) );

define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'ROOT_BASE', ROOT . DS );
define ( 'GLOBAL_BASE', ROOT . DS . '..' . DS );
// 物理目录（LOCAL）
define ( 'ROOT_INCLUDE', ROOT_BASE . 'include' . DS );
define ( 'ROOT_FUNC', ROOT_INCLUDE . 'function' . DS );
define ( 'ROOT_VIEW', ROOT_INCLUDE . 'view' . DS );
define ( 'ROOT_CLASS', ROOT_INCLUDE . 'class' . DS );
define ( 'ROOT_CTRL', ROOT_INCLUDE . 'ctrl' . DS );
/*
 * 全局（global）
*/
define ( 'GLOBAL_INCLUDE', GLOBAL_BASE . 'include' . DS );
define ( 'GLOBAL_FUNC', GLOBAL_INCLUDE . 'function' . DS );
define ( 'GLOBAL_CLASS', GLOBAL_INCLUDE . 'class' . DS );
define ( 'GLOBAL_MODEL', GLOBAL_INCLUDE . 'model' . DS );
define ( 'GLOBAL_LANG', GLOBAL_INCLUDE . 'languages' . DS ); //
// assets
define ( 'GLOBAL_ASSET', GLOBAL_BASE . 'assets' . DS );
// userfile
define ( 'GLOBAL_UF', GLOBAL_BASE . BASE_UF );
// cache
define ( 'GLOBAL_CACHE', GLOBAL_BASE . 'cache/' );
define ( 'GLOBAL_PAGE_CACHE', GLOBAL_CACHE . 'page_cache/' );
//
define ( 'GLOBAL_LOG', GLOBAL_BASE . 'log' . DS );
define ( 'GLOBAL_THEMES', GLOBAL_BASE . '../theme/' );
define ( 'GLOBAL_TMP', GLOBAL_BASE . 'tmp/' );
// 数据库配置信息
define ( 'DB_HOST', '127.0.0.1' ); // 数据库主机
define ( 'DB_USER', 'root' ); // 数据库用户名
define ( 'DB_PW', 'root' ); // 数据库密码
define ( 'DB_NAME', 'shars.com' ); // 数据库名称
define ( 'DB_TABLEPRE', '' ); // 数据表前缀
define ( 'DB_DATABASE', 'mysqxl' ); // 数据库类型
define ( 'DB_PCONNECT', 0 ); // 0或1,是否使用持久连接
define ( 'DB_ISCACHE', 0 ); // 是否启用 sql cache (只对前台起作用，建议在不生成html并且访问量过大时开启)
define ( 'DB_EXPIRES', 900 ); // sql cache 过期时间(秒)
define ( 'DB_CACHEDIR', GLOBAL_CACHE . 'dbcache/' );
define ( 'DB_CHARSET', 'utf8' ); // 数据库连接字符集

define ( 'HOME_DIC', 'E:/shars/' ); // #各种csv文件存放的路径，目标文件也会写入该目录，赋予该目录写权限。
define ( 'IMG_PATH', 'E:/shars/test_files/products' ); // #图片文件的路径。

define('LIMIT_VALUE', 5);
define('NEW_LINE', "\r\n");