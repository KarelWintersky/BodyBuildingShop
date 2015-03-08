<?php
Class Adm_Doer{

	private $registry;
	private $rp;
		
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_doer',$this);
	}

	public function set_rp($rp){
		$this->rp = $rp;
	}
	
	public function go(){
		$this->rp = (isset($_POST['rp'])) ? $_POST['rp'] : $_SERVER['HTTP_REFERER'];
		
		$func = explode('::',$_POST['func']);
		
		$classname = $func[0]; $method = $func[1];
		
		$CL = new $classname($this->registry); 
		$CL->$method();
		
		header('Location: '.$this->rp);
		exit();
	}
	
}
?>