<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/books/core/lib/bootstrap.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/books/lib/amazon_bootstrap.php';

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

$book = $_books->getBook($_POST['id']);
	
$conf = new GenericConfiguration();

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
$lookup->setResponseGroup(array('Medium', 'Large'));
$lookup->setItemId($book['ASIN']);

$formattedResponse = $apaiIO->runOperation($lookup);

$results = new SimpleXMLElement($formattedResponse); 

$books = $dups = array();
if (count($results->Items)) {
	foreach ($results->Items->Item as $item) {
		//debug($item, true);
		$related = array();
		if (!empty($item->SimilarProducts->SimilarProduct)) {
			foreach ($item->SimilarProducts->SimilarProduct as $sim) {
				$related[] = (string) $sim->ASIN;
			}
		}
		//debug($item->CustomerReviews->IFrameURL, true);
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
			'reviews'=>		(string) $item->CustomerReviews->IFrameURL,
			'description'=>	(isset($item->EditorialReviews->EditorialReview[1])) ? (string) $item->EditorialReviews->EditorialReview[1]->content : "" 
		);
		?>
        <div id='book_info'>
            <div class='book' data-title='<?=$book['title']?>' data-author='<?=$book['author']?>' data-asin='<?=$book['ASIN']?>' data-isbn='<?=$book['ISBN']?>' data-thumbnail='<?=$book['thumbnail']?>' data-image='<?=$book['image']?>' data-published='<?=$book['published']?>' data-url='<?=$book['url']?>' data-rel='<?=(is_array($book['related'])) ? implode(',', $book['related']) : $book['related'] ; ?>'>
                <img src='<?=$book['image']?>' alt="<?=$book['title']?> by <?=$book['author']?>" />
                <h1 style='margin-top:0'><?=$book['title']?><br /><em><span style='font-weight:normal'><?=$book['author']?></span></em></h1>
                <p><?=$book['description']?></p>
                
                <p><a href='javascript:void(0)' onclick="list.add($(this).parent().parent())">Add to my book shelf</a> | <a href='javascript:void(0)' onclick="book.read('<?=$book['ASIN']?>');">I've read this book</a> | <a href='javascript:void(0)' onclick="book.discard('<?=$book['ASIN']?>')">Discard this book</a></p>
                
                <iframe src="<?=$book['reviews']?>" frameborder="0" width="72%" height="300"></iframe>
                
                <p style='clear:both'><a href="<?=$book['url']?>" target="_blank">Buy Now</a></p>
                
                <div style='clear:both'></div>
            </div>
       	</div>
        <?php
		exit();
	}
} 