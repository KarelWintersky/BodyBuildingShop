<?
	session_start();

	error_reporting (E_ALL ^ E_STRICT ^ E_DEPRECATED);
	
	require('kernel/config.php');
	require(ROOT_PATH.'kernel/libs/ezc/Base/base.php');
	require(ROOT_PATH.'kernel/libs/tcpdf/tcpdf.php');

	function __autoload($className){

		if(substr($className, 0, 3) == 'ezc'){
			ezcBase::autoload($className);
		}elseif(substr($className, 0, 9) == 'PHPExcel_'){
			PHPExcel_Autoloader::Load($className);
		}else{
		   $fileName = strtolower($className).'.php';
		   $f = ROOT_PATH.'kernel/classes'.DIRSEP.$fileName;
		   if(!file_exists($f)){return false;}
		   include ($f);
	   }
	}

	$registry = new Registry;
	$db = new Database();
	$logic = new Logic($registry);
		$logic->register_params();
	$registry->set ('logic', $logic);
	$registry->set ('db', $db);
	$registry->set ('f_404', true);

	$user = new User($registry);
	$registry->set ('user', $user);
	
	$template = new Template($registry);
		$registry->set('template', $template);
		
	$router = new Router($registry);
		$registry->set ('router', $router);
		$router->trailing_slash();
		$router->setPath(ROOT_PATH.'kernel/controllers');
		$router->delegate();
		
	$registry['template']->show();

?>
