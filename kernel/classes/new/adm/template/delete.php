<?php
Class Adm_Template_Delete Extends Common_Rq{

	/*
	 * класс удаления сущностей
	 * */
	
	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_delete',$this);
	}	
	
	public function do_block($id,$func){
		
		$a = array(
				'id' => $id,
				'func' => $func
				);
		
		return $this->do_rq('storage',$a);
	}
	
	public function do_delete(){
		$rp = (isset($_POST['rp']))
			? $_POST['rp']
			: $_SERVER['HTTP_REFERER'];
		
		$rp = trim($rp,'/');
		$rp = explode('/',$rp);
		array_pop($rp);
		$rp = implode('/',$rp);
		$rp.='/';
		
		$this->registry['CL_doer']->set_rp($rp);
		
		$func = explode('::',$_POST['then_func']);
		
		$classname = $func[0]; $method = $func[1];
		
		$CL = new $classname($this->registry);
		$CL->$method();
	}
	
}
?>