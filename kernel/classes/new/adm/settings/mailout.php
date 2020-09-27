<?php

class Adm_Settings_Mailout
{

    private $registry;

    private $file;

    private $Front_Template_Links;
    private $Front_Template_Images;
    /**
     * @var string
     */
    private $root_path;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->root_path = ROOT_PATH;

        $this->file = ROOT_PATH.'files/news_work_file.txt';

        $this->Front_Template_Links = new Front_Template_Links( $this->registry );
        $this->Front_Template_Images = new Front_Template_Images( $this->registry );
    }

    public function do_mailout()
    {
        $news_str = file_get_contents( $this->file );

        $news_d = explode( '::', $news_str );

        $start_time = date( 'd.m.Y H:i' );

        $qLnk = mysql_query( "SELECT users.id, users.name, users.email FROM users WHERE users.get_news = 1;" );
        $count = 0;
        while ($u = mysql_fetch_assoc( $qLnk )) {

            //$news_text = preg_replace('/\v+|\\\[rn]/','<br/>',$news_d[1]);
            $news_text = $news_d[ 1 ];

            $replace_arr = array(
                'USER_NAME' => $u[ 'name' ],
                'USER_ID' => $u[ 'id' ],
                'NEWS_TEXT' => $news_text,
                'NEWS_TOPIC' => $news_d[ 0 ],
            );

            $mailer = new Mailer( $this->registry, 6, $replace_arr, $u[ 'email' ], false, 'windows-1251' );

            $count++;
        }

        $fin_time = date( 'd.m.Y H:i' );

        $emails = explode( '::', ADMINS_EMAILS );
        if (count( $emails ) > 0) {
            foreach ($emails as $admin_mail) {
                $replace_arr = array(
                    'MAIL_CHAIN_NAME' => '«Новости»',
                    'COUNT' => $count,
                    'MAIL_CHAIN_START' => $start_time,
                    'MAIL_CHAIN_FIN' => $fin_time,
                    'MAIL_TOPICS' => $news_d[ 0 ],
                );

                $mailer = new Mailer( $this->registry, 10, $replace_arr, $admin_mail );
            }
        }

    }

    public function mailout_initiate()
    {
        foreach ($_POST as $key => $val) $$key = $val;

        $news_text = $this->Front_Template_Links->do_links( $news_text );
        $news_text = $this->Front_Template_Images->do_images( $news_text );

        file_put_contents(
            $this->file,
            $news_topic.'::'.$news_text
        );

        //@todo: path
        exec( "/usr/bin/php {$this->root_path}kernel/cron.php do_news", $output );
    }

}

