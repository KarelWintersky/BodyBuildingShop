<?php
Class Front_Order_Crumbs Extends Common_Rq{

	private $registry;
	private $items;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->items = array(
				1 => array('Оформление заказа','order'),
				2 => array('Выбор доставки','order/delivery'),
				3 => array('Выбор оплаты','order/payment'),
				4 => array('Проверка','order/check'),
				);
	}	
		
	public function do_crumbs($cur){
		$html = array();
		
		$i = 1; $do_link = true;
		foreach($this->items as $key => $arr){
			$active = ($key==$cur);
			if($active) $do_link = false;
			
			$a = array(
					'name' => $arr[0],
					'num' => $i,
					'active' => ($active) ? 'active' : '',
					'link' => ($do_link) ? sprintf('/%s/',$arr[1]) : false,
					);
			
			$html[] = $this->do_rq('item',$a,true);
			
			$i++;
		}
		
		return $this->do_rq('main',implode('',$html));
	}
		
}
?>