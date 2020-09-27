<?php

class Front_Order_Data_Delivery_Zipcode
{
    
    public static function get_zipcode_data($zipcode)
    {
        $zipcode_data = self::get_query( $zipcode );
        
        return $zipcode_data;
    }
    
    public static function get_query($zipcode)
    {
        $zip_code = trim( $zipcode );
        if (!$zipcode) return false;
        
        $qLnk = mysql_query( sprintf( "
				SELECT
					ind,
					region,
					city,
					type_ogr,
					type_dost,
					tarif_pos_basic,
					tarif_pos_add,
					tarif_band_basic,
					tarif_band_add,
					type_dost,
					tarif_post_avia_pos,
					tarif_avia_pos,
					tarif_post_avia_band,
					tarif_avia_band,
					city,
					IF(city LIKE '%%Санкт-Петербург%%',1,0) AS is_spb
				FROM
					indexes
				WHERE
					(ind = '%s'
					OR
					ind_old = '%s')
					AND
					DATE(NOW())
						BETWEEN
						DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',1),'.',1)))
						AND
DATE(CONCAT_WS('-',YEAR(NOW()),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',-1),'.',-1),SUBSTRING_INDEX(SUBSTRING_INDEX(time_ogr,'-',-1),'.',1)))
				",
            mysql_real_escape_string( $zipcode ),
            mysql_real_escape_string( $zipcode )
        ) );
        $data = mysql_fetch_assoc( $qLnk );
        
        return $data;
    }
    
}

