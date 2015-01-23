<?php
Class Front_Articles_Widget Extends Common_Rq{

	private $registry;
	
	private $Front_Articles_Widget_Data;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Front_Articles_Widget_Data = new Front_Articles_Widget_Data($this->registry);
	}	
		
	private function clear_code($text){
		/*
		 * очистка кода от символов, которые мешают распарсить синтаксис вставки статей
		 * */
		
		$text = str_replace('&nbsp;</p>','</p>',$text);
		
		return $text;
	}
	
	public function do_articles($text){
		$text = $this->clear_code($text);
		
		$reg = "/<p>{{a:(.*)}}<\/p>/i";
		$text = preg_replace_callback($reg,array($this,'match_find'),$text);
		
		return $text;		
	}
	
	private function match_find($matches){
		$ids = explode(',',$matches[1]);
		
		return (count($ids)) 
			? $this->do_articles_list($ids) 
			: $matches[0];
	}

	private function do_articles_list($ids){
		$articles = $this->Front_Articles_Widget_Data->get_data($ids);
		
		$html = array();
		
		$count = count($articles); $i = 1;
		foreach($articles as $a){
			$a['classes'] = $this->item_classes($count,$i);
			$a['image'] = sprintf('%sdata/foto/articles/%s',
					THIS_URL,
					$a['avatar']
					);
			
			$html[] = $this->do_rq('item',$a,true);
	
			$i++;
		}
	
		$a = array(
			'list' => implode('',$html),
			'class' => $this->storage_class($count)
		);
	
		return $this->do_rq('storage',$a);
	}
	
	private function storage_class($count){
		if($count==1) return false;
		elseif($count%2==0) return 'doubled';
		else return 'with_last';
	}
	
	private function item_classes($count,$i){
		$classes = array();
	
		if($i==$count) $classes[] = 'last';
		if($i%2==0) $classes[] = 'even';
	
		return implode(' ',$classes);
	}	
}
?>