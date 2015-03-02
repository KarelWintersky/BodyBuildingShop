<?

	/*
	 * старый класс отправки почты
	 * в перспективе его нужно будет снести, но для экономии времени я лишь самые необходимые функции
	 * переключил на новый
	 * а из этого привязался к новому, чтобы отправка производилась в точно одном месте
	 * */

	Class Mailer{

		private $registry;
		private $mail_subject;
		private $mail_text;
		private $replace_arr;
		private $to;

		public function __construct(
				$registry,
				$tpl_id,
				$replace_arr,
				$to,
				$attach = false,
				$encoding='UTF-8'
				){
			
			$this->registry = $registry;
			
			if(!isset($this->registry['CL_mail'])) $Common_Mail = new Common_Mail($this->registry);
					
			$this->replace_arr = $this->add2replacearr($replace_arr);
			$this->to = $to;

			$this->get_tpl($tpl_id);

			$this->registry['CL_mail']->send_mail(
					$this->to,
					$this->mail_subject,
					$this->mail_text,
					$attach,
					($encoding=='UTF-8'), //если отправляем в 1251 - то без обертки
					$encoding
			);			
		}
		
		private function add2replacearr($replace_arr){
			$replace_arr['SITE_URL'] = THIS_URL;
			
			return $replace_arr;
		}

		private function get_tpl($tpl_id){
			$qLnk = mysql_query("SELECT * FROM mailtpls WHERE id = '".$tpl_id."';");
			$tpl_data = mysql_fetch_assoc($qLnk);

			$this->mail_subject = $this->mk_subject($tpl_data['subject']);
			$this->mail_text = $this->mk_tpl_text($tpl_data['content']);
		}

		private function mk_subject($subject){
			foreach($this->replace_arr as $find => $replace)
				$subject = str_replace('{'.$find.'}', $replace, $subject);
			
			return $subject;
		}

		private function mk_tpl_text($tpl_text){
			foreach($this->replace_arr as $find => $replace)
				$tpl_text = str_replace('{'.$find.'}', $replace, $tpl_text);
							
			return $tpl_text;
		}

	}
?>