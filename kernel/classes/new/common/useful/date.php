<?php
Class Common_Useful_Date{

	private static function mkstring($date){
		$date = (is_numeric($date)) ? $date : strtotime($date);
		
		return $date;
	}
	
	public static function date2node($date,$type){
		$date = self::mkstring($date);
		
		//28 февраля 2014
		if($type==1)
			return sprintf('%d %s %d',
					date('d',$date),
					self::date2monthname($date),
					date('Y',$date)
					);
	}	
		
	public static function date2dayofweek($date,$t){
		$date = self::mkstring($date);
		
		$days = array(
				0 => array('вс','воскресение'),
				1 => array('пн','понедельник'),
				2 => array('вт','вторник'),
				3 => array('ср','среда'),
				4 => array('чт','четверг'),
				5 => array('пт','пятница'),
				6 => array('сб','суббота'),
		);
		return $days[date('w',$date)][$t];
	}	
	
	public static function date2monthname($date){
		$date = self::mkstring($date);
		
		$captions = array(
			1 => 'января',
			2 => 'февраля',
			3 => 'марта',
			4 => 'апреля',
			5 => 'мая',
			6 => 'июня',
			7 => 'июля',
			8 => 'августа',
			9 => 'сентября',
			10 => 'октября',
			11 => 'ноября',
			12 => 'декабря'
		);
		return $captions[date('n',$date)];
	}	
	
}
?>