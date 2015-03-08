<?
	session_start();
	
	require('../kernel/config.php');
	require('../kernel/core.php');
	
	$registry = new Registry();
		$registry->set('config',$config);
		
	$db = new Database($registry);
	$logic = new Logic($registry); $logic->register_params();
		
	$registry->set ('logic', $logic);
	$registry->set ('db', $db);
	$registry->set ('f_404', true);

	$user = new User($registry);
	$registry->set ('user', $user);
	
	$template = new Template($registry);
		$registry->set('template', $template);
		
	$router = new Router($registry);
		$registry->set ('router', $router);
		$router->url_low_register(); $router->trailing_slash();
		$router->setPath(ROOT_PATH.'kernel/controllers');
		$router->delegate();
		
	$registry['template']->show();

?>
