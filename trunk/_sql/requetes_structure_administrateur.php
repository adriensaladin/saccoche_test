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
// Ces méthodes ne concernent qu'un administrateur.

class DB_STRUCTURE_ADMINISTRATEUR extends DB
{

/**
 * recuperer_arborescence_paliers
 *
 * @param void
 * @return array
 */
public function DB_recuperer_arborescence_paliers()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_socle_palier ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (palier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
	$DB_SQL.= 'ORDER BY palier_ordre ASC, pilier_ordre ASC, section_ordre ASC, entree_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_amplitude_periodes
 *
 * @param void
 * @return array  de la forme array('tout_debut'=>... , ['toute_fin']=>... , ['nb_jours_total']=>...)
 */
public function DB_recuperer_amplitude_periodes()
{
	$DB_SQL = 'SELECT MIN(jointure_date_debut) AS tout_debut , MAX(jointure_date_fin) AS toute_fin ';
	$DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	if(count($DB_ROW))
	{
		// On ajoute un jour pour dessiner les barres jusqu'au jour suivant (accessoirement, ça évite aussi une possible division par 0).
		$DB_SQL = 'SELECT DATEDIFF(DATE_ADD(:toute_fin,INTERVAL 1 DAY),:tout_debut) AS nb_jours_total ';
		$DB_VAR = array(':tout_debut'=>$DB_ROW['tout_debut'],':toute_fin'=>$DB_ROW['toute_fin']);
		$DB_ROW['nb_jours_total'] = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	return $DB_ROW;
}

/**
 * recuperer_referentiels_domaines
 *
 * @param void
 * @return array
 */
public function DB_recuperer_referentiels_domaines()
{
	$DB_SQL = 'SELECT matiere_id, niveau_id, domaine_nom ';
	$DB_SQL.= 'FROM sacoche_referentiel_domaine ';
	$DB_SQL.= 'ORDER BY domaine_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_referentiels_themes
 *
 * @param void
 * @return array
 */
public function DB_recuperer_referentiels_themes()
{
	$DB_SQL = 'SELECT matiere_id, niveau_id, theme_nom ';
	$DB_SQL.= 'FROM sacoche_referentiel_theme ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'ORDER BY domaine_ordre ASC, theme_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_matieres_partagees_SACoche
 *
 * @param void
 * @return array
 */
public function DB_lister_matieres_partagees_SACoche()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_partage=:partage ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC';
	$DB_VAR = array(':partage'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_matieres_specifiques
 *
 * @param void
 * @return array
 */
public function DB_lister_matieres_specifiques()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_partage=:partage ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC';
	$DB_VAR = array(':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_matieres_etablissement
 *
 * @param string $listing_matieres   id des matières communes choisies séparés par des virgules
 * @param bool   $with_transversal   avec ou non la matière tranversale
 * @param bool   $order_by_name      si FALSE, prendre le champ matiere_ordre
 * @return array
 */
public function DB_lister_matieres_etablissement($listing_matieres,$with_transversal,$order_by_name)
{
	$where_trans = ($with_transversal) ? '' : 'AND matiere_transversal=0 ' ;
	$order_champ = ($order_by_name)    ? '' : 'matiere_ordre ASC, ' ;
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_matiere ';
	$DB_SQL.= ($listing_matieres) ? 'WHERE (matiere_id IN('.$listing_matieres.') OR matiere_partage=:partage) '.$where_trans : 'WHERE matiere_partage=:partage '.$where_trans;
	$DB_SQL.= 'ORDER BY '.$order_champ.'matiere_nom ASC';
	$DB_VAR = array(':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_paliers_SACoche
 *
 * @param void
 * @return array
 */
public function DB_lister_paliers_SACoche()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_socle_palier ';
	$DB_SQL.= 'ORDER BY palier_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_niveaux_SACoche
 * Sans les niveaux de type 'cycles'.
 *
 * @param void
 * @return array
 */
public function DB_lister_niveaux_SACoche()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_niveau ';
	$DB_SQL.= 'WHERE niveau_cycle=0 ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_cycles_SACoche
 *
 * @param void
 * @return array
 */
public function DB_lister_cycles_SACoche()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_niveau ';
	$DB_SQL.= 'WHERE niveau_cycle=1 ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_periodes
 *
 * @param void
 * @return array
 */
public function DB_lister_periodes()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_periode ';
	$DB_SQL.= 'ORDER BY periode_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_groupes_sauf_classes
 *
 * @param void
 * @return array
 */
public function DB_lister_groupes_sauf_classes()
{
	$DB_SQL = 'SELECT groupe_id, groupe_type ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type!=:type ';
	$DB_SQL.= 'ORDER BY groupe_ref ASC';
	$DB_VAR = array(':type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes
 *
 * @param void
 * @return array
 */
public function DB_lister_classes()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY groupe_ref ASC';
	$DB_VAR = array(':type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes
 *
 * @param void
 * @return array
 */
public function DB_lister_groupes()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY groupe_ref ASC';
	$DB_VAR = array(':type'=>'groupe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_avec_niveaux
 *
 * @param string   $niveau_ordre   facultatif, ASC par défaut, DESC possible
 * @return array
 */
public function DB_lister_classes_avec_niveaux($niveau_ordre='ASC')
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre '.$niveau_ordre.', groupe_ref ASC';
	$DB_VAR = array(':type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes_avec_niveaux
 *
 * @param void
 * @return array
 */
public function DB_lister_groupes_avec_niveaux()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC';
	$DB_VAR = array(':type'=>'groupe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_et_groupes_avec_niveaux
 *
 * @param void
 * @return array
 */
public function DB_lister_classes_et_groupes_avec_niveaux()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
	$DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':type1'=>'classe',':type2'=>'groupe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_avec_professeurs
 *
 * @param void
 * @return array
 */
public function DB_lister_classes_avec_professeurs()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE groupe_type=:type AND user_statut=:statut ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':type'=>'classe',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_cibles
 *
 * @param string   $listing_user_id   id des utilisateurs séparés par des virgules
 * @param string   $listing_champs    nom des champs séparés par des virgules
 * @param string   $avec_info         facultatif ; "classe" pour récupérer la classe des élèves | "enfant" pour récupérer une classe et un enfant associé à un parent
 * @return array
 */
public function DB_lister_users_cibles($listing_user_id,$listing_champs,$avec_info='')
{
	if($avec_info=='classe')
	{
		$DB_SQL = 'SELECT '.$listing_champs.',groupe_nom AS info ';
		$DB_SQL.= 'FROM sacoche_user ';
		$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
		$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
		$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, user_nom ASC, user_prenom ASC';
	}
	elseif($avec_info=='enfant')
	{
		$DB_SQL = 'SELECT '.$listing_champs.',GROUP_CONCAT( CONCAT(groupe_ref," ",enfant.user_nom) SEPARATOR " - ") AS info ';
		$DB_SQL.= 'FROM sacoche_user AS parent ';
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON parent.user_id=sacoche_jointure_parent_eleve.parent_id ';
		$DB_SQL.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_jointure_parent_eleve.eleve_id=enfant.user_id ';
		$DB_SQL.= 'LEFT JOIN sacoche_groupe ON enfant.eleve_classe_id=sacoche_groupe.groupe_id ';
		$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
		$DB_SQL.= 'WHERE parent.user_id IN('.$listing_user_id.') ';
		$DB_SQL.= 'GROUP BY parent.user_id ' ;
		$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, enfant.user_nom ASC, enfant.user_prenom ASC';
	}
	else
	{
		$DB_SQL = 'SELECT '.$listing_champs.' ';
		$DB_SQL.= 'FROM sacoche_user ';
		$DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
		$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	}
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_adresses_parents
 *
 * @param void
 * @return array
 */
public function DB_lister_adresses_parents()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_parent_adresse ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_professeurs_par_matiere
 *
 * @param string   $listing_matieres_id   id des matières séparés par des virgules
 * @return array
 */
public function DB_lister_professeurs_par_matiere($listing_matieres_id)
{
	$DB_SQL = 'SELECT matiere_id, matiere_nom, jointure_coord, user_id, user_nom, user_prenom ';
	$DB_SQL.= 'FROM sacoche_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE user_statut=:statut ';
	$DB_SQL.= 'AND (matiere_id IN('.$listing_matieres_id.') OR matiere_partage=:partage) '; // Test matiere car un prof peut être encore relié à des matières décochées par l'admin.
	$DB_SQL.= 'ORDER BY matiere_transversal DESC, matiere_nom ASC, user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':statut'=>1,':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_parents_par_eleve
 *
 * @param void
 * @return array
 */
public function DB_lister_parents_par_eleve()
{
	$DB_SQL = 'SELECT eleve.user_id AS eleve_id,   eleve.user_sconet_id AS eleve_sconet_id,   eleve.user_nom AS eleve_nom,   eleve.user_prenom AS eleve_prenom,   ';
	$DB_SQL.=        'parent.user_id AS parent_id, parent.user_sconet_id AS parent_sconet_id, parent.user_nom AS parent_nom, parent.user_prenom AS parent_prenom, ';
	$DB_SQL.=        'sacoche_jointure_parent_eleve.resp_legal_num ';
	$DB_SQL.= 'FROM sacoche_user AS eleve ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON eleve.user_id=sacoche_jointure_parent_eleve.eleve_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
	$DB_SQL.= 'WHERE eleve.user_profil="eleve" AND eleve.user_sconet_id!=0 ';
	$DB_SQL.= 'ORDER BY eleve_nom ASC, eleve_prenom ASC, resp_legal_num ASC ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_parents_actifs_avec_infos_for_eleve
 *
 * @param int   $eleve_id
 * @return array
 */
 
public function DB_lister_parents_actifs_avec_infos_for_eleve($eleve_id)
{
	$DB_SQL = 'SELECT parent.user_id, parent.user_nom, parent.user_prenom, sacoche_parent_adresse.*, resp_legal_num ';
	$DB_SQL.= 'FROM sacoche_user AS eleve ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON eleve.user_id=sacoche_jointure_parent_eleve.eleve_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_parent_adresse ON sacoche_jointure_parent_eleve.parent_id=sacoche_parent_adresse.parent_id ';
	$DB_SQL.= 'WHERE eleve.user_id=:eleve_id AND parent.user_statut=:statut ';
	$DB_SQL.= 'GROUP BY parent.user_id ';
	$DB_SQL.= 'ORDER BY resp_legal_num ASC ';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':statut'=>1);
	$DB_TAB_parents = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE, TRUE);
	if(!count($DB_TAB_parents))
	{
		return array();
	}
	$listing_parent_id = implode(',',array_keys($DB_TAB_parents));
	$DB_SQL = 'SELECT parent_id, GROUP_CONCAT( CONCAT(enfant.user_nom," ",enfant.user_prenom," (resp légal ",resp_legal_num,")") SEPARATOR " ; ") AS enfants_liste ';
	$DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_jointure_parent_eleve.eleve_id=enfant.user_id ';
	$DB_SQL.= 'WHERE sacoche_jointure_parent_eleve.parent_id IN('.$listing_parent_id.') AND enfant.user_statut=:statut ';
	$DB_SQL.= 'GROUP BY parent_id ';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':statut'=>1);
	$DB_TAB_enfants = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
	$DB_TAB = array();
	foreach($DB_TAB_parents AS $id => $tab)
	{
		$DB_TAB[] = array_merge( $DB_TAB_parents[$id] , $DB_TAB_enfants[$id] , array('parent_id'=>$id) );
	}
	return $DB_TAB;
}

/**
 * lister_jointure_professeurs_matieres
 *
 * @param bool $with_identite
 * @param bool $with_transversal
 * @return array
 */
public function DB_lister_jointure_professeurs_matieres($with_identite,$with_transversal)
{
	$DB_SQL = 'SELECT user_id, matiere_id';
	$DB_SQL.= ($with_identite) ? ',user_nom , user_prenom ' : ' ' ;
	$DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE user_statut=:statut ';
	$DB_SQL.= ($with_transversal) ? '' : 'AND matiere_id!='.ID_MATIERE_TRANSVERSALE.' ' ;
	$DB_SQL.= ($with_identite) ? 'ORDER BY user_nom ASC, user_prenom ASC ' : '' ;
	$DB_VAR = array(':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointure_professeurs_coordonnateurs
 *
 * @param void
 * @return array
 */
public function DB_lister_jointure_professeurs_coordonnateurs()
{
	$DB_SQL = 'SELECT user_id, matiere_id ';
	$DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE jointure_coord=:coord AND user_statut=:statut ';
	$DB_VAR = array(':coord'=>1,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointure_professeurs_principaux
 *
 * @param void
 * @return array
 */
public function DB_lister_jointure_professeurs_principaux()
{
	$DB_SQL = 'SELECT user_id, groupe_id ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE jointure_pp=:pp AND user_statut=:statut AND groupe_type=:type '; // groupe_type pour éviter les groupes de besoin
	$DB_VAR = array(':pp'=>1,':statut'=>1,':type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointure_professeurs_groupes
 *
 * @param string   $listing_profs_id     id des profs séparés par des virgules
 * @param string   $listing_groupes_id   id des groupes séparés par des virgules
 * @return array
 */
public function DB_lister_jointure_professeurs_groupes($listing_profs_id,$listing_groupes_id)
{
	$DB_SQL = 'SELECT groupe_id,user_id FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_profs_id.') AND groupe_id IN('.$listing_groupes_id.') ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, user_nom ASC, user_prenom ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_jointure_groupe_periode_avec_infos_graphiques
 *
 * @param string   $tout_debut   date de début
 * @return array
 */
public function DB_lister_jointure_groupe_periode_avec_infos_graphiques($tout_debut)
{
	$DB_SQL = 'SELECT * , ';
	$DB_SQL.= 'DATEDIFF(jointure_date_debut,:tout_debut) AS position_jour_debut , DATEDIFF(jointure_date_fin,jointure_date_debut) AS nb_jour ';
	$DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'ORDER BY groupe_id ASC, jointure_date_debut ASC, jointure_date_fin ASC';
	$DB_VAR = array(':tout_debut'=>$tout_debut);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users
 *
 * @param string|array   $profil        'eleve' / 'parent' / 'professeur' / 'directeur' / 'administrateur' / ou par exemple array('eleve','professeur','directeur')
 * @param bool           $only_actifs   TRUE pour statut actif uniquement / FALSE pour tout le monde qq soit le statut
 * @param bool           $with_classe   TRUE pour récupérer le nom de la classe de l'élève / FALSE sinon
 * @param bool           $tri_statut    TRUE pour trier par statut décroissant (les actifs en premier), FALSE par défaut
 * @return array
 */
public function DB_lister_users($profil,$only_actifs,$with_classe,$tri_statut=FALSE)
{
	$DB_VAR = array();
	$left_join = '';
	$where     = '';
	$order_by  = ($tri_statut) ? 'user_statut DESC, ' : '' ;
	if(is_string($profil))
	{
		$where .= 'user_profil=:profil ';
		$DB_VAR[':profil'] = $profil;
	}
	else
	{
		foreach($profil as $key => $val)
		{
			$or = ($key) ? 'OR ' : '( ' ;
			$where .= $or.'user_profil=:profil'.$key.' ';
			$DB_VAR[':profil'.$key] = $val;
		}
		$where .= ') ';
		$order_by .= 'user_profil ASC, ';
	}
	if($with_classe)
	{
		$left_join .= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
		$left_join .= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
		$order_by  .= 'niveau_ordre ASC, groupe_ref ASC, ';
	}
	if($only_actifs)
	{
		$where .= 'AND user_statut=:statut ';
		$DB_VAR[':statut'] = 1;
	}
	// On peut maintenant assembler les morceaux de la requête !
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= $left_join;
	$DB_SQL.= 'WHERE '.$where;
	$DB_SQL.= 'ORDER BY '.$order_by.'user_nom ASC, user_prenom ASC ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_parents_actifs_avec_infos_enfants
 *
 * @param bool     $with_adresse
 * @param string   $debut_nom      premières lettres du nom
 * @param string   $debut_prenom   premières lettres du prénom
 * @return array
 */
public function DB_lister_parents_actifs_avec_infos_enfants($with_adresse,$debut_nom='',$debut_prenom='')
{
	$DB_SQL = 'SELECT ' ;
	$DB_SQL.= ($with_adresse) ? 'parent.user_id, parent.user_nom, parent.user_prenom, sacoche_parent_adresse.*, ' : 'parent.*, ' ;
	$DB_SQL.= 'GROUP_CONCAT( CONCAT(eleve.user_nom," ",eleve.user_prenom," (resp légal ",resp_legal_num,")") SEPARATOR "§BR§") AS enfants_liste, ';
	$DB_SQL.= 'COUNT(eleve.user_id) AS enfants_nombre ';
	$DB_SQL.= 'FROM sacoche_user AS parent ';
	$DB_SQL.= ($with_adresse) ? 'LEFT JOIN sacoche_parent_adresse ON parent.user_id=sacoche_parent_adresse.parent_id ' : '' ;
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON parent.user_id=sacoche_jointure_parent_eleve.parent_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS eleve ON sacoche_jointure_parent_eleve.eleve_id=eleve.user_id ';
	$DB_SQL.= 'WHERE parent.user_profil=:profil AND parent.user_statut=:statut ';
	$DB_VAR = array(':profil'=>'parent',':statut'=>1);
	if($debut_nom)
	{
		$DB_SQL .= 'AND parent.user_nom LIKE :nom ';
		$DB_VAR[':nom'] = $debut_nom.'%';
	}
	if($debut_prenom)
	{
		$DB_SQL .= 'AND parent.user_prenom LIKE :prenom ';
		$DB_VAR[':prenom'] = $debut_prenom.'%';
	}
	$DB_SQL.= 'GROUP BY parent.user_id ';
	$DB_SQL.= 'ORDER BY parent.user_nom ASC, parent.user_prenom ASC ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_avec_groupe
 *
 * @param bool   $profil_eleve  TRUE pour eleve / FALSE pour professeur + directeur
 * @param bool   $only_actifs   TRUE pour statut actif uniquement / FALSE pour tout le monde qq soit le statut
 * @return array
 */
public function DB_lister_users_avec_groupe($profil_eleve,$only_actifs)
{
	$egal_eleve  = ($profil_eleve) ? '=' : '!=' ;
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE user_profil'.$egal_eleve.':profil AND groupe_type=:type ';
	$DB_VAR = array(':profil'=>'eleve',':type'=>'groupe');
	if($only_actifs)
	{
		$DB_SQL.= 'AND user_statut=:statut ';
		$DB_VAR[':statut'] = 1;
	}
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_desactives_obsoletes
 *
 * @param void
 * @return array
 */
public function DB_lister_users_desactives_obsoletes()
{
	$DB_SQL = 'SELECT user_id, user_profil ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_statut=:user_statut AND DATE_ADD(user_statut_date,INTERVAL 3 YEAR)<NOW() ';
	$DB_VAR = array(':user_statut'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_professeurs_avec_classes
 *
 * @param void
 * @return array
 */
public function DB_lister_professeurs_avec_classes()
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE user_profil=:profil AND groupe_type=:type AND user_statut=:statut ';
	$DB_VAR = array(':profil'=>'professeur',':type'=>'classe',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_referentiels
 *
 * @param void
 * @return array
 */
public function DB_lister_referentiels()
{
	$DB_SQL = 'SELECT matiere_id, niveau_id, matiere_nom, niveau_nom, referentiel_mode_synthese ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE (matiere_id IN('.$_SESSION['MATIERES'].') OR matiere_partage=:partage) AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') '; // Test matiere pour éviter des matières décochées par l'admin.
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC ';
	$DB_VAR = array(':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * compter_devoirs
 *
 * @param void
 * @return int
 */
public function DB_compter_devoirs()
{
	$DB_SQL = 'SELECT COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_devoir';
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_users_suivant_statut
 *
 * @param string|array   $profil        'eleve' / 'professeur' / 'directeur' / 'administrateur' / ou par exemple array('eleve','professeur','directeur')
 * @return array   [0]=>nb actifs , [1]=>nb inactifs
 */
public function DB_compter_users_suivant_statut($profil)
{
	if(is_string($profil))
	{
		$where = 'user_profil=:profil ';
		$DB_VAR[':profil'] = $profil;
	}
	else
	{
		$DB_VAR = array();
		foreach($profil as $key => $val)
		{
			$DB_VAR[':profil'.$key] = $val;
			$profil[$key] = ':profil'.$key;
		}
		$where = 'user_profil IN('.implode(',',$profil).') ';
	}
	$DB_SQL = 'SELECT user_statut, COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE '.$where;
	$DB_SQL.= 'GROUP BY user_statut';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
	$nb_actif   = ( (count($DB_TAB)) && (isset($DB_TAB[1])) ) ? $DB_TAB[1]['nombre'] : 0 ;
	$nb_inactif = ( (count($DB_TAB)) && (isset($DB_TAB[0])) ) ? $DB_TAB[0]['nombre'] : 0 ;
	return array($nb_actif,$nb_inactif);
}

/**
 * tester_matiere_reference
 *
 * @param string $matiere_ref
 * @param int    $matiere_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public function DB_tester_matiere_reference($matiere_ref,$matiere_id=FALSE)
{
	$DB_SQL = 'SELECT matiere_id ';
	$DB_SQL.= 'FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_ref=:matiere_ref ';
	$DB_VAR = array(':matiere_ref'=>$matiere_ref);
	if($matiere_id)
	{
		$DB_SQL.= 'AND matiere_id!=:matiere_id ';
		$DB_VAR[':matiere_id'] = $matiere_id;
	}
	$DB_SQL.= 'LIMIT 1'; // utile
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_classe_reference
 *
 * @param string $groupe_ref
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public function DB_tester_classe_reference($groupe_ref,$groupe_id=FALSE)
{
	$DB_SQL = 'SELECT groupe_id ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_ref=:groupe_ref ';
	$DB_VAR = array(':groupe_type'=>'classe',':groupe_ref'=>$groupe_ref);
	if($groupe_id)
	{
		$DB_SQL.= 'AND groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1'; // utile
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_groupe_reference
 *
 * @param string $groupe_ref
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public function DB_tester_groupe_reference($groupe_ref,$groupe_id=FALSE)
{
	$DB_SQL = 'SELECT groupe_id ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_ref=:groupe_ref ';
	$DB_VAR = array(':groupe_type'=>'groupe',':groupe_ref'=>$groupe_ref);
	if($groupe_id)
	{
		$DB_SQL.= 'AND groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1'; // utile
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_periode_nom
 *
 * @param string $periode_nom
 * @param int    $periode_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public function DB_tester_periode_nom($periode_nom,$periode_id=FALSE)
{
	$DB_SQL = 'SELECT periode_id ';
	$DB_SQL.= 'FROM sacoche_periode ';
	$DB_SQL.= 'WHERE periode_nom=:periode_nom ';
	$DB_VAR = array(':periode_nom'=>$periode_nom);
	if($periode_id)
	{
		$DB_SQL.= 'AND periode_id!=:periode_id ';
		$DB_VAR[':periode_id'] = $periode_id;
	}
	$DB_SQL.= 'LIMIT 1'; // utile
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Recherche si un identifiant d'utilisateur est déjà pris (sauf éventuellement l'utilisateur concerné)
 *
 * @param string $champ_nom      sans le préfixe 'user_' : login | sconet_id | sconet_elenoet | reference | id_ent | id_gepi
 * @param string $champ_valeur   la valeur testée
 * @param int    $user_id        inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @param string $user_profil    si profil transmis alors parmi les utilisateurs de même profil (sconet_id|sconet_elenoet|reference), sinon alors parmi tout le personnel de l'établissement (login|id_ent|id_gepi)
 * @return int
 */
public function DB_tester_utilisateur_identifiant($champ_nom,$champ_valeur,$user_id=NULL,$user_profil=NULL)
{
	$DB_SQL = 'SELECT user_id ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_'.$champ_nom.'=:champ_valeur ';
	$DB_VAR = array(':champ_valeur'=>$champ_valeur);
	if($user_profil)
	{
		$DB_SQL.= 'AND user_profil=:user_profil ';
		$DB_VAR[':user_profil'] = $user_profil;
	}
	if($user_id)
	{
		$DB_SQL.= 'AND user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1'; // utile
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * rechercher_login_disponible (parmi tout le personnel de l'établissement)
 *
 * @param string $login
 * @return string
 */
public function DB_rechercher_login_disponible($login)
{
	$nb_chiffres = 20-mb_strlen($login);
	$max_result = 0;
	do
	{
		$login = mb_substr($login,0,20-$nb_chiffres);
		$DB_SQL = 'SELECT user_login ';
		$DB_SQL.= 'FROM sacoche_user ';
		$DB_SQL.= 'WHERE user_login LIKE :user_login';
		$DB_VAR = array(':user_login'=>$login.'%');
		$DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$max_result += pow(10,$nb_chiffres);
	}
	while (count($DB_COL)>=$max_result);
	$j=0;
	do
	{
		$j++;
	}
	while (in_array($login.$j,$DB_COL));
	return $login.$j ;
}

/**
 * ajouter_matiere_specifique
 *
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return int
 */
public function DB_ajouter_matiere_specifique($matiere_ref,$matiere_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_matiere(matiere_partage,matiere_transversal,matiere_nb_demandes,matiere_ref,matiere_nom) ';
	$DB_SQL.= 'VALUES(:matiere_partage,:matiere_transversal,:matiere_nb_demandes,:matiere_ref,:matiere_nom)';
	$DB_VAR = array(':matiere_partage'=>0,':matiere_transversal'=>0,':matiere_nb_demandes'=>0,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_groupe_par_admin
 *
 * @param string $groupe_type   'classe' | 'groupe'
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return int
 */
public function DB_ajouter_groupe_par_admin($groupe_type,$groupe_ref,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'INSERT INTO sacoche_groupe(groupe_type,groupe_ref,groupe_nom,niveau_id) ';
	$DB_SQL.= 'VALUES(:groupe_type,:groupe_ref,:groupe_nom,:niveau_id)';
	$DB_VAR = array(':groupe_type'=>$groupe_type,':groupe_ref'=>$groupe_ref,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_periode
 *
 * @param int    $periode_ordre
 * @param string $periode_nom
 * @return int
 */
public function DB_ajouter_periode($periode_ordre,$periode_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_periode(periode_ordre,periode_nom) ';
	$DB_SQL.= 'VALUES(:periode_ordre,:periode_nom)';
	$DB_VAR = array(':periode_ordre'=>$periode_ordre,':periode_nom'=>$periode_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_adresse_parent
 *
 * @param int    $parent_id
 * @param array  $tab_adresse
 * @return void
 */
public function DB_ajouter_adresse_parent($parent_id,$tab_adresse)
{
	$DB_SQL = 'INSERT INTO sacoche_parent_adresse(parent_id,adresse_ligne1,adresse_ligne2,adresse_ligne3,adresse_ligne4,adresse_postal_code,adresse_postal_libelle,adresse_pays_nom) ';
	$DB_SQL.= 'VALUES(:parent_id,:ligne1,:ligne2,:ligne3,:ligne4,:postal_code,:postal_libelle,:pays_nom)';
	$DB_VAR = array(':parent_id'=>$parent_id,':ligne1'=>$tab_adresse[0],':ligne2'=>$tab_adresse[1],':ligne3'=>$tab_adresse[2],':ligne4'=>$tab_adresse[3],':postal_code'=>$tab_adresse[4],':postal_libelle'=>$tab_adresse[5],':pays_nom'=>$tab_adresse[6]);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_jointure_parent_eleve
 *
 * @param int    $parent_id
 * @param int    $eleve_id
 * @param int    $resp_legal_num
 * @return void
 */
public function DB_ajouter_jointure_parent_eleve($parent_id,$eleve_id,$resp_legal_num)
{
	$DB_SQL = 'INSERT INTO sacoche_jointure_parent_eleve(parent_id,eleve_id,resp_legal_num) ';
	$DB_SQL.= 'VALUES(:parent_id,:eleve_id,:resp_legal_num)';
	$DB_VAR = array(':parent_id'=>$parent_id,':eleve_id'=>$eleve_id,':resp_legal_num'=>$resp_legal_num);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Dupliquer pour tous les utilisateurs une série d'identifiants vers un autre champ (exemples : id_gepi=id_ent | id_gepi=login | id_ent=id_gepi | id_ent=login )
 *
 * @param string $champ_depart
 * @param string $champ_arrive
 * @return void
 */
public function DB_recopier_identifiants($champ_depart,$champ_arrive)
{
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET user_'.$champ_arrive.'=user_'.$champ_depart.' ';
	$DB_SQL.= 'WHERE user_'.$champ_depart.'!="" ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_adresse_parent
 *
 * @param string $parent_id
 * @param array  $tab_adresse
 * @return int
 */
public function DB_modifier_adresse_parent($parent_id,$tab_adresse)
{
	$DB_SQL = 'UPDATE sacoche_parent_adresse ';
	$DB_SQL.= 'SET adresse_ligne1=:ligne1, adresse_ligne2=:ligne2, adresse_ligne3=:ligne3, adresse_ligne4=:ligne4, adresse_postal_code=:postal_code, adresse_postal_libelle=:postal_libelle, adresse_pays_nom=:pays_nom ';
	$DB_SQL.= 'WHERE parent_id=:parent_id ';
	$DB_VAR = array(':parent_id'=>$parent_id,':ligne1'=>$tab_adresse[0],':ligne2'=>$tab_adresse[1],':ligne3'=>$tab_adresse[2],':ligne4'=>$tab_adresse[3],':postal_code'=>$tab_adresse[4],':postal_libelle'=>$tab_adresse[5],':pays_nom'=>$tab_adresse[6]);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier un ou plusieurs paramètres d'un utilisateur
 *
 * On ne touche ni à "connexion_date" / "statut" / "user_statut_date" (traités ailleurs).
 * On peut envisager une modification de "user_profil" (passage professeur -> directeur par exemple)
 *
 * @param int     $user_id
 * @param array   array(':sconet_id'=>$val, ':sconet_num'=>$val, ':reference'=>$val , ':profil'=>$val , ':nom'=>$val , ':prenom'=>$val , ':login'=>$val , ':password'=>$val , ':daltonisme'=>$val , ':classe'=>$val , ':id_ent'=>$val , ':id_gepi'=>$val );
 * @return void
 */
public function DB_modifier_user($user_id,$DB_VAR)
{
	$tab_set = array();
	foreach($DB_VAR as $key => $val)
	{
		switch($key)
		{
			case ':sconet_id' :  $tab_set[] = 'user_sconet_id='.$key;      break;
			case ':sconet_num' : $tab_set[] = 'user_sconet_elenoet='.$key; break;
			case ':reference' :  $tab_set[] = 'user_reference='.$key;      break;
			case ':profil' :     $tab_set[] = 'user_profil='.$key;         break;
			case ':nom' :        $tab_set[] = 'user_nom='.$key;            break;
			case ':prenom' :     $tab_set[] = 'user_prenom='.$key;         break;
			case ':login' :      $tab_set[] = 'user_login='.$key;          break;
			case ':password' :   $tab_set[] = 'user_password='.$key;       break;
			case ':daltonisme' : $tab_set[] = 'user_daltonisme='.$key;     break;
			case ':classe' :     $tab_set[] = 'eleve_classe_id='.$key;     break;
			case ':id_ent' :     $tab_set[] = 'user_id_ent='.$key;         break;
			case ':id_gepi' :    $tab_set[] = 'user_id_gepi='.$key;        break;
		}
	}
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET '.implode(', ',$tab_set).' ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_VAR[':user_id'] = $user_id;
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier le statut d'un utilisateur
 *
 * @param int     $user_id
 * @param 0|1     nouveau statut
 * @return void
 */
public function DB_modifier_user_statut($user_id,$user_statut)
{
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET user_statut=:user_statut, user_statut_date=NOW() ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_VAR = array(':user_id'=>$user_id,':user_statut'=>$user_statut);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier la langue du socle pour une liste d'élèves
 *
 * @param string $listing_user_id
 * @param int    $langue
 * @return void
 */
public function DB_modifier_user_langue($listing_user_id,$langue)
{
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET eleve_langue=:langue ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
	$DB_VAR = array(':langue'=>$langue);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_matiere_specifique
 *
 * @param int    $matiere_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return void
 */
public function DB_modifier_matiere_specifique($matiere_id,$matiere_ref,$matiere_nom)
{
	$DB_SQL = 'UPDATE sacoche_matiere ';
	$DB_SQL.= 'SET matiere_ref=:matiere_ref,matiere_nom=:matiere_nom ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_matiere_ordre
 *
 * @param int   $matiere_id
 * @param int   $matiere_ordre
 * @return void
 */
public function DB_modifier_matiere_ordre($matiere_id,$matiere_ordre)
{
	$DB_SQL = 'UPDATE sacoche_matiere ';
	$DB_SQL.= 'SET matiere_ordre=:matiere_ordre ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':matiere_ordre'=>$matiere_ordre);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_groupe_par_admin ; on ne touche pas à 'groupe_type'
 *
 * @param int    $groupe_id
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return void
 */
public function DB_modifier_groupe_par_admin($groupe_id,$groupe_ref,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'UPDATE sacoche_groupe ';
	$DB_SQL.= 'SET groupe_ref=:groupe_ref,groupe_nom=:groupe_nom,niveau_id=:niveau_id ';
	$DB_SQL.= 'WHERE groupe_id=:groupe_id ';
	$DB_VAR = array(':groupe_id'=>$groupe_id,':groupe_ref'=>$groupe_ref,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_user_groupe_par_admin
 *
 * @param int    $user_id
 * @param string $user_profil   'eleve' ou 'professeur'
 * @param int    $groupe_id
 * @param string $groupe_type   'classe' ou 'groupe'
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public function DB_modifier_liaison_user_groupe_par_admin($user_id,$user_profil,$groupe_id,$groupe_type,$etat)
{
	// Dans le cas d'un élève et d'une classe, ce n'est pas dans la table de jointure mais dans la table user que ça se passe
	if( ($user_profil=='eleve') && ($groupe_type=='classe') )
	{
		$DB_SQL = 'UPDATE sacoche_user ';
		if($etat)
		{
			$DB_SQL.= 'SET eleve_classe_id=:groupe_id ';
			$DB_SQL.= 'WHERE user_id=:user_id ';
		}
		else
		{
			$DB_SQL.= 'SET eleve_classe_id=0 ';
			$DB_SQL.= 'WHERE user_id=:user_id AND eleve_classe_id=:groupe_id ';
		}
	}
	else
	{
		if($etat)
		{
			$DB_SQL = 'REPLACE INTO sacoche_jointure_user_groupe (user_id,groupe_id) ';
			$DB_SQL.= 'VALUES(:user_id,:groupe_id)';
		}
		else
		{
			$DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
			$DB_SQL.= 'WHERE user_id=:user_id AND groupe_id=:groupe_id ';
		}
	}
	$DB_VAR = array(':user_id'=>$user_id,':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_professeur_coordonnateur
 *
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public function DB_modifier_liaison_professeur_coordonnateur($user_id,$matiere_id,$etat)
{
	$coord = ($etat) ? 1 : 0 ;
	$DB_SQL = 'UPDATE sacoche_jointure_user_matiere ';
	$DB_SQL.= 'SET jointure_coord=:coord ';
	$DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
	$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>$matiere_id,':coord'=>$coord);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_professeur_principal ; ressemble à la fonction PROF DB_ajouter_liaison_professeur_responsable()
 *
 * @param int    $user_id
 * @param int    $groupe_id
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public function DB_modifier_liaison_professeur_principal($user_id,$groupe_id,$etat)
{
	$pp = ($etat) ? 1 : 0 ;
	$DB_SQL = 'UPDATE sacoche_jointure_user_groupe ';
	$DB_SQL.= 'SET jointure_pp=:pp ';
	$DB_SQL.= 'WHERE user_id=:user_id AND groupe_id=:groupe_id ';
	$DB_VAR = array(':user_id'=>$user_id,':groupe_id'=>$groupe_id,':pp'=>$pp);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_professeur_matiere
 *
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public function DB_modifier_liaison_professeur_matiere($user_id,$matiere_id,$etat)
{
	if($etat)
	{
		// On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd la valeur de jointure_coord.
		$DB_SQL = 'SELECT 1 ';
		$DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
		$DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
		$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>$matiere_id);
		$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		if(!count($DB_ROW))
		{
			$DB_SQL = 'INSERT INTO sacoche_jointure_user_matiere (user_id,matiere_id,jointure_coord) ';
			$DB_SQL.= 'VALUES(:user_id,:matiere_id,:coord)';
			$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>$matiere_id,':coord'=>0);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	else
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
		$DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
		$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>$matiere_id);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * modifier_liaison_groupe_periode
 *
 * @param int|TRUE   $groupe_id          id du groupe ou TRUE pour supprimer la jointure sur tous les groupes
 * @param int|TRUE   $periode_id         id de la période ou TRUE pour supprimer la jointure sur toutes les périodes
 * @param bool       $etat               TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @param string     $date_debut_mysql   date de début au format mysql (facultatif : obligatoire uniquement si $etat=TRUE)
 * @param string     $date_fin_mysql     date de fin au format mysql (facultatif : obligatoire uniquement si $etat=TRUE)
 * @return void
 */
public function DB_modifier_liaison_groupe_periode($groupe_id,$periode_id,$etat,$date_debut_mysql='',$date_fin_mysql='')
{
	if($etat)
	{
		// Ajouter / modifier une liaison
		$DB_SQL = 'REPLACE INTO sacoche_jointure_groupe_periode (groupe_id,periode_id,jointure_date_debut,jointure_date_fin) ';
		$DB_SQL.= 'VALUES(:groupe_id,:periode_id,:date_debut,:date_fin)';
		$DB_VAR = array(':groupe_id'=>$groupe_id,':periode_id'=>$periode_id,':date_debut'=>$date_debut_mysql,':date_fin'=>$date_fin_mysql);
	}
	else
	{
		if( ($groupe_id===TRUE) && ($periode_id===TRUE) )
		{
			// Retirer toutes les liaisons
			$DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
			$DB_VAR = NULL;
		}
		else
		{
			// Retirer une liaison
			$DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
			$DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
			$DB_VAR = array(':groupe_id'=>$groupe_id,':periode_id'=>$periode_id);
		}
	}
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_periode
 *
 * @param int    $periode_id
 * @param int    $periode_ordre
 * @param string $periode_nom
 * @return void
 */
public function DB_modifier_periode($periode_id,$periode_ordre,$periode_nom)
{
	$DB_SQL = 'UPDATE sacoche_periode ';
	$DB_SQL.= 'SET periode_ordre=:periode_ordre,periode_nom=:periode_nom ';
	$DB_SQL.= 'WHERE periode_id=:periode_id ';
	$DB_VAR = array(':periode_id'=>$periode_id,':periode_ordre'=>$periode_ordre,':periode_nom'=>$periode_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer une matière spécifique
 *
 * @param int $matiere_id
 * @return void
 */
public function DB_supprimer_matiere_specifique($matiere_id)
{
	$DB_SQL = 'DELETE FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les enseignants
	$DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les référentiels associés, et donc tous les scores associés (orphelins de la matière)
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_referentiels_matiere($matiere_id);
}

/**
 * Supprimer les référentiels dépendant d'une matière
 *
 * @param int $matiere_id
 * @return void
 */
public function DB_supprimer_referentiels_matiere($matiere_id)
{
	$DB_SQL = 'DELETE sacoche_referentiel, sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (matiere_id,item_id) ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un groupe
 *
 * Par défaut, on supprime aussi les devoirs associés ($with_devoir=TRUE), mais on conserve les notes, qui deviennent orphelines et non éditables ultérieurement.
 * Mais on peut aussi vouloir dans un second temps ($with_devoir=FALSE) supprimer les devoirs associés avec leurs notes en utilisant DB_supprimer_devoir_et_saisies().
 *
 * @param int    $groupe_id
 * @param string $groupe_type   'classe' | 'groupe' | 'besoin' | 'eval'
 * @param bool   $with_devoir
 * @return void
 */
public function DB_supprimer_groupe_par_admin($groupe_id,$groupe_type,$with_devoir=TRUE)
{
	// Il faut aussi supprimer les jointures avec les utilisateurs
	// Il faut aussi supprimer les jointures avec les périodes
	$jointure_periode_delete = ( ($groupe_type=='classe') || ($groupe_type=='groupe') ) ? ', sacoche_jointure_groupe_periode ' : '' ;
	$jointure_periode_join   = ( ($groupe_type=='classe') || ($groupe_type=='groupe') ) ? 'LEFT JOIN sacoche_jointure_groupe_periode USING (groupe_id) ' : '' ;
	// Il faut aussi supprimer les évaluations portant sur le groupe
	$jointure_devoir_delete = ($with_devoir) ? ', sacoche_devoir , sacoche_jointure_devoir_item ' : '' ;
	$jointure_devoir_join   = ($with_devoir) ? 'LEFT JOIN sacoche_devoir USING (groupe_id) LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ' : '' ;
	// Let's go
	$DB_SQL = 'DELETE sacoche_groupe , sacoche_jointure_user_groupe '.$jointure_periode_delete.$jointure_devoir_delete;
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
	$DB_SQL.= $jointure_periode_join.$jointure_devoir_join;
	$DB_SQL.= 'WHERE groupe_id=:groupe_id ';
	$DB_VAR = array(':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Sans oublier le champ pour les affectations des élèves dans une classe
	if($groupe_type=='classe')
	{
		$DB_SQL = 'UPDATE sacoche_user ';
		$DB_SQL.= 'SET eleve_classe_id=0 ';
		$DB_SQL.= 'WHERE eleve_classe_id=:groupe_id';
		$DB_VAR = array(':groupe_id'=>$groupe_id);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * Supprimer les devoirs sans les saisies associées (utilisé uniquement dans le cadre d'un nettoyage annuel ; les groupes de types 'besoin' et 'eval' sont supprimés dans un second temps)
 *
 * @return void
 */
public function DB_supprimer_devoirs_sans_saisies()
{
	// Il faut aussi supprimer les jointures du devoir avec les items
	$DB_SQL = 'DELETE sacoche_devoir, sacoche_jointure_devoir_item ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Supprimer les reliquats de requêtes d'évaluations dans les devoirs (utilisé uniquement dans le cadre d'un nettoyage annuel)
 *
 * @return void
 */
public function DB_supprimer_saisies_REQ()
{
	$DB_SQL = 'DELETE FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE saisie_note="REQ" ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * supprimer_saisies
 *
 * @param void
 * @return void
 */
public function DB_supprimer_saisies()
{
	DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_saisie' , NULL);
}

/**
 * Supprimer toutes les demandes d'évaluations résiduelles dans l'établissement
 *
 * @param void
 * @return void
 */
public function DB_supprimer_demandes_evaluation()
{
	DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_demande' , NULL);
}

/**
 * supprimer_validations
 *
 * @param void
 * @return void
 */
public function DB_supprimer_validations()
{
	DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_jointure_user_entree' , NULL);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_jointure_user_pilier' , NULL);
}

/**
 * supprimer_periode
 *
 * @param int $periode_id
 * @return void
 */
public function DB_supprimer_periode($periode_id)
{
	$DB_SQL = 'DELETE FROM sacoche_periode ';
	$DB_SQL.= 'WHERE periode_id=:periode_id ';
	$DB_VAR = array(':periode_id'=>$periode_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les classes
	$DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'WHERE periode_id=:periode_id ';
	$DB_VAR = array(':periode_id'=>$periode_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_jointures_parents_for_eleves
 *
 * @param bool|string   $listing_eleve_id   id des élèves séparés par des virgules
 * @return void
 */
public function DB_supprimer_jointures_parents_for_eleves($listing_eleve_id)
{
	$DB_SQL = 'DELETE FROM sacoche_jointure_parent_eleve ';
	$DB_SQL.= 'WHERE eleve_id IN('.$listing_eleve_id.') ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Supprimer un utilisateur avec tout ce qui en dépend
 *
 * @param int    $user_id
 * @param string $user_profil   eleve | parent | professeur | directeur | administrateur
 * @return void
 */
public function DB_supprimer_utilisateur($user_id,$user_profil)
{
	$DB_VAR = array(':user_id'=>$user_id);
	$DB_SQL = 'DELETE FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if( ($user_profil=='eleve') || ($user_profil=='professeur') )
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
		$DB_SQL.= 'WHERE user_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	if($user_profil=='eleve')
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_parent_eleve ';
		$DB_SQL.= 'WHERE eleve_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM sacoche_saisie ';
		$DB_SQL.= 'WHERE eleve_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM sacoche_demande ';
		$DB_SQL.= 'WHERE user_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	if($user_profil=='parent')
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_parent_eleve ';
		$DB_SQL.= 'WHERE parent_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM sacoche_parent_adresse ';
		$DB_SQL.= 'WHERE parent_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	if($user_profil=='professeur')
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
		$DB_SQL.= 'WHERE user_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE sacoche_jointure_devoir_item ';
		$DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
		$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
		$DB_SQL.= 'WHERE prof_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		// Groupes type "eval" et "besoin", avec jointures users associées
		$DB_SQL = 'DELETE sacoche_groupe, sacoche_jointure_user_groupe ';
		$DB_SQL.= 'FROM sacoche_devoir ';
		$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
		$DB_SQL.= 'WHERE prof_id=:user_id AND groupe_type IN("besoin","eval") ';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM sacoche_devoir ';
		$DB_SQL.= 'WHERE prof_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'UPDATE sacoche_saisie ';
		$DB_SQL.= 'SET prof_id=0 ';
		$DB_SQL.= 'WHERE prof_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * Optimiser les tables d'une base
 *
 * @param void
 * @return void
 */
public function DB_optimiser_tables_structure()
{
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLE STATUS LIKE "sacoche_%"');
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			DB::query(SACOCHE_STRUCTURE_BD_NAME , 'OPTIMIZE TABLE '.$DB_ROW['Name']);
		}
	}
}

/**
 * Recherche et correction d'anomalies : numérotation des items d'un thème, ou des thèmes d'un domaine, ou des domaines d'un référentiel
 *
 * @param void
 * @return array   tableau avec label et commentaire pour chaque recherche
 */
public function DB_corriger_numerotations()
{
	function make_where($champ,$valeur)
	{
		return $champ.'='.$valeur;
	}
	$tab_bilan = array();
	$tab_recherche = array();
	$tab_recherche[] = array( 'contenant_nom'=>'référentiel' , 'contenant_tab_champs'=>array('matiere_id','niveau_id') , 'element_nom'=>'domaine' , 'element_champ'=>'domaine' , 'debut'=>1 , 'decalage'=>0 );
	$tab_recherche[] = array( 'contenant_nom'=>'domaine'     , 'contenant_tab_champs'=>array('domaine_id')             , 'element_nom'=>'thème'   , 'element_champ'=>'theme'   , 'debut'=>1 , 'decalage'=>0 );
	$tab_recherche[] = array( 'contenant_nom'=>'thème'       , 'contenant_tab_champs'=>array('theme_id')               , 'element_nom'=>'item'    , 'element_champ'=>'item'    , 'debut'=>0 , 'decalage'=>1 );
	foreach($tab_recherche as $tab_donnees)
	{
		extract($tab_donnees,EXTR_OVERWRITE);
		// numéros en double
		$DB_SQL = 'SELECT DISTINCT CONCAT('.implode(',",",',$contenant_tab_champs).') AS contenant_id , COUNT('.$element_champ.'_id) AS nombre ';
		$DB_SQL.= 'FROM sacoche_referentiel_'.$element_champ.' ';
		$DB_SQL.= 'GROUP BY '.implode(',',$contenant_tab_champs).','.$element_champ.'_ordre ';
		$DB_SQL.= 'HAVING nombre>1 ';
		$DB_TAB1 = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE);
		// numéros manquants ou décalés
		$DB_SQL = 'SELECT DISTINCT CONCAT('.implode(',",",',$contenant_tab_champs).') AS contenant_id , MAX('.$element_champ.'_ordre) AS maximum , COUNT('.$element_champ.'_id) AS nombre ';
		$DB_SQL.= 'FROM sacoche_referentiel_'.$element_champ.' ';
		$DB_SQL.= 'GROUP BY '.implode(',',$contenant_tab_champs).' ';
		$DB_SQL.= 'HAVING nombre!=maximum+'.$decalage.' ';
		$DB_TAB2 = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE);
		// en réunissant les 2 requêtes on a repéré tous les problèmes possibles
		$tab_bugs = array_unique( array_merge( array_keys($DB_TAB1) , array_keys($DB_TAB2) ) );
		$nb_bugs = count($tab_bugs);
		if($nb_bugs)
		{
			foreach($tab_bugs as $contenant_id)
			{
				$element_ordre = $debut;
				$contenant_tab_valeur = explode(',',$contenant_id);
				$tab_where = array_map('make_where', $contenant_tab_champs, $contenant_tab_valeur);
				$DB_SQL = 'SELECT '.$element_champ.'_id ';
				$DB_SQL.= 'FROM sacoche_referentiel_'.$element_champ.' ';
				$DB_SQL.= 'WHERE '.implode(' AND ',$tab_where).' ';
				$DB_SQL.= 'ORDER BY '.$element_champ.'_ordre ASC ';
				$DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
				foreach($DB_COL as $element_champ_id)
				{
					$DB_SQL = 'UPDATE sacoche_referentiel_'.$element_champ.' ';
					$DB_SQL.= 'SET '.$element_champ.'_ordre='.$element_ordre.' ';
					$DB_SQL.= 'WHERE '.$element_champ.'_id='.$element_champ_id.' ';
					DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
					$element_ordre++;
				}
			}
		}
		$message = (!$nb_bugs) ? 'rien à signaler' : ( ($nb_bugs>1) ? $nb_bugs.' '.$contenant_nom.'s dont le contenu a été renuméroté' : '1 '.$contenant_nom.' dont le contenu a été renuméroté' ) ;
		$classe  = (!$nb_bugs) ? 'valide' : 'alerte' ;
		$tab_bilan[] = '<label class="'.$classe.'">'.ucfirst($element_nom).'s des '.$contenant_nom.'s : '.$message.'.</label>';
	}
	return $tab_bilan;
}

/**
 * Recherche et suppression de correspondances anormales dans la base
 *
 * @param void
 * @return array   tableau avec label et commentaire pour chaque recherche
 */
public function DB_corriger_anomalies()
{
	$tab_bilan = array();
	// Recherche d'anomalies : référentiels associés à une matière supprimée
	$DB_SQL = 'DELETE sacoche_referentiel,sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE sacoche_matiere.matiere_id IS NULL ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Référentiels : '.$message.'.</label>';
	// Recherche d'anomalies : domaines associés à une matière supprimée...
	$DB_SQL = 'DELETE sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_referentiel_domaine ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE sacoche_matiere.matiere_id IS NULL ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Domaines (arborescence) : '.$message.'.</label>';
	// Recherche d'anomalies : thèmes associés à un domaine supprimé...
	$DB_SQL = 'DELETE sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_referentiel_theme ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE sacoche_referentiel_domaine.domaine_id IS NULL ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Thèmes (arborescence) : '.$message.'.</label>';
	// Recherche d'anomalies : items associés à un thème supprimé...
	$DB_SQL = 'DELETE sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_referentiel_item ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE sacoche_referentiel_theme.theme_id IS NULL ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Items (arborescence) : '.$message.'.</label>';
	// Recherche d'anomalies : demandes d'évaluations associées à un user ou une matière ou un item supprimé...
	$DB_SQL = 'DELETE sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_matiere.matiere_id IS NULL) OR (sacoche_referentiel_item.item_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Demandes d\'évaluations : '.$message.'.</label>';
	// Recherche d'anomalies : saisies de scores associées à un élève ou un item supprimé...
	// Attention, on ne teste pas le professeur ou le devoir, car les saisies sont conservées au delà
	$DB_SQL = 'DELETE sacoche_saisie ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_saisie.eleve_id=sacoche_user.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_referentiel_item.item_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Scores : '.$message.'.</label>';
	// Recherche d'anomalies : devoirs associés à un prof ou un groupe supprimé...
	$DB_SQL = 'DELETE sacoche_devoir, sacoche_jointure_devoir_item ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.prof_id=sacoche_user.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_groupe.groupe_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Évaluations : '.$message.'.</label>';
	// Recherche d'anomalies : jointures période/groupe associées à une période ou un groupe supprimé...
	$DB_SQL = 'DELETE sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_periode.periode_id IS NULL) OR (sacoche_groupe.groupe_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures période/groupe : '.$message.'.</label>';
	// Recherche d'anomalies : jointures user/groupe associées à un user ou un groupe supprimé...
	$DB_SQL = 'DELETE sacoche_jointure_user_groupe ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_groupe.groupe_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures utilisateur/groupe : '.$message.'.</label>';
	// Recherche d'anomalies : jointures user/matière associées à un user ou une matière supprimée...
	$DB_SQL = 'DELETE sacoche_jointure_user_matiere ';
	$DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_matiere.matiere_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures utilisateur/matière : '.$message.'.</label>';
	// Recherche d'anomalies : jointures devoir/item associées à un devoir ou un item supprimé...
	$DB_SQL = 'DELETE sacoche_jointure_devoir_item ';
	$DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'WHERE ( (sacoche_devoir.devoir_id IS NULL) OR (sacoche_referentiel_item.item_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures évaluation/item : '.$message.'.</label>';
	// Recherche d'anomalies : adresse associée à un parent supprimé...
	$DB_SQL = 'DELETE sacoche_parent_adresse ';
	$DB_SQL.= 'FROM sacoche_parent_adresse ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_parent_adresse.parent_id=sacoche_user.user_id ';
	$DB_SQL.= 'WHERE user_id IS NULL ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures parent/adresse : '.$message.'.</label>';
	// Recherche d'anomalies : jointures parent/élève associées à un parent ou un élève supprimé...
	$DB_SQL = 'DELETE sacoche_jointure_parent_eleve ';
	$DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS eleve ON sacoche_jointure_parent_eleve.eleve_id=eleve.user_id ';
	$DB_SQL.= 'WHERE ( (parent.user_id IS NULL) OR (eleve.user_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures parent/enfant : '.$message.'.</label>';
	// Recherche d'anomalies : élèves associés à une classe supprimée...
	// Attention, l'id de classe à 0 est normal pour un élève non affecté ou un autre statut
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
	$DB_SQL.= 'SET sacoche_user.eleve_classe_id=0 ';
	$DB_SQL.= 'WHERE ( (sacoche_user.eleve_classe_id!=0) AND (sacoche_groupe.groupe_id IS NULL) ) ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
	$message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
	$classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
	$tab_bilan[] = '<label class="'.$classe.'">Jointures élève/classe : '.$message.'.</label>';
	return $tab_bilan;
}

}
?>