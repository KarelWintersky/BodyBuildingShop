<?php
Class Front_Template_Texted_Li{
	
		private $registry;

        function __construct($registry) {
	        $this->registry = $registry;
        }

        private function li_replace($matches){
        	return sprintf('<li><span class="texted_li_gray">%s</span></li>',
        			$matches[1]
        			);
        }
        
        private function texted_replace($matches){
        	$reg = '/<li>(.*)<\/li>/sU';
        	
        	$texted = preg_replace_callback(
        			$reg,
        			array($this,'li_replace'),
        			$matches[1]
        	);

        	return sprintf('<div class="texted">%s</div>',$texted);
        }
        
		public function replace_li($html){
			$reg = '/<div class=\"texted\".*>(.*)<\/div>/sU';
			
			$html = preg_replace_callback(
					$reg,
					array($this,'texted_replace'),
					$html
			);			
			
			return $html;
		}	
}
?>