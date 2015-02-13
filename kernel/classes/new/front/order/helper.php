<?php
Class Front_Order_Helper{

	public static function delivery_payment_texts($items){
		$texts = array();
		
		$fields = array();
		foreach($items as $i)
			$fields[] = sprintf("'%s'",$i['field']);
		
		$qLnk = mysql_query(sprintf("
				SELECT
					name,
					value
				FROM
					dp_params
				WHERE
					name  IN (%s)
				",
				implode(",",$fields)
				));
		while($t = mysql_fetch_assoc($qLnk)) $texts[$t['name']] = $t['value'];
		
		return $texts;
	}
			
}
?>