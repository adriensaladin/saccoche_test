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

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent que le socle (validation, besoin d'infos pour les bilans socle, ...).

class DB_STRUCTURE_SOCLE extends DB
{

/**
 * Lister les items des référentiels reliés au socle
 *
 * @param void
 * @return array
 */
public function DB_recuperer_associations_entrees_socle()
{
	$DB_SQL = 'SELECT entree_id , item_nom , matiere_ref , niveau_ref , ';
	$DB_SQL.= 'CONCAT(domaine_ref,theme_ordre,item_ordre) AS item_ref ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'WHERE entree_id>0 AND (matiere_id IN('.$_SESSION['MATIERES'].') OR matiere_partage=:partage) AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') ' ; // Test matiere pour éviter des matières décochées par l'admin.
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les piliers d'un palier donné
 *
 * @param int $palier_id   id du palier
 * @return array|string
 */
public function DB_recuperer_piliers($palier_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_socle_pilier ';
	$DB_SQL.= 'WHERE palier_id=:palier_id ';
	$DB_SQL.= 'ORDER BY pilier_ordre ASC';
	$DB_VAR = array(':palier_id'=>$palier_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_arborescence_pilier
 *
 * @param int      $pilier_id            id du pilier
 * @param string   $listing_domaine_id   id des domaines séparés par des virgules (facultatif, pour restreindre à des domaines précis)
 * @return array
 */
public function DB_recuperer_arborescence_pilier($pilier_id,$listing_domaine_id='')
{
	$where_domaine = ($listing_domaine_id) ? 'AND section_id IN('.$listing_domaine_id.') ' : '';
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_socle_pilier ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
	$DB_SQL.= 'WHERE pilier_id=:pilier_id '.$where_domaine;
	$DB_SQL.= 'ORDER BY section_ordre ASC, entree_ordre ASC';
	$DB_VAR = array(':pilier_id'=>$pilier_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_arborescence_piliers
 *
 * @param string $liste_pilier_id   id des piliers séparés par des virgules
 * @return array
 */
public function DB_recuperer_arborescence_piliers($liste_pilier_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_socle_pilier ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
	$DB_SQL.= 'WHERE pilier_id IN('.$liste_pilier_id.') ' ;
	$DB_SQL.= 'ORDER BY pilier_ordre ASC, section_ordre ASC, entree_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner les résultats pour 1 élève donné, pour 1 item du socle donné
 *
 * @param string $eleve_id
 * @param string $entree_id
 * @param string $user_profil
 * @return array
 */
public function DB_lister_result_eleve_palier($eleve_id,$entree_id)
{
	$DB_SQL = 'SELECT item_id , saisie_note AS note , item_nom , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
	$DB_SQL.= 'matiere_id , '; // Besoin s'il faut filtrer à une langue précise pour la compétence 2
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND entree_id=:entree_id AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') AND saisie_note!="REQ" ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':entree_id'=>$entree_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les élèves actifs (parmi les id transmis) ayant un identifiant Sconet
 *
 * @param string   $listing_eleve_id   id des élèves séparés par des virgules
 * @param bool     $only_sconet_id     restreindre (ou pas) aux élèves ayant un id sconet
 * @return array
 */
public function DB_lister_eleves_cibles_actifs_avec_sconet_id($listing_eleve_id,$only_sconet_id)
{
	$DB_SQL = 'SELECT user_id , user_nom , user_prenom , user_sconet_id ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') AND user_profil=:profil AND user_statut=:statut ';
	$DB_SQL.= $only_sconet_id ? 'AND user_sconet_id>0 ' : '' ;
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>'eleve',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer les informations associées à une liste d'items ; au minimum calcul_methode & calcul_limite, sinon davantage
 *
 * Complément de la fonction DB_lister_result_eleves_palier_sans_infos_items().
 *
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param bool   $detail
 * @return array
 */
public function DB_lister_infos_items($liste_item_id,$detail)
{
	$DB_SQL = 'SELECT item_id , ';
	if($detail)
	{
		$DB_SQL.= 'item_nom , entree_id AS socle_id , ';
		$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
		$DB_SQL.= 'item_coef , item_cart , item_lien , '; // Besoin pour l'élève s'il veut formuler une demande d'évaluation
		$DB_SQL.= 'matiere_id , '; // Besoin pour l'élève s'il ajoute l'item aux demandes d'évaluations
	}
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_referentiel_item ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE item_id IN('.$liste_item_id.') ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les jointures des états de validation des items pour des élèves donnés, pour une liste d'entrées / un domaine / un pilier / un palier
 *
 * @param string   $listing_eleves   id des élèves séparés par des virgules
 * @param string   $listing_entrees  id des entrées séparées par des virgules
 * @param int      $domaine_id       id d'un domaine
 * @param int      $pilier_id        id d'un pilier
 * @param int      $palier_id        id d'un palier
 * @return array
 */
public function DB_lister_jointure_user_entree($listing_eleves,$listing_entrees,$domaine_id,$pilier_id,$palier_id)
{
	if($listing_entrees)
	{
		$DB_SQL = 'SELECT sacoche_jointure_user_entree.* ';
		$DB_SQL.= 'FROM sacoche_jointure_user_entree ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') AND entree_id IN('.$listing_entrees.') ';
		$DB_VAR = array();
	}
	elseif($domaine_id)
	{
		$DB_SQL = 'SELECT sacoche_jointure_user_entree.* ';
		$DB_SQL.= 'FROM sacoche_socle_section ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_entree USING (entree_id) ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') AND section_id=:section_id ';
		$DB_VAR = array(':section_id'=>$domaine_id);
	}
	elseif($pilier_id)
	{
		$DB_SQL = 'SELECT sacoche_jointure_user_entree.* ';
		$DB_SQL.= 'FROM sacoche_socle_pilier ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_entree USING (entree_id) ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') AND pilier_id=:pilier_id ';
		$DB_VAR = array(':pilier_id'=>$pilier_id);
	}
	elseif($palier_id)
	{
		$DB_SQL = 'SELECT sacoche_jointure_user_entree.* ';
		$DB_SQL.= 'FROM sacoche_socle_palier ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (palier_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_entree USING (entree_id) ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') AND palier_id=:palier_id ';
		$DB_VAR = array(':palier_id'=>$palier_id);
	}
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les jointures des états de validation des compétences pour des élèves donnés, pour un pilier / une liste de piliers / un palier
 *
 * @param string   $listing_eleves   id des élèves séparés par des virgules
 * @param string   $listing_piliers  id des piliers séparées par des virgules
 * @param int      $palier_id        id d'un palier
 * @return array
 */
public function DB_lister_jointure_user_pilier($listing_eleves,$listing_piliers,$palier_id)
{
	if($palier_id)
	{
		$DB_SQL = 'SELECT sacoche_jointure_user_pilier.* ';
		$DB_SQL.= 'FROM sacoche_socle_palier ';
		$DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (palier_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_pilier USING (pilier_id) ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') AND palier_id=:palier_id ';
		$DB_VAR = array(':palier_id'=>$palier_id);
	}
	elseif($listing_piliers)
	{
		$DB_SQL = 'SELECT * ';
		$DB_SQL.= 'FROM sacoche_jointure_user_pilier ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') AND pilier_id IN('.$listing_piliers.') ';
		$DB_VAR = array();
	}
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les états de validation des items pour des élèves donnés
 *
 * @param string   $listing_eleves   id des élèves séparés par des virgules
 * @param bool     $only_positives
 * @return array
 */
public function DB_lister_validations_items($listing_eleves,$only_positives)
{
	$DB_SQL = 'SELECT palier_id , pilier_id , sacoche_jointure_user_entree.* ';
	$DB_SQL.= 'FROM sacoche_jointure_user_entree ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (entree_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (section_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (pilier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_palier USING (palier_id) ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') ';
	$DB_SQL.= ($only_positives) ? 'AND validation_entree_etat=1 ' : '' ;
	$DB_SQL.= 'ORDER BY palier_ordre, pilier_ordre, section_ordre, entree_ordre ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les états de validation des compétences pour des élèves donnés
 *
 * @param string   $listing_eleves   id des élèves séparés par des virgules
 * @param bool   $only_positives
 * @return array
 */
public function DB_lister_validations_competences($listing_eleves,$only_positives)
{
	$DB_SQL = 'SELECT palier_id , pilier_id , sacoche_jointure_user_pilier.* ';
	$DB_SQL.= 'FROM sacoche_jointure_user_pilier ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (pilier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_palier USING (palier_id) ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_eleves.') ';
	$DB_SQL.= ($only_positives) ? 'AND validation_pilier_etat=1 ' : '' ;
	$DB_SQL.= 'ORDER BY palier_ordre, pilier_ordre ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les identités & numéro Sconet des élèves
 *
 * @param void
 * @return array
 */
public function DB_lister_eleves_identite_et_sconet()
{
	$DB_SQL = 'SELECT user_sconet_id, user_nom, user_prenom ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:statut ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
	$DB_VAR = array(':profil'=>'eleve',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter les élèves n'ayant pas d'identifiant Sconet renseigné
 *
 * @param void
 * @return int
 */
public function DB_compter_eleves_actifs_sans_id_sconet()
{
	$DB_SQL = 'SELECT COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:statut AND user_sconet_id=:sconet_id ';
	$DB_VAR = array(':profil'=>'eleve',':statut'=>1,':sconet_id'=>0);
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter une validation du socle
 *
 * @param string $type   'entree' ou 'pilier'
 * @param int    $user_id
 * @param int    $element_id
 * @param int    $validation_etat
 * @param string $validation_date_mysql
 * @param string $validation_info
 * @return void
 */
public function DB_ajouter_validation($type,$user_id,$element_id,$validation_etat,$validation_date_mysql,$validation_info)
{
	$DB_SQL = 'INSERT INTO sacoche_jointure_user_'.$type.' ';
	$DB_SQL.= 'VALUES(:user_id,:'.$type.'_id,:validation_etat,:validation_date_mysql,:validation_info)';
	$DB_VAR = array(':user_id'=>$user_id,':'.$type.'_id'=>$element_id,':validation_etat'=>$validation_etat,':validation_date_mysql'=>$validation_date_mysql,':validation_info'=>$validation_info);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier un état de validation du socle
 *
 * @param string $type   'entree' ou 'pilier'
 * @param int    $user_id
 * @param int    $element_id
 * @param int    $validation_etat
 * @param string $validation_date_mysql
 * @param string $validation_info
 * @return void
 */
public function DB_modifier_validation($type,$user_id,$element_id,$validation_etat,$validation_date_mysql,$validation_info)
{
	$DB_SQL = 'UPDATE sacoche_jointure_user_'.$type.' ';
	$DB_SQL.= 'SET validation_'.$type.'_etat=:validation_etat, validation_'.$type.'_date=:validation_date_mysql, validation_'.$type.'_info=:validation_info ';
	$DB_SQL.= 'WHERE user_id=:user_id AND '.$type.'_id=:'.$type.'_id ';
	$DB_VAR = array(':user_id'=>$user_id,':'.$type.'_id'=>$element_id,':validation_etat'=>$validation_etat,':validation_date_mysql'=>$validation_date_mysql,':validation_info'=>$validation_info);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer une validation du socle
 *
 * @param string $type   'entree' ou 'pilier'
 * @param int    $user_id
 * @param int    $element_id
 * @return void
 */
public function DB_supprimer_validation($type,$user_id,$element_id)
{
	$DB_SQL = 'DELETE FROM sacoche_jointure_user_'.$type.' ';
	$DB_SQL.= 'WHERE user_id=:user_id AND '.$type.'_id=:'.$type.'_id ';
	$DB_VAR = array(':user_id'=>$user_id,':'.$type.'_id'=>$element_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>