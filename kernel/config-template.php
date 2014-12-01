<?
	error_reporting (E_ALL ^ E_STRICT ^ E_DEPRECATED);

	define('DIRSEP',DIRECTORY_SEPARATOR,true);
	$site_path = realpath(dirname(__FILE__).DIRSEP.'..'.DIRSEP).DIRSEP;
	define ('ROOT_PATH',$site_path);

	date_default_timezone_set('Europe/Moscow');
	
	define('DB_HOST','localhost',true);
	define('DB_U','whbody2',true);
	define('DB_P','54321155',true);
	define('DB_NAME','whbody2',true);

	define('THIS_URL','http://www.bodybuilding-shop.ru/',true);
	
	define('CLOSE_FRONTEND',true,true); //закрыть фронтенд от незалогиненных в админку
	
	define('HIDE_COUNTERS',false,true); //не выводить код счетчиков в шаблон
?>