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

/**
 * DB_WEBMESTRE_recuperer_structure (complémentaire à DB_WEBMESTRE_lister_structures car utilisation de queryRow à la place de queryTab)
 *
 * @param int base_id
 * @return array
 */

function DB_WEBMESTRE_recuperer_structure($base_id)
{
	$DB_SQL = 'SELECT * FROM sacoche_structure ';
	$DB_SQL.= 'WHERE sacoche_base=:base_id ';
	$DB_SQL.= 'LIMIT 1 ';
	$DB_VAR = array(':base_id'=>$base_id);
	return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_WEBMESTRE_recuperer_structure_by_UAI
 *
 * @param string uai
 * @return array
 */

function DB_WEBMESTRE_recuperer_structure_by_UAI($uai)
{
	$DB_SQL = 'SELECT * FROM sacoche_structure ';
	$DB_SQL.= 'WHERE structure_uai=:uai ';
	$DB_SQL.= 'LIMIT 1 ';
	$DB_VAR = array(':uai'=>$uai);
	return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_WEBMESTRE_compter_structure
 *
 * @param void
 * @return string   n structures
 */

function DB_WEBMESTRE_compter_structure()
{
	$DB_SQL = 'SELECT COUNT(sacoche_base) AS nombre FROM sacoche_structure ';
	$DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
	$s = ($DB_ROW['nombre']>1) ? 's' : '' ;
	return $DB_ROW['nombre'].' structure'.$s;
}

/**
 * DB_WEBMESTRE_lister_zones
 *
 * @param void
 * @return array
 */

function DB_WEBMESTRE_lister_zones()
{
	$DB_SQL = 'SELECT * FROM sacoche_geo ';
	$DB_SQL.= 'ORDER BY geo_ordre ASC';
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_WEBMESTRE_lister_structures (complémentaire à DB_WEBMESTRE_recuperer_structure car utilisation de queryTab à la place de queryRow)
 *
 * @param void|string $listing_base_id   id des bases séparés par des virgules (tout si rien de transmis)
 * @return array
 */

function DB_WEBMESTRE_lister_structures($listing_base_id=false)
{
	$nb_ids = substr_count($listing_base_id,',')+1;
	$DB_SQL = 'SELECT * FROM sacoche_structure ';
	$DB_SQL.= 'LEFT JOIN sacoche_geo USING (geo_id) ';
	$DB_SQL.= ($listing_base_id==false) ? '' : 'WHERE sacoche_base IN('.$listing_base_id.') ' ;
	$DB_SQL.= 'ORDER BY geo_ordre ASC, structure_localisation ASC, structure_denomination ASC ';
	$DB_SQL.= ($listing_base_id==false) ? '' : 'LIMIT '.$nb_ids ;
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_WEBMESTRE_lister_contacts_cibles
 *
 * @param string $listing_base_id   id des bases séparés par des virgules
 * @return array                    le tableau est de la forme [i] => array('contact_id'=>...,'contact_nom'=>...,'contact_prenom'=>...,'contact_courriel'=>...);
 */

function DB_WEBMESTRE_lister_contacts_cibles($listing_base_id)
{
	$DB_SQL = 'SELECT sacoche_base AS contact_id , structure_contact_nom AS contact_nom , structure_contact_prenom AS contact_prenom , structure_contact_courriel AS contact_courriel FROM sacoche_structure ';
	$DB_SQL.= 'WHERE sacoche_base IN('.$listing_base_id.') ';
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_WEBMESTRE_tester_zone_nom
 *
 * @param string $geo_nom
 * @param int    $geo_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_WEBMESTRE_tester_zone_nom($geo_nom,$geo_id=false)
{
	$DB_SQL = 'SELECT geo_id FROM sacoche_geo ';
	$DB_SQL.= 'WHERE geo_nom=:geo_nom ';
	$DB_VAR = array(':geo_nom'=>$geo_nom);
	if($geo_id)
	{
		$DB_SQL.= 'AND geo_id!=:geo_id ';
		$DB_VAR[':geo_id'] = $geo_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_WEBMESTRE_tester_structure_UAI
 *
 * @param string $structure_uai
 * @param int    $base_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_WEBMESTRE_tester_structure_UAI($structure_uai,$base_id=false)
{
	$DB_SQL = 'SELECT sacoche_base FROM sacoche_structure ';
	$DB_SQL.= 'WHERE structure_uai=:structure_uai ';
	$DB_VAR = array(':structure_uai'=>$structure_uai);
	if($base_id)
	{
		$DB_SQL.= 'AND sacoche_base!=:base_id ';
		$DB_VAR[':base_id'] = $base_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_WEBMESTRE_ajouter_zone
 *
 * @param int    $geo_ordre
 * @param string $geo_nom
 * @return int
 */

function DB_WEBMESTRE_ajouter_zone($geo_ordre,$geo_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_geo(geo_ordre,geo_nom) ';
	$DB_SQL.= 'VALUES(:geo_ordre,:geo_nom)';
	$DB_VAR = array(':geo_ordre'=>$geo_ordre,':geo_nom'=>$geo_nom);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
}

/**
 * DB_WEBMESTRE_ajouter_structure
 *
 * @param int    $base_id   Pour forcer l'id de la base de la structure ; normalement transmis à 0 (=> auto-increment), sauf dans un cadre de gestion interne à Sésamath
 * @param int    $geo_id
 * @param string $structure_uai
 * @param string $localisation
 * @param string $denomination
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @param string $inscription_date   Pour forcer la date d'inscription, par exemple en cas de transfert de bases académiques (facultatif).
 * @return int
 */

function DB_WEBMESTRE_ajouter_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel,$inscription_date=0)
{
	$chaine_date = ($inscription_date) ? ':inscription_date' : 'NOW()' ;
	// Insérer l'enregistrement dans la base du webmestre
	if($base_id==0)
	{
		$DB_SQL = 'INSERT INTO sacoche_structure(geo_id,structure_uai,structure_localisation,structure_denomination,structure_contact_nom,structure_contact_prenom,structure_contact_courriel,structure_inscription_date) ';
		$DB_SQL.= 'VALUES(:geo_id,:structure_uai,:localisation,:denomination,:contact_nom,:contact_prenom,:contact_courriel,'.$chaine_date.')';
		$DB_VAR = array(':geo_id'=>$geo_id,':structure_uai'=>$structure_uai,':localisation'=>$localisation,':denomination'=>$denomination,':contact_nom'=>$contact_nom,':contact_prenom'=>$contact_prenom,':contact_courriel'=>$contact_courriel,':inscription_date'=>$inscription_date);
		DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
		$base_id = DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
	}
	else
	{
		$DB_SQL = 'INSERT INTO sacoche_structure(sacoche_base,geo_id,structure_uai,structure_localisation,structure_denomination,structure_contact_nom,structure_contact_prenom,structure_contact_courriel,structure_inscription_date) ';
		$DB_SQL.= 'VALUES(:base_id,:geo_id,:structure_uai,:localisation,:denomination,:contact_nom,:contact_prenom,:contact_courriel,'.$chaine_date.')';
		$DB_VAR = array(':base_id'=>$base_id,':geo_id'=>$geo_id,':structure_uai'=>$structure_uai,':localisation'=>$localisation,':denomination'=>$denomination,':contact_nom'=>$contact_nom,':contact_prenom'=>$contact_prenom,':contact_courriel'=>$contact_courriel,':inscription_date'=>$inscription_date);
		DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Génération des paramètres de connexion à la base de données
	$BD_name = 'sac_base_'.$base_id; // Limité à 64 caractères (tranquille...)
	$BD_user = 'sac_user_'.$base_id; // Limité à 16 caractères (attention !)
	$BD_pass = fabriquer_mdp();
	// Créer le fichier de connexion de la base de données de la structure
	fabriquer_fichier_connexion_base($base_id,SACOCHE_WEBMESTRE_BD_HOST,SACOCHE_WEBMESTRE_BD_PORT,$BD_name,$BD_user,$BD_pass);
	// Créer la base de données de la structure
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE DATABASE sac_base_'.$base_id );
	// Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
	// On doit créer en réalité un user sur "localhost" et un autre sur "%" car on doit pouvoir se connecter suivant les configurations depuis la machine locale comme depuis n'importe quel autre serveur (http://dev.mysql.com/doc/refman/5.0/fr/adding-users.html).
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE USER '.$BD_user.'@"localhost" IDENTIFIED BY "'.$BD_pass.'"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE USER '.$BD_user.'@"%" IDENTIFIED BY "'.$BD_pass.'"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.$BD_name.'.* TO '.$BD_user.'@"localhost"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.$BD_name.'.* TO '.$BD_user.'@"%"' );
	/* Il reste à :
		+ Lancer les requêtes pour installer et remplir les tables, éventuellement personnaliser certains paramètres de la structure
		+ Insérer le compte administrateur dans la base de cette structure, éventuellement lui envoyer un courriel
		+ Créer un dossier pour les les vignettes images
	*/
	return $base_id;
}

/**
 * DB_WEBMESTRE_modifier_structure
 *
 * @param int    $base_id
 * @param int    $geo_id
 * @param string $structure_uai
 * @param string $localisation
 * @param string $denomination
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @return void
 */

function DB_WEBMESTRE_modifier_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel)
{
	$DB_SQL = 'UPDATE sacoche_structure ';
	$DB_SQL.= 'SET geo_id=:geo_id,structure_uai=:structure_uai,structure_localisation=:localisation,structure_denomination=:denomination,structure_contact_nom=:contact_nom,structure_contact_prenom=:contact_prenom,structure_contact_courriel=:contact_courriel ';
	$DB_SQL.= 'WHERE sacoche_base=:base_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':base_id'=>$base_id,':geo_id'=>$geo_id,':structure_uai'=>$structure_uai,':localisation'=>$localisation,':denomination'=>$denomination,':contact_nom'=>$contact_nom,':contact_prenom'=>$contact_prenom,':contact_courriel'=>$contact_courriel);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_WEBMESTRE_modifier_zone
 *
 * @param int    $geo_id
 * @param int    $geo_ordre
 * @param string $geo_nom
 * @return void
 */

function DB_WEBMESTRE_modifier_zone($geo_id,$geo_ordre,$geo_nom)
{
	$DB_SQL = 'UPDATE sacoche_geo ';
	$DB_SQL.= 'SET geo_ordre=:geo_ordre,geo_nom=:geo_nom ';
	$DB_SQL.= 'WHERE geo_id=:geo_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':geo_id'=>$geo_id,':geo_ordre'=>$geo_ordre,':geo_nom'=>$geo_nom);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_WEBMESTRE_supprimer_zone
 *
 * @param int $geo_id
 * @return void
 */

function DB_WEBMESTRE_supprimer_zone($geo_id)
{
	$DB_SQL = 'DELETE FROM sacoche_geo ';
	$DB_SQL.= 'WHERE geo_id=:geo_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':geo_id'=>$geo_id);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi mettre à jour les jointures avec les structures
	$DB_SQL = 'UPDATE sacoche_structure ';
	$DB_SQL.= 'SET geo_id=1 ';
	$DB_SQL.= 'WHERE geo_id=:geo_id ';
	$DB_VAR = array(':geo_id'=>$geo_id);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	// Log de l'action
	ajouter_log_SACoche('Suppression de la zone géographique '.$geo_id.'.');
}

/**
 * DB_WEBMESTRE_supprimer_multi_structure
 *
 * @param int    $BASE 
 * @return void
 */

function DB_WEBMESTRE_supprimer_multi_structure($BASE)
{
	global $CHEMIN_MYSQL,$CHEMIN_CONFIG;
	// Paramètres de connexion à la base de données
	$BD_name = 'sac_base_'.$BASE;
	$BD_user = 'sac_user_'.$BASE; // Limité à 16 caractères
	// Supprimer la base associée à la structure
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP DATABASE '.$BD_name );
	// Retirer les droits et supprimer l'utilisateur pour la base de données de la structure
	// Apparemment et curieusement, il faut le droit 'CREATE TEMPORARY TABLES' pour pouvoir effectuer un REVOKE...
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.$BD_user.'@"localhost"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.$BD_user.'@"%"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP USER '.$BD_user.'@"localhost"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP USER '.$BD_user.'@"%"' );
	// Supprimer le fichier de connexion
	unlink($CHEMIN_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php');
	// Supprimer la structure dans la base du webmestre
	$DB_SQL = 'DELETE FROM sacoche_structure ';
	$DB_SQL.= 'WHERE sacoche_base=:base ';
	$DB_VAR = array(':base'=>$BASE);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	// Supprimer le dossier pour accueillir les vignettes verticales avec l'identité des élèves
	Supprimer_Dossier('./__tmp/badge/'.$BASE);
	// Supprimer les éventuels fichiers de blocage
	@unlink($CHEMIN_CONFIG.'blocage_webmestre_'.$BASE.'.txt');
	@unlink($CHEMIN_CONFIG.'blocage_administrateur_'.$BASE.'.txt');
	@unlink($CHEMIN_CONFIG.'blocage_automate_'.$BASE.'.txt');
	// Log de l'action
	ajouter_log_SACoche('Suppression de la structure '.$BASE.'.');
}

/**
 * DB_WEBMESTRE_creer_remplir_tables_webmestre
 *
 * @param string $dossier_requetes   '...../structure/' ou '...../webmestre/'
 * @return void
 */

function DB_WEBMESTRE_creer_remplir_tables_webmestre($dossier_requetes)
{
	$tab_files = Lister_Contenu_Dossier($dossier_requetes);
	foreach($tab_files as $file)
	{
		$extension = pathinfo($file,PATHINFO_EXTENSION);
		if($extension=='sql')
		{
			$requetes = file_get_contents($dossier_requetes.$file);
			DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
			/*
			La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
			La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
			*/
			DB::close(SACOCHE_WEBMESTRE_BD_NAME);
		}
	}
}

/**
 * Retourner un tableau [valeur texte optgroup] des structures (choix d'établissements en page d'accueil)
 * l'indice géographique sert à pouvoir regrouper les options
 *
 * @param void
 * @return array|string
 */

function DB_WEBMESTRE_OPT_structures_sacoche()
{
	$DB_SQL = 'SELECT * FROM sacoche_structure ';
	$DB_SQL.= 'LEFT JOIN sacoche_geo USING (geo_id) ';
	$DB_SQL.= 'ORDER BY geo_ordre ASC, structure_localisation ASC, structure_denomination ASC';
	$DB_TAB = DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
	if(count($DB_TAB))
	{
		$tab_retour_champs = array();
		foreach($DB_TAB as $DB_ROW)
		{
			$GLOBALS['tab_select_optgroup'][$DB_ROW['geo_id']] = $DB_ROW['geo_nom'];
			$tab_retour_champs[] = array('valeur'=>$DB_ROW['sacoche_base'],'texte'=>$DB_ROW['structure_localisation'].' | '.$DB_ROW['structure_denomination'],'optgroup'=>$DB_ROW['geo_id']);
		}
		return $tab_retour_champs;
	}
	else
	{
		return 'Aucun établissement n\'est enregistré !';
	}
}

?>