<?php
/**
 * Façade permettant de simplifier l'accès aux objet de connexion
 *
 * Cet objet permet d'accéder directement aux méthodes begin, query, queryTab, queryRow, commit, rollback
 * en passant le nom de la connection
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */

// Classe de gestion des connexions aux pools
require_once("database/DatabaseManager.class.php");
 
 
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
	public static function query ($connection_name, $query, $param=""){
		if(isset($_GET['show']) && eregi('sql', $_GET['show'])){
			$time_start = microtime(true);
		}

		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = "";
		if(is_object($connection)){
			if($param != ""){
				array_map("html_entity_decode",$param);
			}
			$connection->query($query, $param);
		}
		if(isset($_GET['show']) && eregi('sql', $_GET['show'])){
			$time_end = microtime(true);
			$duree = $time_end - $time_start;
			echo sprintf("%01.5f", $duree)."&nbsp;sec<br/><br/>";
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
		if(isset($_GET['show']) && eregi('sql', $_GET['show'])){
			$time_start = microtime(true);
		}

		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){
			if($param != ""){
				array_map("html_entity_decode",$param);
			}
			$rs = $connection->queryRow($query, $param);
		}

		if(isset($_GET['show']) && eregi('sql', $_GET['show'])){
			$time_end = microtime(true);
			$duree = $time_end - $time_start;
			echo sprintf("%01.5f", $duree)."&nbsp;sec<br/><br/>";
		}

		if(isset($_GET['show']) && eregi('explain', $_GET['show'])){
			$expl = $connection->queryRow('explain ' . $query, $param);
			echo "<br>###EXPLAIN###:<pre>";print_r($expl);echo"</pre>";
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
	public static function queryTab ($connection_name, $query, $param=null, $indexkey=false){
		if(isset($_GET['show']) && eregi('sql', $_GET['show'])){
			$time_start = microtime(true);
		}		
		
		$databaseManager = DatabaseManager::getInstance();
		$connection = $databaseManager->getConnexion($connection_name);
		$rs = false;
		if(is_object($connection)){
			if($param !== null){
				array_map("html_entity_decode",$param);
			}
			$rs = $connection->queryTab($query, $param, $indexkey);
		}

		if(isset($_GET['show']) && eregi('sql', $_GET['show'])){
			$time_end = microtime(true);
			$duree = $time_end - $time_start;
			echo sprintf("%01.5f", $duree)."&nbsp;sec<br/><br/>";
		}
		
		if(isset($_GET['show']) && eregi('explain', $_GET['show'])){
			$expl = $connection->queryRow('explain ' . $query, $param);
			echo "<br>###EXPLAIN###:<pre>";print_r($expl);echo"</pre>";
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
}
?>
