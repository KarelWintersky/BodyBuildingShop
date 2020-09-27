<?php

class Settings_Indexes
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_settings_indexes', $this );
    }
    
    public function item_rq($name, $a = NULL)
    {
        require($this->registry[ 'template' ]->TF.'item/settings/indexes/'.$name.'.html');
    }
    
    public function index_find(&$html, &$count)
    {
        $html = '';
        $count = 0;
        if (!isset( $_GET[ 'index' ] )) return false;
        
        $indexes = array();
        $qLnk = mysql_query( sprintf( "
					SELECT
						*
					FROM
						indexes
					WHERE
						ind = '%s' OR ind_old = '%s';
					",
            $_GET[ 'index' ], $_GET[ 'index' ]
        ) );
        $count = mysql_num_rows( $qLnk );
        while ($i = mysql_fetch_assoc( $qLnk )) $indexes[] = $i;
        
        if (count( $indexes ) == 0) $indexes[] = array(
            'id' => md5( time() ),
        );
        
        ob_start();
        foreach ($indexes as $i) {
            $this->item_rq( 'index', $i );
        }
        
        $html = ob_get_clean();
    }
    
    public function sav_indexes()
    {
        if (!isset( $_POST[ 'indexes' ] )) return false;
        
        foreach ($_POST[ 'indexes' ] as $id => $data) {
            if (is_numeric( $id )) {
                
                if (isset( $data[ 'del' ] )) {
                    mysql_query( sprintf( "DELETE FROM indexes WHERE id = '%d'", $id ) );
                    continue;
                }
                
                mysql_query( sprintf( "
							UPDATE
								indexes
							SET
								ind = '%s',
								ind_old = '%s',
								actual = '%s',
								time_ogr = '%s',
								kol_str = '%s',
								type_ogr = '%s',
								type_dost = '%s',
								region = '%s',
								rajon = '%s',
								city = '%s',
								tarif_pos_basic = '%s',
								tarif_pos_add = '%s',
								tarif_band_basic = '%s',
								tarif_band_add = '%s',
								tarif_post_avia_pos = '%s',
								tarif_avia_pos = '%s',
								tarif_post_avia_band = '%s',
								tarif_avia_band = '%s'
							WHERE
								id = '%d'
							",
                    $data[ 'ind' ],
                    $data[ 'ind_old' ],
                    $data[ 'actual' ],
                    $data[ 'time_ogr' ],
                    $data[ 'kol_str' ],
                    $data[ 'type_ogr' ],
                    $data[ 'type_dost' ],
                    $data[ 'region' ],
                    $data[ 'rajon' ],
                    $data[ 'city' ],
                    $data[ 'tarif_pos_basic' ],
                    $data[ 'tarif_pos_add' ],
                    $data[ 'tarif_band_basic' ],
                    $data[ 'tarif_band_add' ],
                    $data[ 'tarif_post_avia_pos' ],
                    $data[ 'tarif_avia_pos' ],
                    $data[ 'tarif_post_avia_band' ],
                    $data[ 'tarif_avia_band' ],
                    $id
                ) );
            } else {
                mysql_query( sprintf( "
							INSERT INTO
								indexes
								(
								ind,
								ind_old,
								actual,
								time_ogr,
								kol_str,
								type_ogr,
								type_dost,
								region,
								rajon,
								city,
								tarif_pos_basic,
								tarif_pos_add,
								tarif_band_basic,
								tarif_band_add,
								tarif_post_avia_pos,
								tarif_avia_pos,
								tarif_post_avia_band,
								tarif_avia_band							
								)
								VALUES
								(
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s'								
								)
							",
                    $data[ 'ind' ],
                    $data[ 'ind_old' ],
                    $data[ 'actual' ],
                    $data[ 'time_ogr' ],
                    $data[ 'kol_str' ],
                    $data[ 'time_ogr' ],
                    $data[ 'type_dost' ],
                    $data[ 'region' ],
                    $data[ 'rajon' ],
                    $data[ 'city' ],
                    $data[ 'tarif_pos_basic' ],
                    $data[ 'tarif_pos_add' ],
                    $data[ 'tarif_band_basic' ],
                    $data[ 'tarif_band_add' ],
                    $data[ 'tarif_post_avia_pos' ],
                    $data[ 'tarif_avia_pos' ],
                    $data[ 'tarif_post_avia_band' ],
                    $data[ 'tarif_avia_band' ]
                ) );
            }
        }
        
    }
    
    public function upload_indexes()
    {
        
        //die('Проверить механизм импорта индексного файла чтобы он не перетирал добавленные руками индексы');
        
        if (isset( $_FILES[ 'indexes' ] ) && is_file( $_FILES[ 'indexes' ][ 'tmp_name' ] )) {
            $q_arr = array();
            $indexes = file( $_FILES[ 'indexes' ][ 'tmp_name' ] );
            $count_lines = count( $indexes );
            
            foreach ($indexes as $key => $line) {
                $line = iconv( 'cp1251', 'UTF-8', $line );
                $la = explode( '::', $line );
                $q_arr[] = "(
					'".$la[ 0 ]."',
					'".$la[ 1 ]."',
					'".$la[ 2 ]."',
					'".$la[ 3 ]."',
					'".$la[ 4 ]."',
					'".$la[ 5 ]."',
					'".$la[ 6 ]."',
					'".$la[ 7 ]."',
					'".$la[ 8 ]."',
					'".$la[ 9 ]."',
					'".$la[ 10 ]."',
					'".$la[ 11 ]."',
					'".$la[ 12 ]."',
					'".$la[ 13 ]."',
					'".$la[ 14 ]."',
					'".$la[ 15 ]."',
					'".$la[ 16 ]."',
					'".$la[ 17 ]."'
					)";
            }
            
            mysql_query( "TRUNCATE TABLE indexes;" );
            foreach ($q_arr as $l) {
                mysql_query( "
							INSERT INTO
							indexes
							(ind,
							ind_old,
							actual,
							kol_str,
							region,
							rajon,
							city,
							type_ogr,
							tarif_pos_basic,
							tarif_pos_add,
							tarif_band_basic,
							tarif_band_add,
							type_dost,
							time_ogr,
							tarif_post_avia_pos,
							tarif_avia_pos,
							tarif_post_avia_band,
							tarif_avia_band)
							VALUES
							".$l."
							" );
            }
            
        }
    }
    
}
