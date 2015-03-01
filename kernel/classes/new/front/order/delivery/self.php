<?php
Class Front_Order_Delivery_Self Extends Common_Rq{
	
	private $registry;
					
	public function __construct($registry){
		$this->registry = $registry;
	}	

	public function do_text($data){
		return $this->do_rq('text',NULL);
	}	
	
	public function calculate_cost($data){	
		return $this->do_rq('cost',NULL);
	}
	
	public function extra_fields(){
		
		$a = array(
				'name' => ($this->registry['CL_storage']->get_storage('self_name'))
					? $this->registry['CL_storage']->get_storage('self_name')
					: (($this->registry['userdata']) ? $this->registry['userdata']['name'] : ''),
				'phone' => ($this->registry['CL_storage']->get_storage('self_phone'))
					? $this->registry['CL_storage']->get_storage('self_phone')
					: (($this->registry['userdata']) ? $this->registry['userdata']['phone'] : ''),				
				);
		
		return $this->do_rq('fields',$a);
	}	
			
}
?>