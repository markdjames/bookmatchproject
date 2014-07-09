<?php
/**
 * The core image processing class
 *
 * @package	Classes\Core
 */
class ImageOutput {
	
	public $class = "";
	public $dir = "";

	public $counter = 0;
	
	public $lazyload = false;
	
	public $quality = 'standard'; // high / standard / low accepatble
	
	/**
	 * Output an image
	 *
	 * @param	string	$src		The location of the image
	 * @param	string	$alt		The alt tag of the image
	 * @param	int		$w			The required width of the output image
	 * @param	int		$h			The required height of the output image
	 * @param	string	$styles		Any inline styles to add to the image
	 * @param	array	$crop		Array of cropping co-ordinates (x1, y1, x2, y2, w, h)
	 * @param	boolean	$src_only	Choose whether to return full image tag, or just the URL (i.e. src)
	 * @param	string	$credit		Credit to add to bottom right corner of image
	 *
	 * @return 	string	Either the full HTML img tag or the img SRC
	 */
	public function outputImage($src, $alt, $w=0, $h=0, $styles='', $crop=array(), $src_only=false, $credit=NULL) {
		/**
		 * Get the resolve() to help get the image 
		 */
		require_once $_SERVER['DOCUMENT_ROOT'].BASE."/core/lib/functions/resolve_path.php"; // TODO - check if this is needed
		$loc = resolve($src);
		
		if (!empty($src) && is_file($loc)) {
			$w = ($w==0) ? imagesx($loc) : $w ;
			$h = ($h==0) ? imagesy($loc) : $h ;
			
			$src = str_replace($_SERVER['DOCUMENT_ROOT'], "", $loc);

			$crop_url = (is_object($crop)&&count($crop)) ? http_build_query($crop) : "" ;
			$src_url = str_replace("//", '/', $src)."?width=".$w."&height=".$h."&".$crop_url."&credit=".$credit;
			$src_url .= ($this->quality!='standard') ? "&quality=".$this->quality : ""; 
			$src_url .= ($crop=='no') ? "&crop=no":"";

			if ($src_only==false) {
				$this->counter++;
				
				if ($this->lazyload && !$_SESSION['mobile']) {
					$output = "<img style='".$styles."' class='page_image";
					$output .= (strpos($src_url, "no_image.png")!==false)?" noimage":"";
					$output .= "'  alt='".str_replace("_", " ", $alt)."' src='".BASE."/images/design/white_pixel.png?width=".$w."&height=".$h."' width='".$w."' data-original='".$src_url."' rel='v:photo' data-loc='".str_replace("//", '/', $src)."' />";
					$output .= "<noscript><img style='".$styles."' class='page_image' alt='".str_replace("_", " ", $alt)."' src='".$src_url."' rel='v:photo'  /></noscript>";
				} else {
					
					$output = "<img style='".$styles;
					$output .= (strpos($src_url, "no_image.png")!==false)?" noimage":"";
					$output .= "' class='page_image' alt='".str_replace("_", " ", $alt)."' src='".$src_url."' rel='v:photo' data-loc='".str_replace("//", '/', $src)."' />";
				}
				return $output;
			} else {
				return $src_url;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Get crop settings from database
	 * 
	 * @param	string	$dir	The directory location of the relevent image
	 * @param	string	$path	The full path to the image if known
	 *
	 * @return	array	Relevent settings for image
	 */
	public function getImageSettings($dir, $path=NULL) {
		global $db;	
		$db->type = 'site';
		$db->vars['dir'] = $dir;
		if (!empty($path)) {
			$db->vars['path'] = $path;
			$check = $db->select("SELECT * FROM images WHERE dir=:dir AND path=:path");
		} else {
			$check = $db->select("SELECT * FROM images WHERE dir=:dir AND path IS NULL", true);
		}
		return (count($check)) ? $check[0] : false;
	}
	
	/**
	 * Save remote image
	 * 
	 * @param	string	$remote	Remote URL
	 * @param	string	$local 	Local directory to save to
	 * @paran	string	$name	Manual name for local file - defaults to save as remote
	 */
	function saveRemoteImage($remote, $local, $name=NULL) {
		global $curl;
		
		return $curl->save_image($remote, $name, $local);
	}
	 
}

$image = new ImageOutput();