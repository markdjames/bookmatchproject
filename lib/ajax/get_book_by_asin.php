<?php
require_once '../amazon_bootstrap.php';
require_once '../config.php';

use ApaiIO\Request\RequestFactory;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\ResponseTransformer\ObjectToArray;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\SimilarityLookup;
use ApaiIO\Operations\CartCreate;
use ApaiIO\ApaiIO;
use ApaiIO\Operations\BrowseNodeLookup;
use ApaiIO\Operations\CartAdd;
	
if (!empty($_GET['q'])) {

	$conf = new GenericConfiguration();
	
	$q = ucwords(urldecode($_GET['q']));
	
	try {
		$conf
			->setCountry('co.uk')
			->setAccessKey(AWS_API_KEY)
			->setSecretKey(AWS_API_SECRET_KEY)
			->setAssociateTag(AWS_ASSOCIATE_TAG);
	} catch (\Exception $e) {
		echo $e->getMessage();
	}
	$apaiIO = new ApaiIO($conf);
	
	$lookup = new Lookup();
	$lookup->setResponseGroup(array('Medium'));
	$lookup->setItemId($q);
	
	$formattedResponse = $apaiIO->runOperation($lookup);
	
	$results = new SimpleXMLElement($formattedResponse); 

	$books = $dups = array();
	if (count($results->Items)) {
		foreach ($results->Items->Item as $item) {

			$book = $item->ItemAttributes;
			$books = array( 
				'title'=>		preg_replace('/\s+/', ' ', preg_replace("/\((.*?)\)/", "", (string) $book->Title)),
				'author'=>		(string) $book->Author,
				'thumbnail'=>	(string) $item->MediumImage->URL,
				'image'=>		(string) $item->LargeImage->URL,
				'id'=>			(string) $item->ASIN
			);
			echo json_encode($books);
			exit();
		}
	} 
	
}