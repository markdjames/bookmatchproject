<?php
require_once '../../core/lib/bootstrap.php';
require_once '../amazon_bootstrap.php';

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
	
	$asins = array();
	$local_books = $_books->getBooks($_GET['q']);
	foreach($local_books as $lb) $asins[] = $lb['ASIN'];

	$q = ucwords(urldecode($_GET['q']));
	
	$conf = new GenericConfiguration();
	
	try {
		$conf
			->setCountry($_SESSION['location']['store'])
			->setAccessKey(AWS_API_KEY)
			->setSecretKey(AWS_API_SECRET_KEY)
			->setAssociateTag(AWS_ASSOCIATE_TAG);
	} catch (\Exception $e) {
		echo $e->getMessage();
	}
	$apaiIO = new ApaiIO($conf);
	
	$search = new Search();
	$search->setCategory('Books');
	$search->setKeywords($q);
	$search->setPage(1);
	$search->setResponseGroup(array('Medium'));
	$amazon_books = $_books->formatResponse($apaiIO->runOperation($search));
	
	if (count($amazon_books)) {
		$books = $amazon_books;
	} else {
		$books = array();
	}

	$books = array_merge($local_books, $books);
	$books = $_books->deDuplicate($books);
	
	foreach ($books as $key=>$book) {
		if (in_array($book['ASIN'], $asins)) {
			unset($books[$key]);
		}
	}
	
	echo json_encode($books);
} else {
	echo json_encode(array());
}