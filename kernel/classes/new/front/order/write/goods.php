<?php
Class Front_Order_Write_Goods{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_write(){
		$goods_ids = array();
		$goods_q_arr = array();
		
		foreach($order_vals['goods_data'] as $goods_rec){
			$goods_ids[$goods_rec['goods_id']] = (isset($goods_ids[$goods_rec['goods_id']])) ? ($goods_ids[$goods_rec['goods_id']]+$goods_rec['amount']) : $goods_rec['amount'];
		
			if($goods_rec['color']>0){
				$qLnk = mysql_query(sprintf("SELECT IFNULL(name,'') FROM features WHERE id = '%d'",$goods_rec['color']));
				$color = mysql_result($qLnk,0);
			}else $color = '';
		
			$goods_q_arr[] = "
			('".$order_id."',
			'".$goods_rec['barcode']."',
			'".$goods_rec['full_name']."',
			'".$goods_rec['packing']."',
			'".$goods_rec['amount']."',
			'".$goods_rec['price']."',
			'".$goods_rec['discount']."',
			'".$goods_rec['price']."',
			'".$color."')
			";
		}
		
		mysql_query("
				INSERT INTO
				orders_goods
				(order_id,
				goods_barcode,
				goods_full_name,
				goods_packing,
				amount,
				price,
				discount,
				final_price,
				goods_feats_str)
				VALUES
				".implode(', ',$goods_q_arr)."
				");		
	}
}
?>