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

/** Classe d'abstraction DAO utilisant PDO */
require_once("drivers/DAO.class.php");
/** Classe d'abstraction MYSQL */
//require_once("drivers/Mysql.class.php"); 
 
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
	 			// Gestion des options du driver PDO
		 		if(isset($_CONST["POOL"][$pool]["FORCE_ENCODING"]) && $_CONST["POOL"][$pool]["FORCE_ENCODING"]!='') {
		 			$driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES ".$_CONST["POOL"][$pool]["FORCE_ENCODING"];
		 		}
		 		if(isset($_CONST["POOL"][$pool]["PERSISTENT"]) && $_CONST["POOL"][$pool]["PERSISTENT"]===true) {
		 			$driverOptions[PDO::ATTR_PERSISTENT] = true;
		 		} 
	 			
	 			// Ouverture d'une connexion avec PDO
	 			$connexion = new DAO($_CONST["POOL"][$pool]["TYPE"], $dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);
	 		}elseif($_CONST["POOL"][$pool]["ABSTRACTION"] == "MYSQL"){
				// Ouverture d'une connexion avec MYSQL
	 			$connexion = new Mysql($_CONST["POOL"][$pool]["TYPE"],$dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);				
	 		}else{
	 			// Génération d'une DataBaseException
	 			throw new DataBaseException("Erreur de connection '".$dbname."'","La couche d'abastraction '".$_CONST["POOL"][$pool]["ABSTRACTION"]."' ne peut pas être impléméntée","");
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