<?php
Class Common_Avatar{

        private $registry;
        private $parts;
        
        function __construct($registry) {
			$this->registry = $registry;
			$this->registry->set('CL_avatar_logic',$this);
			
			$this->parts = $this->registry['config']['avatar_settings'];
        }

        public function part_check($alias){
        	if(!isset($this->parts[$alias])) return false;
        	
        	return $this->parts[$alias];
        }

}
?>