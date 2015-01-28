<?php
Class Front_Order_Data{

	private $registry;
	
	private $Front_Order_Data_Cart;
	private $Front_Order_Data_Params;
	
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_data',$this);
		
		$this->Front_Order_Data_Cart = new Front_Order_Data_Cart($this->registry);
		$this->Front_Order_Data_Params = new Front_Order_Data_Params($this->registry);
	}	
		
	public function get_data(){
		$data = $this->Front_Order_Data_Params->get_params(
				$this->Front_Order_Data_Cart->get_data()
				);
		
		return $data;
	}
	
}
?>