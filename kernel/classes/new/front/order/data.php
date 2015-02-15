<?php
Class Front_Order_Data{

	private $registry;
	
	private $Front_Order_Data_Cart;
	private $Front_Order_Data_Params;
	private $Front_Order_Data_Discount;
	
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_data',$this);
		
		$this->Front_Order_Data_Cart = new Front_Order_Data_Cart($this->registry);
		$this->Front_Order_Data_Params = new Front_Order_Data_Params($this->registry);
		$this->Front_Order_Data_Discount = new Front_Order_Data_Discount($this->registry);
		
		$this->Front_Order_Data_Delivery_Zipcode = new Front_Order_Data_Delivery_Zipcode($this->registry);
		$this->Front_Order_Data_Delivery_Post = new Front_Order_Data_Delivery_Post($this->registry);
		$this->Front_Order_Data_Delivery_Courier = new Front_Order_Data_Delivery_Courier($this->registry);
		
		if(!isset($this->registry['CL_storage'])) $Front_Order_Storage = new Front_Order_Storage($this->registry);
	}	
		
	public function get_data($cart = false){
		$data = $this->Front_Order_Data_Cart->get_data($cart);
		if(!$data) return false;
		
		$data = $this->Front_Order_Data_Delivery_Post->calculate_costs($data);
		$data = $this->Front_Order_Data_Delivery_Courier->calculate_costs($data);
		
		$data = $this->Front_Order_Data_Params->get_params($data);
		$data = $this->Front_Order_Data_Discount->get_discount($data);
		
		return $data;
	}
	
}
?>