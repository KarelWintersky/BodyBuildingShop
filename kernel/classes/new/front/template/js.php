<?php
Class Front_Template_Js{

		private $registry;
		private $data = array();

        function __construct($registry) {
			$this->registry = $registry;
			$this->registry->set('CL_js',$this);

			$this->set(
				array(
					'http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js',
					'lib/chosen.jquery.min',
					'lib/jquery.cookie',
					'lib/jquery.tools_tooltip.min',
					'dropdown-block',
					'uses',
				)
			);
        }

        public function set($files){
        	if(!is_array($files)) $files = array($files);

        	foreach($files as $f){
				$this->data[$f] = true;
			}
        }

        private function mk_src($file, &$ver){
			if(strpos($file,'http://')===false && strpos($file,'https://')===false && strpos($file,'//')===false){

				if(OPTIMISE_FRONTEND && strpos($file,'.min')===false){
					$file_js = sprintf('/browser/front/js/%s.js',$file);
					$file_js_min = sprintf('/browser/front/js/%s.min.js', $file);

					//touch(ROOT_PATH.'public_html'.$file_js); //for regenerate files manually
					if(!file_exists(ROOT_PATH.'public_html'.$file_js_min) || filemtime(ROOT_PATH.'public_html'.$file_js) != filemtime(ROOT_PATH.'public_html'.$file_js_min)){
						$buffer = file_get_contents(ROOT_PATH.'public_html'.$file_js);
						// remove comments
						$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
						$buffer = preg_replace('/([^:])\/\/.*/', '$1', $buffer);
						// remove tabs, spaces, new lines, etc.
						//$buffer = str_replace(array("\t", '  ', '    ', '    '), '', $buffer);
						/* DELETE  ??? */
						$buffer = str_replace(array("\r\n", "\r", "\n",), '', $buffer);
						$buffer = str_replace(array("\t",), ' ', $buffer);
						$buffer = str_replace(array('  ', '    ', '    '), ' ', $buffer);
						$buffer = str_replace(array('  ', '    ', '    '), ' ', $buffer);
						$buffer = str_replace(array('  ', '    ', '    '), ' ', $buffer);
						$buffer = str_replace(array('  ', '    ', '    '), ' ', $buffer);
						$buffer = preg_replace('/^\s*/', '', $buffer);
						// remove unnecessary spaces
						$buffer = str_replace('{ ', '{', $buffer);
						$buffer = str_replace(' {', '{', $buffer);
						$buffer = str_replace('} ', '}', $buffer);
						$buffer = str_replace(' }', '}', $buffer);
						$buffer = str_replace('] ', ']', $buffer);
						$buffer = str_replace(' ]', ']', $buffer);
						$buffer = str_replace('[ ', '[', $buffer);
						$buffer = str_replace(' [', '[', $buffer);
						$buffer = str_replace('= ', '=', $buffer);
						$buffer = str_replace(' =', '=', $buffer);
						$buffer = preg_replace('/\s+"\s+/', ' " ', $buffer);
						/*$buffer = str_replace(' " ', '"', $buffer);*/
						$buffer = str_replace(' ? ', '?', $buffer);
						$buffer = str_replace(' ! ', '!', $buffer);
						$buffer = str_replace(' + ', '+', $buffer);
						$buffer = str_replace(' - ', '-', $buffer);
						$buffer = str_replace(' / ', '/', $buffer);
						$buffer = str_replace(' * ', '*', $buffer);
						$buffer = str_replace(' > ', '>', $buffer);
						$buffer = str_replace(' < ', '<', $buffer);
						$buffer = str_replace(' ; ', ';', $buffer);
							$buffer = str_replace('; ', ';', $buffer);
						$buffer = str_replace(' , ', ',', $buffer);
						$buffer = str_replace(' : ', ':', $buffer);
						$buffer = str_replace(' ) ', ')', $buffer);
						$buffer = str_replace(' ( ', '(', $buffer);
							$buffer = str_replace(' ){', '){', $buffer);
							$buffer = str_replace(') {', '){', $buffer);
							$buffer = str_replace('}, ', '},', $buffer);
							$buffer = str_replace('$( ', '$(', $buffer);
							$buffer = str_replace('}; ', '};', $buffer);
							$buffer = str_replace('" )', '")', $buffer);
							$buffer = str_replace('( "', '("', $buffer);
							$buffer = str_replace(' );', ');', $buffer);
							$buffer = str_replace(')( ', ')(', $buffer);
							$buffer = str_replace('( $', '($', $buffer);
							$buffer = str_replace('function( ', 'function(', $buffer);
							$buffer = str_replace(' ).', ').', $buffer);
							$buffer = str_replace(') !', ')!', $buffer);
							$buffer = str_replace('), ', '),', $buffer);
						$buffer = str_replace('|| ', '||', $buffer);
						$buffer = str_replace(' ||', '||', $buffer);
						$buffer = str_replace('&& ', '&&', $buffer);
						$buffer = str_replace(' &&', '&&', $buffer);
						//if($file == 'index_srch') {echo '<pre>'; echo($buffer); die();}
						//die($buffer);
						file_put_contents(ROOT_PATH.'public_html'.$file_js_min, $buffer);
						touch(ROOT_PATH.'public_html'.$file_js_min);
						touch(ROOT_PATH.'public_html'.$file_js);
					}
					clearstatcache(); //clear cache for filemtime
					$file = $file_js_min;
				}
				else {
					$file = sprintf('/browser/front/js/%s.js',$file);
				}

				if(file_exists(ROOT_PATH.'public_html'.$file)){
					$ver .= filemtime(ROOT_PATH.'public_html'.$file);
				}
			}
        	return $file;
        }

		public function go(){
			$output = array();
			$output_head = array();
			$ver = '';

			foreach($this->data as $file => $true){
				$file_js = $this->mk_src($file, $ver);
				$this->data[$file] = $file_js;
			}
			$ver = hash('crc32', $ver);

			foreach($this->data as $file => $file_js){
				if(strpos($file,'http://')===false && strpos($file,'https://')===false && strpos($file,'//')===false)
					$file_js = sprintf($file_js.'?ver=%s',$ver);

				$output[] = sprintf('<script src="%s" type="text/javascript"></script>', //defer
						$file_js
						);
			}

			$this->registry['CL_template_vars']->set('js',implode("\n",$output));
		}

}
?>