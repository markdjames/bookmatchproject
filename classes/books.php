<?php
/**
 * Group of methods used to retrieve and manipulate the books database tables
 *
 * @package 	Classes\Custom
 */
class Books {
	
	/**
	 * Get distinct book by ID
	 * 
	 * @param	$id	int	
	 */
	public function getBook($id) {
		global $db;
		
		$db->vars['id'] = $id;
		$output = $db->select("SELECT b.*, a.fullname AS author 
								FROM books AS b
								LEFT JOIN authors AS a ON b.author_id=a.id
								WHERE b.id=:id");

		return (count($output)) ? $output[0] : false ;
	}
	
	/**
	 * Get all books or specify a search query for basic filter
	 * 
	 * @param	$q	string	Search query
	 */
	public function getBooks($q=NULL) {
		global $db;
			
		$sql = "";	
		if (!empty($q) && strlen($q)>3) {
			$db->vars['title'] = '%'.$q.'%';
			$db->vars['author'] = '%'.$q.'%';
			$sql = "WHERE b.title LIKE :title OR a.fullname LIKE :author";
		} elseif(!empty($q)) {
			return array();
		} 
			
		$output = $db->select("SELECT b.*, a.fullname AS author 
								FROM books AS b
								LEFT JOIN authors AS a ON b.author_id=a.id
								".$sql." 
								ORDER BY a.fullname, b.title ASC");

		return (count($output)) ? $output : array() ;
	}
	
	/**
	 * Get book by ASIN number
	 * 
	 * @param	$id	string	ASIN number
	 */
	public function getBookByASIN($id) {
		global $db;
		
		$db->vars['id'] = $id;
		$output = $db->select("SELECT b.*, a.fullname AS author 
								FROM books AS b
								LEFT JOIN authors AS a ON b.author_id=a.id
								WHERE ASIN=:id");

		return (count($output)) ? $output[0] : false ;
	}
	
	/**
	 * Get book by ISBN number
	 * 
	 * @param	$id	string	ISBN number
	 */
	public function getBookByISBN($id) {
		global $db;
		
		$db->vars['id'] = $id;
		$output = $db->select("SELECT b.*, a.fullname AS author 
								FROM books AS b
								LEFT JOIN authors AS a ON b.author_id=a.id
								WHERE ISBN=:id");

		return (count($output)) ? $output[0] : false ;
	}
	
	/**
	 * Get all users books on their shelf
	 * 
	 * @param	$uid	int	Users ID
	 */
	public function getUsersShelf($uid) {
		global $db;
		
		$db->vars['uid'] = $uid;
		$output = $db->select("SELECT b.*, a.fullname AS author, s.id AS shelf_id
								FROM books AS b
								LEFT JOIN authors AS a ON b.author_id=a.id
								LEFT JOIN book_shelf AS s ON b.id=s.book_id
								WHERE 
									s.user_id=:uid
								ORDER BY a.fullname, b.title ASC");

		return (count($output)) ? $output : false ;
	}
	
	/**
	 * Look for any similarities between users
	 * 
	 * @param	$books	array	Array of book IDs for matching
	 */
	public function getMatches($books) {
		global $db;
		global $u;
		
		$db->vars['uid'] = $_SESSION['userid'];
		$matches = $db->select("SELECT b.*, a.fullname AS author, s.id AS shelf_id, u.id AS user_id, u.email
								FROM books AS b
								LEFT JOIN authors AS a ON b.author_id=a.id
								LEFT JOIN book_shelf AS s ON b.id=s.book_id
								LEFT JOIN users AS u ON s.user_id=u.id
								WHERE 
									s.user_id!=:uid
									AND
									s.book_id IN (".implode(",",$books).")
								ORDER BY a.fullname, b.title ASC");
								
		if (count($matches)) {
			foreach ($matches as $match) {
				$umatches[$match['user_id']][] = $match;
			}
			//debug($umatches);
			do {
				//uksort($umatches, function($a, $b) { return count($b) - count($a); });
				array_multisort(array_map('count', $umatches), SORT_DESC, $umatches);
				
				$top_match 		= $umatches[0];
				$matched_shelf 	= $this->getUsersShelf($top_match[0]['user_id']);

				if (count($top_match) < count($matched_shelf)) { 			
					$output['user']  = $u->getUser($top_match[0]['user_id']);
					$output['shelf'] = $matched_shelf;
					$output['count'] = count($top_match); 
					break;
				} else {
					array_shift($umatches);
				}
			} while (count($umatches));
		
			return (isset($output)) ? $output : false;

		} else {
			return false;
		}
	}
	
	/**
	 * Output a users shelf
	 * 
	 * @param	$shelf	array	Result of getUsersShelf function
	 */
	public function outputShelf($shelf) {
		global $image;
		if ($shelf) {
			ob_start();
			foreach ($shelf as $book) {
				?>
				<div class='book' data-shelfid="<?=$book['shelf_id']?>">
                	<a href="<?=BASE?>/books/<?=$book['id']?>/<?=urlify($book['title']." - ".$book['author'])?>" data-id="<?=$book['id']?>"><?=$image->outputImage($book['thumbnail'], $book['title']." by ".$book['author'], 100, 160);?></a>
				</div>
				<?php
			}
			$output = ob_get_clean();
		} else {
			if (!empty($_SESSION['userid'])) {
				$output = "<span style='color:#eee; font-size:50px'>YOUR SHELF IS EMPTY</span>";
			}
		}
			
		return $output;
	}
	
	
	/**
	 * Output recommended books
	 * 
	 * @param	$books	array	Array of books
	 * @param	$authors	array	Array of author names not to output
	 */
	public function outputRecommended($book, $match=false) {
		global $image;
		if ($book) {
			ob_start();
			?>
            <div class='book<?=($match)?" match":""?>'>
	            <a href="<?=BASE?>/books/<?=$book['id']?>/<?=urlify($book['title']." - ".$book['author'])?>" data-id="<?=$book['id']?>"><?=$image->outputImage($book['thumbnail'], $book['title']." by ".$book['author'], 100, 160);?></a>
            </div>
            <?php
			$output = ob_get_clean();
		} else {
			$output = "";
		}
			
		return $output;
	}
	
	
	/**
	 * Add an author to database (include duplication test)
	 *
	 * @param	$author	string	Author's name
	 */
	public function addAuthor($author) {
		global $db; 
		 
		if (!empty($author)) {
			$db->vars['name'] = $author;
			$dup_check = $db->select("SELECT * FROM authors WHERE fullname=:name");
			
			if (!count($dup_check)) {
				$db_author = array(
					'fullname'=>	$author,
				);
				$db->insert("authors", $db_author);
				$db->doCommit();
				$author_id = $db->lastId;
			} else {
				$author_id = $dup_check[0]['id'];
			}
			return $author_id;
		} else {
			return false;
		}
		
	}
	 
	/**
	 * Add a book to database (include duplication test)
	 *
	 * @param	$book	string	Book array
	 * @param	$author_id 	int	Author dB ID
	 */
	public function addBook($book, $author_id) {
		global $db; 
		
		if ($author_id) {
		
			$db->vars['asin'] = $book['ASIN'];
			$dup_check = $db->select("SELECT * FROM books WHERE ASIN=:asin");

			if (!count($dup_check)) {
				$db_book = array(
					'title'=>		$book['title'],
					'author_id'=>	$author_id,
					'thumbnail'=>	$book['thumbnail'],
					'image'=>		$book['image'],
					'ASIN'=>		$book['ASIN'],
					'ISBN'=>		$book['ISBN'],
					'published'=>	$book['published'],
					'related'=>		(is_array($book['related'])) ? implode(',', $book['related']) : $book['related'],
					'url'=>			$book['url']
				);
				
				$db->insert("books", $db_book);
				$db->doCommit();
				$book_id = $db->lastId;
			} else {
				$book_id = $dup_check[0]['id'];
			}
			
			return $book_id;
		} else {
			return false;
		}
	}
	 
	/**
	 * Add a book to a user's 'shelf'(include duplication test)
	 *
	 * @param	$book_id	int	Book ID
	 * @param	$user_id 	int	USer ID
	 */
	public function addBookToShelf($book_id, $user_id) {
		global $db; 
		 
		$db->delete('book_shelf', array('book_id', 'user_id'), array($book_id, $user_id));
		$db->doCommit();
		
		$values = array(
			'book_id'=>		$book_id,
			'user_id'=>		$user_id
		);
		$db->insert("book_shelf", $values);
		$db->doCommit();
	}
	 
	/**
	 * Process an Amazon XML object and return in local array format
	 *
	 * @param	$input	string	The Amazon SOAP response
	 */
	public function formatResponse($input) {
		
		try {
			$results = new SimpleXMLElement($input); 
		}  catch (Exception $e) {
			exit();
		}
	
		$books = $dups = array();
		if (count($results->Items)) {
			foreach ($results->Items->Item as $item) {
				$book = $item->ItemAttributes;
				$dup_title = preg_replace('/\s+/', ' ', preg_replace("/\((.*?)\)/", "", strtolower((string) $book->Title." ".(string) $book->Author)));
				if (!in_array($dup_title, $dups) 
					&&
					!empty($book->Title)
					&&
					!empty($book->Author)
					&&
					is_numeric((int) $item->ASIN) // check ASIN is a valid ISBN to keep results clean
					&& 
					strpos((string) $book->Title, (string) $book->Author)===false
					) {
						
					$title = trim(preg_replace('/\s+/', ' ', preg_replace("/\((.*?)\)/", "", str_replace("&", "and", (string) $item->ItemAttributes->Title))), " :.)( ");
					
					if (strpos($title, ":")!==false && strpos(strtolower($title), 'notes', strpos($title, ":"))!==false) continue;
					if (empty($item->MediumImage->URL)) continue;

					if (!empty($item->ItemAttributes->Binding)) {
						if ((string) $item->ItemAttributes->Binding != 'Paperback' && (string) $item->ItemAttributes->Binding != 'Hardcore') continue;
					}
						
					if (isset($item->ItemAttributes->Languages)) {
						$original_language = $published_language = "" ;
						foreach ($item->ItemAttributes->Languages->Language as $language) {
							
							if ((string) $language->Type == 'Original Language') $original_language = (string) $language->Name;
							if ((string) $language->Type == 'Published') $published_language = (string) $language->Name;
							if ((string) $language->Type == 'Translation' && (string) $language->Name != 'English') continue 2;
						}
						if ($original_language != $published_language) continue;
					}
					
					if (empty($item->ItemAttributes->ISBN)) continue;
					if ((string) $item->ItemAttributes->ISBN != (string) $item->ASIN) continue;					
						
					$related = array();
					if (!empty($item->SimilarProducts->SimilarProduct)) {
						foreach ($item->SimilarProducts->SimilarProduct as $sim) {
							$related[] = (string) $sim->ASIN;
						}
					} else {
						$related = array();
					}

					$books[] = array( 
						'title'=>		$title,
						'author'=>		preg_replace('/[^\da-z ]/i', '', (string) $book->Author),
						'thumbnail'=>	(string) $item->MediumImage->URL,
						'image'=>		(string) $item->LargeImage->URL,
						'ASIN'=>		(string) $item->ASIN,
						'ISBN'=>		(string) $book->ISBN,
						'published'=>	(string) $book->PublicationDate,
						'related'=>		$related,
						'url'=>			(string) $item->DetailPageURL,
					);
					$dups[] = $dup_title;				
				}
			}
			return $books;
		} else {
			return false;
		}		
	}
	
	function deDuplicate($books) {
		if (count($books)) {
			$original = $books;
			
			/**
			 * First get rid of any obvious matches
			 */
			$dups = array();
			foreach ($books as $key=>$book){
				if (in_array($book['title'].$book['author'], $dups)) {
					unset($original[$key]);
				} else {
					$dups[] = $book['title'].$book['author'];
				}
			}
			
			$numbers 		= array('0',	'1',	'2',	'3',	'4',	'5',	'6',	'7',	'8',	'9'		);
			$numbers_words 	= array('zero',	'one',	'two',	'three','four',	'five',	'six',	'seven','eight','nine'	);				
			
			foreach ($books as $key=>$book) {
				$author_array = explode(" ", (string) $book['author']);
				$author_surname = array_pop($author_array);
				
				$title_array = explode(" ", (string) $book['title']);
				if (count($title_array)>1) {
					$title = "";
					foreach ($title_array as $tw) {
						if (strlen($tw)>3) $title .= $tw;
					}
					if (empty($title)) $title = (string) $book['title'];
				} else {
					$title = (string) $book['title'];
				}	

				$title = trim(preg_replace('/[^a-z]/i', '', strtolower($title)));	
				
				$by_author[$author_surname][] = array(
					'key'=>$key,
					'title'=>$title,
					'title_length'=>strlen($book['title']),
				);
			}
			
			foreach($by_author as $author=>$books) {
				usort($books, function($a, $b) {
					return $a['title_length'] - $b['title_length'];
				});			

				$base_title = array_shift($books);
				$match_titles = array($base_title['title']);
				
				foreach ($books as $book) {
					$match = false;
					foreach ($match_titles as $title) {
						if (stripos($book['title'], $title)!==false) {
							unset($original[$book['key']]);
							$match = true;
						}
					}
					if ($match == false) {
						$match_titles[] = $book['title'];
					}
				}
			}

			return $original;
		} else {
			return $books;
		}
	}
	
	/** 
	 * Get master edition of a book by ISBN 
	 */
	function getMasterEdition($isbn) {
		global $db;
		
		$db->vars['isbn'] = $isbn;
		$editions = $db->select("SELECT * FROM book_editions WHERE ISBN=:isbn");
		
		if (count($editions)) {
			$master = $this->getBookByISBN($editions[0]['parent']);
		} else {
			$master = $this->getBookByISBN($isbn);
		}
		
		return $master;
	}
	
	/** 
	 * Get all editions of a book by ISBN 
	 */
	function getEditions($isbn) {
		global $db;
		
		$db->vars['isbn'] = $isbn;
		$editions = $db->select("SELECT * FROM book_editions WHERE parent=:isbn ORDER BY release_date ASC");
		
		return (count($editions)) ? $editions : false ;
	}
		 
}
$_books = new Books();