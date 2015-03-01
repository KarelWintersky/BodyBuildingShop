<?php
Class Front_Order_Delivery_Post Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function do_text($data){
		$arr = $data['costs']['post'];

		/*доставка почтой доступна, а клиент из Санкт-Петербурга*/
		if(isset($arr['post_available']) && $arr['post_available'] && $arr['is_spb'])
			$type = 1;
		
		/*труднодоступный регион, доставка есть*/
		elseif(isset($arr['hard_cost']) && $arr['hard_cost'] && isset($arr['post_available']) && $arr['post_available'])
			$type = 2;
		
		/*труднодоступный регион, доставки нет*/
		elseif(!$arr['no_zip_code'] && isset($arr['post_available']) && !$arr['post_available'])
			$type = 3;
		
		/*индекс не найден в базе данных индексов*/
		elseif(!isset($arr['post_available']) && !$arr['no_zip_code'])
			$type = 4;
		
		/*индекс у покупателя вообще не указан*/
		elseif($this->registry['userdata'] && !isset($arr['post_available']) && $arr['no_zip_code'])
			$type = 5;
		
		/*покупатель не зарегистрирован*/
		elseif(!$this->registry['userdata'])
			$type = 6;
				
		$type = (isset($type)) ? $type : false;
		
		return $this->do_rq('text',$type);
	}
	
	public function extra_fields(){ return false; }
	
	public function calculate_cost($data){
		if(!$this->registry['userdata']) return false;
		
		$arr = $data['costs']['post'];
		
		$a = array(
				'price' => (isset($arr['cost'])) ? Common_Useful::price2read($arr['cost']) : false,
				);
		if(!$a['price']) return false;
		
		return $this->do_rq('cost',$a);
	}	

			
}
?>