<?php
Class Front_Order_Write_Query{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	public function do_query($data){
		mysql_query(sprintf("
				INSERT INTO
					orders
						(
						id,
						status,
						user_num,
						made_on,
						payed_on,
						delivery_costs,
						sum,
						overall_price,
						payment_method,
						user_id,
						discount,
						wishes,
						delivery_type,
						from_account,
						pay2courier,
						phone_number,
						by_card,
						coupon_discount
						)
					VALUES
						(
						'%d',
						'%d',
						'%d',
						NOW(),
						%s,
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d,
						'%s'
						);
				",
				$data['payment_number'],
				$data['order_status'],
				$data['user_num'],
				$data['payed_on'],
				$data['delivery_costs'],
				$data['sum_with_discount'],
				$data['overall_price'],
				$data['payment_method_code'],
				$data['user_id'],
				$data['wishes'],
				$data['overall_discount'],
				$data['delivery_type'],
				$data['from-account'],
				$data['pay2courier'],
				$data['phone'],
				($data['by_card']) ? 1 : 0,
				$data['coupon_discount']
				));		
	}
}
?>