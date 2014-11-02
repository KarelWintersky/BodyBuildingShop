<?
	define('DIRSEP',DIRECTORY_SEPARATOR,true);
	$site_path = realpath(dirname(__FILE__).DIRSEP.'..'.DIRSEP).DIRSEP;
	define ('ROOT_PATH',$site_path);

	date_default_timezone_set('Europe/Moscow');
	
	define('DB_HOST','localhost',true);
	define('DB_U','whbody2',true);
	define('DB_P','54321155',true);
	define('DB_NAME','whbody2',true);

	define('THIS_URL','http://www.bodybuilding-shop.ru/',true);
?>