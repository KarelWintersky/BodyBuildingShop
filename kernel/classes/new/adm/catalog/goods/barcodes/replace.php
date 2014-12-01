<?php
Class Adm_Catalog_Goods_Barcodes_Replace{

	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}
	
	private function in_goods($output){
		foreach($output as $old_barcode => $new_barcode){
			mysql_query(sprintf("
					UPDATE
						goods
					SET
						barcode = '%s'
					WHERE
						barcode = '%s'
					",
					mysql_real_escape_string($new_barcode),
					mysql_real_escape_string($old_barcode)					
					));
			
			mysql_query(sprintf("
					UPDATE
						goods
					SET
						parent_barcode = '%s'
					WHERE
						parent_barcode = '%s'
					",
					mysql_real_escape_string($new_barcode),
					mysql_real_escape_string($old_barcode)
			));			
		}
	}
	
	private function in_orders_goods($output){
		foreach($output as $old_barcode => $new_barcode){
			mysql_query(sprintf("
					UPDATE
						orders_goods
					SET
						goods_barcode = '%s'
					WHERE
						goods_barcode = '%s'
					",
					mysql_real_escape_string($new_barcode),
					mysql_real_escape_string($old_barcode)
					));
			
		}
	}
	
	public function do_replace($barcodes){
		$output = array();
		foreach($barcodes as $b)
			if($b['barcode']!=$b['barcode_old'])
				$output[$b['barcode_old']] = $b['barcode'];
				
		$this->in_orders_goods($output);
		$this->in_goods($output);
	}
	
}
?>