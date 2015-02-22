<?php
Class Front_Order_Data_Cart_Goods{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function get_goods($cart){
		$barcodes = array();
		foreach($cart as $key => $val){
			$key = explode(':',$key);
			
			$barcodes[] = sprintf("'%s'",$key[0]);
		}
		
		$goods = array();
		
		$qLnk = Front_Order_Data_Cart_Query::do_query($barcodes);
		while($g = mysql_fetch_assoc($qLnk)){	
			$g = $this->price_discount($g);
			
			$goods[$g['barcode']] = $g;
		}
		
		return $goods;
	}
	
	private function price_discount($g){
		$g['price'] = round($g['price']); //на всякий случай округляем, чтобы точно не было копеек
		
		$g['old_price'] = $g['price'];
		
		if($g['personal_discount']) $g['price'] = $g['price'] - $g['price']*$g['personal_discount']/100;
		$g['price'] = round($g['price']);
		
		return $g;
	}
	
	private function make_array($goods,$cart){
		/*
		 * для того, чтобы можно было добавить несколько строк товаров с одним штрихкодом
		 * одежда разных цветов, например
		 * */
		
		$output = array();
		foreach($cart as $key => $val){
			$arr = explode(':',$key);
				
			$g = $goods[$arr[0]];
				
			$g['amount'] = $val['amount'];
			$g['color'] = $val['color'];
				
			$output[$key] = $g;
		}
		
		return $output;		
	}
	
	private function get_colors($cart,$goods){
		/*
		 * выбор цветов в массив. для раздела "Одежда"
		 * */
		$keys = array();
		foreach($cart as $barcode => $line){
			if(!$line['color'] || !isset($goods[$barcode])) continue;
			
			$qLnk = mysql_query(sprintf("
					SELECT
						IFNULL(name,'')
					FROM
						features
					WHERE
						id = '%d'
					",$line['color']));
			
			$goods[$barcode]['color_name'] = mysql_result($qLnk,0);
		}
		
		return $goods;	
	}
	
	public function get_data($cart){
		if(!$cart) return false;
		
		$goods = $this->get_goods($cart);
			$goods = $this->make_array($goods,$cart);
		
		$goods = $this->get_colors($cart,$goods);
		
		return $goods;		
	}
		
}
?>