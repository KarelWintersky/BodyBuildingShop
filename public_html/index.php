<?php

session_start();

require('../kernel/config.php');
require('../kernel/core.php');

$registry = new Registry();
$registry->set( 'config', $config );
$registry->set( 'ROOT_PATH', ROOT_PATH ); // with tailing slash

if (array_key_exists('SMTP_Transport', $config) && $config['SMTP_Transport']['enable']) {
    $registry->set('SMTP_Transport', array(
        'ssl'      =>   'tls',
        'port'     =>   587,
        'auth'     =>   'login',
        'username' =>   $config['SMTP_Transport']['username'],
        'password' =>   $config['SMTP_Transport']['password']
    ));
}

$db = new Database( $registry );
$logic = new Logic( $registry );
$logic->register_params();

$registry->set( 'logic', $logic );
$registry->set( 'db', $db );
$registry->set( 'f_404', true );

$user = new User( $registry );
$registry->set( 'user', $user );

$template = new Template( $registry );
$registry->set( 'template', $template );

$router = new Router( $registry );
$registry->set( 'router', $router );

$router->url_low_register();
$router->trailing_slash();
$router->setPath( ROOT_PATH.'kernel/controllers' );
$router->delegate();

$registry[ 'template' ]->show();

