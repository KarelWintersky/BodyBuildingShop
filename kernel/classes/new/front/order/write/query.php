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
						id, status, user_num, payment_method, payment_method_id,
						made_on, payed_on,
						delivery_costs, sum, overall_price, discount, from_account, coupon_discount,
						user_id, wishes, delivery_type,
						pay2courier,
						phone_number,
						by_card,
						courier_data, self_data
						)
					VALUES
						(
						'%s', '%s', '%s', '%s', '%s',
						 %s, %s,
						'%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', '%s',
						'%s',
						'%s',
						'%s',
						'%s', '%s'
						);
				",
				$data['payment_number'], $data['order_status'], $data['user_num'], $data['payment_method_code'], $data['payment_method'],
				"NOW()", $data['payed_on'],
				$data['delivery_costs'], $data['sum_with_discount'], $data['overall_price'], $data['overall_discount'], $data['from_account'], $data['coupon_discount'],
				$data['user_id'], $data['wishes'], $data['delivery_type'],
				$data['pay2courier'],
				$data['phone'],
				($data['by_card']) ? 1 : 0,
				$data['courier_data'], $data['self_data']
				));	

	}
}
?>