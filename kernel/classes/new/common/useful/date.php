<?php
Class Common_Useful_Date{

	public static function read2date($read){
		$arr = explode('.',$read);
		return $arr[2].'-'.$arr[1].'-'.$arr[0];
	}	
	
	public static function date2read($date){
		$date = explode('-',$date);
		return $date[2].'.'.$date[1].'.'.$date[0];
	}	
	
	public static function date2dayofweek($date,$t){
		$days = array(
				0 => array('вс','воскресение'),
				1 => array('пн','понедельник'),
				2 => array('вт','вторник'),
				3 => array('ср','среда'),
				4 => array('чт','четверг'),
				5 => array('пт','пятница'),
				6 => array('сб','суббота'),
		);
		return $days[date('w',strtotime($date))][$t];
	}	
	
	public static function date2monthname($date){
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
		return $captions[date('n',strtotime($date))];
	}	
	
}
?>