<?php
Class Front_Contacts_Form_Send Extends Common_Rq{

	private $registry;
			
	public function __construct($registry){
		$this->registry = $registry;
		
		$Common_Mail = new Common_Mail($this->registry);
	}	
		
	private function get_to($topic){
		$qLnk = mysql_query(sprintf("
				SELECT 
					email,
					name
				FROM 
					feedback_mail 
				WHERE 
					id = '%d';",
				$topic
				));
		return mysql_fetch_assoc($qLnk);
	}
	
	private function get_text($post,$topic){
		$post['topic'] = $topic;
		return $this->do_rq('tpl',$post);
	}
	
	public function do_send(){
		if(!count($_POST)) return false;
						
		$to = $this->get_to(
				($_POST['topic']) ? $_POST['topic'] : 1
				);
		if(!$to) return false;
		
		$this->registry['CL_mail']->send_mail(
				$to['email'],
				'Сообщение с формы обратной связи',
				$this->get_text($_POST,$to['name']),
				false,
				false,
				'windows-1251',
				array(
						'email' => $_POST['email'],
						'name' => $_POST['name']
						)
		);		
	}		
}
?>