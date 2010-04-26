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
	
	private static $queryLog = array();


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
					echo "Connexion BDD impossible.<br/>".$e->getMessage()."\n<!-- ";print_r($e);echo ' -->';
					exit;
				}

				$this->connexions[$pool] = null;
			}
		}else{
			echo "IMPOSSIBLE DE CREER UNE CONNECTION SUR LA BASE DE DONNEES : ".$pool." / ".$dbname." / ".$connection_name;
			die();
		}

		return $this->connexions[$pool];
	}
	
	public function closeConnexion($connection_name){
		global $_CONST;
		$pool = $_CONST["CONNECTION"][$connection_name]["POOL"];
		
		if(isset($this->connexions[$pool])){
			unset($this->connexions[$pool]);
			return true;
		}
		return false;		
	}
  
    /**
	 * Construit le tableau de log qui sera envoyé par le destructeur
	 *
	 */
    public static function setLog($connection, $time=0) {

        if(!empty($connection->logType)) {
            $error = !empty($connection->errorParams['errorInfo']) ? $connection->errorParams['errorInfo'] : null;
            if($connection->logType=='errfile' && empty($error)) return;
            
            $aBacktrace = array();
			foreach(debug_backtrace() as $key=>$val) {
				if(!isset($val['class']) || ($val['class']!='DatabaseManager' && $val['class']!='DAO' && !($val['class']=='DB' && $val['function']=='log'))) {
					$aBacktrace[]='<b>'.(isset($val['class']) ? $val['class'].'::' : '').$val['function'].'</b> called at ['.$val['file'].':'.$val['line'].']';
				}
			}
            
            self::$queryLog[$connection->databaseName.'|'.$connection->logType][] = array(
                'time'       => sprintf("%01.5f", $time),
                'query'      => $connection->query,
                'bind'       => $connection->param,
                'result'     => $connection->data,
                'backtrace'  => $aBacktrace,
                'error'      => $error
            );
            
        }
    }
    
    /**
	 * Fusionne le tableau de bind avec la requête pour les logs
	 *
	 */
    private static function mergeBindInQuery($query, $params=array()){
		if(is_array($params)) foreach($params as $k=>$v) {
			if($v===null) $query = str_replace($k, "NULL", $query);
			else $query = str_replace($k, "'".$v."'", $query);
		}
		return $query;
    }

	/**
	 * Destructeur de la classe
	 *
	 */
	function __destruct() {

		try{
		    if(!empty(self::$queryLog))
		    foreach(self::$queryLog as $databaseName_logType=>$qLogs) {
		        list($databaseName, $logType) = explode('|', $databaseName_logType);
		        
		        switch($logType) {
		            case 'firebug':
		            case 'firebug2':
	                    $firephp = FirePHP::getInstance(true);
	        
	    	            $table = array();
	          		    $table[] = array('Time','Query','Bind','Result');
	            		 
	            		$totalTime = 0;
	            		foreach($qLogs as $qLog) {
	           		        $table[] = array($qLog['time'], $qLog['query'], $qLog['bind'], $qLog['result']);
	            		    $totalTime += $qLog['time'];
	            		}
	            		
	                    $firephp->table('SQL: '.$databaseName.' ('.count($qLogs).' @ '.$totalTime.')', $table);
	                    break;
	                case 'screen':
	                    foreach($qLogs as $qLog) {
	                        echo $databaseName.' ('.$qLog['time'].'): '.self::mergeBindInQuery($qLog['query'], $qLog['bind']).'<br/>';
	                    }
	                    break;   
	                case 'file':
	                case 'errfile':
	                    $log = '';						
	                    foreach($qLogs as $qLog) {
	                        $log.= date('d/m/Y H:i:s').': '.$databaseName.' ('.$qLog['time'].")\n";
	            			$log.= strip_tags(implode("\n", $qLog['backtrace']))."\n";
	            			$log.= self::mergeBindInQuery($qLog['query'], $qLog['bind']).(!empty($qLog['error']) ? "\nERROR: ".$qLog['error'][2] : '')."\n\n";
	            		}
						
						$logFile = dirname(__FILE__).'/../../../' . ($logType=='errfile' ? 'SQLerr' : 'SQLlog');
						if(isset($_SESSION['connexion']['etab'])) {
							$etab = unserialize($_SESSION['connexion']['etab']);
							$logFile .= '-'.$etab->id;
						}
	        			file_put_contents($logFile.'.txt', $log, FILE_APPEND);
	                    break;
		        }	        	        
		    }    
		} catch(Exception $e) {
			echo"<xmp>";print_r($e);echo"</xmp>";
		}
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
