<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 *
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 *
 * Ce fichier est une partie de SACoche.
 *
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 *
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 *
 */
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent que la base WEBMESTRE (donc une installation multi-structure).
// Ces méthodes ne concernent que les utilisateurs non identifiés.

class DB_WEBMESTRE_PUBLIC extends DB
{

/**
 * Récupérer les informations concernant les tables présentes dans la base.
 *
 * Retourne une ligne par table, avec pour chacune les champs Engine / Version / Row_format / Rows / Avg_row_length / Data_length / Max_data_length / Index_length / Data_free / Auto_increment / Create_time / Update_time / Check_time / Collation / Checksum / Create_options / Comment
 *
 * @param void
 * @return array
 */
public function DB_recuperer_tables_informations()
{
	$DB_SQL = 'SHOW TABLE STATUS ';
	$DB_SQL.= 'LIKE "sacoche_%" ';
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}
/**
 * Récupérer la valeur d'une variable système de MySQL
 *
 * Retourne un tableau a deux entrées : "Variable_name" (le nom de la variable) et "Value" (sa valeur).
 *
 * @param string $variable_nom   max_allowed_packet | max_user_connections | group_concat_max_len
 * @return array
 */
public function DB_recuperer_variable_MySQL($variable_nom)
{
	$DB_SQL = 'SHOW VARIABLES LIKE "'.$variable_nom.'"';
	return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la version de MySQL
 *
 * Avec une connexion classique style mysql_connect() on peut utiliser mysql_get_server_info() .
 *
 * @param void
 * @return string
 */
public function DB_recuperer_version_MySQL()
{
	$DB_SQL = 'SELECT VERSION()';
	return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer l'identifiant d'une base d'établissement à partir du numéro UAI d'une structure (mode multi-structures)
 *
 * @param string uai
 * @return int | NULL
 */
public function DB_recuperer_structure_id_base_for_UAI($uai)
{
	$DB_SQL = 'SELECT sacoche_base ';
	$DB_SQL.= 'FROM sacoche_structure ';
	$DB_SQL.= 'WHERE structure_uai=:uai ';
	$DB_VAR = array(':uai'=>$uai);
	return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer le nom d'un établissement à partir du numéro de la base d'une structure (mode multi-structures)
 *
 * @param int base_id
 * @return string | NULL
 */
public function DB_recuperer_structure_nom_for_Id($base_id)
{
	$DB_SQL = 'SELECT structure_denomination ';
	$DB_SQL.= 'FROM sacoche_structure ';
	$DB_SQL.= 'WHERE sacoche_base=:base_id ';
	$DB_VAR = array(':base_id'=>$base_id);
	return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter le nombre de structure inscrites (mode multi-structures)
 *
 * @param void
 * @return string   n . structure(s)
 */
public function DB_compter_structure()
{
	$DB_SQL = 'SELECT COUNT(sacoche_base) AS nombre ';
	$DB_SQL.= 'FROM sacoche_structure ';
	$DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
	$s = ($DB_ROW['nombre']>1) ? 's' : '' ;
	return $DB_ROW['nombre'].' structure'.$s;
}

/**
 * Créer les tables de la base du webmestre et les remplir (mode multi-structures)
 *
 * @param void
 * @return void
 */
public function DB_creer_remplir_tables_webmestre()
{
	$tab_files = array_diff( scandir(CHEMIN_SQL_WEBMESTRE) , array('.','..') ); // fonction Lister_Contenu_Dossier() inaccessible depuis la classe
	foreach($tab_files as $file)
	{
		$extension = pathinfo($file,PATHINFO_EXTENSION);
		if($extension=='sql')
		{
			$requetes = file_get_contents(CHEMIN_SQL_WEBMESTRE.$file);
			DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
			/*
			La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
			La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
			*/
			DB::close(SACOCHE_WEBMESTRE_BD_NAME);
		}
	}
}


}
?>