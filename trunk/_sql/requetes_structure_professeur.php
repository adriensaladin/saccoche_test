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
// Ces méthodes ne concernent que les professeurs.

class DB_STRUCTURE_PROFESSEUR extends DB
{

/**
 * recuperer_item_popularite
 * Calculer pour chaque item sa popularité, i.e. le nb de demandes pour les élèves concernés.
 *
 * @param string $listing_demande_id   id des demandes séparés par des virgules
 * @param string $listing_user_id      id des élèves séparés par des virgules
 * @return array   [i]=>array('item_id','popularite')
 */
public function DB_recuperer_item_popularite($listing_demande_id,$listing_user_id)
{
	$DB_SQL = 'SELECT item_id , COUNT(item_id) AS popularite ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') AND user_id IN('.$listing_user_id.') ';
	$DB_SQL.= 'GROUP BY item_id ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_matieres_professeur_infos_referentiel
 *
 * @param string $listing_matieres   id des matières de l'établissement séparées par des virgules
 * @param int $user_id
 * @return array|string
 */
public function DB_lister_matieres_professeur_infos_referentiel($listing_matieres,$user_id)
{
	$DB_SQL = 'SELECT matiere_id, matiere_nom, matiere_partage, matiere_nb_demandes, jointure_coord ';
	$DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'WHERE (matiere_id IN('.$listing_matieres.') OR matiere_partage=:partage) AND user_id=:user_id '; // Test matiere car un prof peut être encore relié à des matières décochées par l'admin.
	$DB_SQL.= 'ORDER BY matiere_nom ASC';
	$DB_VAR = array(':user_id'=>$user_id,':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes_professeur
 *
 * @param int $prof_id
 * @return array
 */
public function DB_lister_groupes_professeur($prof_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE user_id=:user_id AND groupe_type!=:type4 ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':user_id'=>$prof_id,':type4'=>'eval');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_professeurs_groupe
 *
 * @param int $groupe_id
 * @return array
 */
/*
public function DB_lister_professeurs_groupe($groupe_id)
{
	$DB_SQL = 'SELECT user_id, user_nom, user_prenom ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE groupe_id=:groupe_id AND user_profil=:profil AND user_statut=:statut ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':groupe_id'=>$groupe_id,':profil'=>'professeur',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}
*/

/**
 * lister_classes_groupes_professeur
 *
 * @param int $prof_id
 * @return array
 */
public function DB_lister_classes_groupes_professeur($prof_id)
{
	$DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_type ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE user_id=:user_id AND groupe_type IN (:type1,:type2) ';
	$DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':user_id'=>$prof_id,':type1'=>'classe',':type2'=>'groupe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes_besoins
 * Il s'agit des groupes de besoins dont un prof est propriétaire, pas de ceux auxquels il a accès parce que partagés par un collègue.
 *
 * @param int    $prof_id
 * @param bool   $is_proprio
 * @return array
 */
public function DB_lister_groupes_besoins($prof_id,$is_proprio)
{
	$proprio = ($is_proprio) ? 1 : 0 ;
	$DB_SQL = 'SELECT groupe_id, groupe_nom, niveau_id, niveau_ordre, niveau_nom ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE user_id=:user_id AND jointure_pp=:proprio AND groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':user_id'=>$prof_id,':proprio'=>$proprio,':type'=>'besoin');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes_besoins_non_proprietaire_avec_infos
 * Il s'agit des groupes de besoins dont un prof n'est pas propriétaire, mais qui lui est affecté, avec l'info sur son propriétaire.
 *
 * @param int    $prof_id
 * @return array
 */
/*
public function DB_lister_groupes_besoins_non_proprietaire_avec_infos($prof_id)
{
	$DB_SQL = 'SELECT groupe_id, groupe_nom, user_nom, user_prenom ';
	$DB_SQL.= 'FROM sacoche_user AS locataire ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe ON locataire.user_id=sacoche_jointure_user_groupe.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS locataire ';
	$DB_SQL.= 'WHERE user_id=:user_id AND jointure_pp=:proprio AND groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':user_id'=>$prof_id,':proprio'=>1,':type'=>'besoin');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}
*/

/**
 * lister_eleves_classes
 *
 * @param string   $listing_classe_id   id des classes séparés par des virgules
 * @return array
 */
public function DB_lister_eleves_classes($listing_classe_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:statut AND eleve_classe_id IN ('.$listing_classe_id.') ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>'eleve',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleves_groupes
 *
 * @param string   $listing_groupe_id   id des groupes séparés par des virgules
 * @return array
 */
public function DB_lister_eleves_groupes($listing_groupe_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
	$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:statut AND groupe_id IN ('.$listing_groupe_id.') ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>'eleve',':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_avec_groupes_besoins
 *
 * @param string   $profil               "eleve" | "professeur"
 * @param string   $listing_groupes_id   liste des ids des groupes séparés par des virgules
 * @return array
 */
public function DB_lister_users_avec_groupes_besoins($profil,$listing_groupes_id)
{
	$DB_SQL = 'SELECT groupe_id, user_nom, user_prenom, jointure_pp ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE user_profil=:profil AND groupe_id IN('.$listing_groupes_id.') AND user_statut=:statut ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>$profil,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_demandes_prof
 *
 * @param int    $matiere_id        id de la matière du prof
 * @param int    $listing_user_id   id des élèves du prof séparés par des virgules
 * @return array
 */
public function DB_lister_demandes_prof($matiere_id,$listing_user_id)
{
	$DB_SQL = 'SELECT sacoche_demande.*, ';
	$DB_SQL.= 'CONCAT(niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
	$DB_SQL.= 'item_nom, user_nom, user_prenom ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') AND sacoche_demande.matiere_id=:matiere_id ';
	$DB_SQL.= 'ORDER BY niveau_ref ASC, domaine_ref ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoirs_prof
 *
 * @param int    $prof_id
 * @param int    $groupe_id        id du groupe ou de la classe pour un devoir sur une classe ou un groupe ; 0 pour un devoir sur une sélection d'élèves ; -1 pour les devoirs de toutes les classes / tous les groupes
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @return array
 */
public function DB_lister_devoirs_prof($prof_id,$groupe_id,$date_debut_mysql,$date_fin_mysql)
{
	// DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
	// Il faut ajouter dans la requête des "DISTINCT" sinon la liaison avec "sacoche_jointure_user_groupe" duplique tout x le nb d'élèves associés pour une évaluation sur une sélection d'élèves.
	$DB_SQL = 'SELECT sacoche_devoir.*, groupe_type, groupe_nom, ';
	$DB_SQL.= 'GROUP_CONCAT(DISTINCT item_id SEPARATOR "_") AS items_listing, COUNT(DISTINCT item_id) AS items_nombre ';
	if(!$groupe_id)
	{
		$DB_SQL .= ', '.'GROUP_CONCAT(DISTINCT user_id SEPARATOR "_") AS users_listing, COUNT(DISTINCT user_id) AS users_nombre ';
	}
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	if(!$groupe_id)
	{
		$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
	}
	$DB_SQL.= 'WHERE ( prof_id=:prof_id OR devoir_partage LIKE :prof_id_like ) ';
	$DB_SQL.= ($groupe_id) ? 'AND groupe_type!=:type4 ' : 'AND groupe_type=:type4 ' ;
	$DB_SQL.= ($groupe_id>0) ? 'AND groupe_id='.$groupe_id.' ' : '' ;
	$DB_SQL.= (!$groupe_id) ? 'AND user_id!=:prof_id ' : '' ; // Sinon le prof (aussi rattaché au groupe du devoir) est compté parmi la liste des élèves.
	$DB_SQL.= 'AND devoir_date>="'.$date_debut_mysql.'" AND devoir_date<="'.$date_fin_mysql.'" ' ;
	$DB_SQL.= 'GROUP BY devoir_id ';
	$DB_SQL.= 'ORDER BY devoir_date DESC, groupe_nom ASC';
	$DB_VAR = array(':prof_id'=>$prof_id,':prof_id_like'=>'%_'.$prof_id.'_%',':type4'=>'eval');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoirs_prof_groupe_sans_infos_last
 *
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param string $groupe_type   groupe | select
 * @return array
 */
public function DB_lister_devoirs_prof_groupe_sans_infos_last($prof_id,$groupe_id,$groupe_type)
{
	$DB_SQL = 'SELECT sacoche_devoir.* ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE prof_id=:prof_id ';
	$DB_SQL.= ($groupe_type=='groupe') ? 'AND groupe_type!=:type4 AND groupe_id=:groupe_id ' : 'AND groupe_type=:type4 ' ;
	$DB_SQL.= 'ORDER BY devoir_date DESC ';
	$DB_SQL.= 'LIMIT 20 ';
	$DB_VAR = array(':prof_id'=>$prof_id,':groupe_id'=>$groupe_id,':type4'=>'eval');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_items_devoir
 * Retourner les items d'un devoir
 *
 * @param int  $devoir_id
 * @return array
 */
public function DB_lister_items_devoir($devoir_id)
{
	$DB_SQL = 'SELECT item_id, item_nom, entree_id, ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref ';
	$DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id ';
	$DB_SQL.= 'ORDER BY jointure_ordre ASC, matiere_ref ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':devoir_id'=>$devoir_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_saisies_devoir
 *
 * @param int   $devoir_id
 * @param bool  $with_REQ   // Avec ou sans les repères de demandes d'évaluations
 * @return array
 */
public function DB_lister_saisies_devoir($devoir_id,$with_REQ)
{
	// On évite les élèves désactivés pour ces opérations effectuées sur les pages de saisies d'évaluations
	$DB_SQL = 'SELECT eleve_id, item_id, saisie_note ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_saisie.eleve_id=sacoche_user.user_id ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND user_statut=:statut ';
	if(!$with_REQ)
	{
		$DB_SQL.= 'AND saisie_note!="REQ" ';
	}
	$DB_VAR = array(':devoir_id'=>$devoir_id,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_prof_principal
 *
 * @param int $user_id
 * @return int
 */
public function DB_tester_prof_principal($user_id)
{
	$DB_SQL = 'SELECT groupe_id ';
	$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'WHERE user_id=:user_id AND jointure_pp=:pp ';
	$DB_SQL.= 'LIMIT 1'; // utile
	$DB_VAR = array(':user_id'=>$user_id,':pp'=>1);
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_groupe_nom
 *
 * @param string $groupe_nom
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public function DB_tester_groupe_nom($groupe_nom,$groupe_id=FALSE)
{
	$DB_SQL = 'SELECT groupe_id ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_nom=:groupe_nom ';
	$DB_VAR = array(':groupe_type'=>'groupe',':groupe_nom'=>$groupe_nom);
	if($groupe_id)
	{
		$DB_SQL.= 'AND groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1'; // utile
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_groupe_par_prof
 *
 * @param string $groupe_type   'besoin' | 'eval'
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return int
 */
public function DB_ajouter_groupe_par_prof($groupe_type,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'INSERT INTO sacoche_groupe(groupe_type,groupe_ref,groupe_nom,niveau_id) ';
	$DB_SQL.= 'VALUES(:groupe_type,:groupe_ref,:groupe_nom,:niveau_id)';
	$DB_VAR = array(':groupe_type'=>$groupe_type,':groupe_ref'=>'',':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_liaison_professeur_responsable ; ressemble à la fonction ADMIN DB_modifier_liaison_professeur_principal()
 *
 * @param int    $user_id
 * @param int    $groupe_id
 * @return void
 */
public function DB_ajouter_liaison_professeur_responsable($user_id,$groupe_id)
{
	$DB_SQL = 'UPDATE sacoche_jointure_user_groupe ';
	$DB_SQL.= 'SET jointure_pp=:pp ';
	$DB_SQL.= 'WHERE user_id=:user_id AND groupe_id=:groupe_id ';
	$DB_VAR = array(':user_id'=>$user_id,':groupe_id'=>$groupe_id,':pp'=>1);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_devoir
 *
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param string $date_mysql
 * @param string $info
 * @param string $date_visible_mysql
 * @param string $listing_id_profs   id des profs avec qui on partage l'éval (séparés par des _) ; facultatif car non transmis si éval sur des élèves sélectionnés
 * @return int
 */
public function DB_ajouter_devoir($prof_id,$groupe_id,$date_mysql,$info,$date_visible_mysql,$listing_id_profs='')
{
	$listing_id_profs = ($listing_id_profs) ? '_'.$listing_id_profs.'_' : $listing_id_profs ;
	$DB_SQL = 'INSERT INTO sacoche_devoir(prof_id,groupe_id,devoir_date,devoir_info,devoir_visible_date,devoir_partage) ';
	$DB_SQL.= 'VALUES(:prof_id,:groupe_id,:date,:info,:visible_date,:devoir_partage)';
	$DB_VAR = array(':prof_id'=>$prof_id,':groupe_id'=>$groupe_id,':date'=>$date_mysql,':info'=>$info,':visible_date'=>$date_visible_mysql,':devoir_partage'=>$listing_id_profs);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_saisie
 * Si la note est "REQ" (pour marquer une demande d'évaluation), on utilise un REPLACE au lieu d'un INSERT car une saisie peut déjà exister (si le prof ajoute les demandes à un devoir existant).
 *
 * @param int    $prof_id
 * @param int    $eleve_id
 * @param int    $devoir_id
 * @param int    $item_id
 * @param string $item_date_mysql
 * @param string $item_note
 * @param string $item_info
 * @param string $item_date_visible_mysql
 * @return void
 */
public function DB_ajouter_saisie($prof_id,$eleve_id,$devoir_id,$item_id,$item_date_mysql,$item_note,$item_info,$item_date_visible_mysql)
{
	$commande = ($item_note!='REQ') ? 'INSERT' : 'REPLACE' ;
	$DB_SQL = $commande.' INTO sacoche_saisie ';
	$DB_SQL.= 'VALUES(:prof_id,:eleve_id,:devoir_id,:item_id,:item_date,:item_note,:item_info,:item_date_visible)';
	$DB_VAR = array(':prof_id'=>$prof_id,':eleve_id'=>$eleve_id,':devoir_id'=>$devoir_id,':item_id'=>$item_id,':item_date'=>$item_date_mysql,':item_note'=>$item_note,':item_info'=>$item_info,':item_date_visible'=>$item_date_visible_mysql);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_groupe_par_prof ; on ne touche pas à "groupe_type" (ni à "groupe_ref" qui reste vide)
 *
 * @param int    $groupe_id
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return void
 */
public function DB_modifier_groupe_par_prof($groupe_id,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'UPDATE sacoche_groupe ';
	$DB_SQL.= 'SET groupe_ref=:groupe_ref,groupe_nom=:groupe_nom,niveau_id=:niveau_id ';
	$DB_SQL.= 'WHERE groupe_id=:groupe_id ';
	$DB_VAR = array(':groupe_id'=>$groupe_id,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_ordre_item
 *
 * @param int    $devoir_id
 * @param array  $tab_items   tableau des id des items
 * @return void
 */
public function DB_modifier_ordre_item($devoir_id,$tab_items)
{
	$DB_SQL = 'UPDATE sacoche_jointure_devoir_item ';
	$DB_SQL.= 'SET jointure_ordre=:ordre ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id=:item_id ';
	$ordre = 1;
	foreach($tab_items as $item_id)
	{
		$DB_VAR = array(':devoir_id'=>$devoir_id,':item_id'=>$item_id,':ordre'=>$ordre);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$ordre++;
	}
}

/**
 * modifier_saisie
 *
 * @param int    $eleve_id
 * @param int    $devoir_id
 * @param int    $item_id
 * @param string $saisie_note
 * @param string $saisie_info
 * @return void
 */
public function DB_modifier_saisie($eleve_id,$devoir_id,$item_id,$saisie_note,$saisie_info)
{
	$DB_SQL = 'UPDATE sacoche_saisie ';
	$DB_SQL.= 'SET saisie_note=:saisie_note,saisie_info=:saisie_info ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:item_id ';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':devoir_id'=>$devoir_id,':item_id'=>$item_id,':saisie_note'=>$saisie_note,':saisie_info'=>$saisie_info);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_devoir
 *
 * @param int    $devoir_id
 * @param int    $prof_id
 * @param string $date_mysql
 * @param string $info
 * @param string $date_visible_mysql
 * @param array  $tab_items          tableau des id des items
 * @param string $listing_id_profs   id des profs avec qui on partage l'éval (séparés par des _) ; facultatif car non transmis si éval sur des élèves sélectionnés
 * @return void
 */
public function DB_modifier_devoir($devoir_id,$prof_id,$date_mysql,$info,$date_visible_mysql,$tab_items,$listing_id_profs='')
{
	$listing_id_profs = ($listing_id_profs) ? '_'.$listing_id_profs.'_' : $listing_id_profs ;
	// sacoche_devoir (maj)
	$DB_SQL = 'UPDATE sacoche_devoir ';
	$DB_SQL.= 'SET devoir_date=:date, devoir_info=:devoir_info, devoir_visible_date=:visible_date, devoir_partage=:devoir_partage ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND prof_id=:prof_id ';
	$DB_VAR = array(':date'=>$date_mysql,':devoir_info'=>$info,':visible_date'=>$date_visible_mysql,':devoir_partage'=>$listing_id_profs,':devoir_id'=>$devoir_id,':prof_id'=>$prof_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// sacoche_saisie (maj)
	$saisie_info = $info.' ('.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.)';
	$DB_SQL = 'UPDATE sacoche_saisie ';
	$DB_SQL.= 'SET saisie_date=:date, saisie_info=:saisie_info, saisie_visible_date=:visible_date ';
	$DB_SQL.= 'WHERE prof_id=:prof_id AND devoir_id=:devoir_id ';
	$DB_VAR = array(':prof_id'=>$prof_id,':devoir_id'=>$devoir_id,':date'=>$date_mysql,':saisie_info'=>$saisie_info,':visible_date'=>$date_visible_mysql);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_user_groupe_par_prof
 *
 * @param int    $user_id
 * @param string $user_profil   'eleve' ou 'professeur'
 * @param int    $groupe_id
 * @param string $groupe_type   'besoin' MAIS PAS 'eval' POUR LES ELEVES car géré par DB_modifier_liaison_devoir_user()
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public function DB_modifier_liaison_user_groupe_par_prof($user_id,$user_profil,$groupe_id,$groupe_type,$etat)
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
 * modifier_liaison_devoir_item
 *
 * @param int    $devoir_id
 * @param array  $tab_items   tableau des id des items
 * @param string $mode        {creer;dupliquer} => insertion dans un nouveau devoir || {substituer} => maj avec delete / insert || {ajouter} => maj avec insert uniquement
 * @param int    $devoir_ordonne_id   Dans le cas d'une duplication, id du devoir dont il faut récupérer l'ordre des items.
 * @return void
 */
public function DB_modifier_liaison_devoir_item($devoir_id,$tab_items,$mode,$devoir_ordonne_id=0)
{
	if( ($mode=='creer') || ($mode=='dupliquer') )
	{
		// Dans le cas d'une duplication, il faut aller rechercher l'ordre éventuel des items de l'évaluation d'origine pour ne pas le perdre
		$tab_ordre = array();
		if($devoir_ordonne_id)
		{
			$DB_SQL = 'SELECT item_id,jointure_ordre FROM sacoche_jointure_devoir_item ';
			$DB_SQL.= 'WHERE devoir_id=:devoir_id AND jointure_ordre>0 ';
			$DB_VAR = array(':devoir_id'=>$devoir_ordonne_id);
			$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			if(count($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					$tab_ordre[$DB_ROW['item_id']] = $DB_ROW['jointure_ordre'];
				}
			}
		}
		// Insertion des items
		$DB_SQL = 'INSERT INTO sacoche_jointure_devoir_item(devoir_id,item_id,jointure_ordre) ';
		$DB_SQL.= 'VALUES(:devoir_id,:item_id,:ordre)';
		foreach($tab_items as $item_id)
		{
			$ordre = (isset($tab_ordre[$item_id])) ? $tab_ordre[$item_id] : 0 ;
			$DB_VAR = array(':devoir_id'=>$devoir_id,':item_id'=>$item_id,':ordre'=>$ordre);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	else
	{
		// On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd l'info sur l'ordre des items.
		// Alors on récupère la liste des items déjà présents, et on étudie les différences pour faire des DELETE et INSERT sélectifs
		// -> on récupère les items actuels
		$DB_SQL = 'SELECT item_id  ';
		$DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
		$DB_SQL.= 'WHERE devoir_id=:devoir_id ';
		$DB_VAR = array(':devoir_id'=>$devoir_id);
		$tab_old_items = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		// -> on supprime si besoin les anciens items associés à ce devoir qui ne sont plus dans la liste transmise
		// -> on supprime si besoin les saisies des anciens items associés à ce devoir qui ne sont plus dans la liste transmise
		//   (concernant les saisies superflues concernant les items, voir DB_modifier_liaison_devoir_item() )
		if($mode=='substituer')
		{
			$tab_items_supprimer = array_diff($tab_old_items,$tab_items);
			if(count($tab_items_supprimer))
			{
				$chaine_item_id = implode(',',$tab_items_supprimer);
				$DB_SQL = 'DELETE FROM sacoche_jointure_devoir_item ';
				$DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id IN('.$chaine_item_id.')';
				$DB_VAR = array(':devoir_id'=>$devoir_id);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
				// sacoche_saisie (retirer superflu concernant les items ; concernant les élèves voir DB_modifier_liaison_devoir_user() )
				$DB_SQL = 'DELETE FROM sacoche_saisie ';
				$DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id IN('.$chaine_item_id.')';
				$DB_VAR = array(':devoir_id'=>$devoir_id);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
		// -> on ajoute les nouveaux items non anciennement présents
		$tab_items_ajouter = array_diff($tab_items,$tab_old_items);
		if(count($tab_items_ajouter))
		{
			foreach($tab_items_ajouter as $item_id)
			{
				$DB_SQL = 'INSERT INTO sacoche_jointure_devoir_item(devoir_id,item_id) ';
				$DB_SQL.= 'VALUES(:devoir_id,:item_id)';
				$DB_VAR = array(':devoir_id'=>$devoir_id,':item_id'=>$item_id);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
	}
}

/**
 * modifier_liaison_devoir_user
 * Uniquement pour les évaluations de type 'eval' ; voir DB_modifier_liaison_devoir_groupe() pour les autres
 *
 * @param int    $devoir_id
 * @param int    $groupe_id
 * @param array  $tab_eleves   tableau des id des élèves
 * @param string $mode         'creer' pour un insert dans un nouveau devoir || 'substituer' pour une maj delete / insert || 'ajouter' pour maj insert uniquement
 * @return void
 */
public function DB_modifier_liaison_devoir_user($devoir_id,$groupe_id,$tab_eleves,$mode)
{
	// -> on récupère la liste des élèves actuels déjà associés au groupe (pour la comparer à la liste transmise)
	if($mode!='creer')
	{
		// DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
		$DB_SQL = 'SELECT GROUP_CONCAT(user_id SEPARATOR " ") AS users_listing ';
		$DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
		$DB_SQL.= 'LEFT JOIN sacoche_user USING(user_id) ';
		$DB_SQL.= 'WHERE groupe_id=:groupe_id AND user_profil=:profil ';
		$DB_SQL.= 'GROUP BY groupe_id';
		$DB_VAR = array(':groupe_id'=>$groupe_id,':profil'=>'eleve');
		$users_listing = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$tab_eleves_avant = ($users_listing) ? explode(' ',$users_listing) : array() ;
	}
	else
	{
		$tab_eleves_avant = array() ;
	}
	// -> on supprime si besoin les anciens élèves associés à ce groupe qui ne sont plus dans la liste transmise
	// -> on supprime si besoin les saisies des anciens élèves associés à ce devoir qui ne sont plus dans la liste transmise
	//   (pour les saisies superflues concernant les items, voir DB_modifier_liaison_devoir_item() )
	if($mode=='substituer')
	{
		$tab_eleves_moins = array_diff($tab_eleves_avant,$tab_eleves);
		if(count($tab_eleves_moins))
		{
			$chaine_user_id = implode(',',$tab_eleves_moins);
			$DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
			$DB_SQL.= 'WHERE user_id IN('.$chaine_user_id.') AND groupe_id=:groupe_id ';
			$DB_VAR = array(':groupe_id'=>$groupe_id);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			$DB_SQL = 'DELETE FROM sacoche_saisie ';
			$DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id IN('.$chaine_user_id.')';
			$DB_VAR = array(':devoir_id'=>$devoir_id);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	// -> on ajoute si besoin les nouveaux élèves dans la liste transmise qui n'étaient pas déjà associés à ce groupe
	$tab_eleves_plus = array_diff($tab_eleves,$tab_eleves_avant);
	if(count($tab_eleves_plus))
	{
		foreach($tab_eleves_plus as $user_id)
		{
			$DB_SQL = 'INSERT INTO sacoche_jointure_user_groupe (user_id,groupe_id) ';
			$DB_SQL.= 'VALUES(:user_id,:groupe_id)';
			$DB_VAR = array(':user_id'=>$user_id,':groupe_id'=>$groupe_id);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
}

/**
 * modifier_liaison_devoir_groupe
 * Uniquement pour les évaluations sur une classe ou un groupe (pas de type 'eval')
 * RETIRÉ APRÈS REFLEXION : IL N'Y A PAS DE RAISON DE CARRÉMENT CHANGER LE GROUPE D'UNE ÉVALUATION => AU PIRE ON LA DUPLIQUE POUR UN AUTRE GROUPE PUIS ON LA SUPPRIME.
 *
 * @param int    $devoir_id
 * @param int    $groupe_id
 * @return void
 */
/*
public function DB_modifier_liaison_devoir_groupe($devoir_id,$groupe_id)
{
	// -> on récupère l'id du groupe antérieurement associé au devoir
	$DB_SQL = 'SELECT groupe_id ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id ';
	$DB_VAR = array(':devoir_id'=>$devoir_id);
	if( $groupe_id != DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) )
	{
		// sacoche_devoir (maj)
		$DB_SQL = 'UPDATE sacoche_devoir ';
		$DB_SQL.= 'SET groupe_id=:groupe_id ';
		$DB_SQL.= 'WHERE devoir_id=:devoir_id ';
		$DB_VAR = array(':devoir_id'=>$devoir_id,':groupe_id'=>$groupe_id);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		// sacoche_saisie : on ne s'embête pas à essayer de voir s'il y aurait intersection entre les deux groupes, on supprime les saisies du groupe antérieur...
		$DB_SQL = 'DELETE FROM sacoche_saisie ';
		$DB_SQL.= 'WHERE devoir_id=:devoir_id ';
		$DB_VAR = array(':devoir_id'=>$devoir_id);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}
*/

/**
 * modifier_statut_demandes
 *
 * @param string $listing_demande_id   id des demandes séparées par des virgules
 * @param string $statut               'prof' ou ...
 * @return void
 */
public function DB_modifier_statut_demandes($listing_demande_id,$statut)
{
	$DB_SQL = 'UPDATE sacoche_demande ';
	$DB_SQL.= 'SET demande_statut=:demande_statut ';
	$DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') ';
	$DB_VAR = array(':demande_statut'=>$statut);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_groupe_par_prof
 * Par défaut, on supprime aussi les devoirs associés ($with_devoir=TRUE), mais on conserve les notes, sui deviennent orphelines et non éditables ultérieurement.
 * Mais on peut aussi vouloir dans un second temps ($with_devoir=FALSE) supprimer les devoirs associés avec leurs notes en utilisant DB_supprimer_devoir_et_saisies().
 *
 * @param int    $groupe_id
 * @param string $groupe_type   'besoin' | 'eval'
 * @param bool   $with_devoir
 * @return void
 */
public function DB_supprimer_groupe_par_prof($groupe_id,$groupe_type,$with_devoir=TRUE)
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
 * supprimer_devoir_et_saisies
 *
 * @param int   $devoir_id
 * @param int   $prof_id   Seul un prof peut se supprimer une évaluation avec ses scores ; son id sert de sécurité.
 * @return void
 */
public function DB_supprimer_devoir_et_saisies($devoir_id,$prof_id)
{
	// Il faut aussi supprimer les jointures du devoir avec les items
	$DB_SQL = 'DELETE sacoche_devoir, sacoche_jointure_devoir_item, sacoche_saisie ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (devoir_id,prof_id) ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND prof_id=:prof_id ';
	$DB_VAR = array(':devoir_id'=>$devoir_id,':prof_id'=>$prof_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_saisie
 *
 * @param int   $eleve_id
 * @param int   $devoir_id
 * @param int   $item_id
 * @return void
 */
public function DB_supprimer_saisie($eleve_id,$devoir_id,$item_id)
{
	$DB_SQL = 'DELETE FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:item_id ';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':devoir_id'=>$devoir_id,':item_id'=>$item_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer les demandes d'évaluations listées (correspondant à une évaluation)
 *
 * @param string   $listing_demande_id   id des demandes séparées par des virgules
 * @return void
 */
public function DB_supprimer_demandes_devoir($listing_demande_id)
{
	$DB_SQL = 'DELETE FROM sacoche_demande ';
	$DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * supprimer_demande_precise
 *
 * @param int   $eleve_id
 * @param int   $item_id
 * @return void
 */
public function DB_supprimer_demande_precise($eleve_id,$item_id)
{
	$DB_SQL = 'DELETE FROM sacoche_demande ';
	$DB_SQL.= 'WHERE user_id=:eleve_id AND item_id=:item_id ';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':item_id'=>$item_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>