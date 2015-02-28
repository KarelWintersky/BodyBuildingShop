<?php
Class Common_Mail{
	
	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
		$this->registry->set('CL_mail',$this);

		set_include_path(ROOT_PATH.'kernel/libs/');
		require_once('Zend/Mail.php');
	}

	public function send_mail(
			$emails,
			$subject,
			$text,
			$attach = false,
			$wrap = true,
			$encoding='UTF-8'){
		
		$emails = $this->to_emails($emails);
		if(!count($emails)) return false;
		
		$mail = new Zend_Mail($encoding);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		
		$mail->setSubject(
				$this->set_encoding($subject,$encoding)
				);
		
		foreach($emails as $email) $mail->addTo($email);
		
		if($wrap) $text = $this->wrap_text($text);
		
		$mail->setBodyHtml(
				$this->set_encoding($text,$encoding),
				$encoding,
				Zend_Mime::MULTIPART_RELATED				
				);
		
		$mail->setFrom(
				'no-reply@bodybuilding-shop.ru',
				$this->set_encoding('Бодибилдинг Магазин',$encoding)
				);		
		
		$attachment = $this->attachment($attach); if($attachment) $mail->addAttachment($attachment);

		if($wrap){
			$img = $mail->createAttachment(
					file_get_contents(ROOT_PATH.'public_html/browser/front/i/logo.jpg'), 
					'image/png', 
					Zend_Mime::DISPOSITION_INLINE, 
					Zend_Mime::ENCODING_BASE64
					);
			$img->id = 'logomail';
		}

		$mail->send();
	}	
		
	private function wrap_text($text){
		ob_start();
		require(ROOT_PATH.'tpl/mail/wrap.html');
		return ob_get_clean();
	}
	
	private function to_emails($emails){
		$emails = (is_array($emails)) ? $emails : array($emails);
		
		foreach($emails as $k => $v)
			if(!$v || !filter_var($v, FILTER_VALIDATE_EMAIL))
				unset($emails[$k]);
		
		return $emails;		
	}
	
	private function set_encoding($string,$encoding){
		if($encoding=='UTF-8') return $string;
		
		return iconv('utf-8',$encoding.'//IGNORE',$string);
	}
	
	private function attachment($attach){
		if(!$attach) return false;
		
		$at = new Zend_Mime_Part($attach);
		$at->disposition = Zend_Mime::DISPOSITION_INLINE;
		$at->encoding = Zend_Mime::ENCODING_BASE64;
		$at->filename = 'kvitancija.pdf';

		return $at;
	}
			
}
?>