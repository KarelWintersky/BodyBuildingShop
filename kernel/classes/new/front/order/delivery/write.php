<?php
Class Front_Order_Delivery_Write{

	private $registry;
	
	private $Front_Order_Storage;
				
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Order_Storage = new Front_Order_Storage($this->registry);
	}	
		
	public function do_write(){
		if(!isset($_POST['delivery'])) return false;
		$method_id = $_POST['delivery'];
		
		$methods = Front_Order_Data_Delivery::get_methods();
		if(!isset($methods[$method_id])) return false;
		
		$this->Front_Order_Storage->write_to_storage('delivery',$method_id);
	}
			
}
?>