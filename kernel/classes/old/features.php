<?php

class Features
{
    
    private $registry;
    
    public function __construct($registry, $frompage = true)
    {
        $this->registry = $registry;
        
        if ($frompage) {
            $route = $this->registry[ 'aias_path' ];
            array_shift( $route );
            
            if (count( $route ) == 0) {
                $this->registry[ 'f_404' ] = false;
                $this->registry[ 'template' ]->set( 'c', 'features/main' );
            }
        }
        
    }
    
    private function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/features'.DIRSEP.$name.'.html');
    }
    
    public function features_group_list()
    {
        $qLnk = mysql_query( "
								SELECT
									feature_groups.*
								FROM
									feature_groups
								ORDER BY
									feature_groups.name ASC;
								" );
        while ($g = mysql_fetch_assoc( $qLnk )) {
            $this->item_rq( 'group', $g );
        }
    }
    
    public function features_list($group_id, &$count, &$html, &$next_sort)
    {
        $qLnk = mysql_query( "
								SELECT
									features.*
								FROM
									features
								WHERE
									features.group_id = '".$group_id."'
								ORDER BY
									features.sort ASC;
								" );
        $count = mysql_num_rows( $qLnk );
        ob_start();
        $i = 1;
        while ($f = mysql_fetch_assoc( $qLnk )) {
            $f[ 'sort' ] = $i;
            $this->item_rq( 'feature', $f );
            $i++;
        }
        $next_sort = $i;
        $html = ob_get_contents();
        ob_end_clean();
    }
    
    public function form_tog_display($tog_id, $t)
    {
        if (isset( $_COOKIE[ 'form_tog_opened' ][ $this->registry[ 'userdata' ][ 'id' ] ][ $tog_id ] )) {
            return ($t == 0) ? 'свернуть' : 'block';
        } else {
            return ($t == 0) ? 'развернуть' : 'none';
        }
    }
    
    public function del_group()
    {
        foreach ($_POST as $key => $val) {
            $$key = (is_array( $val )) ? $val : mysql_real_escape_string( $val );
        }
        
        $photomanager = new Photomanager( $this->registry );
        
        $qLnk = mysql_query( "
								SELECT
									features.image AS alias
								FROM
									features
								WHERE
									features.group_id = '".$group_id."'
									AND
									features.image <> '';
								" );
        while ($f = mysql_fetch_assoc( $qLnk )) {
            $photomanager->unlink_feat_photo( $f[ 'alias' ] );
        }
        
        mysql_query( "DELETE FROM goods_features WHERE goods_features.feature_id IN (SELECT features.id FROM features WHERE features.group_id = '".$group_id."');" );
        mysql_query( "DELETE FROM feature_groups WHERE feature_groups.id = '".$group_id."';" );
        mysql_query( "DELETE FROM features WHERE features.group_id = '".$group_id."';" );
        
    }
    
    public function upd_group()
    {
        foreach ($_POST as $key => $val) {
            $$key = (is_array( $val )) ? $val : mysql_real_escape_string( $val );
        }
        
        mysql_query( "UPDATE feature_groups SET feature_groups.name = '".$name."' WHERE feature_groups.id = '".$group_id."';" );
    }
    
    public function add_feat()
    {
        foreach ($_POST as $key => $val) {
            $$key = (is_array( $val )) ? $val : mysql_real_escape_string( $val );
        }
        
        $photomanager = new Photomanager( $this->registry );
        $image = $photomanager->new_feat_photo();
        
        mysql_query( "
						INSERT INTO
							features
							(name,
								group_id,
									image,
										sort)
							VALUES
							('".$name."',
								'".$group_id."',
									'".$image."',
										'".$sort."');
						" );
    }
    
    public function sav_feat()
    {
        foreach ($_POST as $key => $val) {
            $$key = (is_array( $val )) ? $val : mysql_real_escape_string( $val );
        }
        
        $del_arr = array();
        foreach ($feature as $id => $arr) {
            if (isset( $arr[ 'del' ] )) {
                $del_arr[] = $id;
            }
        }
        
        $photomanager = new Photomanager( $this->registry );
        $feat_photo = $photomanager->upload_feat_photo( $del_arr );
        
        foreach ($feature as $id => $arr) {
            if (isset( $arr[ 'del' ] )) {
                mysql_query( "DELETE FROM features WHERE features.id = '".$id."';" );
                mysql_query( "DELETE FROM goods_features WHERE goods_features.feature_id = '".$id."';" );
                $photomanager->unlink_feat_photo( $arr[ 'old_image' ] );
            } else {
                
                $image = (isset( $feat_photo[ $id ] ) && $arr[ 'old_image' ] != $feat_photo[ $id ]) ? $feat_photo[ $id ] : $arr[ 'old_image' ];
                
                mysql_query( sprintf( "
								UPDATE
									features
								SET
									features.name = '".$arr[ 'name' ]."',
									features.sort = '".$arr[ 'sort' ]."',
									features.image = '".$image."',
									features.delimiter = '%d'
								WHERE
									features.id = '".$id."';
								",
                    (isset( $arr[ 'delimiter' ] )) ? 1 : 0
                ) );
            }
            
        }
        
    }
    
    public function add_group()
    {
        foreach ($_POST as $key => $val) {
            $$key = (is_array( $val )) ? $val : mysql_real_escape_string( $val );
        }
        
        mysql_query( "
						INSERT INTO
							feature_groups
							(name)
							VALUES
							('".$name."');
						" );
    }
    
}
