<?php
Class Adm_Catalog_Goods_Barcodes_Check{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
	
	public function barcode_check(){
		$barcode = $_POST['barcode'];
		$barcode_old = $_POST['barcode_old'];
		
		$qLnk = mysql_query(sprintf("
				SELECT
					COUNT(*) 
				FROM
					goods_barcodes
				WHERE
					barcode = '%s'
				",
				mysql_real_escape_string($barcode)
				));
		$count = mysql_result($qLnk,0);
		
		//новый штрихкод
		if(!$barcode_old) $flag = ($count) ? false : true;
			
		//существующий штрихкод	
		else $flag = ($count>1) ? false : true;	
		
		echo ($flag) ? 1 : 0;
	}	

}
?>