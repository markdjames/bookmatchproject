<?php

class Curl {

	function import($path, $local=true) {
		
		global $base_url;
	
		$ch = curl_init();		
		$useragent = "User-Agent: Book Match Project (+http://www.bookmatchproject.co.uk/)";
		
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

		$output = curl_exec($ch);
		curl_close($ch); 
		return $output;
	}
	
	function save_image($img, $output=NULL, $folder='/images/tmp'){

		$ch = curl_init ($img);
		$useragent = "User-Agent: Book Match Project (+http://www.bookmatchproject.co.uk/)";
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		$rawdata=curl_exec($ch);

		curl_close ($ch);
		$filename = basename($img);
		
		$fileNameNoExt 	= explode(".", $filename);
		$ext      		= strtolower(substr(strrchr($filename, "."), 1)); 
		$ext 			= (substr($ext, 0, 3) == "php")?"jpg":$ext;

		$fullpath = $_SERVER['DOCUMENT_ROOT'].BASE.$folder.'/';
		
		if ($output!=NULL) {
			$fileNameNoExt[0] = $output;
		} else {
			$fileNameNoExt[0] = urlify(basename($img, $ext));
		}
		
		$fullpath .= $fileNameNoExt[0] .".".$ext;
		$fileref = $folder.'/'.$fileNameNoExt[0].".".$ext;

		if ($ext == 'jpg' || $ext == 'png' || $ext == 'gif' || $ext == 'jpeg' || $ext == 'php') {
			
			if(file_exists($fullpath)){
				unlink($fullpath);
			}

			$fp = @fopen($fullpath,'x');
			@fwrite($fp, $rawdata);
			@fclose($fp);
	
			return $fileref;
		} else {
			return false;
		}
		
		
	}
}
$curl = new Curl();
?>