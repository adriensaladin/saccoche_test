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
class DB_Manager {

  /**
   * Instance de la classe DB_Manager
   *
   * @var DB_Manager $_instance
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
  private function __construct(){
  }

  /**
   * Cette méthode retourne ou crée l'instance de l'objet DB_Manager
   *
   * @return DB_Manager
   */
  public static function getInstance(){
    if (!isset(self::$_instance)){
      self::$_instance = new DB_Manager();
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
      try{
        if (!isset($this->connexions[$pool])){
          // Création de la connection au pool et à la base de données
          $this->connexions[$pool] = self::createConnexion($pool,$dbname);
        } else {
          $this->connexions[$pool]->selectDB($dbname);
        }
        $this->connexions[$pool]->data = false;

        return $this->connexions[$pool];

      } catch (DataBaseException $e) {

        // Il ne faut pas bloquer l'internaute si la connexion n'est pas critique (par exemple pour une database de log)
        if($critical==true) {
          //include("/indispo.tpl.php");
          die("Connexion BDD impossible. ".$e->getMessage()."<br/>");
        } else {
          throw $e;
        }
      }
    }else{
      die("IMPOSSIBLE DE SE CONNECTER A LA BASE DE DONNEES : ".$pool." / ".$dbname." / ".$connection_name);
    }
  }

  /**
   * Cette méthode permet de créer l'objet de connexion à une base de données
   *
   * @param string $pool Nom du pool de connection
   * @param string $dbname Nom de la base de données
   * @return DatabaseInterface
   */
  private static function createConnexion($pool, $dbname){
    global $_CONST;
     
    if (isset($dbname) && isset($_CONST["POOL"][$pool]["ABSTRACTION"])){
      $driverOptions = array();

      // Connexion
      if($_CONST["POOL"][$pool]["ABSTRACTION"] == "PDO"){
        // Classe d'abstraction utilisant PDO
        require_once("driver/DB_driver_PDO.class.php");
        	
        // Gestion des options du driver PDO
        if(isset($_CONST["POOL"][$pool]["FORCE_ENCODING"]) && $_CONST["POOL"][$pool]["FORCE_ENCODING"]!='') {
          if(defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES ".$_CONST["POOL"][$pool]["FORCE_ENCODING"];
            $fix_force_encoding_bug = false;
          } else {
            $fix_force_encoding_bug = true;
          }
        }
        if(isset($_CONST["POOL"][$pool]["PERSISTENT"]) && $_CONST["POOL"][$pool]["PERSISTENT"]===true) {
          $driverOptions[PDO::ATTR_PERSISTENT] = true;
        }
        	
        // Ouverture d'une connexion avec PDO
        $connexion = new DB_driver_PDO($_CONST["POOL"][$pool]["TYPE"], $dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);
        	
        if(isset($fix_force_encoding_bug) && $fix_force_encoding_bug) {
          $connexion->setCharset($_CONST["POOL"][$pool]["FORCE_ENCODING"]);
        }

        	
      }elseif($_CONST["POOL"][$pool]["ABSTRACTION"] == "MYSQL"){
        /** Classe d'abstraction MYSQL */
        require_once("driver/DB_driver_mysqli.class.php");
        	
        // Ouverture d'une connexion avec MYSQL
        $connexion = new DB_driver_mysqli($_CONST["POOL"][$pool]["TYPE"],$dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);
        	
        if(isset($_CONST["POOL"][$pool]["FORCE_ENCODING"]) && $_CONST["POOL"][$pool]["FORCE_ENCODING"]!='') {
          $connexion->setCharset($_CONST["POOL"][$pool]["FORCE_ENCODING"]);
        }
        	
      }else{
        // Génération d'une DataBaseException
        throw new DataBaseException("La couche d'abastraction '".$_CONST["POOL"][$pool]["ABSTRACTION"]."' n'est pas impléméntée !");
      }

      $connexion->error_type = isset($_CONST["POOL"][$pool]["ERROR"]) ? $_CONST["POOL"][$pool]["ERROR"] : null;
      $connexion->debug_type = isset($_CONST["POOL"][$pool]["DEBUG"]) ? $_CONST["POOL"][$pool]["DEBUG"] : null;

    }else{
      // Génération d'une DataBaseException
      throw new DataBaseException("La base de données  '".$dbname."' n'est pas configurée !");
    }
    return $connexion;
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
   * Traite les erreurs
   *
   */
  public static function handleError($connection, $exception){
    self::debug($connection, 0, $exception);

  		switch($connection->error_type){
  		  case 'exception':
  		    throw $exception;
  		    break;

  		  case 'silent':
  		    $exception->log();
  		    break;
  		    	
  		  case 'warning':
  		  default:
  		    $exception->warn();
  		}
  }

  /**
   * Construit le tableau de log qui sera envoyé par le destructeur pour debug (évitez au maximum de l'utiliser en PROD)
   *
   */
  public static function debug($connection, $time=0, $databaseException=null) {

    if(!empty($connection->debug_type)) {
      //$error = !empty($connection->errorParams['errorInfo']) ? $connection->errorParams['errorInfo'] : null;
      //if($connection->debug_type=='errfile' && empty($error)) return;

      $aBacktrace = array();
      foreach(debug_backtrace() as $key=>$val) {
        if(!isset($val['class']) || strpos($val['class'], 'DB')!==0) {
          $aBacktrace[]='<strong>'.(isset($val['class']) ? $val['class'].'::' : '').$val['function'].'</strong> called at ['.$val['file'].':'.$val['line'].']';
        }
      }

      self::$queryLog[$connection->databaseName.'|'.$connection->debug_type][] = array(
                'time'       => sprintf("%01.5f", $time),
                'query'      => $connection->query,
                'bind'       => $connection->param,
                'result'     => $databaseException===null ? $connection->data : $databaseException->getMessage(),
                'backtrace'  => $aBacktrace,
                'error'		 => $databaseException===null ? false : true
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
   * 		Traite le debug (attention: Pour le mode "firebug", verifiez "output_buffering = On" dans votre conf PHP)
   */
  function __destruct() {

    try{
      if(!empty(self::$queryLog))
      foreach(self::$queryLog as $databaseName_logType=>$qLogs) {
        list($databaseName, $logType) = explode('|', $databaseName_logType);

        switch($logType) {
          case 'firebug':
            // On verifie que la bufferisation est activée (sinon => erreur "Headers already sent ...") et la classe FirePHP chargée
            $buffer_status = ob_get_status();
            if(!empty($buffer_status) && class_exists('FirePHP')) {
              $firephp = FirePHP::getInstance(true);

              $table = array();
              $table[] = array('Time','Query','Bind','Result');
               
              $totalTime = 0;
              foreach($qLogs as $qLog) {
                $table[] = array($qLog['time'], $qLog['query'], $qLog['bind'], $qLog['result']);
                $totalTime += $qLog['time'];
              }

              $firephp->table('SQL: '.$databaseName.' ('.count($qLogs).' @ '.$totalTime.')', $table);
            }
            break;
          case 'screen':
            foreach($qLogs as $qLog) {
              echo $databaseName.' ('.$qLog['time'].'): '.self::mergeBindInQuery($qLog['query'], $qLog['bind']).'<br/>';
              if($qLog['error'])
              echo " => ".$qLog['result'].'<br/>';
            }
            break;
            /*case 'file':
             case 'errfile':
             $log = '';
             foreach($qLogs as $qLog) {
             $log.= date('d/m/Y H:i:s').': '.$databaseName.' ('.$qLog['time'].")\n";
             $log.= strip_tags(implode("\n", $qLog['backtrace']))."\n";
             $log.= self::mergeBindInQuery($qLog['query'], $qLog['bind']).(!empty($qLog['error']) ? "\nERROR: ".$qLog['error'][2] : '')."\n\n";
             }

             $logFile = dirname(__FILE__).'/../../../log/' . ($logType=='errfile' ? 'SQLerr' : 'SQLlog');
             if(isset($_SESSION['connexion']['etab'])) {
             $etab = unserialize($_SESSION['connexion']['etab']);
             $logFile .= '-'.$etab->id;
             }
             file_put_contents($logFile.'.txt', $log, FILE_APPEND);
             break;*/
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
  public function __construct($message='', $code=0) {
    parent::__construct($message, (int)$code);
  }

  public function warn() {
    trigger_error($this->message, E_USER_WARNING);
  }
   
  public function log() {
  		//error_log(print_r($this,true));
  		error_log($this->message);
  }
}
?>
