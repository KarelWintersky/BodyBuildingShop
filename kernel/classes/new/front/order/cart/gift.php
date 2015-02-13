<?php
Class Front_Order_Cart_Gift Extends Common_Rq{

	private $registry;
	private $deadline = 800;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
	
	private function get_data($sum){		
		$top_price = $sum*GIFT_PERCENT/100;
		
		$goods = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					goods.name,
					goods_barcodes.barcode,
					goods_barcodes.packing,
					goods_barcodes.feature,
					growers.name AS grower_name
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				LEFT OUTER JOIN growers ON growers.id = goods.grower_id
				WHERE
					goods.published = 1
					AND
					goods_barcodes.weight > 0
					AND
					goods_barcodes.present = 1
					AND
					goods_barcodes.price <= '%s'
				ORDER BY
					goods.level_id ASC,
					goods.name ASC
				",
				$top_price
				));
		while($g = mysql_fetch_assoc($qLnk)){
			$g['name'] = ($g['grower_name']) 
				? sprintf('«%s». %s',$g['grower_name'],$g['name'])
				: $g['name'];
			
			$g['name'].= ', '.$g['packing'];
			
			if($g['feature']) $g['name'].= ', '.$g['feature'];
			
			$goods[$g['barcode']] = $g['name'];
		}
		
		return $goods;
	}
	
	private function gifts_list($sum){
		$active = $this->registry['CL_storage']->get_storage('gift');
		
		$goods = $this->get_data($sum);
		
		$data = array();
		$data[] = array(
			'val' => 0,
			'name' => 'Подарок на усмотрение администрации',
		);		
		
		foreach($goods as $barcode => $name)
			$data[] = array(
					'val' => $barcode,
					'name' => $name,
					'selected' => ($active==$barcode),
			);
		
		return Front_Template_Select::opts($data);
	}
	
	public function do_block($data){
		$a = array(
				'list' => ($data['sum']>=$this->deadline) ? $this->gifts_list($data['sum']) : false,
				'extra_sum' => $this->deadline-$data['sum'],
				'deadline' => $this->deadline
				);
		
		return $this->do_rq('gift',$a);
	}
	
}
?>