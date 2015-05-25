<?
	Class Doer{

		private $registry;
		public $rp;

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('doer',$this);
			$catalog = new Catalog($this->registry,false);
			$pages = new Pages($this->registry,false);
			$features = new Features($this->registry,false);
			$growers = new Growers($this->registry,false);
			$settings = new Settings($this->registry,false);
			$settings_indexes = new Settings_Indexes($this->registry);
			$blocks = new Blocks($this->registry,false);
			$users = new Users($this->registry,false);
			$articles = new Articles($this->registry,false);
			$orders = new Adm_Orders_Save($this->registry,false);
			$accountorders = new Accountorders($this->registry,false);
			
			if(isset($_POST['d_action'])){

				$this->rp = (isset($_POST['rp'])) ? $_POST['rp'] : $_SERVER['HTTP_REFERER'];

				switch($_POST['d_action']){
					case 50:
						$catalog->sav_level();
						break;
					case 51:
						$catalog->add_level();
						break;
					case 52:
						$catalog->level_good_sort();
						break;
					case 53:
						$catalog->level_del();
						break;
					case 100:
						$catalog->sav_good();
						break;
					case 101:
						$catalog->add_good();
						break;
					case 102:
						$catalog->goods_del();
						break;
					case 103:
						$catalog->goods_photo_upd();
						break;
					case 104:
						$catalog->goods_features_upd();
						break;
					case 105:
						$catalog->goods_photo_upload();
						break;

					case 200:
						$pages->pag_sav();
						break;
					case 201:
						$pages->pag_add();
						break;
					case 202:
						$pages->pag_del();
						break;

					case 300:
						$Adm_News_Save = new Adm_News_Save($this->registry);
						$Adm_News_Save->do_save();
						break;
					case 302:
						//$news->news_del();
						break;

					case 400:
						$features->add_group();
						break;
					case 401:
						$features->sav_feat();
						break;
					case 402:
						$features->add_feat();
						break;
					case 403:
						$features->del_group();
						break;
					case 404:
						$features->upd_group();
						break;

					case 500:
						$growers->upd_grower();
						break;
					case 501:
						$growers->add_grower();
						break;
					case 502:
						$growers->del_grower();
						break;
					case 503:
						$growers->growers_sort();
						break;

					case 600:
						$settings_indexes->upload_indexes();
						break;
					case 601:
						$settings->sav_mail_tpls();
						break;
					case 602:
						$settings->sav_mail_opts();
						break;
					case 603:
						$settings->add_mail_opt();
						break;
					case 604:
						$settings->sav_params();
						break;
					case 605:
						$settings->sav_gradations();
						break;
					case 606:
						$settings->add_gradation();
						break;
					case 607:
						$settings->admin_change_type();
						break;
					case 608:
						$settings->del_user_from_maillist();
						break;
					case 609:
						$Adm_Settings_Mailout = new Adm_Settings_Mailout($this->registry);
						$Adm_Settings_Mailout->mailout_initiate();
						break;
					case 611:
						$settings->sav_module_file();
						break;
					case 612:
						$settings->yandex_market_xml();
						break;
					case 613:
						$Adm_Settings_Sitemap = new Adm_Settings_Sitemap($this->registry);
						$Adm_Settings_Sitemap->mk_files();
						break;
					case 614:
						$Adm_Prices_Excel = new Adm_Prices_Excel($this->registry);
						$Adm_Prices_Excel->make_pricelist();
						break;
					case 615:
						$settings->coupons_list_sav();
						break;
					case 616:
						$settings->coupon_add();
						break;
					case 617:
						$settings->ostatok_add_init();
						break;
					case 618:
						$settings->ostatok_blocks_add();
						break;
					case 619:
						$settings->ostatki_sav();
						break;
					case 621:
						$settings_indexes->sav_indexes();
						break;											

					case 800:
						$users->user_sav();
						break;
					case 802:
						$users->user_relogin();
						break;
					case 801:
						$accountorders->sav();
						break;

					case 900:
						$articles->articles_sort();
						break;
					case 901:
						$articles->article_sav();
						break;
					case 902:
						$articles->article_add();
						break;

					case 1000:
						$orders->order_save();
						break;
					case 1001:
						$Front_Order_Mail = new Front_Order_Mail($this->registry);
						$Front_Order_Mail->send_only_message();
						break;
					case 1002:
						$Front_Order_Mail = new Front_Order_Mail($this->registry);
						$Front_Order_Mail->send_only_bill();
						break;

					case 1100:
						$catalog->search_goods_by_id();
						break;

					case 1200:
						$accountorders->sav();
						break;
					case 1201:
						$accountorders->cancel_expired();
						break;

					case 6000:
						$f_register = new f_Register($this->registry);
						$f_register->step_2();
						break;
					case 6001:
						$f_profile = new f_Profile($this->registry);
						$f_profile->sav_profile();
						break;
					case 6002:
						$f_fyp = new f_Forgotyourpassword($this->registry);
						$f_fyp->pwd_recover();
						break;

					case 7000:
						$statistics = new Statistics($this->registry,false);
						$statistics->add_goods_stat();
					case 7001:
						$statistics = new Statistics($this->registry,false);
						$statistics->del_goods_stat();

				}

				header('Location: '.$this->rp);

			}elseif(isset($_GET['d_action'])){
				switch($_GET['d_action']){
					case 666:
						$photomanager = new Photomanager($this->registry);
						$photomanager->upload_goods_photo();
						break;
				}
			}elseif(isset($_POST['func'])){
				$Adm_Doer = new Adm_Doer($this->registry);
				$Adm_Doer->go();
			}else{
				header('Location: /');
			}

		}

		public function set_rp($rp){$this->rp = $rp;}


	}
?>