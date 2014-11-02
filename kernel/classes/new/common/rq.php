<?php
Abstract Class Common_Rq{

	protected function do_rq($name,$a,$loop = false){
		$path = explode('_',get_called_class());
		foreach($path as $key => $val) $path[$key] = mb_strtolower($val,'utf-8');
		
		$folder = array_shift($path);
		
		$part = (!$loop) ? 'content' : 'item';
		$dir = sprintf('%stpl/%s/%s/',
				ROOT_PATH,
				$folder,
				$part
		);
			
		$file = sprintf('%s%s/%s.html',
				$dir,
				implode('/',$path),
				$name
		);
						
		ob_start();
		if(is_file($file)) require($file);
		return ob_get_clean();
	}	
	
}
?>