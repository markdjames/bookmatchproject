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

$lookup = new Lookup();
$lookup->setResponseGroup(array('Large'));
$lookup->setItemId($_POST['ASIN']);

$formattedResponse = $apaiIO->runOperation($lookup);

$results = new SimpleXMLElement($formattedResponse); 

if (count($results->Items)) {
	foreach ($results->Items->Item as $item) {
		$related = array();
		if (!empty($item->SimilarProducts->SimilarProduct)) {
			foreach ($item->SimilarProducts->SimilarProduct as $sim) {
				$related[] = (string) $sim->ASIN;
			}
		} else {
			$related = array();
		}
		$book = array( 		
			'title'=>		preg_replace('/\s+/', ' ', preg_replace("/\((.*?)\)/", "", (string) $item->ItemAttributes->Title)),
			'author'=>		(string) $item->ItemAttributes->Author,
			'thumbnail'=>	(string) $item->MediumImage->URL,
			'image'=>		(string) $item->LargeImage->URL,
			'ASIN'=>		(string) $item->ASIN,
			'ISBN'=>		(string) $item->ItemAttributes->ISBN,
			'published'=>	(string) $item->ItemAttributes->PublicationDate,
			'related'=>		$related,
			'url'=>			(string) $item->DetailPageURL,
		);
	}
} 

$author_id 	= $_books->addAuthor($_POST['author']);
$book_id 	= $_books->addBook($book, $author_id);

if (!empty($_SESSION['userid'])) {
	$shelf = $_books->getUsersShelf($_SESSION['userid']);
	
	if (count($shelf)<10) {
		$_books->addBookToShelf($book_id, $_SESSION['userid']);
	}
	
	$shelf = $_books->getUsersShelf($_SESSION['userid']);
	echo $_books->outputShelf($shelf);
}



	
if (!empty($_GET['q'])) {

	
	
}