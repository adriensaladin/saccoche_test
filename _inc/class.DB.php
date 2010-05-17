<?php
/**
 * Façade permettant de simplifier l'accès aux objet de connexion
 *
 * Cet objet permet d'accéder directement aux méthodes begin, query, queryTab, queryRow, commit, rollback
 * en passant le nom de la connection
 * Dernière version disponible à l'adresse https://svn.devsesamath.net/svn/labomep/trunk/includes/classes/
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */

// Classe de gestion des connexions aux pools
require_once("database/DatabaseManager.class.php");

/*
SELECT une LIGNE			:	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT ... FROM ... WHERE ... LIMIT 1' , $DB_VAR );
nb lignes retournées	:	count($DB_ROW)

SELECT multi LIGNE		:	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT ... FROM ... WHERE ...' , $DB_VAR [, bool $indexkey] );
nb lignes retournées	:	count($DB_TAB)

UPDATE								:	DB::query(SACOCHE_STRUCTURE_BD_NAME, 'UPDATE ... SET ... WHERE ...' , $DB_VAR );
nb lignes modifiées		:	DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);

DELETE								:	DB::query(SACOCHE_STRUCTURE_BD_NAME, 'DELETE ... FROM ... WHERE ...' , $DB_VAR );
nb lignes modifiées		:	DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);

REPLACE								:	DB::query(SACOCHE_STRUCTURE_BD_NAME, 'REPLACE INTO ... VALUES ...' , $DB_VAR );
nb lignes modifiées		:	DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);	// (comme un REPLACE c'est un DELETE + un INSERT, à tester si ça renvoie le double de lignes remplacées ou pas...)

INSERT								:	DB::query(SACOCHE_STRUCTURE_BD_NAME, 'INSERT INTO ... VALUES ...' , $DB_VAR );
valeur AUTO-INCREMENT	: DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);

Pour fermer la connexion (évite un bug avec les requêtes multiples) :
DB::close(SACOCHE_STRUCTURE_BD_NAME);

Dans ./satabase/DatabaseFactory.class.php correction d'un pb dû à l'oubli de la constante MYSQL_ATTR_INIT_COMMAND en PHP 5.3, et sa valeur 1002 ne passe pas : http://bugs.php.net/bug.php?id=47224

*/

class DB {
	
	/**
	 * Permet d'exécuter une requête.
	 *
	 * Aucun résultat n'est renvoyé par cette fonction. Elle doit être utilisé pour effectuer
	 * des insertions, des updates... Elle est de même utilisée par les
	 * autres fonctions de la classe comme queryRow() et queryTab().
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @param string $query chaine SQL
	 * @param mixed $param variables bind de type array(":bind"=>"value")
	 * @return void
	 */
	public static function query($connection_name, $query, $param=""){
		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		if(is_object($connection)){
			$time_start = microtime(true);
			$connection->query($query, $param);
			DatabaseManager::setLog($connection, microtime(true) - $time_start);			
		}
	}

	/**
	 *
	 * Permet d'exécuter une requête devant renvoyer une seule ligne de résultat.
	 * le tableau de résultat est à 2 niveaux (lignes, champs)
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @param string $query chaine SQL
	 * @param mixed $param variables bind de type array(":bind"=>"value")
	 * @return mixed
	 */
	public static function queryRow ($connection_name, $query, $param=""){

		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){
			$time_start = microtime(true);
			$rs = $connection->queryRow($query, $param);
			DatabaseManager::setLog($connection, microtime(true) - $time_start);
		}

		return $rs;
	}

	/**
	 *
	 * Permet d'exécuter une requête devant renvoyer plusieurs lignes de résultat.
	 * le tableau de résultat est à 2 niveaux (lignes, champs)
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @param string $query chaine SQL
	 * @param mixed $param variables bind de type array(":bind"=>"value")
	 * @param bool $indexkey si true alors prend la première colonne des resultats comme indice du tableau de resultats
	 * @return mixed
	 */
	public static function queryTab ($connection_name, $query, $param="", $indexkey=false){

		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){			
			$time_start = microtime(true);
			$rs = $connection->queryTab($query, $param, $indexkey);		
			DatabaseManager::setLog($connection, microtime(true) - $time_start);
			
		}
		
		return $rs;
	}

	/**
	 * Initialise le début d'une transaction (autocommit à false par défaut)
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @return boolean
	 */
	public static function begin ($connection_name){

		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){
			$rs = $connection->beginTransaction();
		}
		return $rs;
	}

	/**
	 * Commit des requêtes exécutées
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @return boolean
	 */
	public static function commit($connection_name){
		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){
			$rs = $connection->commit();
		}
		return $rs;
	}

	/**
	 * Rollback des requêtes exécutées
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @return boolean
	 */
	public static  function rollback($connection_name) {
		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){
			$rs = $connection->rollback();
		}
		return $rs;
	}

	/**
	 * Permet de récupérer l'id du dernier objet inséré dans la base, si la requête est de type INSERT
	 *
	 * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @return mixed
	 */
	public static  function getLastOid($connection_name) {
		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);

		$result = $connection->getLastOid();

		return $result;
	}
	
	/**
   * Permet de récupérer le nombre d'enregistrements affectés par la dernière requete
   *
   * @param string $connection_name nom de la connection définie dans le fichier de configuration
	 * @return mixed
   */
  public static  function rowCount($connection_name) {
  	$databaseManager = DatabaseManager::getInstance();
  	$connection = $databaseManager->getConnexion($connection_name);
  	
  	$result = $connection->rowCount();
  	
  	return $result;
  }
  
  /**
   * Permet de fermer une connexion à la base de données (ne sert que dans des cas bien préçis, ne pas utiliser si pas necessaire)
   *
   * @param string $connection_name nom de la connection définie dans le fichier de configuration
   */
  public static  function close($connection_name) {
  	$databaseManager = DatabaseManager::getInstance();
  	return $databaseManager->closeConnexion($connection_name);
  }
}
?>
