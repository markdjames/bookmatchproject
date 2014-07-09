<?php
class Database {
	private $dbtype = "mysql";
	private $dbh;
	private $meta_debug = false;
	private $profile = false;
	
	public $type = 'site';
	public $result;
	public $count;
	public $lastId;
	public $status = true;
	public $queries = array();
	public $errors = array();
	public $vars = array();
	
	private $userid; 
	
	function __construct($profiling=true) {
		
		if ($profiling==false) {
			$this->profile = false;
		} elseif (isset($_SESSION['userid'])) {
			$this->userid = $_SESSION['userid'];
			if ($_SESSION['userid']==1) {
				$this->meta_debug = false;
			}
		}
	}
	
	public function pdoConnection() {
		global $dbhost;
		
		global $dbref1;
		global $dbname1;
		global $dbuser1;
		global $dbpass1;
		
		global $dbref2;
		global $dbname2;
		global $dbuser2;
		global $dbpass2;
		
		if ($this->type==$dbref1) {
			$dbname = $dbname1;
			$dbuser = $dbuser1;
			$dbpass = $dbpass1;
			
		} elseif ($this->type==$dbref2) {
			$dbname = $dbname2;
			$dbuser = $dbuser2;
			$dbpass = $dbpass2;
			
		} else {
			echo "error";
			exit();
		}
		
		$this->dbh = new PDO($this->dbtype.":dbname=".$dbname.";host=".$dbhost.";charset=UTF8", $dbuser, $dbpass);
		$this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	// the UPDATE, DELETE, INSERT queries are checked to make sure all are valid before being run 
	function update ($table, $fields, $values, $debug=false) {
		if (count($fields) && count($values)) {

			$query = "UPDATE " . $table . " SET ";
			foreach ($values as $field => $value) {
				if (empty($value) && $value!=0) {
					unset($values[$field]);
				} else {
					$query .= "`" . $field . "`=:".$field.", ";
					$pvalues[$field] = $value;
				}
			}
			$query = substr($query, 0, -2);
			$query .= " WHERE ";
			
			foreach ($fields as $field => $value) {
				if (empty($value) && $value!=0) {
					unset($values[$field]);
				} else {
					$query .= "`" . $field . "`=:".$field." AND ";
					$pvalues[$field] = $value;
				}
			}
			$query = substr($query, 0, -5);

			if ($debug == true) {
				debug($fields);
				debug($values);
				debug($query);
			}
			
			$this->check($query, $pvalues);
		}
	}
		
	function insert ($table, $values, $debug=false) {
		
		$query = "INSERT INTO " . $table . " (";
		foreach ($values as $field => $value) {
			$query .= "`" . $field . "`, ";
			$pvalues[$field] = $value;
		}
		$query = substr($query, 0, -2);
		$query .= ") VALUES (";
		foreach ($values as $field => $value) {
			$query .= ":".$field . ", ";
			$pvalues[$field] = $value;
		}
		$query = substr($query, 0, -2);
		$query .= ")";
		if ($debug == true) {
			echo $query;
		}
		
		$this->check($query, $pvalues);
	}
	
	function delete ($table, $field, $value, $debug=false) {
		
		if (is_array($field)) {
			$i=0;
			$sql = "";
			foreach ($field as $f) {
				$sql .= "`" . $f . "`=:".$f." AND ";
				$pvalues[$f] = $value[$i];
				$i++;
			}
			$query = "DELETE FROM ".$table." WHERE ".rtrim($sql, " AND ");
		} else {
			$query = "DELETE FROM ".$table." WHERE `".$field."`=:".$field;
			$pvalues[$field] = $value;
		}
		if ($debug == true) {
			echo $query;
		}
		
		$this->check($query, $pvalues);
	}
	
	function select ($sql_string, $debug=false, $explicit_debug_only=false) {
		$this->pdoConnection();
		
		if (is_object($this->dbh)) {
			if ($debug == true || ($this->meta_debug==true && $explicit_debug_only==false)) {
				print_r($this->vars);
				echo "<br />".htmlentities($sql_string);
				//debug_print_backtrace();
				echo "<br />";
				$time_start = microtime(true);
			}
			
			$sth = $this->dbh->prepare($sql_string, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute($this->vars);
			
			$this->result = NULL;
			$results = $sth->fetchAll();
			
			if (count($results) > 0) {
				$i = 0;
				foreach ($results as $row) {
					foreach ($row as $key=>$val) {
						if (!is_int($key))
							$this->result[$i][$key] = $val;
					}
					$i++;
				}	
			}
			$vars = $this->vars;
			$this->dbh = NULL;
			$this->vars = NULL;	
			
			if ($this->profile) {
				$profiler = new Profiler();
				$profiler->newProfile($sql_string, $vars);
			}
			
			if ($debug == true || ($this->meta_debug==true && $explicit_debug_only==false)) {
				$time_end = microtime(true);
				$time = $time_end - $time_start;
				echo "<strong>Transaction time</strong>: ".$time;
				echo "<br /><br />";
			}
			return $this->result;	
		}
	}
	
	// for special querys, eg ALTER or DELETE - bypasses checks
	function custom ($sql_string, $debug=false) {
		$this->pdoConnection();
		if ($debug == true) {
			echo $sql_string."<br />";
		}
		if (strpos($sql_string, "SELECT")===false) {
			$this->dbh->exec($sql_string);
			$this->lastId = $this->dbh->lastInsertId();
		} else {
			$query_result = $this->dbh->query($sql_string);
		}
		$this->dbh = NULL;
		
		return (isset($query_result)) ? $query_result : true;		
	}
	
	// function checks if all statements being run are ok and installs them all in an array to run once confirmed
	function check ($query, $values=NULL) {
		
		$this->pdoConnection();
		if (is_object($this->dbh)) {
			
			$this->dbh->beginTransaction();

			if (empty($values)) {
				try {
					$query_result = $this->dbh->query($query);
				} catch (PDOException $err) {
					$this->errors[] = $err;
					$this->status = false;
				}	
			} else {
				$sth = $this->dbh->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				try {
					$sth->execute($values);
				} catch (PDOException $err) {
					$this->errors[] = $err;
					$this->status = false;
				}
			}
			if ($this->status) {
				array_push($this->queries, array('sql'=>$query, 'values'=>$values));
			} 
			$this->dbh->rollBack();
			$this->dbh = NULL;	
		}
	}
	
	function commit ($query, $values) {
		
		$this->pdoConnection();
		$this->dbh->beginTransaction();
		$sth = $this->dbh->prepare($query);
		$sth->execute($values); 
		$this->lastId = $this->dbh->lastInsertId();
		$this->dbh->commit();
		$this->dbh = NULL;

		// if profiling then log
		if ($this->profile) {
			$profiler = new Profiler();
			$profiler->newProfile($query, $values);
		}
	}
	
	function doCommit() {
		global $_GET;
		global $_SESSION;
		
		if ($this->status && count($this->queries)) {
			foreach($this->queries as $sql) {
				$this->commit($sql['sql'], $sql['values']);
			}
			unset($this->queries);
			$this->queries = array();
			
		} elseif (count($this->errors)) {

			if ($_SESSION['userid']!=NULL) {
				$string = "User id = ".$_SESSION['userid']."\n\n";
			} elseif ($_GET['userid']!=NULL) {
				$string = "User id = ".$_GET['userid']."\n\n";
			}
			foreach ($this->queries as $key=>$query) {

				if (!empty($query['values'])) {
					$string .= $query['sql']."\m";
					foreach ($query['values'] as $v) {
						$string .= $v."\n";
					}
				} else {
					$string .= $query."\n\n";
				}
			}
			foreach ($this->errors as $key=>$err) {
				$string .= $err."\n\n";
			}
			
			debug($string);
			
			$subject 		= "SQL ERROR";
			$email   		= '';
			$message 		= nl2br($string);		
			//mail($email, $subject, $message, "MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\nFrom: Webmaster <$email>\r\nReturn-path: $email", '-f webmaster@website.co.uk');
			
			$_SESSION['dberror'] = true;
			unset($string);
		}
	}
	
	function sqlify($value, $type='text') {
		return $value;
		$type = (is_numeric($value) && strpos($value, '0')!==0 && strpos($value, '.')===false)?'int':'text';
		
		$value = (!get_magic_quotes_gpc()) ? mysql_escape_string($value) : $value;
		
		$value = strip_tags($value, "<a><br><p><em><strong><h1><h2><h3><h4><h5><address><ul><li><ol><blockquote><cite><span>");
		switch ($type) {
			case "text":
				$value = ($value != "") ? "'" . $value . "'" : "NULL";
				break;    
			case "int":
				$value = ($value != "") ? intval($value) : "NULL";
				break;
			case "float":
				$value = ($value != "") ? floatval($value) : "NULL";
				break;
			case "date":
				$value = ($value != "") ? "'" . $value . "'" : "NULL";
				break;
			case "html":
			default:
				$value = ($value != "") ? "'" . mysql_escape_string($value) . "'" : "NULL";
				break;
		}
		return $value;
	}
	
	function check_permissions($name) {
		global $db;
		$permissions = $db->output("*", "permissions", array('name'=>$name));

		return $permissions[0]['value'];
	}
	
	// MYSQL ONLY FUNTION
	public function describe($name) {
		global $dbhost;
		
		global $dbref1;
		global $dbname1;
		global $dbuser1;
		global $dbpass1;
		
		global $dbref2;
		global $dbname2;
		global $dbuser2;
		global $dbpass2;
		
		if ($this->type==$dbref1) {
			$dbname = $dbname1;
			$dbuser = $dbuser1;
			$dbpass = $dbpass1;
			
		} elseif ($this->type==$dbref2) {
			$dbname = $dbname2;
			$dbuser = $dbuser2;
			$dbpass = $dbpass2;
			
		} else {
			echo "error";
			exit();
		}

		mysql_connect($dbhost, $dbuser, $dbpass);
		mysql_select_db($dbname);
		$res = mysql_query('DESCRIBE '.$name);
		$i=0;
		
		while($row = mysql_fetch_array($res)) {
			$output[$i]['field'] = $row['Field'];
			$output[$i]['type'] = $row['Type'];
			$output[$i]['key'] = $row['Key'];
			$i++;
		}
		return $output;
	}
	
	function dump_table($name) {
	
		$table_structure = $this->describe($name);
		$results = $this->output("*", $name, NULL);
		
		$output = "<table>";
		for ($i=0; $i<count($table_structure); $i++) {
			$output .= "<th>".ucwords(str_replace("_"," ",$table_structure[$i]['field']))."</th>";
		}
		
		foreach ($results as $record) {
			$output .= "<tr>";
			for ($i=0; $i<count($table_structure); $i++) {
				$output .= "<td>".$record[$table_structure[$i]['field']]."</td>";
			}
			$output .= "</tr>";
		}
		
		$output .= "</table>";
		
		return $output;

	}
	
	function checkPermissions($value, $userid) {
	
		$this->pdoConnection();
		$this->type = 'site';

		if (isset($_SESSION['permissions']) && isset($_SESSION['permissions'][$value]) && ($_SESSION['permissions'][$value]!=1||$_SESSION['permissions'][$value]!=0)) {
			return $_SESSION['permissions'][$value];
		} else {
			
			$this->vars['user'] = $userid;
			$this->vars['value'] = $value;
			$permissions_results = $this->select("SELECT * FROM permissions AS p LEFT JOIN permissions_bridge AS pb ON p.id = pb.permission_id WHERE pb.user_id=:user AND p.type=:value");
			
			if ($this->meta_debug) {
				echo "SELECT * FROM permissions AS p LEFT JOIN permissions_bridge AS pb ON p.id = pb.permission_id WHERE pb.user_id=".$userid." AND p.type='".$value."'<br />";
			}
			
			if (count($permissions_results)>0) {
				$_SESSION['permissions'][$value] = 1;
				return true;
			} else {
				$_SESSION['permissions'][$value] = 0;
				return false;
			}		
		}
	}
	
	// pass setting name to check
	function checkSettings($value) {
		if ($value=='theme' && !empty($_GET['theme'])) {
			return $_GET['theme'];
			
		} elseif (isset($_SESSION['settings']) && isset($_SESSION['settings'][$value]) && $_SESSION['settings'][$value] && strpos($value, 'spotify')===false) {
			return $_SESSION['settings'][$value];
			
		} else {		
			$this->pdoConnection();
			$this->type = 'site';
			$this->vars = array();
			$this->vars['name'] = $value;
			$settings_results = $this->select("SELECT * FROM settings WHERE name=:name");
			$_SESSION['settings'][$value] = $settings_results[0]['value'];
			
			return $settings_results[0]['value'];
		}
	}
		
}

$db = new Database();
?>
