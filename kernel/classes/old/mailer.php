<?
	Class Mailer{

		private $registry;
		private $mail_subject;
		private $mail_text;
		private $replace_arr;
		private $to;

		public function __construct($registry,$tpl_id,$replace_arr,$to,$attach = false,$encoding='UTF-8'){

			$this->registry = $registry;

			set_include_path(ROOT_PATH.'kernel/libs/');
			require_once('Zend/Mail.php');

			$this->replace_arr = $this->add2replacearr($replace_arr);
			$this->to = $to;

			$this->get_tpl($tpl_id,$encoding);

			$mail =  new Zend_Mail($encoding);

				$mail->setSubject($this->mail_subject);
				$mail->addTo($this->to);

				if($tpl_id==3){
					$mail->setFrom($replace_arr['F_EMAIL'], $replace_arr['F_NAME']);
				}else{
					$from_name = ($encoding=='UTF-8') ? 'Бодибилдинг Магазин' : iconv('utf-8',$encoding,'Бодибилдинг Магазин');
					$mail->setFrom('no-reply@bodybuilding-shop.ru', $from_name);
				}

				$mail->setBodyHtml($this->mail_text);

				/*картинка в шапку*/
				if($tpl_id!=12 && $tpl_id!=27 && $tpl_id!=3 && $tpl_id!=28 && $tpl_id!=29 && $tpl_id!=30 && $tpl_id!=31 && $tpl_id!=32): //для служебного письма не нужно
					$idata = file_get_contents('http://www.bodybuilding-shop.ru/public/tpl/mail/logo.jpg');
					//$idata = file_get_contents(THIS_URL.'public/tpl/mail/logo.jpg');
					$img = $mail->createAttachment($idata, 'image/png', Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64);
					$img->id = 'logomail';
				endif;
				/*картинка в шапку end*/

				if($attach){
					$at = new Zend_Mime_Part($attach);
						$at->disposition = Zend_Mime::DISPOSITION_INLINE;
						$at->encoding = Zend_Mime::ENCODING_BASE64;
						$at->filename = 'kvitancija.pdf';

					$mail->addAttachment($at);
				}

				if(filter_var($this->to,FILTER_VALIDATE_EMAIL)){
					$mail->send();
				}

		}

		private function add2replacearr($replace_arr){
			$replace_arr['SITE_URL'] = THIS_URL;
			return $replace_arr;
		}

		private function get_tpl($tpl_id,$encoding){
			$qLnk = mysql_query("SELECT mailtpls.* FROM mailtpls WHERE mailtpls.id = '".$tpl_id."';");
			$tpl_data = mysql_fetch_assoc($qLnk);

			$subject_tpl = ($encoding=='UTF-8') ? $tpl_data['subject'] : iconv('utf-8',$encoding.'//IGNORE',$tpl_data['subject']);

			$this->mail_subject = $this->mk_subject($subject_tpl);

			$tpl_content = ($encoding=='UTF-8') ? $tpl_data['content'] : iconv('utf-8',$encoding.'//IGNORE',$tpl_data['content']);
			$this->mail_text = $this->mk_tpl_text($tpl_content,$tpl_id);

		}

		private function mk_subject($subject){
			foreach($this->replace_arr as $find => $replace){
				$subject = str_replace('{'.$find.'}', $replace, $subject);
			}
			return $subject;
		}

		private function mk_tpl_text($tpl_text,$tpl_id){
			foreach($this->replace_arr as $find => $replace){
				$tpl_text = str_replace('{'.$find.'}', $replace, $tpl_text);
			}

			$style_replace = array(
				'<p class="order_num">' => '<p style="font-size:18px;color:#000;margin-bottom:0;">',
				'<p class="order_date">' => '<p style="font-size:12px;color:#999;margin-top:6px;">',
				'<div class="user_contacts">' => '<div style="border:1px solid #ccc;background:#f2f2f2;padding:20px 40px;margin:10px 0 25px 0;color:#343434;">',
				'<p class="user_address_hint">' => '<p style="color:#999;font-size:11px;border-bottom:1px dashed #b2b2b2;padding-bottom:18px;">',
				'<th>' => '<th style="background:#f2f2f2;border:1px solid #ccc;color:#999;text-align:center;font-weight:normal;padding:5px 0;">',
				'<td class="td_goods"' => '<td style="border:1px solid #ccc;padding:5px 0 5px 5px;text-align:left;"',
				'<td class="td_right"' => '<td style="border:1px solid #ccc;padding:5px 5px 5px 0;text-align:right;"',
				'<div class="pass_hint">' => '<div style="border:1px solid #ccc;background:#f2f2f2;padding:10px 40px;margin:20px 0 25px 0;color:#343434;">',
				'<p class="pass_hint_notice">' => '<p style="color:#999;font-size:11px;margin-top:10px;">',
			);

			if($tpl_id!=12 && $tpl_id!=27 && $tpl_id!=3 && $tpl_id!=28 && $tpl_id!=29 && $tpl_id!=30 && $tpl_id!=31 && $tpl_id!=32): //письмо с параметрами должно уходить без стилей
				foreach($style_replace as $find => $replace){
					$tpl_text = str_replace($find, $replace, $tpl_text);
				}
				ob_start();
				require(ROOT_PATH.'public/tpl/mail/common.html');
				$tpl_html = ob_get_contents();
				ob_end_clean();
			else:
				$tpl_html = $tpl_text;
			endif;

			return $tpl_html;
		}

	}
?>