<?php
/**
 * The core image processing class
 *
 * @package	Classes\Core
 */
class ImageBooks extends ImageOutput {
	
	
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
		
		$file_array 	= explode(".", $src);
		$ext      		= strtolower(substr(strrchr(basename($src), "."), 1)); 
		
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].BASE."/images/books/".urlify(basename($src, $ext)).".".$ext)) {
			
			$this->saveRemoteImage($src, '/images/books');
			
		}

		return parent::outputImage("/images/books/".urlify(basename($src, $ext)).".".$ext, $alt, $w, $h, $styles, $crop, $src_only, $credit);
		
	}
	
	
}

$image = new ImageBooks();