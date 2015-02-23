<?php
Class Adm_Orders_Customer Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}
			
	private function get_from_order($order){
		//курьер
		if($order['delivery_type']==2){
			$data = explode('::',$order['courier_data']);
			
			$customer = array(
					'fio' => $data[0],
					'phone' => $data[1],
					'address' => Common_Address::from_courier($order['courier_data']),
					'email' => $data[6],
			);			
			
		//самовывоз
		}elseif($order['delivery_type']==4){
			$data = explode('::',$order['self_data']);
			
			$customer = array(
				 	'fio' => $data[0], 
				 	'phone' => $data[1], 
				 	'address' => false, 
				 	'email' => false, 
					);
		}
		
		return $customer;
	}
	
	public function print_block($order){

		$customer = (!$order['user_id']) ? $this->get_from_order($order) : array();
		
		$a = array(
				'fio' => (isset($customer['fio']) && $customer['fio']) 
					? $customer['fio'] 
					: (($order['user_name']) ? $order['user_name'] : false),
				'email' => (isset($customer['email']) && $customer['email'])
					? $customer['email']
					: (($order['user_email']) ? $order['user_email'] : false),
				'phone' => (isset($customer['phone']) && $customer['phone'])
					? $customer['phone']
					: (($order['user_phone']) ? $order['user_phone'] : false),
				'address' => (isset($customer['address']) && $customer['address'])
					? $customer['address']
					: Common_Address::implode_address($order),				
				'user_id' => $order['user_id'],
				);
		
		return $this->do_rq('customer',$a);
	}
	
}
?>