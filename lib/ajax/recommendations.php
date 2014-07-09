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
} catch (Exception $e) {
	echo $e->getMessage();
}
$apaiIO = new ApaiIO($conf);

$user 	= $u->getUser($_SESSION['userid']);
$shelf 	= $_books->getUsersShelf($_SESSION['userid']);

if ($shelf) {

	$read 		= (!empty($user['read'])) 		? json_decode($user['read']) 		: array() ;
	$discarded 	= (!empty($user['discarded'])) 	? json_decode($user['discarded'])	: array() ;
	
	/**
	 * First we check for real matches with other users
	 */
	foreach ($shelf as $book) $bookids[] = $book['id'];
	$matches = $_books->getMatches($bookids);
	
	if ($matches) {
		?>
        <h2>We've found a match!</h2>
        <p>Your favourite books are best matched with <a href='<?=DIR?>/users/<?=$matches['user']['id']?>/'><?=$matches['user']['firstname']." ".$matches['user']['surname']?></a>. You have <?=$matches['count']?>/<?=count($shelf)?> books in common. See what you think of their other choices...</p>
        <div id='matches'>
			<?php
			foreach ($matches['shelf'] as $match) {
				if (in_array($match['id'], $bookids)) {
					echo $_books->outputRecommended($match, true);
				}
			}
			foreach ($matches['shelf'] as $match) {
				if (!in_array($match['id'], $bookids)) {
					echo $_books->outputRecommended($match, false);
				}
			}
		   ?>
		</div>
        <?php
		
	/**
	 * If no real matches found, we get amazon similar books
	 */	
	} else {
		foreach ($shelf as $book) {
			$add[] = $book['ASIN'];
			$authors[] = $book['author'];
		}
		$i=0;
		foreach ($shelf as $book) {
			$related = explode(",", $book['related']);
	
			foreach ($related as $id) {
				if (!in_array($id, $add) && !in_array($id, $read) && !in_array($id, $discarded)) {
					$rel[] = $id;
					
				}
			}
		}
		
		shuffle($rel);
		
		$acv = array_count_values($rel); 	
		arsort($acv); 
		
		$i=0;
		ob_start();
		foreach ($acv as $rel=>$k) {
			
			$check_db = $_books->getBookByASIN($rel);
			
			if ($check_db) {
				
				echo $_books->outputRecommended($check_db);
				$i++;		
				
			} else {
				$lookup = new Lookup();
				$lookup->setResponseGroup(array('Large', 'Small'));
				$lookup->setItemId($rel);
				
				$book = $_books->formatResponse($apaiIO->runOperation($lookup));
				if (isset($book[0])) {
					$author_id 	= $_books->addAuthor($book[0]['author']);
					$book_id 	= $_books->addBook($book[0], $author_id);
		
					echo $_books->outputRecommended($book[0]);
					$i++;
				}
				sleep(1);
			}
			
			if ($i==3) break;
		}
		
		$recommended = ob_get_clean();

		if (!empty($recommended)) {
			?>
			<h2>Your <span style='font-size:14px; font-weight:normal'>(temporary*)</span> Recommendations</h2>
			<?=$recommended?>
			<p style='clear:both; padding-top:20px; font-size:12px'>* These are just a few starter recommendations while we wait for some better matches with our other users.</p>
			<?php
		}
	}
}