<?php 

require(ROOT_PATH.'kernel/libs/ezc/Base/base.php');
require(ROOT_PATH.'kernel/libs/tcpdf/tcpdf.php');

function __autoload($className){

	if(substr($className, 0, 3) == 'ezc') ezcBase::autoload($className);
	elseif(substr($className, 0, 9) == 'PHPExcel_') PHPExcel_Autoloader::Load($className);
	else{
		
		$arr = explode('_',$className);
		
		//старые классы будут лежать в директории old, а новые будем помалу переносить в new
		if($className=='Controller_Base' || $className=='Front_Catalog_Barcodes' || count($arr)==1 || (count($arr)==2 && $arr[0]=='f')){
			
			$fileName = strtolower($className);
			$dir = 'old';
		}else{
			$fileName = implode('/',$arr);
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