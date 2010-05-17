<?php
/**
 * Factory de création des accès aux bases de données
 *
 * Dans cette classe est définie la méthode permettant d'instancier l'objet d'abstraction de
 * connexion à la base de données à partir des paramètres définis dans le fichier de configuration 
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */

abstract class DatabaseFactory {
	
	 /**
	  * Cette méthode permet de créer l'objet de connexion à une base de données
	  *
	  * @param string $pool Nom du pool de connection
	  * @param string $dbname Nom de la base de données
	  * @return DatabaseInterface
	  */
	 static function createConnexion($pool, $dbname){
	 	global $_CONST;
	 	
	 	if (isset($dbname) && isset($_CONST["POOL"][$pool]["ABSTRACTION"])){
	 		$driverOptions = array();    
	 		
	 		// Connexion
	 		if($_CONST["POOL"][$pool]["ABSTRACTION"] == "PDO"){
	 			// Classe d'abstraction DAO utilisant PDO
				require_once("drivers/DAO.class.php");
	 			
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
	 			$connexion = new DAO($_CONST["POOL"][$pool]["TYPE"], $dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);
	 			
	 			if(isset($fix_force_encoding_bug) && $fix_force_encoding_bug) {
 					$connexion->query("SET NAMES ".$_CONST["POOL"][$pool]["FORCE_ENCODING"]);
 				}
				
	 			
	 		}elseif($_CONST["POOL"][$pool]["ABSTRACTION"] == "MYSQL"){
	 			/** Classe d'abstraction MYSQL */
				require_once("drivers/Mysqlux.class.php"); 
	 			
				// Ouverture d'une connexion avec MYSQL
	 			$connexion = new Mysqlux($_CONST["POOL"][$pool]["TYPE"],$dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);				
	 			
	 			if(isset($_CONST["POOL"][$pool]["FORCE_ENCODING"]) && $_CONST["POOL"][$pool]["FORCE_ENCODING"]!='') {
 					$connexion->query("SET NAMES ".$_CONST["POOL"][$pool]["FORCE_ENCODING"]);
 				}
	 			
	 		}else{
	 			// Génération d'une DataBaseException
	 			throw new DataBaseException("Erreur de connection '".$dbname."'","La couche d'abastraction '".$_CONST["POOL"][$pool]["ABSTRACTION"]."' n'est pas impléméntée !","");
	 		}
	 		
	 		$connexion->logType = isset($_CONST["POOL"][$pool]["LOG"]) ? $_CONST["POOL"][$pool]["LOG"] : null;
	 		
	 	}else{
	 		// Génération d'une DataBaseException
	 		throw new DataBaseException("Erreur de connection '".$dbname."'","La base de données  '".$dbname."' n'est pas configurée !","");
	 	}	 	
	 	return $connexion;
	 }


}
?>