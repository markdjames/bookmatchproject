<?php
class OutputHelper {
	
	public function output($row, $field) {
		$output = $this->locale($row, $field);

		return $output;
	}
	
	public function outputTitle($row, $field='title') {
		global $is_admin;
		global $mod;
		
		if (stripos($row[$field], "<h1>") !== false) {
			$output .= $this->output($row, $field);
		} else {
			$output = ($is_admin && isset($row['id']) && !isset($mod['title'])) ? "<h1 class='editable' data-col='title' data-table='pages' data-db='site' data-id='".$row['id']."'>":"<h1>";
			$output .= $this->output($row, $field)."  <span id='sub_title'>";
			$output .= (isset($row['subtitle'])) ? $row['subtitle'] : "";
			$output .= "</span></h1>";
		}
		return $output;
	}
	
	public function locale($row, $field) {
		if (!empty($_SESSION['lang']) && !empty($row['locale'])) {
			$lang = $_SESSION['lang'];
			$locale = json_decode($row['locale'], true);
			
			if (!empty($locale[$lang][$field])) {
				$output = htmlspecialchars_decode($locale[$lang][$field]);
			} else {
				$output = $row[$field];
			}
			
			$output .= $this->languageChoices($row, $field);
			
		} else {
			$output = $row[$field];
		}
		return $output;
	}
	
	/****************************
	* Extracts the relevent text from /core/lib/locale.php
	****************************/
	public function getLocale ($text) {
		global $_locale;
		$lang = $_SESSION['lang'];
		return (!empty($_locale[$text][$lang])) ? $_locale[$text][$lang] : $_locale[$text]['English'] ;
	}
	
	public function makeHTMLsafe($values) {
		if (is_array($values)) {
			foreach($values as $k=>$val) {
				$output[$k] = $this->makeHTMLsafe($val);
			}
		} else {
			$output = addslashes(htmlspecialchars($values));
		}
		return $output;		
	}
	
	public function localeFlag($loc, $width=50) {
		$loc = strtolower($loc);
		
		return "<a class='location_flag' href='".BASE.$_GET['id']."?lang=".ucwords($loc)."'><img src='".BASE."/core/images/flags/".$loc.".jpg' width='".$width."' /></a>";
	}
	
	public function languageChoices($row, $field) {
		
		$locale = json_decode($row['locale'], true);

		if (count($locale)) {	
			$other_choices = false;
			foreach ($locale as $loc=>$data) {
				if (!empty($data[$field])) {
					$other_choices = true;
					break;
				}
			}
			if ($other_choices) {
				$outputted = 0;
				$output = "<div class='language_choices'><p><span style='float:left'>".$this->getLocale("read this page in").":</span>";
				$output .= $this->localeFlag('english');
				foreach ($locale as $loc=>$data) {
					if (!empty($data[$field])) {
						$output .= $this->localeFlag($loc);
						$outputted++;
					}
				}
				return ($outputted>0) ? $output."</p><div style='clear:both'></div></div>" : "";
			}
		}
	}
	
	public function linkify($link) {
		$link = strtolower($link);
		if (strpos($link, 'http://')===false && strpos($link, 'https://')===false) {
			$link = "http://".$link;
		}
		$display = trim(str_replace('http://','',str_replace('https://','',str_replace("www.",'', $link))),"/");
		
		$output = "<a target='_blank' href='".$link."'>".$display."</a>";
		return $output;
	}
	
	
	public function breadcrumb($path) {
		global $url_vars;
		global $module;

		if ($path!='home' && $_GET['id']!=$module) {
			$chunks = explode("/", $path);
			array_pop($chunks);
			if ($module=="concerts" && count($chunks)==6) array_pop($chunks);
			
			$new_path = $output = ""; 
			
			$i=0;
			// limit to 4 crumbs
			if (count($chunks)>3) $chunks = array_slice($chunks, 0, 4);
			foreach ($chunks as $chunk) {
				// avoid outputing db IDs
				if (!is_numeric($chunk) && !empty($chunk)) {
					$new_path .= "/".$chunk;
					$output .= "<span typeof='v:Breadcrumb'><a href='".DIR.$new_path."' rel='v:url' property='v:title'>".clever_ucwords(str_replace("_", " ", $chunk))."</a></span> ";
					if (!MOBILE) {
						$output .= "<span class='breadcrumb_separator'>/</span> ";
					} elseif ($i<count($chunks)-1) {
						$output .= "<span>&gt;</span>";
					}	
				}
				$i++;
			}
			if (empty($output)) return false;
			if (MOBILE && !empty($output)) $output = "<strong>Back to</strong>: ".$output;
			return "<div id='breadcrumb' xmlns:v='http://rdf.data-vocabulary.org/#'>".$output."</div>";
		} else {
			return false;
		}
	}
	
	/************************
	* In put a section of text, will echo out the first paragraph and return the rest of the text
	************************/
	public function firstParagraph($input) {
		$first_para = substr($input, 0, strpos($input, "</p>")+4);
		echo "<div class='intro'>".strip_tags($first_para, "<p><a><em>")."</div>";

		$remainder = str_replace($first_para, "", $input);
		$remainder = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $remainder);
		$remainder = trim($remainder);
		
		return $remainder;
	}
	
}
$o = new OutputHelper();