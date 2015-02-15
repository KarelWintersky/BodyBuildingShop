<?php
Class Front_Order_Data_Cart{

	private $registry;
	
	private $Front_Order_Data_Cart_String;
	private $Front_Order_Data_Cart_Goods;
	private $Front_Order_Data_Cart_Gift;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Data_Cart_String = new Front_Order_Data_Cart_String($this->registry);
		$this->Front_Order_Data_Cart_Goods = new Front_Order_Data_Cart_Goods($this->registry);
		$this->Front_Order_Data_Cart_Gift = new Front_Order_Data_Cart_Gift($this->registry);
	}	
			
	private function calculate_nalog($sum){
		/*
		 * сумма заказа при оплате наложенным платежом
		 * */
		
		$sum_nalog = $sum*PREPAY_DISCOUNT/100;
		$sum_nalog = intval($sum_nalog);
		
		return array(
				'costs' => $sum_nalog,
				'sum' => $sum_nalog + $sum,
				);
	}
	
	private function calculate_sum($goods){
		$sum = 0;
		
		foreach($goods as $g){
			$price = intval($g['price']);
			$sum+= $g['amount']*$price;
		}

		return $sum;
	}	
	
	private function calculate_weight($goods){
		$sum = 0;
		
		foreach($goods as $g){
			$weight = intval($g['weight']);
			$sum+= $g['amount']*$weight;
		}
	
		return $sum;
	}	
	
	public function get_data($cart){
		/*
		 * иногда на входе у нас уже есть готовая корзина
		* тогда мы сразу передаем ее в работу и не парсим куки почем зря
		* */		
		
		$cart = (!$cart) ? $this->Front_Order_Data_Cart_String->get_cart_from_string() : $cart;
		if(!$cart) return false;
		
		$goods = $this->Front_Order_Data_Cart_Goods->get_data($cart);
		
		$sum = $this->calculate_sum($goods);
		
		$output = array(
				'cart' => $cart,
				'goods' => $goods,
				'sum' => $sum,
				'nalog' => $this->calculate_nalog($sum),
				'weight' => $this->calculate_weight($goods),
				'gift' => $this->Front_Order_Data_Cart_Gift->get_data()
				);
		
		return $output;		
	}
	
}
?>