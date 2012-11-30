<?php defined('SYSPATH') OR die('No direct access allowed.');

class Helper_Crossbrowser {
	public static function img_size($raw_width, $raw_height, $width, $height, $scale=1){
		if($width==-1){
		// width is not set
			$width = $raw_width;
			$height= $raw_height;
		}else if($width>0 && $width<1){
		//width between 0.1-9.9, overload width as scale
			$scale = $width;
			$width = round($raw_width*$scale);
			$height = round($raw_height*$scale);
		}else if($height==-1 && $width>=1){
		//auto resize by set width with number
			$height = round($raw_height*($width/$raw_width));
		}else if($width==-1 && $height>=1){
		//auto resize by set height with number
			$width = round($raw_width*($height/$raw_height));
		}
		
		return array('width' => $width, 'height' => $height);
	}
	
	public static function img_url($filename, $width=-1, $height=-1, $crop=FALSE, $portrait=FALSE){
		$scale = 1;
		list($raw_width, $raw_height)= getimagesize(DOCROOT.$filename);
		
		$dimension = Helper_Crossbrowser::img_size($raw_width, $raw_height, $width, $height, $scale);
		$resized_image_path=URL::site('imagefly/w'.$dimension['width'].'-h'.$dimension['height'].($crop?'-c':'').($portrait?'-p10':'').'/'.$filename);
		
		return $resized_image_path;
	}
	
    public static function img($url,$width=-1,$height=-1,$crop=FALSE, $portrait=FALSE){
		$request = Request::instance();
		$browser = $request->user_agent('browser');
		$version = $request->user_agent('version');

		$scale = 1;
		$html = array();

		//allow dimension as point for scaling.
		list($raw_width, $raw_height)= getimagesize(DOCROOT.$url);

		if($width==-1){
		// width is not set
			$width = $raw_width;
			$height= $raw_height;
		}else if($width>0 && $width<1){
		//width between 0.1-9.9, overload width as scale
			$scale = $width;
			$width = round($raw_width*$scale);
			$height = round($raw_height*$scale);
		}else if($height==-1 && $width>=1){
		//auto resize by set width with number
			$height = round($raw_height*($width/$raw_width));
		}else if($width==-1 && $height>=1){
		//auto resize by set height with number
			$width = round($raw_width*($height/$raw_height));
		}

		$resized_image_path=URL::site('imagefly/w'.$width.'-h'.$height.($crop?'-c':'').($portrait?'-p10':'').'/'.$url);
		//only for IE and only render png
		if((preg_match('/.png/',$url)>0) && ($browser=='Internet Explorer')){
			$version = explode('.',$version);
			switch($version[0]){
				case(5):
					return '[png not support]';
					break;
				case(6):
				case(7):
					return '<img width="'.$width.'" height="'.$height.'" src="'.URL::site('media/images/spacer.gif').'" style="background-image: none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\''.$resized_image_path.'\', sizingMethod=\'crop\');"/>';
					break;
				case(8):
					break;
			}
		}

		return '<img width="'.$width.'" height="'.$height.'" src="'.$resized_image_path.'"/>';
    }

	public static function center_img($url,$width,$height,$attr=array()){
		$resized_image_path=URL::site('imagefly/w'.$width.'-h'.$height.'/'.$url,TRUE);
		$attr['width'] = $width;
		$attr['height'] = $height;
		
		return
		'<div style="background:url(\''.$resized_image_path.'\') no-repeat center center">'.
		HTML::image('media/images/spacer.gif', $attr).
		'</div>';
	}

	public static function style_background($url,$attr){
		$html = array();

		if((preg_match('/.png/',$url)>0) && (Request::instance()->user_agent('browser')=='Internet Explorer')){
			$version = explode('.',Request::instance()->user_agent('version'));

			list($width, $height)= getimagesize(DOCROOT.$url);

			switch($version[0]){
				case(5):
					return '';
					break;
				case(6):
				case(7):
					return 'width:'.$width.'px; height:'.$height.'px; background-image: none '.$attr.'; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\''.url::base().$url.'\');';
					break;
				case(8):
					break;
			}
		}

		return 'background: url('.url::base().$url.') '.$attr;
	}

	public static function table($cells,$attr=array()){
		if(Request::instance()->user_agent('browser')=='Internet Explorer'){
			$version = explode('.',Request::instance()->user_agent('version'));
			$html = array();
			switch($version[0]){
				case(5):
				case(6):
				case(7):
					$table_tag = '<table border="0" cellspacing="0" cellpadding="0" ';
					foreach($attr as $key=>$value){
						$table_tag .= $key.'="'.$value.'" ';
					}
					$html[] = $table_tag.'>';

					foreach($cells as $row){
						$html[] = '<tr>';
							foreach($row as $cell){
								$cell_tag = '<td ';
								if(isset($cell['attr'])){
									foreach($cell['attr'] as $key=>$value){
										$cell_tag .= $key.'="'.$value.'" ';
									}
								}
								$html[] = $cell_tag.'>';
								$html[] = $cell['text'];
								$html[] = '</td>';
							}
						$html[] = '</tr>';
					}
					$html[] = '</table>';
					return implode(PHP_EOL,$html);
					break;
			}
		}

		$html = array();
		$html[] = '<div class="table">';

		foreach($cells as $row){
				foreach($row as $cell){
					$cell_tag = '<div ';
					if(isset($cell['attr'])){
						foreach($cell['attr'] as $key=>$value){
							$cell_tag .= $key.'="'.$value.'" ';
						}
					}
					$html[] = $cell_tag.'>';
					$html[] = $cell['text'];
					$html[] = '</div>';
				}
		}
		$html[] = '</div>';
		return implode(PHP_EOL,$html);
	}
	
	public static function load_template_style($url){
		//TODO:external url skip checking

		$url = 'media/'.$url;
		$file = DOCROOT.$url;
		if(file_exists($file)){
			Helper_Template::instance()->add_style($url);
		}
		//fix IE style
		if(Request::instance()->user_agent('browser')=='Internet Explorer'){
			$version = explode('.',Request::instance()->user_agent('version'));
			switch($version[0]){
				case(5):
					if(file_exists($file.'.ie5.css')){Helper_Template::instance()->add_style($url.'.ie5.css');}
					break;
				case(6):
					if(file_exists($file.'.ie6.css')){Helper_Template::instance()->add_style($url.'.ie6.css');}
					break;
				case(7):
					if(file_exists($file.'.ie6.css')){Helper_Template::instance()->add_style($url.'.ie6.css');}
					if(file_exists($file.'.ie7.css')){Helper_Template::instance()->add_style($url.'.ie7.css');}
					break;
				case(8):
					if(file_exists($file.'.ie8.css')){Helper_Template::instance()->add_style($url.'.ie8.css');}
					break;
			}
		}
	}

	public static function load_template_script($url){
		//TODO:external url skip checking
		$url = 'media/'.$url;

		$file = DOCROOT.$url;

		if(file_exists($file)){
			Helper_Template::instance()->add_script($url);
		}
		//fix IE style
		if(Request::instance()->user_agent('browser')=='Internet Explorer'){
			$version = explode('.',Request::instance()->user_agent('version'));
			switch($version[0]){
				case(5):
					if(file_exists($file.'.ie5.js')){Helper_Template::instance()->add_script($url.'.ie5.js');}
					break;
				case(6):
					if(file_exists($file.'.ie6.js')){Helper_Template::instance()->add_script($url.'.ie6.js');}
					break;
				case(7):
					if(file_exists($file.'.ie6.js')){Helper_Template::instance()->add_script($url.'.ie6.js');}
					if(file_exists($file.'.ie7.js')){Helper_Template::instance()->add_script($url.'.ie7.js');}
					break;
				case(8):
					if(file_exists($file.'.ie8.js')){Helper_Template::instance()->add_script($url.'.ie8.js');}
					break;
			}
		}
	}
}
?>