<?php
Class Front_Order_Delivery_Courier Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function extra_fields(){
		
		$a = array(
				'name' => ($this->registry['CL_storage']->get_storage('courier_name'))
					? $this->registry['CL_storage']->get_storage('courier_name')
					: (($this->registry['userdata']) ? $this->registry['userdata']['name'] : ''),
				'phone' => ($this->registry['CL_storage']->get_storage('courier_phone'))
					? $this->registry['CL_storage']->get_storage('courier_phone')
					: '',
				'email' => ($this->registry['CL_storage']->get_storage('courier_email'))
					? $this->registry['CL_storage']->get_storage('courier_email')
					: (($this->registry['userdata']) ? $this->registry['userdata']['email'] : ''),
				'zipcode' => ($this->registry['CL_storage']->get_storage('courier_zipcode'))
					? $this->registry['CL_storage']->get_storage('courier_zipcode')
					: (($this->registry['userdata']) ? $this->registry['userdata']['zip_code'] : ''),
				'city' => ($this->registry['CL_storage']->get_storage('courier_city'))
					? $this->registry['CL_storage']->get_storage('courier_city')
					: (($this->registry['userdata']) ? $this->registry['userdata']['city'] : ''),
				'street' => ($this->registry['CL_storage']->get_storage('courier_street'))
					? $this->registry['CL_storage']->get_storage('courier_street')
					: (($this->registry['userdata']) ? $this->registry['userdata']['street'] : ''),
				'house' => ($this->registry['CL_storage']->get_storage('courier_house'))
					? $this->registry['CL_storage']->get_storage('courier_house')
					: (($this->registry['userdata']) ? $this->registry['userdata']['house'] : ''),
				);
		
		return $this->do_rq('fields',$a);	
	}	
	
	public function calculate_cost($data){
		$arr = $data['costs']['courier'];
				
		$a = array(
				'price' => Common_Useful::price2read($arr['sum']),
				'free_delivery_sum' => Common_Useful::price2read(FREE_DELIVERY_SUM),
				'free_delivery_diff' => Common_Useful::price2read(FREE_DELIVERY_SUM - $data['sum_with_discount']),
				'is_spb' => $arr['is_spb'],
				'is_zipcode' => $arr['is_zipcode']
				);
		
		return $this->do_rq('cost',$a);
	}

	public function recalculate(){
		$Front_Order_Storage = new Front_Order_Storage($this->registry);
		$Front_Order_Data = new Front_Order_Data($this->registry);
	
		$Front_Order_Storage->write_to_storage('courier_zipcode',$_POST['zipcode']);
		$Front_Order_Storage->write_to_storage('courier_city',$_POST['city']);
	
		$data = $Front_Order_Data->get_data();
		
		echo $this->calculate_cost($data);
	}	
}
?>