<?php

class Photomanager
{
    
    private $registry;
    
    function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function upload_goods_photo()
    {
        if (isset( $GLOBALS[ "HTTP_RAW_POST_DATA" ] )) {
            
            $image_data = $GLOBALS[ "HTTP_RAW_POST_DATA" ];
            
            $goods_id = $_GET[ 'good_id' ];
            $sort = $this->goods_photo_max_sort( $goods_id );
            
            $alias = str_replace( '&-', '', $_GET[ 'name' ] );
            $alias = str_replace( '&', '', $alias );
            
            $i = 1;
            while (!$this->new_alias_check( $alias )) {
                $aliar_arr = explode( '.', $alias );
                $ext = array_pop( $aliar_arr );
                
                $alias = implode( '.', $aliar_arr ).'-'.$i.'.'.$ext;
                
                $i++;
            }
            
            $src_dir = $this->mk_img_dir( 'src', $goods_id );
            if (!is_dir( $src_dir )) {
                mkdir( $src_dir );
            }
            $src_full_path = $src_dir.$alias;
            
            $fp = fopen( $src_full_path, 'wb' );
            fwrite( $fp, $image_data );
            fclose( $fp );
            
            $photo_dim_pairs = explode( ',', PHOTO_DIM_STR );
            
            foreach ($photo_dim_pairs as $pair) {
                $dest_dir = $this->mk_img_dir( $pair, $goods_id );
                if (!is_dir( $dest_dir )) {
                    mkdir( $dest_dir );
                }
                
                $dest_full_path = $dest_dir.$alias;
                
                $dim_arr = explode( 'x', $pair );
                $this->image_resize( $src_full_path, $dest_full_path, intval( $dim_arr[ 0 ] ), intval( $dim_arr[ 1 ] ) );
            }
            
            mysql_query( "INSERT INTO goods_photo (alias, sort, goods_id) VALUES ('".$alias."','".$sort."','".$goods_id."')" );
            
        }
    }
    
    public function image_resize($s, $d, $w, $h)
    {
        
        $settings = new ezcImageConverterSettings(
            array(
                new ezcImageHandlerSettings( 'GD', 'ezcImageGdHandler' ),
            ),
            array( 'image/gif' => 'image/png', )
        );
        
        $converter = new ezcImageConverter( $settings );
        
        $filters = array(
            new ezcImageFilter(
                'filledThumbnail',
                array(
                    'width' => $w,
                    'height' => $h,
                    'color' => array(
                        255,
                        255,
                        255,
                    ),
                )
            ),
        );
        
        $converter->createTransformation( 'thumbnail', $filters, array( 'image/jpeg', 'image/png' ) );
        
        try {
            $converter->transform(
                'thumbnail',
                $s,
                $d
            );
        } catch (ezcImageTransformationException $e) {
            die( "Error transforming the image: <{$e->getMessage()}>" );
        }
        
    }
    
    private function mk_img_dir($dim, $goods_id)
    {
        return GOODS_PHOTO_DIR.$dim.DIRSEP.$goods_id.DIRSEP;
    }
    
    private function new_alias_check($alias)
    {
        $qLnk = mysql_query( "SELECT COUNT(*) FROM goods_photo WHERE goods_photo.alias = '".$alias."';" );
        return (mysql_result( $qLnk, 0 ) > 0) ? false : true;
    }
    
    private function goods_photo_max_sort($goods_id)
    {
        $qLnk = mysql_query( "SELECT IFNULL(MAX(goods_photo.sort)+1,1) FROM goods_photo WHERE goods_photo.goods_id = '".$goods_id."';" );
        return mysql_result( $qLnk, 0 );
    }
    
    public function unlink_images($alias, $goods_id, $fld = false)
    {
        
        $photo_dim_pairs = explode( ',', PHOTO_DIM_STR );
        $photo_dim_pairs[] = 'src';
        
        foreach ($photo_dim_pairs as $folder) {
            if ($fld) {
                $f = $this->mk_img_dir( $folder, $goods_id );
                $d = dir( $f );
                while ($entry = $d->read()) {
                    if ($entry != "." && $entry != "..") {
                        unlink( $f.$entry );
                    }
                }
                $d->close();
                rmdir( $f );
            } else {
                $f = $this->mk_img_dir( $folder, $goods_id ).$alias;
                if (is_file( $f )) {
                    unlink( $f );
                }
            }
        }
    }
    
    public function unlink_feat_photo($alias)
    {
        $f = FEAT_PHOTO_DIR.$alias;
        if (is_file( $f )) {
            unlink( $f );
        }
    }
    
    public function new_feat_photo()
    {
        $image = '';
        if ($_FILES[ 'image' ][ 'size' ] > 0) {
            $ext = mb_strtolower( end( explode( '.', $_FILES[ 'image' ][ 'name' ] ) ), 'utf-8' );
            $alias = md5( rand() + time() ).'.'.$ext;
            if (move_uploaded_file( $_FILES[ 'image' ][ 'tmp_name' ], FEAT_PHOTO_DIR.$alias )) {
                $image = $alias;
            }
        }
        return $image;
    }
    
    public function upload_feat_photo($del_arr)
    {
        
        $img_data_arr = array();
        $result_arr = array();
        
        foreach ($_FILES[ 'feature' ][ 'name' ] as $feature_id => $arr) {
            if ($arr[ 'img' ] != '' && !in_array( $feature_id, $del_arr )) {
                $img_data_arr[ $feature_id ][ 'ext' ] = mb_strtolower( end( explode( '.', $arr[ 'img' ] ) ), 'utf-8' );
            }
        }
        
        foreach ($_FILES[ 'feature' ][ 'tmp_name' ] as $feature_id => $arr) {
            if ($arr[ 'img' ] != '' && !in_array( $feature_id, $del_arr )) {
                $img_data_arr[ $feature_id ][ 'tmp_name' ] = $arr[ 'img' ];
            }
        }
        
        foreach ($img_data_arr as $feature_id => $arr) {
            $alias = md5( rand() + time() ).'.'.$arr[ 'ext' ];
            if (move_uploaded_file( $arr[ 'tmp_name' ], FEAT_PHOTO_DIR.$alias )) {
                $result_arr[ $feature_id ] = $alias;
            }
        }
        
        return $result_arr;
        
    }
    
    private function article_avatar_check($avatar, $article_id)
    {
        $qLnk = mysql_query( "
							SELECT
								COUNT(*)
							FROM
								articles
							WHERE
								articles.avatar = '".$avatar."'
								AND
								articles.id <> '".$article_id."'
							" );
        return (mysql_result( $qLnk, 0 ) > 0) ? false : true;
    }
    
    private function article_avatar_generate($avatar, $article_id)
    {
        $i = 1;
        while (!$this->article_avatar_check( $avatar, $article_id )) {
            $avatar_arr = explode( '.', $avatar );
            $ext = array_pop( $avatar_arr );
            
            $avatar = implode( '.', $avatar_arr ).'-'.$i.'.'.$ext;
            
            $i++;
        }
        return $avatar;
    }
    
    public function upload_article_avatar($old_avatar, $article_id)
    {
        if ($_FILES[ 'avatar' ][ 'size' ] > 0) {
            
            $name = $_FILES[ 'avatar' ][ 'name' ];
            $name = $this->article_avatar_generate( $name, $article_id );
            $src_full_path = $_FILES[ 'avatar' ][ 'tmp_name' ];
            
            $this->image_resize( $src_full_path, ARTICLE_PHOTO_DIR.$name, 66, 66 );
            
            return $name;
            
        }
        
        return $old_avatar;
    }
    
    public function upload_grower_avatar($old_avatar)
    {
        if ($_FILES[ 'avatar' ][ 'size' ] > 0) {
            
            $name = $_FILES[ 'avatar' ][ 'name' ];
            
            $src_full_path = $_FILES[ 'avatar' ][ 'tmp_name' ];
            
            if (move_uploaded_file( $src_full_path, GROWER_PHOTO_DIR.$name )) {
                return $name;
            }
            
        }
        
        return $old_avatar;
    }
    
    public function unlink_grower_avatar($alias)
    {
        if (is_file( GROWER_PHOTO_DIR.$alias )) {
            unlink( GROWER_PHOTO_DIR.$alias );
        }
    }
    
    public function upload_level_avatar($old_avatar)
    {
        if ($_FILES[ 'avatar' ][ 'size' ] > 0) {
            
            $name = $_FILES[ 'avatar' ][ 'name' ];
            
            $src_full_path = $_FILES[ 'avatar' ][ 'tmp_name' ];
            
            $dim_pairs = explode( ',', LEV_PHOTO_DIM_STR );
            
            foreach ($dim_pairs as $pair) {
                
                $dest_full_path = LEV_PHOTO_DIR.$pair.DIRSEP.$name;
                
                $dim_arr = explode( 'x', $pair );
                $this->image_resize( $src_full_path, $dest_full_path, intval( $dim_arr[ 0 ] ), intval( $dim_arr[ 1 ] ) );
                
            }
            
            return $name;
            
        }
        
        return $old_avatar;
    }
    
}
