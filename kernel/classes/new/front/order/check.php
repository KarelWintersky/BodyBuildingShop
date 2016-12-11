<?php
Class Front_Order_Check Extends Common_Rq{

	private $registry;
	
	private $Front_Order_Crumbs;
	private $Front_Order_Cart_Table;
	private $Front_Order_Cart_Values;
	private $Front_Order_Check_Params;
	private $Front_Order_Check_Payment;
	private $Front_Order_Check_Courier;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Crumbs = new Front_Order_Crumbs($this->registry);
		$this->Front_Order_Cart_Table = new Front_Order_Cart_Table($this->registry);
		$this->Front_Order_Cart_Values = new Front_Order_Cart_Values($this->registry);
		$this->Front_Order_Check_Params = new Front_Order_Check_Params($this->registry);
		$this->Front_Order_Check_Payment = new Front_Order_Check_Payment($this->registry);
		$this->Front_Order_Check_Courier = new Front_Order_Check_Courier($this->registry);
	}	
				
	public function do_vars(){
		$data = $this->registry['CL_data']->get_data();
		
		$this->registry->set('longtitle','Проверьте внимательно Ваш заказ');

		$vars = array(
			'crumbs' => $this->Front_Order_Crumbs->do_crumbs(4),
			'table' => $this->Front_Order_Cart_Table->do_table($data,true,$ostatkiDontMatch),
			'values' => $this->Front_Order_Cart_Values->do_block($data,true),
			'params' => $this->Front_Order_Check_Params->do_params($data),
			'payment' => $this->Front_Order_Check_Payment->do_block($data),
			'courier' => $this->Front_Order_Check_Courier->notify_block($data)
		);
		foreach($vars as $k => $v){
			$this->registry['CL_template_vars']->set($k,$v);
		}

	}
			
}
?>