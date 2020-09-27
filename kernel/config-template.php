<?php

error_reporting( E_ALL ^ E_STRICT ^ E_DEPRECATED );

define( 'DIRSEP', DIRECTORY_SEPARATOR, true );
$site_path = realpath( dirname( __FILE__ ).DIRSEP.'..'.DIRSEP ).DIRSEP;
define( 'ROOT_PATH', $site_path );

define( 'THIS_URL', 'http://bodybuilding-shop/', true );

$config = array(
    'db' => array(
        'host' => '',
        'u' => '',
        'p' => '',
        'db' => '',
    ),
    'robokassa' => array(
        'login' => 'bodybuilding-test',
        'pass' => 'IJsd89ds',
        'pass2' => 'Lsdlsdl3ed',
        'curr' => 'BANKOCEAN2R',
        'lang' => 'ru',
        'url' => 'http://test.robokassa.ru/Index.aspx',
    ),
    'yandex_money' => array(
        'account_number' => '',
        'secret' => '',
    ),
    'hide_counters' => true, //не выводить код счетчиков в шаблон,
    'optimise_frontend' => false, //включаем оптимизацию фронтенда (css, js, html),
    'SMTP_Transport'    => array(
        'enable'    =>  false,
        'username'  =>  '',
        'password'  =>  ''
    ),
);

