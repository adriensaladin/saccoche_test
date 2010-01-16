<?php
/**
 * Multiton - Gestion des accès aux bases de données
 *
 * Dans cette classe de type Multiton sont définies les méthodes permettant de gérer une connection unique
 * aux différentes bases de données
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */
class DatabaseManager {

	/**
	 * Instance de la classe DatabaseManager
	 *
	 * @var DatabaseManager $_instance
	 */
	private static $_instance;


	/**
	 * Tableau de sauvegarde des objets de type AbstractDatabase
	 *
	 * @var array $connexions
	 */
	private $connexions = array();


	/**
	 * Constructeur de la classe
	 *
	 */
	function __construct(){
	}

	/**
	 * Cette méthode retourne ou crée l'instance de l'objet DatabaseManager
	 *
	 * @return DatabaseManager
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)){
			self::$_instance = new DatabaseManager();
		}
		return self::$_instance;
	}


	/**
	 * Cette méthode retourne un objet DatabaseInterface (Couche d'abstraction de connexion à la base de données)
	 *
	 * @param String $connection_name nom de la connexion à la base de données défini dans le fichier database.conf.php
	 * @return DatabaseInterface
	 */
	public function getConnexion($connection_name){
		global $_CONST;
		$pool = $_CONST["CONNECTION"][$connection_name]["POOL"];
		$dbname = $_CONST["CONNECTION"][$connection_name]["DB_NAME"];
		$critical = isset($_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["CRITICAL"]) ? $_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["CRITICAL"] : false;
		$force_encoding = (isset($_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["FORCE_ENCODING"]) && $_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["FORCE_ENCODING"]!='') ? $_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["FORCE_ENCODING"] : false;

		if($pool != "" & $dbname != ""){
			// Classe Factory de création de l'objet de connection à la base de données
			require_once("DatabaseFactory.class.php");

			try{
				if (!isset($this->connexions[$pool])){
					// Création de la connection au pool et à la base de données
					$this->connexions[$pool] = DatabaseFactory::createConnexion($pool,$dbname);
				} else {
					$this->connexions[$pool]->selectDB($dbname);
					$this->connexions[$pool]->data = null;
				}
				
			} catch (DataBaseException $e) {

				// Il ne faut pas bloquer l'internaute si la connexion n'est pas critique 
				if($critical==true) {
					//include("/indispo.tpl.php");
					echo "Connexion BDD impossible.<br/>\n<!-- ";print_r($e);echo ' -->';
					exit;
				}

				$this->connexions[$pool] = null;
			}
		}else{
			echo "IMPOSSIBLE DE CREER UNE CONNECTION SUR LA BASE DE DONNEES : ".$pool." / ".$dbname." / ".$connection_name;
			die();
		}

		if (isset($_GET['show']) && eregi('database', $_GET['show'])) {
			echo '<xmp>databaseManager<br>';print_r($this);echo'</xmp>';
		}
		return $this->connexions[$pool];
	}
		
	/**
	 * Fonction de log
	 *
	 */
	public static function log($cnxObj, $forceErrFile=false) {
		
		// Préparation des éléments à logguer
		if($cnxObj->logType!='' || (isset($_GET['show']) && eregi('sql', $_GET['show']))) {
			// backtrace
			$aBacktrace = array();
			foreach(debug_backtrace() as $key=>$val) {
				if(!isset($val['class']) || ($val['class']!='DatabaseManager' && $val['class']!='DAO' && !($val['class']=='DB' && $val['function']=='log'))) {
					$aBacktrace[]='<b>'.(isset($val['class']) ? $val['class'].'::' : '').$val['function'].'</b> called at ['.$val['file'].':'.$val['line'].']';
				}
			}
			// requete
			$query = $cnxObj->query;
			if(is_array($cnxObj->param)) foreach($cnxObj->param as $k=>$v) {
				if($v===null) $query = str_replace($k, "<font color=red>NULL</font>", $query);
				else $query = str_replace($k, "<font color=red>'".$v."'</font>", $query);
			}			
		} else return;
		
		// log ecran		
		if($cnxObj->logType=='screen' || (isset($_GET['show']) && eregi('sql', $_GET['show']))) {
			echo '<font color="orange">'.$cnxObj->databaseName.'</font>';
			echo '<div style="color:gray;">'.implode('<br/>',$aBacktrace).'</div>';
			echo '<pre style="margin:0">';print_r($query);echo '</pre>';
		}
		// log firebug
		elseif($cnxObj->logType=='firebug') {
			echo '<script>console.info(\''.$cnxObj->databaseName.'\');';
			echo 'console.log(\''.addslashes(strip_tags(implode(" ## ",$aBacktrace))).'\');';
			echo 'console.log(\''.addslashes(strip_tags($query)).'\');</script>';
		}
		// log fichier
		elseif($cnxObj->logType=='file' || ($cnxObj->logType=='errfile' && $forceErrFile)) {
			$logFile = dirname(__FILE__).'/../../' . ($forceErrFile ? 'SQLerr.txt' : 'SQLlog.txt');
			$log = date('d/m/Y H:i:s').': '.$cnxObj->databaseName."\n";
			$log.= strip_tags(implode("\n",$aBacktrace))."\n";
			$log.= strip_tags($query).($cnxObj->logType=='errfile' ? "\n".$cnxObj->errorParams['errorInfo'][2] : '')."\n\n";
			file_put_contents($logFile, $log, FILE_APPEND);
		}
  }

	/**
	 * Destructeur de la classe
	 *
	 */
	function __destruct() {

	}
}





/**
 * Gestion des Exceptions de base de données
 *
 * @package Lib
 * @subpackage Database
 */
class DatabaseException extends Exception {
     public function __construct($msg, $error) {
          parent::__construct($msg.' ('.$error.')');
     }
     
     public function __toString() {
    	return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  	}
}
?>
