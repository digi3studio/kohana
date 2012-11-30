<?php 
class Helper_String{

	//For UTF-8 encoding only, modified from http://www.linux-field.com/archives/468
	//added ellipses and strip_html support
	public static function trim_text($input, $length = NULL, $ellipses = true, $strip_html = true){
		$input = html_entity_decode($input, ENT_COMPAT, 'UTF-8');
		//strip tags, if desired
		if ($strip_html) {
			$input = strip_tags($input);
		}

		//no need to trim, already shorter than trim length
		if ((strlen($input) <= $length) || ($length == NULL)){
			$trimmed_text = trim($input);
		}else{
		//otherwise, start shorten
		//find last space within length
			$last_space = strrpos(substr($input, 0, $length), ' ');
			$trimmed_text = substr($input, 0, $last_space);
			//add ellipses (...)
			if ($ellipses) {
				$trimmed_text .= ' ...';
			}
		}

		return htmlentities($trimmed_text, ENT_COMPAT, 'UTF-8');
	}


	public static function is_valid_email($email){
		return preg_match( '/[.+a-zA-Z0-9_-]+@[a-zA-Z0-9-]+.[a-zA-Z]+/', $email);
	}

  public static function rand_password($length){
    return substr(md5(rand().rand()), 0, $length);
  }

	public static function guess_full_name($first_name, $last_name){
		if(empty($first_name)){
			return $last_name;
		}
		
		if(empty($last_name)){
			return $first_name;
		}
		
		if (!preg_match('/[^A-Za-z0-9]/', $first_name) && !preg_match('/[^A-Za-z0-9]/', $last_name)){
			//both contains only english letters & digits
			return $first_name.' '.$last_name;
		}

		//maybe asian name?
		return $last_name.$first_name;
	}

  /*
   * Append a query string to the end of the given URL, regardless of the URL format
   * e.g. http://www.example.com, http://www.example.com/a/, http://www.example.com/a/?q1=one
   */
  public static function append_query_string($url, $key, $value){
    $separator = (parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&';
    $url .= $separator . $key.'='.$value;

    return $url;
  }
}