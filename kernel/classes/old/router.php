<?php


Class Router {

        private $registry;
        private $path;
        private $args = array();

        function __construct($registry) {
           $this->registry = $registry;
        }

        public function url_low_register(){
        	$path = $_SERVER['REQUEST_URI'];
        		$path = explode('?',$path);
        		
        	$url_lower=mb_strtolower($path[0],'utf-8');
        	if($url_lower==$path[0]) return false;
        	
        	$url = (isset($path[1])) 
        		? sprintf('%s?%s',$url_lower,$path[1])
        		: $url_lower;

        	header('HTTP/1.1 301 Moved Permanently');
        	header('Location: '.$url_lower);
        	exit();        	
        }
        
        function trailing_slash(){
        	$path = explode('?',$_SERVER['REQUEST_URI']);
        	        	
        	if(strpos($path[0],'&')!==false){
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: /');
					exit();
        	}
        	
        	if(count(explode('.',end($path)))==1){ //если последняя часть пути содержит точу - это наверняка файл, который не нужно никак закрывать слешем
	        	$new_path_0 = trim($path[0],'/').'/';
	        	if('/'.$new_path_0!=$path[0] && $_SERVER['REQUEST_URI']!='/'){
	        		$path[0] = $new_path_0;
	        		$path_str = implode('?',$path);

					header('HTTP/1.1 301 Moved Permanently');
					header('Location: '.THIS_URL.$path_str);
					exit();

	        	}
        	}
        }

		function setPath($path) {
		    //$path = trim($path, '/\\');
		    $path.= DIRSEP;

	        if (!is_dir($path)) {
	           throw new Exception ('Invalid controller path: `' . $path . '`');
	        }

		    $this->path = $path;
		}


		private function getController(&$file, &$controller, &$action, &$args) {
		    $route = (empty($_GET['route'])) ? '' : $_GET['route'];
		    
			if (empty($route)) { $route = 'index'; }

			// Получаем раздельные части
			$route = trim($route, '/\\');
			$parts = explode('/', $route);

	        // Находим правильный контроллер

	        $cmd_path = $this->path;
	        
	        foreach ($parts as $part) {
                $fullpath = $cmd_path.$part;

                // Есть ли папка с таким путём?
                if (is_dir($fullpath)) {
                   $cmd_path.=$part.DIRSEP;
                   
                    array_shift($parts);
                    continue;
                }
                
                // Находим файл
                if (is_file($fullpath.'.php')) {
					$controller = $part;
					array_shift($parts);
					break;

                }

	        }
	        
	        if (empty($controller)) { $controller = 'index'; };
	        
			// Получаем действие
			$action = array_shift($parts);
			
			if (empty($action)) { $action = 'index'; }

			$file = $cmd_path.$controller.'.php';
			
			$args = $parts;
		}


		function delegate() {

			// Анализируем путь
			$this->getController($file, $controller, $action, $args);
			
			/*file_put_contents('/var/www/new2.bodybuilding-shop.ru/log/router.log', json_encode([
			    'controller'   =>  $controller,
	    		    'file'          =>  $file,
	                    'action'        =>  $action,
	                    'args'          =>  $args
	                    ]) . PHP_EOL , FILE_APPEND);*/
	                    
			
			// Файл доступен?
			if(!is_readable($file)) {
		        $this->registry['f_404'] = true;
		        return false;
			}

			// Подключаем файл
			include ($file);

			// Создаём экземпляр контроллера
			$class = 'Controller_' . $controller;
			$c = new $class($this->registry);

			// Действие доступно?
			if (!is_callable(array($c, $action))) {
				array_unshift($args,$action);
				//$args = $action;
				$action=$controller;
				//ДОПИСАТЬ, ЧТОБЫ НОРМАЛЬНО ОПРЕДЕЛЯЛ 404
			    //$this->registry['f_404'] = true;
			    //return false;
			}

			// Выполняем действие
			$c->$action($args);

		}

		public function path_check(){

			$f_catalog = new f_Catalog($this->registry);
			$this->registry->set('f_catalog',$f_catalog);
			
			$f_pages = new f_Pages($this->registry);
			$this->registry->set('f_pages',$f_pages);
			
			$f_articles = new f_Pitanie($this->registry);
			
			$Front_News = new Front_News($this->registry);
			
			if(!$this->redirect_check()){
				if(!$f_catalog->path_check()){
					if(!$this->method_check()){
						if(!$f_pages->path_check()){
							if(!$Front_News->path_check()){
								
							}
						}
					}
				}
			}
		}

		private function redirect_check(){
			//костыль для редиректа на морду страниц
			$path_arr = $this->registry['route_path'];

			if(count($path_arr)==1 && ($path_arr[0]=='creatine.html' || $path_arr[0]=='vitamine.html')){
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: /');
				exit();
			}elseif(count($path_arr)==2 && $path_arr[0]=='articles'){
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: /'.$path_arr[1].'/');
				exit();
			}

			return $this->registry['f_catalog']->redirect_check();
		}

		private function method_check(){

			$path_arr = $this->registry['route_path'];

			$probable_class = 'f_'.array_shift($path_arr);

			if(class_exists($probable_class) && method_exists($probable_class,'pgc')){
				$this->registry['route_path'] = $path_arr;
				$cl = new $probable_class($this->registry);
				return $cl->path_check();
			}else{
				return false;
			}
		}
}


?>