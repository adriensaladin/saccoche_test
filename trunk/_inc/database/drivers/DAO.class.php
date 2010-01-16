<?php
/**
 * Couche d'abstraction DAO utilisant PDO
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */


class DAO {

	/**
	 * Identifiant de l'objet de connexion
	 * @var PDO $id
	 */
	protected  $id;

	/**
	 * Nom de la base de données
	 * @var string $databaseName
	 */
	public $databaseName;

	/**
	 * Nom du dns de connection à PDO
	 *
	 * @var string $dsn
	 */
	private $dsn;

	/**
	 * Login de connection à la base de données
	 *
	 * @var string $username
	 */
	private $username;

	/**
	 * Password de connection à la base de données
	 *
	 * @var unknown_type
	 */
	private $password;
	
	/**
	 * Objet contenant le résultat de la dernère requête préparée
	 * @var PDOStatement $result
	 */
	public $lPrepare;

	/**
	 * Tableau contenant les résultats des requêtes préparées
	 * @var PDOStatement $result
	 */
	public $aPrepare;

	/**
	 * Tableau contenant le résultat de la dernière requête. Tableau à 2 niveaux (champs, lignes)
	 * @var mixed $data
	 */
	public $data;

	/**
	 * Requête en cours
	 * @var string $query
	 */
	public $query;

	/**
	 * Tableau contenant les variables bind de la requête en cours
	 * @var array $param
	 */
	public $param;

	/**
	 * Commiter automatiquement ou non les requêtes (true par défaut)
	 * @var boolean $autoCommit
	 */
	public $autoCommit = true;

	/**
	 * Indique le début d'une transaction pour exécuter un commit ou un rollback à la fin de la connexion
	 * @var boolean $transaction
	 */
	public $transaction = false;

	/**
	 * Paramètres de la requête lors d'une erreur
	 *
	 * @var array $errorParams
	 */
	public $errorParams;
	
	/**
	 * Type de log des requêtes
	 *
	 * @var string $logType
	 */
	public $logType;

	/**
	 * Constructeur. Permet de se connecter à la base de données
	 *
	 * @param string  $databaseName  le nom de la base de données
	 * @param string  $username   le nom de l'utilisateur servant à la connexion
	 * @param string  $password   le mot de passe de connexion
	 * @param string  $host    l'adresse IP du serveur
	 * @param string  $port    le port de connexion (optionnel) : 3306 par défaut
	 * @param array		$driverOptions	paramètres spécifiques de connexion au driver (persistance, encodage...)
	 * @return void
	 */
	function __construct($databaseType, $databaseName, $username, $password, $host, $port = "3306", $driverOptions = array()) {

		$this->databaseType = $databaseType;
		$this->databaseName = $databaseName;
		$this->username = $username;
		$this->password = $password;
		$this->errorParams = array();
		$this->autoCommit = true;

		// Construction de la chaîne de connexion
		switch ($this->databaseType) {
			case 'mysql': case 'sybase': case 'mssql':
				$this->dsn = $this->databaseType.':host='.$host.';dbname='.$this->databaseName.';port='.$port;
				break;
			case 'oracle':
				$this->dsn = 'oci:dbname=//'.$host.':'.$port.'/'.$this->databaseName;
				break;
			case 'firebird':
				$this->dsn = $this->databaseType.':User='.$username.';Password='.$password.';Database='.$this->databaseName.';DataSource='.$host.';Port='.$port.';';
				break;
			case 'pgsql':
				$this->dsn = $this->databaseType.':host='.$host.' port='.$port.' dbname='.$this->databaseName.' user='.$username.' password='.$password;
				break;
		}

		try{
			$driverOptions[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
			$this->id = new PDO($this->dsn, $this->username, $this->password, $driverOptions);
		} catch (PDOException $e) {
			//Génération d'une DataBaseException
			throw new DataBaseException("Erreur de connexion PDO",$e->getMessage(),$this->errorParams);
		}
	}

	/**
	 * Récupération des données issues d'une requête (lignes, champs, types de champs, lignes affectées)
	 *
	 * @return void
	 * @param bool $indexkey si true alors prend la première colonne des resultats comme indice du tableau de resultats
	 * @param bool $fetchAll si true alors ne fait qu'un seul fetch
	 */
	private function queryInit($indexkey = false, $fetchAll = true) {
		try{
			if(!$indexkey) {
				if($fetchAll) {
					$this->data = @$this->lPrepare->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$this->data = @$this->lPrepare->fetch(PDO::FETCH_ASSOC);
				}
			}
			else {
				$this->data = @$this->lPrepare->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
			}
			
			if(!is_array($this->data)) $this->data=array();			
						
		} catch (PDOException $e) {
			//Génération d'une DataBaseException
			throw new DataBaseException("Erreur sur le fetchAll() de PDO",$e->getMessage(),"");
		}
	}
	
	/**
	 * Permet d'exécuter une requête.
	 *
	 * Aucun résultat n'est renvoyé par cette fonction. Elle doit être utilisé pour effectuer
	 * des insertions, des updates... Elle est de même utilisée par les
	 * autres fonction de la classe comme queryRow() et queryTab().
	 *
	 * @param string $query chaine SQL
	 * @param mixed $param variables bind de type array(":bind"=>"value")
	 * @return void
	 */
	public function query($query, $param = array()) {

		global $sysNbQuery;
		$query = (isset($_SERVER['HOSTNAME']) ? '/*SIG'.$_SERVER['HOSTNAME'].'SIG*/ ' : '').$query;

		// execution de la requête
		$this->query = $query;
		$this->param = $param;
		$this->errorParams['Request'] = $query;
		$this->errorParams['Bind'] = $param;

		$this->id->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->getCommitMode());
		$this->id->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		
		DatabaseManager::log($this);

		$sysNbQuery = (!isset($sysNbQuery) || $sysNbQuery<=0) ? 1 : $sysNbQuery+1;
		
		$queryMD5 = md5($query);					
		if(!isset($this->aPrepare[$queryMD5])) {
    	$this->lPrepare = $this->id->prepare($query);
    	$this->aPrepare[$queryMD5] = $this->lPrepare;
		} else {
			$this->lPrepare = $this->aPrepare[$queryMD5];
		}  

		if ($this->lPrepare!==false) {
			// Bind des paramètres
			if ($param) {
				foreach ($param as $key => $val) {
					if (strpos($query, $key) !== false) {
						if($param[$key]===null) $this->lPrepare->bindParam(trim($key), $param[$key], PDO::PARAM_NULL);
						else $this->lPrepare->bindParam(trim($key), @stripslashes($param[$key])); // sinon avertissement : "Only variables should be passed by reference"
					}
				}
			}

			if(!(isset($_GET['show']) && eregi('sqltest', $_GET['show']))) { // Mode test des requêtes (affichage sans execution)
				$this->lPrepare->execute();
				
				if ($this->lPrepare->errorCode() != PDO::ERR_NONE) {
					$this->errorParams['errorInfo'] = $this->lPrepare->errorInfo();
					echo'<xmp>Erreur execute() de PDO : '.$this->errorParams['errorInfo'][2];print_r($query);echo'</xmp>';
					DatabaseManager::log($this, true);
					//Génération d'une DataBaseException
					//throw new DataBaseException("Erreur execute() de PDO",$error[2],$this->errorParams);
				}
			}
		}else{
			$this->errorParams['errorInfo'] = $this->lPrepare->errorInfo();
			echo'<xmp>Erreur prepare() de PDO : '.$this->errorParams['errorInfo'][2];print_r($query);echo'</xmp>';
			DatabaseManager::log($this, true);
			//throw new DataBaseException("Erreur prepare() de PDO",$this->id->errorInfo(),$this->errorParams);
		}
	}

	/**
	 *
	 * Permet d'exécuter une requête devant renvoyer une seule ligne de résultat.
	 * le tableau de résultat est à 2 niveaux (lignes, champs)
	 *
	 * @param string $query chaine SQL
	 * @param mixed $param variables bind de type array(":bind"=>"value")
	 * @return mixed
	 */
	public function queryRow($query, $param = array()) {

		$this->query($query, $param);
		//Récupération des données issues d'une requête
		$this->queryInit(false, false);
		
		return ($this->data);
	}


	/**
	 *
	 * Permet d'exécuter une requête devant renvoyer plusieurs lignes de résultat.
	 * le tableau de résultat est à 2 niveaux (lignes, champs)
	 *
	 * @param string $query chaine SQL
	 * @param mixed $param variables bind de type array(":bind"=>"value")
	 * @param bool $indexkey si true alors prend la première colonne des resultats comme indice du tableau de resultats
	 * @return mixed
	 */
	public function queryTab($query, $param = array(), $indexkey = false) {

		$this->query($query, $param);
		//Récupération des données issues d'une requête
		$this->queryInit($indexkey);

		return ($this->data);
	}

	/**
	 * Permet de fermer la connexion avec la base
	 *
	 * @return void
	 */
	private function close() {

		// Si une transaction a été ouverte, il faut faire commit ou un rollback
		if($this->transaction){
			if (!connection_aborted()) {
				$this->commit();
			} else {
				$this->rollback();
			}
		}

		// Fermeture de la connexion
		$this->id = null;
		return null;
	}

	/**
	 * Définition du commit automatique ou non
	 *
	 * @param boolean activation du commit (true par défaut)
	 * @return void
	 */
	public function setAutoCommit($bCommit = true) {
		$this->autoCommit = $bCommit;
	}

	/**
	 * Retourne la constante d'exécution d'une requête : commit immédiat ou non
	 *
	 * @return bool
	 */
	public function getCommitMode() {
		if ($this->autoCommit) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Initialise le début d'une transaction (autocommit à false par défaut)
	 *
	 * @return boolean
	 */
	public function beginTransaction (){
		if ($this->id && !$this->transaction) {
			try{
				$this->autoCommit = false;
				$temp = @$this->id->beginTransaction();
				$this->transaction = true;
			} catch (PDOException $e) {
				//Génération d'une DataBaseException
				throw new DataBaseException("Erreur du beginTransaction() de PDO",$e->getMessage(),"");
			}
			return $temp;
		} else {
			return true;
		}
	}

	/**
	 * Commit des requêtes exécutées
	 *
	 * @return boolean
	 */
	public function commit() {
		if ($this->id) {
			try{
				$temp = @$this->id->commit();
				$this->transaction = false;
			} catch (PDOException $e) {
				//Génération d'une DataBaseException
				throw new DataBaseException("Erreur du commit() de PDO",$e->getMessage(),"");
			}
			return $temp;
		} else {
			return true;
		}
	}

	/**
	 * Rollback des requêtes exécutées
	 *
	 * @return boolean
	 */
	public function rollback() {
		if ($this->id) {
			try{
				$temp = @$this->id->rollBack();
				$this->transaction = false;
			} catch (PDOException $e) {
				//Génération d'une DataBaseException
				throw new DataBaseException("Erreur du rollBack() de PDO",$e->getMessage(),"");
			}
			return $temp;
		} else {
			return true;
		}
	}

	/**
	 * Permet de récupérer l'id du dernier objet inséré dans la base
	 *
	 * @return int $lastInsertedId
	 */
	public function getLastOid() {
		$lastInsertedId = "";
		try{
			$lastInsertedId = $this->id->lastInsertId();
		}catch (PDOException $e) {
			//Génération d'une DataBaseException
			throw new DataBaseException("Erreur du lastInsertId() de PDO",$e->getMessage(),"");
		}
		return $lastInsertedId > 0 ? $lastInsertedId : null;
	}

	/**
	 * Permet de récupérer le nombre de lignes affectées par la dernière requete
	 *
	 * @return int $lastInsertedId
	 */
	public function rowCount() {
		$numRows = 0;
		try{
			if($this->lPrepare) {
				$numRows = $this->lPrepare->rowCount();
			}
		}catch (PDOException $e) {
			//Génération d'une DataBaseException
			throw new DataBaseException("Erreur du rowCount de PDO",$e->getMessage(),"");
		}
		return $numRows;
	}

	/**
	 *  Permet de sélectionner la base de données concernée
	 *
	 * @param unknown_type $dbname
	 */
	public function selectDB($dbname) {

		if($dbname != $this->databaseName){
			$this->databaseName = $dbname;
			switch ($this->databaseType) {
				case 'mysql':
					$this->id->exec("use ".$dbname.";");
					break;
				case 'mssql':
					$this->id->exec("use ".$dbname." GO");
					break;
				case 'oracle':
					$dsn = 'oci:dbname=//'.$host.':'.$port.'/'.$this->databaseName;
					$this->id = new PDO($this->dsn, $this->username, $this->password);
					break;
				default:
					break;
			}

		}
	}


	/**
	 * Destructeur de la classe
	 *
	 */
	public function __destruct() {
		$this->close();
	}
}
?>
