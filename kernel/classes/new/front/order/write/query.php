<?php
Class Front_Order_Write_Query{
	
	private $registry;
	
	public function __construct($registry){
		$this->registry = $registry;
	}	
			
	/*sum_full - полная сумма
	sum_with_discount - сумма со скидкой
	delivery_costs - расходы на доставку
	nalog_costs - расходы на доставку наложенным платежом
	discount_percent - скидка в %
	overall_sum - sum_with_discount+delivery_costs+nalog_costs
	from_account - сколько оплачено со счета
	pay2courier - оплата курьеру или нет
	by_card - оплата картой или нет*/
	
	public function do_query($data){
	
	$sql = sprintf("
				INSERT INTO
					orders
						(
						id, status, user_num, payment_method, payment_method_id,
						made_on, payed_on,
						user_id, wishes, delivery_type,
						phone_number,
						courier_data, self_data,
				
						sum_full, sum_with_discount,
						delivery_costs, nalog_costs,
						discount_percent,
						overall_sum,
						from_account, pay2courier, by_card,
						account_extra_payment,
						gift_barcode,
						is_spb
						)
					VALUES
						(
						'%s', '%s', '%s', '%s', '%s',
						 %s, %s,
						'%s', '%s', '%s',
						'%s',
						'%s', '%s',
				
						'%s', '%s',
						 %s, 
						'%s',
						'%s',
						'%s',
						'%s', '%s', '%s',
						'%s',
						 %s,	
						'%d'
						);
				",
				$data['payment_number'], $data['order_status'], $data['user_num'], $data['payment_method_code'], $data['payment_method'],
				"NOW()", $data['payed_on'],
				$data['user_id'], $data['wishes'], $data['delivery_type'],
				$data['phone'],
				$data['courier_data'], $data['self_data'],
				
				$data['sum'], $data['sum_with_discount'],
				($data['delivery_costs']===false) ? "NULL" : sprintf("'%s'",$data['delivery_costs']), 
				$data['nalog_costs'],
				$data['discount_percent'],
				$data['overall_sum'],
				$data['from_account'], $data['pay2courier'], $data['by_card'],
				$data['account_extra_payment'],
				($data['gift_barcode']!==false) 
					? sprintf("'%s'",$data['gift_barcode'])
					: "NULL",
				($data['is_spb']) ? 1 : 0
				);
				
		// var_dump($sql); die;
		
		mysql_query($sql);
		
	}
}
?>