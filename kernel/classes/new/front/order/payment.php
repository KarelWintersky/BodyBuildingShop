<?php
Class Front_Order_Payment Extends Common_Rq{

	private $registry;
	private $methods;
	
	private $Front_Order_Crumbs;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		
		$this->methods = array(
				1 => array(
					'name' => 'Заказать наложенным платежом',
				),
				2 => array(
					'name' => 'Получить счет на предоплату через банк',
				),
				3 => array(
					'name' => 'Оплата через WebMoney, Яндекс-деньги',
				),
				4 => array(
					'name' => 'Оплата банковской картой',
				),
				5 => array(
					'name' => 'Наличными курьеру или в магазине',
				),
				6 => array(
					'name' => 'Оплата c лицевого счета в нашем магазине',
					'add_txt' => '(<span><a href="/profile/accountorder/">пополнить счет</a></span>)',
				),
				7 => array(
					'name' => 'Другие платежные системы',
					'add_txt' => '(QIWI, RBK Money, моб. телефон, Альфа-банк...)',
				)				
			);
	}	
		
	public function do_vars(){
		$vars = array(
				'crumbs' => $this->Front_Order_Crumbs->do_crumbs(3)
		);
	
		foreach($vars as $k => $v) $this->registry['CL_template_vars']->set($k,$v);
	}
			
}
?>