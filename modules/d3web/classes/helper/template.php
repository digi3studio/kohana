<?php
defined('SYSPATH') or die('No direct script access.');

//Common class for displaying template
//this class is newer than amstyle template
class Helper_Template {
	private static $ins;
	private $seed;

	public $title;
	public $scripts;
	public $ext_scripts;
	public $styles;
	public $ext_styles;//style from external links;
	public $l10n_styles;
	public $metatags;

	public static function instance()
	{
		if (!self::$ins){
			self::$ins = new Helper_Template();
		}
		return self::$ins;
	}

    private function __construct(){
		$this->seed = rand(0,10000);
		$this->scripts 	= array();
		$this->ext_scripts= array();

		$this->styles 	= array();
		$this->ext_styles = array();
		$this->l10n_styles= array();

		$this->metatags	= array();

		$this->title = Kohana::config(Kohana::$environment.'.site.title');
    }

	public function error_message($errors){
		$html = '';
		foreach($errors as $i){
			$html .= '<div>'.$i.'</div>';
		}
		return $html;
	}

	public function add_locale_style($str,$city,$lang){
		//eg, $str = 'site/css/t.css'
		//check stylesheet at locale
		$file_lookups = array();
		$file_lookups[] = $str;
		if($city!='asia')$file_lookups[] = 'asia/'.$lang.'/'.$str;
		$file_lookups[] = $city.'/'.$lang.'/'.$str;

		$media = DOCROOT.'media/';
		foreach($file_lookups as $locale_css){
			if(file_exists($media.$locale_css)){Helper_Template::instance()->add_style($locale_css);}
		}
	}

	public function add_style($str){
		//add style to this->styles 
		$aryStyle = &$this->styles;
		//if the style is external, add to this->ext_styles,
		if(preg_match('/http:\/\//',$str)>0){
			$aryStyle = &$this->ext_styles;
		}

		//do not duplicate the stylesheet in array
		foreach($aryStyle as $style){
			if($style == $str.'?r='.$this->seed)return;
		}

		//store the stylesheet
		$aryStyle[] = $str.'?r='.$this->seed;
	}

	public function remove_style($str){
		//use this->styles 
		$aryStyle = &$this->styles;
		//if the style is external, use this->ext_styles,
		if(preg_match('/http:\/\//',$str)>0){
			$aryStyle = &$this->ext_styles;
		}

		foreach($aryStyle as $key=>$style){
			if($style == $str.'?r='.$this->seed){
				//remove the style from style array
				unset($aryStyle[$key]);
				return;
			}
		}
	}

	public function add_script($str){
		//add style to this->styles 
		$aryScript = &$this->scripts;
		//if the style is external, add to this->ext_styles,
		if(preg_match('/http:\/\//',$str)>0){
			$aryScript = &$this->ext_scripts;
		}

		//append random number with & or ?
		$key = (strrpos($str,'?')==FALSE)?'?':'&';

		//do not duplicate the stylesheet in array
		foreach($aryScript as $script){
			if($script == $str.$key.'r='.$this->seed)return;
		}

		$aryScript[] = $str.$key.'r='.$this->seed;
	}

	public function add_meta($str){
		$this->metatags[] = $str;
	}

	public function summary($text,$limit){
		return Helper_String::trim_text($text,$limit,true,true);
	}
}
?>