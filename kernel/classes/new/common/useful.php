<?php
Class Common_Useful{

	public static function price2read($price){
		return number_format($price,0,'',' ');
	}
	
	
	public static function rus2translit($s) {
		$converter = array(
				'а' => 'a',   'б' => 'b',   'в' => 'v',
				'г' => 'g',   'д' => 'd',   'е' => 'e',
				'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
				'и' => 'i',   'й' => 'y',   'к' => 'k',
				'л' => 'l',   'м' => 'm',   'н' => 'n',
				'о' => 'o',   'п' => 'p',   'р' => 'r',
				'с' => 's',   'т' => 't',   'у' => 'u',
				'ф' => 'f',   'х' => 'h',   'ц' => 'c',
				'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
				'ь' => '',    'ы' => 'y',   'ъ' => '',
				'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
				' ' => '-',	  '"' => '',    '«' => '',
				'»' => '',    '(' => '',    ')' => '',
				'.' => '',    ',' => '',    '&' => 'and',
				'+' => '',    '%' => 'percent', ';' => '',
				':' => '', '№' => '',       '!' => '',
				'/' => '', '|' => '',       DIRSEP => '-',
				'?' => ''
		);
		return strtr(mb_strtolower(trim($s),'utf8'), $converter);
	}	
	
}
?>