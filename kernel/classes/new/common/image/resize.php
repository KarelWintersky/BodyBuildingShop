<?php
Class Common_Image_Resize{
	
	public static function do_resize($s,$d,$w,$h,$type_id){
	
		$settings = new ezcImageConverterSettings(
				array(
						new ezcImageHandlerSettings( 'GD', 'ezcImageGdHandler' ),
				),
				array('image/gif' => 'image/png',)
				//array('image/gif' => 'image/jpeg',)
		);
	
		$converter = new ezcImageConverter( $settings );
	
		if($type_id==1){
			$filters = array(
					new ezcImageFilter(
							'croppedThumbnail',
							array(
									'width' => $w,
									'height' => $h,
									'color' => array(
											200,
											200,
											200,
									),
							)
					)
			);
	
			$converter->createTransformation('thumbnail', $filters, array( 'image/jpeg', 'image/png' ) );
			//$converter->createTransformation('thumbnail', $filters, array( 'image/jpeg' ) );
		}elseif($type_id==2){
			$filters = array(
					new ezcImageFilter(
							'scale',
							array(
									'width' => $w,
									'height' => $h,
									'direction' => ezcImageGeometryFilters::SCALE_DOWN
							)
					));
	
			$converter->createTransformation('preview', $filters, array( 'image/jpeg', 'image/png' ) );
			//$converter->createTransformation('preview', $filters, array( 'image/jpeg' ) );
		}
	
	
		try
		{
			$converter->transform(
					($type_id==1) ? 'thumbnail' : 'preview',
					$s,
					$d
			);
		}
		catch (ezcImageTransformationException $e)
		{
			die( "Error transforming the image: <{$e->getMessage()}>" );
		}
	
	}
        
}
?>