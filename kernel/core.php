<?php 

require(ROOT_PATH.'kernel/libs/ezc/Base/base.php');
require(ROOT_PATH.'kernel/libs/tcpdf/tcpdf.php');

define('PHOTO_DIM_STR','80x80,122x122',true);
define('LEV_PHOTO_DIM_STR','160x160',true);
define('GOODS_PHOTO_DIR',ROOT_PATH.'public_html/data/foto/goods'.DIRSEP,true);
define('LEV_PHOTO_DIR',ROOT_PATH.'public_html/data/foto/levels'.DIRSEP,true);
define('FEAT_PHOTO_DIR',ROOT_PATH.'public_html/data/foto/features'.DIRSEP,true);
define('GROWER_PHOTO_DIR',ROOT_PATH.'public_html/data/foto/growers'.DIRSEP,true);
define('ARTICLE_PHOTO_DIR',ROOT_PATH.'public_html/data/foto/articles'.DIRSEP,true);

define('NEWS_PAGINATE',10,true);
define('POPULAR_MAX',15,true);

//robokassa
define('ROBOKASSA_LG','bodybuilding-shop',true);
define('ROBOKASSA_PW','Isdfisdoj23423',true);
define('ROBOKASSA_PW_2','sdfsd2323423ss',true);
define('ROBOKASSA_CURR','BANKOCEAN2R',true); //валюта
define('ROBOKASSA_LANG','ru',true); //язык


function __autoload($className){

	if(substr($className, 0, 3) == 'ezc') ezcBase::autoload($className);
	elseif(substr($className, 0, 9) == 'PHPExcel_') PHPExcel_Autoloader::Load($className);
	else{
		
		$arr = explode('_',$className);
				
		//старые классы будут лежать в директории old, а новые будем помалу переносить в new
		if($className=='Settings_Indexes' || $className=='Controller_Base' || $className=='Front_Catalog_Barcodes' || count($arr)==1 || (count($arr)==2 && $arr[0]=='f')){
			
			$fileName = strtolower($className);
			$dir = 'old';
		}else{
			$fileName = strtolower(implode('/',$arr));
			$dir = 'new';
		}
		
		$file = sprintf('%skernel/classes/%s/%s.php',
				ROOT_PATH,
				$dir,
				$fileName
				);
						
		if(!file_exists($file)) return false;
					
		require_once($file);
	}
}

?>