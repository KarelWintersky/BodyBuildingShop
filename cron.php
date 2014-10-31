<?
	error_reporting (E_ALL ^ E_STRICT ^ E_DEPRECATED);
	require('kernel/config.php');

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

	if(isset($argv[1])){
		switch($argv[1]){
			case 'do_orders':
				$blocks = new Blocks($registry,false);
				$blocks->cron_do_orders();
				break;
			case 'go_goods_present':
				$blocks = new Blocks($registry,false);
				$blocks->cron_go_goods_present();
				break;
			case 'do_goods_absence':
				$blocks = new Blocks($registry,false);
				$blocks->cron_do_goods_absence();
				break;
			case 'do_goods_prices':
				$blocks = new Blocks($registry,false);
				$blocks->cron_do_goods_prices();
				break;
			case 'do_news':
				$seettings = new Settings($registry,false);
				$seettings->cron_do_news();
				break;
			case 'do_rezerv_orders':
				$seettings = new Settings($registry,false);
				$seettings->do_rezerv_orders();
		}
	}

?>