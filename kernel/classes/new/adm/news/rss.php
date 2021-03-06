<?

class Adm_News_Rss
{
    
    private $registry;
    
    private $RSS;
    private $file;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->file = ROOT_PATH.'public_html/rss.xml';
    }
    
    private function get_data()
    {
        $news = array();
        $qLnk = mysql_query( "
					SELECT
						*
					FROM
						news
					WHERE
						published = 1
						AND
						rss = 1
					ORDER BY
						date DESC;
					" );
        while ($n = mysql_fetch_assoc( $qLnk )) $news[] = $n;
        
        return $news;
    }
    
    public function do_rss()
    {
        $news = $this->get_data();
        
        $this->RSS = new DOMDocument( '1.0', 'UTF-8' );
        $this->RSS->formatOutput = true;
        
        //общий контейнер
        $this->container = $this->RSS->createElement( 'rss' );
        $this->container->appendChild(
            $this->RSS->createAttribute( 'version' ) )->appendChild(
            $this->RSS->createTextNode( '2.0' )
        );
        $this->RSS->appendChild( $this->container );
        
        //канал
        $this->channel = $this->RSS->createElement( 'channel' );
        $this->container->appendChild( $this->channel );
        
        //свойства канала
        $title = $this->RSS->createElement( 'title', 'Спортивное питание' );
        $this->channel->appendChild( $title );
        
        $link = $this->RSS->createElement( 'link', THIS_URL );
        $this->channel->appendChild( $link );
        
        $description = $this->RSS->createElement( 'description', 'Магазин Спортивного Питания' );
        $this->channel->appendChild( $description );
        
        $language = $this->RSS->createElement( 'language', 'ru' );
        $this->channel->appendChild( $language );
        
        $lastBuildDate = $this->RSS->createElement( 'lastBuildDate', $this->prepare_date( $news[ 0 ][ 'date' ] ) );
        $this->channel->appendChild( $lastBuildDate );
        
        $this->print_news( $news );
        
        $this->RSS->save( $this->file );
    }
    
    private function print_news($news)
    {
        foreach ($news as $n) {
            
            $item = $this->RSS->createElement( 'item' );
            
            $title = $this->RSS->createElement( 'title', $n[ 'name' ] );
            $item->appendChild( $title );
            $link = $this->RSS->createElement( 'link', $this->prepare_url( $n ) );
            $item->appendChild( $link );
            $description = $this->RSS->createElement( 'description', $this->prepare_content( $n[ 'content' ] ) );
            $item->appendChild( $description );
            $pubDate = $this->RSS->createElement( 'pubDate', $this->prepare_date( $n[ 'date' ] ) );
            $item->appendChild( $pubDate );
            
            $this->channel->appendChild( $item );
            
        }
    }
    
    private function mk_description($news)
    {
        if ($news[ 'introtext' ]) $content = $news[ 'introtext' ];
        else {
            $content = explode( '</p>', $news[ 'content' ] );
            $content = str_replace( '<p>', '', $content[ 0 ] );
        }
        
        return $this->prepare_content( $content );
    }
    
    private function prepare_url($n)
    {
        return ($n[ 'type' ] == 1)
            ? sprintf( '%snews/%s/',
                THIS_URL,
                $n[ 'alias' ]
            )
            : sprintf( '%s%s/',
                THIS_URL,
                $n[ 'alias' ]
            );
    }
    
    private function prepare_date($date)
    {
        $months = array(
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        );
        
        $arr = explode( ' ', $date );
        $day = explode( '-', $arr[ 0 ] );
        $time = explode( ':', $arr[ 1 ] );
        
        $result = date( 'D', strtotime( $date ) ).', '.$day[ 2 ].' '.$months[ $day[ 1 ] ].' '.$day[ 0 ].' '.$time[ 0 ].':'.$time[ 1 ].':'.$time[ 2 ].' +0300';
        
        return $result;
    }
    
    private function prepare_content($content)
    {
        $content = htmlspecialchars_decode( $content );
        
        $replace = array(
            '&laquo;' => '«',
            '&raquo;' => '»',
            '&ndash;' => '-',
            '&nbsp;' => ' ',
            '&eacute;' => '',
            '&uuml;' => '',
            '&ouml;' => '',
            '&iacute;' => '',
            '&oacute;' => '',
            '&aacute;' => '',
            '&Aacute;' => '',
            '&auml;' => '',
            '&hellip;' => '',
            '&Ccedil;' => '',
            '&ccedil;' => '',
            '&euro;' => '',
        );
        
        foreach ($replace as $s => $r) $content = str_replace( $s, $r, $content );
        
        $content = $this->strip_imgs( $content );
        $content = strip_tags( $content );
        
        return $content;
    }
    
    private function strip_imgs($content)
    {
        $reg = "/<img .+>/i";
        $content = preg_replace( $reg, '', $content );
        
        return $content;
    }
    
}

