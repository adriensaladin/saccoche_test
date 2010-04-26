<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
 * DB_lister_matieres_partagees_SACoche
 * 
 * @param void
 * @return array
 */

function DB_lister_matieres_partagees_SACoche()
{
	$DB_SQL = 'SELECT * FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_partage=:partage ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC';
	$DB_VAR = array(':partage'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_matieres_specifiques
 * 
 * @param void
 * @return array
 */

function DB_lister_matieres_specifiques()
{
	$DB_SQL = 'SELECT * FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_partage=:partage ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC';
	$DB_VAR = array(':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_matieres_etablissement
 * 
 * @param string $listing_matieres   id des matières communes choisies séparés par des virgules
 * @param bool   $with_transversal   avec ou non la matière tranversale
 * @return array
 */

function DB_lister_matieres_etablissement($listing_matieres,$with_transversal)
{
	$where_trans = ($with_transversal) ? '' : 'AND matiere_transversal=0 ' ;
	$DB_SQL = 'SELECT * FROM sacoche_matiere ';
	$DB_SQL.= ($listing_matieres) ? 'WHERE matiere_id IN('.$listing_matieres.') OR matiere_partage=:partage '.$where_trans : 'WHERE matiere_partage=:partage ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC';
	$DB_VAR = array(':partage'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_paliers_SACoche
 * 
 * @param void
 * @return array
 */

function DB_lister_paliers_SACoche()
{
	$DB_SQL = 'SELECT * FROM sacoche_socle_palier ';
	$DB_SQL.= 'ORDER BY palier_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_lister_niveaux_SACoche
 * 
 * @param void
 * @return array
 */

function DB_lister_niveaux_SACoche()
{
	$DB_SQL = 'SELECT * FROM sacoche_niveau ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_lister_niveaux_etablissement
 * 
 * @param string      $listing_niveaux   id des niveaux séparés par des virgules
 * @param string|bool $listing_paliers   id des paliers séparés par des virgules ; false pour ne pas retourner les paliers
 * @return array
 */

function DB_lister_niveaux_etablissement($listing_niveaux,$listing_paliers)
{
	$DB_SQL = 'SELECT * FROM sacoche_niveau ';
	$DB_SQL.= 'WHERE niveau_id IN('.$listing_niveaux.') ';
	$DB_SQL.= ($listing_paliers) ? 'OR palier_id IN('.$_SESSION['PALIERS'].') ' : '' ;
	$DB_SQL.= 'ORDER BY niveau_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_lister_periodes
 * 
 * @param void
 * @return array
 */

function DB_lister_periodes()
{
	$DB_SQL = 'SELECT * FROM sacoche_periode ';
	$DB_SQL.= 'ORDER BY periode_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_lister_zones
 * 
 * @param void
 * @return array
 */

function DB_lister_zones()
{
	$DB_SQL = 'SELECT * FROM sacoche_geo ';
	$DB_SQL.= 'ORDER BY geo_ordre ASC';
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_dates_periode
 * 
 * @param int    $groupe_id    id du groupe
 * @param int    $periode_id   id de la période
 * @return array
 */

function DB_dates_periode($groupe_id,$periode_id)
{
	$DB_SQL = 'SELECT jointure_date_debut, jointure_date_fin ';
	$DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':groupe_id'=>$groupe_id,':periode_id'=>$periode_id);
	return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_amplitude_periodes
 * 
 * @param void
 * @return array  de la forme array('tout_debut'=>... , ['toute_fin']=>... , ['nb_jours_total']=>...)
 */

function DB_amplitude_periodes()
{
	$DB_SQL = 'SELECT MIN(jointure_date_debut) AS tout_debut , MAX(jointure_date_fin) AS toute_fin FROM sacoche_jointure_groupe_periode ';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
	if(count($DB_ROW))
	{
		// On ajoute un jour pour dessiner les barres jusqu'au jour suivant (accessoirement, ça évite aussi une possible division par 0).
		$DB_SQL = 'SELECT DATEDIFF(DATE_ADD(:toute_fin,INTERVAL 1 DAY),:tout_debut) AS nb_jours_total ';
		$DB_VAR = array(':tout_debut'=>$DB_ROW['tout_debut'],':toute_fin'=>$DB_ROW['toute_fin']);
		$DB_ROX = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_ROW['nb_jours_total'] = $DB_ROX['nb_jours_total'];
	}
	return $DB_ROW;
}

/**
 * DB_lister_classes
 * 
 * @param void
 * @return array
 */

function DB_lister_classes()
{
	$DB_SQL = 'SELECT * FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY groupe_ref ASC';
	$DB_VAR = array(':type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_classes_avec_niveaux
 * 
 * @param void
 * @return array
 */

function DB_lister_classes_avec_niveaux()
{
	$DB_SQL = 'SELECT * FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC';
	$DB_VAR = array(':type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_groupes_avec_niveaux
 * 
 * @param void
 * @return array
 */

function DB_lister_groupes_avec_niveaux()
{
	$DB_SQL = 'SELECT * FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC';
	$DB_VAR = array(':type'=>'groupe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_groupes_besoins
 * 
 * @param int    $prof_id
 * @return array
 */

function DB_lister_groupes_besoins($prof_id)
{
	$DB_SQL = 'SELECT groupe_id, groupe_nom, niveau_id, niveau_ordre, niveau_nom FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_prof_id=:prof_id AND groupe_type=:type ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':prof_id'=>$prof_id,':type'=>'besoin');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_classes_et_groupes_avec_niveaux
 * 
 * @param void
 * @return array
 */

function DB_lister_classes_et_groupes_avec_niveaux()
{
	$DB_SQL = 'SELECT * FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_type ASC, groupe_nom ASC';
	$DB_VAR = array(':type1'=>'classe',':type2'=>'groupe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_users_cibles
 * 
 * @param string   $listing_user_id   id des utilisateurs séparés par des virgules
 * @param bool     $info_classe       pour les élèves, récupérer la classe associée
 * @return array
 */

function DB_lister_users_cibles($listing_user_id,$info_classe=false)
{
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	if($info_classe)
	{
		$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
		$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	}
	$DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
	$order_classe = ($info_classe) ? 'niveau_ordre ASC, groupe_ref ASC, ' : '' ;
	$DB_SQL.= 'ORDER BY '.$order_classe.'user_nom ASC, user_prenom ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_lister_eleves_cibles
 * 
 * @param int    $listing_eleve_id   id des élèves séparés par des virgules
 * @return array|string              le tableau est de la forme [i] => array('eleve_id'=>...,'eleve_nom'=>...,'eleve_prenom'=>...,'eleve_id_gepi'=>...);
 */

function DB_lister_eleves_cibles($listing_eleve_id)
{
	$DB_SQL = 'SELECT user_id AS eleve_id , user_nom AS eleve_nom , user_prenom AS eleve_prenom , user_id_gepi AS eleve_id_gepi FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') AND user_profil=:profil ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>'eleve');
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucun élève trouvé correspondant aux identifiants transmis !' ;
}

/**
 * DB_lister_eleves_tri_statut_classe
 * 
 * @param void
 * @return array
 */

function DB_lister_eleves_tri_statut_classe()
{
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE user_profil=:profil ';
	$DB_SQL.= 'ORDER BY user_statut DESC, niveau_ordre ASC, groupe_ref ASC, user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>'eleve');
	return $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_professeurs_et_directeurs
 * 
 * @param void
 * @return array
 */

function DB_lister_professeurs_et_directeurs()
{
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil IN(:profil1,:profil2) ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil1'=>'professeur',':profil2'=>'directeur');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_professeurs_et_directeurs_tri_statut
 * 
 * @param void
 * @return array
 */

function DB_lister_professeurs_et_directeurs_tri_statut()
{
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil IN(:profil1,:profil2) ';
	$DB_SQL.= 'ORDER BY user_statut DESC, user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil1'=>'professeur',':profil2'=>'directeur');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_jointure_professeurs_coordonnateurs
 * 
 * @param void
 * @return array
 */

function DB_lister_jointure_professeurs_coordonnateurs()
{
	$DB_SQL = 'SELECT user_id,matiere_id FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE jointure_coord=:coord AND user_statut=:statut ';
	$DB_VAR = array(':coord'=>1,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_jointure_professeurs_principaux
 * 
 * @param void
 * @return array
 */

function DB_lister_jointure_professeurs_principaux()
{
	$DB_SQL = 'SELECT user_id,groupe_id FROM sacoche_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE jointure_pp=:pp AND user_statut=:statut ';
	$DB_VAR = array(':pp'=>1,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_users
 * 
 * @param string   $profil        'eleve' ou 'professeur' ou 'directeur' ou 'administrateur' ou 'tous'
 * @param bool     $only_actifs   true pour statut actif uniquement / false pour tout le monde qq soit le statut
 * @param bool     $with_classe   true pour récupérer le nom de la classe de l'élève / false sinon
 * @return array
 */

function DB_lister_users($profil,$only_actifs,$with_classe)
{
	$DB_VAR = array();
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	if($with_classe)
	{
		$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
	}
	if($profil!='tous')
	{
		$DB_SQL.= 'WHERE user_profil=:profil ';
		$DB_VAR[':profil'] = $profil;
	}
	if($only_actifs)
	{
		$DB_SQL.= 'AND user_statut=:statut ';
		$DB_VAR[':statut'] = 1;
	}
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_eleves_actifs_avec_groupe
 * 
 * @param int    $prof_id       0 pour les élèves des groupes type "groupe" , l'id du prof pour les élèves des groupes type "besoin" d'un prof
 * @param bool   $only_actifs   true pour statut actif uniquement / false pour tout le monde qq soit le statut
 * @return array
 */

function DB_lister_eleves_avec_groupe($prof_id,$only_actifs)
{
	$groupe_type = ($prof_id) ? 'besoin' : 'groupe' ;
	$DB_VAR = array(':profil'=>'eleve',':type'=>$groupe_type);
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE user_profil=:profil AND groupe_type=:type ';
	if($prof_id)
	{
		$DB_SQL.= 'AND groupe_prof_id=:prof_id ';
		$DB_VAR[':prof_id'] = $prof_id;
	}
	if($only_actifs)
	{
		$DB_SQL.= 'AND user_statut=:statut ';
		$DB_VAR[':statut'] = 1;
	}
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_eleves_actifs_regroupement
 * 
 * @param string $groupe_type   valeur parmi [sdf] [all] [niveau] [classe] [groupe] [besoin] 
 * @param int    $groupe_id     id du niveau ou de la classe ou du groupe
 * @return array
 */

function DB_lister_eleves_actifs_regroupement($groupe_type,$groupe_id)
{
	$DB_SQL = 'SELECT * FROM sacoche_user ';
	switch ($groupe_type)
	{
		case 'sdf' :	// On veut les élèves non affectés dans une classe
			$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:user_statut AND eleve_classe_id=:classe ';
			$DB_VAR = array(':profil'=>'eleve',':user_statut'=>1,':classe'=>0);
			break;
		case 'all' :	// On veut tous les élèves de l'établissement
			$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:user_statut ';
			$DB_VAR = array(':profil'=>'eleve',':user_statut'=>1);
			break;
		case 'niveau' :	// On veut tous les élèves d'un niveau
			$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
			$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:user_statut AND niveau_id=:niveau ';
			$DB_VAR = array(':profil'=>'eleve',':user_statut'=>1,':niveau'=>$groupe_id);
			break;
		case 'classe' :	// On veut tous les élèves d'une classe (on utilise "eleve_classe_id" de "sacoche_user")
			$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:user_statut AND eleve_classe_id=:classe ';
			$DB_VAR = array(':profil'=>'eleve',':user_statut'=>1,':classe'=>$groupe_id);
			break;
		case 'groupe' :	// On veut tous les élèves d'un groupe (on utilise la jointure de "sacoche_jointure_user_groupe")
		case 'besoin' :	// On veut tous les élèves d'un groupe de besoin (on utilise la jointure de "sacoche_jointure_user_groupe")
		case 'eval'   :	// On veut tous les élèves d'un groupe utilisé pour une évaluation (on utilise la jointure de "sacoche_jointure_user_groupe")
			$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
			$DB_SQL.= 'WHERE user_profil=:profil AND user_statut=:user_statut AND groupe_id=:groupe ';
			$DB_VAR = array(':profil'=>'eleve',':user_statut'=>1,':groupe'=>$groupe_id);
			break;
	}
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_demandes_prof
 * 
 * @param int    $matiere_id        id de la matière du prof
 * @param int    $listing_user_id   id des élèves du prof séparés par des virgules
 * @return array
 */

function DB_lister_demandes_prof($matiere_id,$listing_user_id)
{
	$DB_SQL = 'SELECT sacoche_demande.*, ';
	$DB_SQL.= 'CONCAT(niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
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
 * DB_lister_demandes_eleve
 * 
 * @param int    $user_id   id de l'élève
 * @return array
 */

function DB_lister_demandes_eleve($user_id)
{
	$DB_SQL = 'SELECT sacoche_demande.*, ';
	$DB_SQL.= 'CONCAT(niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
	$DB_SQL.= 'item_nom , matiere_nom ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_referentiel_domaine.matiere_id=sacoche_matiere.matiere_id ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_SQL.= 'ORDER BY sacoche_demande.matiere_id ASC, niveau_ref ASC, domaine_ref ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':user_id'=>$user_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_devoirs
 * 
 * @param int    $prof_id
 * @param int    $groupe_id        id du groupe ou de la classe pour un devoir sur une classe ou un groupe ; 0 pour un devoir sur une sélection d'élèves
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @return array
 */

function DB_lister_devoirs($prof_id,$groupe_id,$date_debut_mysql,$date_fin_mysql)
{
	// DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
	// Il faut ajouter dans la requête des "DISTINCT" sinon la liaison avec "sacoche_jointure_user_groupe" duplique tout x le nb d'élèves associés pour une évaluation sur une sélection d'élèves.
	$DB_SQL = 'SELECT *, ';
	$DB_SQL.= 'GROUP_CONCAT(DISTINCT item_id SEPARATOR "_") AS competences_listing, COUNT(DISTINCT item_id) AS competences_nombre ';
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
	$DB_SQL.= 'WHERE prof_id=:prof_id ';
	$DB_SQL.= ($groupe_id) ? 'AND groupe_type!=:type4 AND groupe_id='.$groupe_id.' ' : 'AND groupe_type=:type4 ' ;
	$DB_SQL.= 'AND devoir_date>="'.$date_debut_mysql.'" AND devoir_date<="'.$date_fin_mysql.'" ' ;
	$DB_SQL.= 'GROUP BY devoir_id ';
	$DB_SQL.= 'ORDER BY devoir_date DESC, groupe_nom ASC';
	$DB_VAR = array(':prof_id'=>$prof_id,':type4'=>'eval');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_items_devoir
 * Retourner les items d'une devoir et les infos associées (tableau issu de la requête SQL)
 * 
 * @param int  $devoir_id
 * @return array
 */

function DB_lister_items_devoir($devoir_id)
{
	$DB_SQL = 'SELECT ';
	$DB_SQL.= 'item_id, item_nom, entree_id, ';
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
 * DB_lister_saisies_devoir
 * 
 * @param int  $devoir_id
 * @return array
 */

function DB_lister_saisies_devoir($devoir_id)
{
	// On évite les élèves désactivés pour ces opérations effectuées sur les pages de saisies d'évaluations
	$DB_SQL = 'SELECT eleve_id,item_id,saisie_note FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_saisie.eleve_id=sacoche_user.user_id ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND user_statut=:statut ';
	$DB_VAR = array(':devoir_id'=>$devoir_id,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_structures
 * 
 * @param void
 * @return array
 */

function DB_lister_structures()
{
	$DB_SQL = 'SELECT * FROM sacoche_structure ';
	$DB_SQL.= 'LEFT JOIN sacoche_geo USING (geo_id) ';
	$DB_SQL.= 'ORDER BY geo_ordre ASC, structure_localisation ASC, structure_denomination ASC';
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_lister_contacts_cibles
 * 
 * @param int    $listing_base_id   id des bases séparés par des virgules
 * @return array                    le tableau est de la forme [i] => array('contact_id'=>...,'contact_nom'=>...,'contact_prenom'=>...,'contact_courriel'=>...);
 */

function DB_lister_contacts_cibles($listing_base_id)
{
	$DB_SQL = 'SELECT sacoche_base AS contact_id , structure_contact_nom AS contact_nom , structure_contact_prenom AS contact_prenom , structure_contact_courriel AS contact_courriel FROM sacoche_structure ';
	$DB_SQL.= 'WHERE sacoche_base IN('.$listing_base_id.') ';
	return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_compter_eleves_suivant_statut
 * 
 * @param void
 * @return array   [0]=>nb actifs , [1]=>nb inactifs
 */

function DB_compter_eleves_suivant_statut()
{
	$DB_SQL = 'SELECT user_statut, COUNT(*) AS nombre FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil=:profil ';
	$DB_SQL.= 'GROUP BY user_statut';
	$DB_VAR = array(':profil'=>'eleve');
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$nb_actif   = ( (count($DB_TAB)) && (isset($DB_TAB[1])) ) ? $DB_TAB[1][0]['nombre'] : 0 ;
	$nb_inactif = ( (count($DB_TAB)) && (isset($DB_TAB[0])) ) ? $DB_TAB[0][0]['nombre'] : 0 ;
	return array($nb_actif,$nb_inactif);
}

/**
 * DB_compter_professeurs_directeurs_suivant_statut
 * 
 * @param void
 * @return array   [0]=>nb actifs , [1]=>nb inactifs
 */

function DB_compter_professeurs_directeurs_suivant_statut()
{
	$DB_SQL = 'SELECT user_statut, COUNT(*) AS nombre FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_profil IN(:profil1,:profil2) ';
	$DB_SQL.= 'GROUP BY user_statut';
	$DB_VAR = array(':profil1'=>'professeur',':profil2'=>'directeur');
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$nb_actif   = ( (count($DB_TAB)) && (isset($DB_TAB[1])) ) ? $DB_TAB[1][0]['nombre'] : 0 ;
	$nb_inactif = ( (count($DB_TAB)) && (isset($DB_TAB[0])) ) ? $DB_TAB[0][0]['nombre'] : 0 ;
	return array($nb_actif,$nb_inactif);
}

/**
 * DB_tester_matiere_reference
 * 
 * @param string $matiere_ref
 * @param int    $matiere_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_matiere_reference($matiere_ref,$matiere_id=false)
{
	$DB_SQL = 'SELECT matiere_id FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_ref=:matiere_ref ';
	$DB_VAR = array(':matiere_ref'=>$matiere_ref);
	if($matiere_id)
	{
		$DB_SQL.= 'AND matiere_id!=:matiere_id ';
		$DB_VAR[':matiere_id'] = $matiere_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_classe_reference
 * 
 * @param string $groupe_ref
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_classe_reference($groupe_ref,$groupe_id=false)
{
	$DB_SQL = 'SELECT groupe_id FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_ref=:groupe_ref ';
	$DB_VAR = array(':groupe_ref'=>$groupe_ref);
	if($groupe_id)
	{
		$DB_SQL.= 'AND groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_groupe_nom
 * 
 * @param string $groupe_nom
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_groupe_nom($groupe_nom,$groupe_id=false)
{
	$DB_SQL = 'SELECT groupe_id FROM sacoche_groupe ';
	$DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_nom=:groupe_nom ';
	$DB_VAR = array(':groupe_type'=>'groupe',':groupe_nom'=>$groupe_nom);
	if($groupe_id)
	{
		$DB_SQL.= 'AND groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_periode_nom
 * 
 * @param string $periode_nom
 * @param int    $periode_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_periode_nom($periode_nom,$periode_id=false)
{
	$DB_SQL = 'SELECT periode_id FROM sacoche_periode ';
	$DB_SQL.= 'WHERE periode_nom=:periode_nom ';
	$DB_VAR = array(':periode_nom'=>$periode_nom);
	if($periode_id)
	{
		$DB_SQL.= 'AND periode_id!=:periode_id ';
		$DB_VAR[':periode_id'] = $periode_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_zone_nom
 * 
 * @param string $geo_nom
 * @param int    $geo_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_zone_nom($geo_nom,$geo_id=false)
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
 * DB_tester_utilisateur_idENT (parmi tout le personnel de l'établissement, sauf éventuellement l'utilisateur concerné)
 * 
 * @param string $user_id_ent
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_idENT($user_id_ent,$user_id=false)
{
	$DB_SQL = 'SELECT user_id FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id_ent=:user_id_ent ';
	$DB_VAR = array(':user_id_ent'=>$user_id_ent);
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_utilisateur_idGepi (parmi tout le personnel de l'établissement, sauf éventuellement l'utilisateur concerné)
 * 
 * @param string $user_id_gepi
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_idGepi($user_id_gepi,$user_id=false)
{
	$DB_SQL = 'SELECT user_id FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id_gepi=:user_id_gepi ';
	$DB_VAR = array(':user_id_gepi'=>$user_id_gepi);
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_utilisateur_numSconet (parmi tout le personnel de l'établissement de même profil, sauf éventuellement l'utilisateur concerné)
 * 
 * @param int    $user_num_sconet
 * @param string $user_profil
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_numSconet($user_num_sconet,$user_profil,$user_id=false)
{
	$DB_SQL = 'SELECT user_id FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_num_sconet=:user_num_sconet AND user_profil=:user_profil ';
	$DB_VAR = array(':user_num_sconet'=>$user_num_sconet,':user_profil'=>$user_profil);
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_utilisateur_reference (parmi tout le personnel de l'établissement de même profil, sauf éventuellement l'utilisateur concerné)
 * 
 * @param string $user_reference
 * @param string $user_profil
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_reference($user_reference,$user_profil,$user_id=false)
{
	$DB_SQL = 'SELECT user_id FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_reference=:user_reference AND user_profil=:user_profil ';
	$DB_VAR = array(':user_reference'=>$user_reference,':user_profil'=>$user_profil);
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_tester_structure_UAI
 * 
 * @param string $structure_uai
 * @param int    $base_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_structure_UAI($structure_uai,$base_id=false)
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
 * DB_tester_login (parmi tout le personnel de l'établissement)
 * 
 * @param string $user_login
 * @param int    $user_id     inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_login($user_login,$user_id=false)
{
	$DB_SQL = 'SELECT user_id FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_login=:user_login ';
	$DB_VAR = array(':user_login'=>$user_login);
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * DB_rechercher_login_disponible (parmi tout le personnel de l'établissement)
 * 
 * @param string $login
 * @return string
 */

function DB_rechercher_login_disponible($login)
{
	$nb_chiffres = 20-mb_strlen($login);
	$max_result = 0;
	do
	{
		$login = mb_substr($login,0,20-$nb_chiffres);
		$DB_SQL = 'SELECT user_login FROM sacoche_user ';
		$DB_SQL.= 'WHERE user_login LIKE :user_login';
		$DB_VAR = array(':user_login'=>$login.'%');
		$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , 'user_login');
		$max_result += pow(10,$nb_chiffres);
	}
	while (count($DB_TAB)>=$max_result);
	$j=0;
	do
	{
		$j++;
	}
	while (array_key_exists($login.$j,$DB_TAB));
	return $login.$j ;
}

/**
 * DB_ajouter_matiere_specifique
 * 
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return int
 */

function DB_ajouter_matiere_specifique($matiere_ref,$matiere_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_matiere(matiere_partage,matiere_transversal,matiere_ref,matiere_nom) ';
	$DB_SQL.= 'VALUES(:matiere_partage,:matiere_transversal,:matiere_ref,:matiere_nom)';
	$DB_VAR = array(':matiere_partage'=>0,':matiere_transversal'=>0,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * DB_ajouter_groupe
 * 
 * @param string $groupe_type      'classe' ou 'groupe' ou 'besoin' ou 'eval'
 * @param int    $groupe_prof_id   id du prof dans le cas d'un groupe de besoin ou pour une évaluation (0 sinon)
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return int
 */

function DB_ajouter_groupe($groupe_type,$groupe_prof_id,$groupe_ref,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'INSERT INTO sacoche_groupe(groupe_type,groupe_prof_id,groupe_ref,groupe_nom,niveau_id) ';
	$DB_SQL.= 'VALUES(:groupe_type,:groupe_prof_id,:groupe_ref,:groupe_nom,:niveau_id)';
	$DB_VAR = array(':groupe_type'=>$groupe_type,':groupe_prof_id'=>$groupe_prof_id,':groupe_ref'=>$groupe_ref,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * DB_ajouter_periode
 * 
 * @param int    $periode_ordre
 * @param string $periode_nom
 * @return int
 */

function DB_ajouter_periode($periode_ordre,$periode_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_periode(periode_ordre,periode_nom) ';
	$DB_SQL.= 'VALUES(:periode_ordre,:periode_nom)';
	$DB_VAR = array(':periode_ordre'=>$periode_ordre,':periode_nom'=>$periode_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * DB_ajouter_zone
 * 
 * @param int    $geo_ordre
 * @param string $geo_nom
 * @return int
 */

function DB_ajouter_zone($geo_ordre,$geo_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_geo(geo_ordre,geo_nom) ';
	$DB_SQL.= 'VALUES(:geo_ordre,:geo_nom)';
	$DB_VAR = array(':geo_ordre'=>$geo_ordre,':geo_nom'=>$geo_nom);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
}

/**
 * DB_ajouter_structure
 * 
 * @param int    $geo_id
 * @param string $structure_uai
 * @param string $localisation
 * @param string $denomination
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @return int
 */

function DB_ajouter_structure($geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel)
{
	// Insérer l'enregistrement dans la base du webmestre
	$DB_SQL = 'INSERT INTO sacoche_structure(geo_id,structure_uai,structure_localisation,structure_denomination,structure_contact_nom,structure_contact_prenom,structure_contact_courriel) ';
	$DB_SQL.= 'VALUES(:geo_id,:structure_uai,:localisation,:denomination,:contact_nom,:contact_prenom,:contact_courriel)';
	$DB_VAR = array(':geo_id'=>$geo_id,':structure_uai'=>$structure_uai,':localisation'=>$localisation,':denomination'=>$denomination,':contact_nom'=>$contact_nom,':contact_prenom'=>$contact_prenom,':contact_courriel'=>$contact_courriel);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	$base_id = DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
	// Génération des paramètres de connexion à la base de données
	$BD_name = 'sacoche_structure_'.$base_id;
	$BD_user = 'sql_user_'.$base_id; // Limité à 16 caractères
	$BD_pass = fabriquer_mdp();
	// Créer le fichier de connexion de la base de données de la structure
	fabriquer_fichier_connexion_base($base_id,SACOCHE_WEBMESTRE_BD_HOST,$BD_name,$BD_user,$BD_pass);
	// Créer la base de données de la structure
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE DATABASE sacoche_structure_'.$base_id );
	// Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE USER '.$BD_user.' IDENTIFIED BY "'.$BD_pass.'"' );
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.$BD_name.'.* TO '.$BD_user );
	/* Il reste à :
		+ Lancer les requêtes pour installer et remplir les tables, éventuellement personnaliser certains paramètres de la structure
		+ Insérer le compte administrateur dans la base de cette structure, éventuellement lui envoyer un courriel
	*/
	return $base_id;
}

/**
 * DB_ajouter_utilisateur
 * 
 * @param string $user_num_sconet
 * @param string $user_reference
 * @param string $user_profil
 * @param string $user_nom
 * @param string $user_prenom
 * @param string $user_login
 * @param string $user_password
 * @param int    $eleve_classe_id   facultatif, 0 si pas de classe ou profil non élève
 * @param string $user_id_ent       facultatif
 * @param string $user_id_gepi      facultatif
 * @return int
 */

function DB_ajouter_utilisateur($user_num_sconet,$user_reference,$user_profil,$user_nom,$user_prenom,$user_login,$user_password,$eleve_classe_id=0,$user_id_ent='',$user_id_gepi='')
{
	$password_crypte = crypter_mdp($user_password);
	$DB_SQL = 'INSERT INTO sacoche_user(user_num_sconet,user_reference,user_profil,user_nom,user_prenom,user_login,user_password,eleve_classe_id,user_id_ent,user_id_gepi) ';
	$DB_SQL.= 'VALUES(:user_num_sconet,:user_reference,:user_profil,:user_nom,:user_prenom,:user_login,:password_crypte,:eleve_classe_id,:user_id_ent,:user_id_gepi)';
	$DB_VAR = array(':user_num_sconet'=>$user_num_sconet,':user_reference'=>$user_reference,':user_profil'=>$user_profil,':user_nom'=>$user_nom,':user_prenom'=>$user_prenom,':user_login'=>$user_login,':password_crypte'=>$password_crypte,':eleve_classe_id'=>$eleve_classe_id,':user_id_ent'=>$user_id_ent,':user_id_gepi'=>$user_id_gepi);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	$user_id = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
	// Pour un professeur, l'affecter obligatoirement à la matière transversale
	if($user_profil=='professeur')
	{
		$DB_SQL = 'INSERT INTO sacoche_jointure_user_matiere (user_id ,matiere_id,jointure_coord) ';
		$DB_SQL.= 'VALUES(:user_id,:matiere_id,:jointure_coord)';
		$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>ID_MATIERE_TRANSVERSALE,':jointure_coord'=>0);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	return $user_id;
}

/**
 * DB_ajouter_devoir
 * 
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param string $date_mysql
 * @param string $info
 * @return int
 */

function DB_ajouter_devoir($prof_id,$groupe_id,$date_mysql,$info)
{
	$DB_SQL = 'INSERT INTO sacoche_devoir(prof_id,groupe_id,devoir_date,devoir_info) ';
	$DB_SQL.= 'VALUES(:prof_id,:groupe_id,:date,:info)';
	$DB_VAR = array(':prof_id'=>$prof_id,':groupe_id'=>$groupe_id,':date'=>$date_mysql,':info'=>$info);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * DB_ajouter_saisie
 * 
 * @param int    $prof_id
 * @param int    $eleve_id
 * @param int    $devoir_id
 * @param int    $competence_id
 * @param string $competence_date_mysql
 * @param string $competence_note
 * @param string $competence_info
 * @return void
 */

function DB_ajouter_saisie($prof_id,$eleve_id,$devoir_id,$competence_id,$competence_date_mysql,$competence_note,$competence_info)
{
	$DB_SQL = 'INSERT INTO sacoche_saisie ';
	$DB_SQL.= 'VALUES(:prof_id,:eleve_id,:devoir_id,:competence_id,:competence_date,:competence_note,:competence_info)';
	$DB_VAR = array(':prof_id'=>$prof_id,':eleve_id'=>$eleve_id,':devoir_id'=>$devoir_id,':competence_id'=>$competence_id,':competence_date'=>$competence_date_mysql,':competence_note'=>$competence_note,':competence_info'=>$competence_info);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_parametres
 * 
 * @param array tableau $parametre_nom => $parametre_valeur des paramètres à modfifier
 * @return void
 */

function DB_modifier_parametres($tab_parametres)
{
	/*
		modifier_matieres_partagees
			On ne défait pas pour autant les liaisons avec les enseignants... simplement elles n'apparaitront plus dans les formulaires.
			Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
		modifier_niveaux
			On ne défait pas pour autant les liaisons avec les groupes... simplement ils n'apparaitront plus dans les formulaires.
			Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
		modifier_paliers
			On ne défait pas pour autant les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
	*/
	$DB_SQL = 'UPDATE sacoche_parametre ';
	$DB_SQL.= 'SET parametre_valeur=:parametre_valeur ';
	$DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
	$DB_SQL.= 'LIMIT 1';
	foreach($tab_parametres as $parametre_nom => $parametre_valeur)
	{
		$DB_VAR = array(':parametre_nom'=>$parametre_nom,':parametre_valeur'=>$parametre_valeur);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * DB_recopier_identifiants (exemples : id_gepi=id_ent id_gepi=login id_ent=id_gepi id_ent=login 
 * 
 * @param string $champ_depart
 * @param string $champ_arrive
 * @return void
 */

function DB_recopier_identifiants($champ_depart,$champ_arrive)
{
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET sacoche_user_'.$champ_arrive.'=sacoche_user_'.$champ_depart.' ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_modifier_utilisateur (on ne touche pas à 'user_profil')
 * 
 * @param int     $user_id
 * @param array   array(':num_sconet'=>$val, ':reference'=>$val , ':nom'=>$val , ':prenom'=>$val , ':login'=>$val , ':password'=>$val , ':statut'=>$val , ':classe'=>$val , ':id_ent'=>$val , ':id_gepi'=>$val );
 * @return void
 */

function DB_modifier_utilisateur($user_id,$DB_VAR)
{
	$tab_set = array();
	foreach($DB_VAR as $key => $val)
	{
		switch($key)
		{
			case ':num_sconet' : $tab_set[] = 'user_num_sconet='.$key; break;
			case ':reference' :  $tab_set[] = 'user_reference='.$key;  break;
			case ':nom' :        $tab_set[] = 'user_nom='.$key;        break;
			case ':prenom' :     $tab_set[] = 'user_prenom='.$key;     break;
			case ':login' :      $tab_set[] = 'user_login='.$key;      break;
			case ':password' :   $tab_set[] = 'user_password=:password_crypte'; $DB_VAR[':password_crypte'] = crypter_mdp($DB_VAR[':password']); unset($DB_VAR[':password']); break;
			case ':statut' :     $tab_set[] = 'user_statut='.$key;     break;
			case ':classe' :     $tab_set[] = 'eleve_classe_id='.$key; break;
			case ':id_ent' :     $tab_set[] = 'user_id_ent='.$key;     break;
			case ':id_gepi' :    $tab_set[] = 'user_id_gepi='.$key;    break;
		}
	}
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET '.implode(',',$tab_set).' ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR[':user_id'] = $user_id;
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_structure
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

function DB_modifier_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel)
{
	$DB_SQL = 'UPDATE sacoche_structure ';
	$DB_SQL.= 'SET geo_id=:geo_id,structure_uai=:structure_uai,structure_localisation=:localisation,structure_denomination=:denomination,structure_contact_nom=:contact_nom,structure_contact_prenom=:contact_prenom,structure_contact_courriel=:contact_courriel ';
	$DB_SQL.= 'WHERE sacoche_base=:base_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':base_id'=>$base_id,':geo_id'=>$geo_id,':structure_uai'=>$structure_uai,':localisation'=>$localisation,':denomination'=>$denomination,':contact_nom'=>$contact_nom,':contact_prenom'=>$contact_prenom,':contact_courriel'=>$contact_courriel);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_mdp_utilisateur
 * Remarque : cette fonction n'est pas appelée pour un professeur ou un élève si le mode de connexion est SSO
 * 
 * @param int    $user_id
 * @param string $password_ancien
 * @param string $password_nouveau
 * @return string   'ok' ou 'Le mot de passe actuel est incorrect !'
 */

function DB_modifier_mdp_utilisateur($user_id,$password_ancien,$password_nouveau)
{
	// Tester si l'ancien mot de passe correspond à celui enregistré
	$password_ancien_crypte = crypter_mdp($password_ancien);
	$DB_SQL = 'SELECT user_id FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id=:user_id AND user_password=:password_crypte ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':user_id'=>$user_id,':password_crypte'=>$password_ancien_crypte);
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_ROW))
	{
		return 'Le mot de passe actuel est incorrect !';
	}
	// Remplacer par le nouveau mot de passe
	$password_nouveau_crypte = crypter_mdp($password_nouveau);
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET user_password=:password_crypte ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':user_id'=>$user_id,':password_crypte'=>$password_nouveau_crypte);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return 'ok';
}

/**
 * DB_modifier_mdp_webmestre
 * 
 * @param string $password_ancien
 * @param string $password_nouveau
 * @return string   'ok' ou 'Le mot de passe actuel est incorrect !'
 */

function DB_modifier_mdp_webmestre($password_ancien,$password_nouveau)
{
	// Tester si l'ancien mot de passe correspond à celui enregistré
	$password_ancien_crypte = crypter_mdp($password_ancien);
	if($password_ancien_crypte!=WEBMESTRE_PASSWORD_MD5)
	{
		return 'Le mot de passe actuel est incorrect !';
	}
	// Remplacer par le nouveau mot de passe
	$password_nouveau_crypte = crypter_mdp($password_nouveau);
	fabriquer_fichier_hebergeur_info(HEBERGEUR_INSTALLATION,HEBERGEUR_DENOMINATION,HEBERGEUR_LOGO,HEBERGEUR_CNIL,WEBMESTRE_NOM,WEBMESTRE_PRENOM,WEBMESTRE_COURRIEL,$password_nouveau_crypte);
	return 'ok';
}

/**
 * DB_modifier_matiere_specifique
 * 
 * @param int    $matiere_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return void
 */

function DB_modifier_matiere_specifique($matiere_id,$matiere_ref,$matiere_nom)
{
	$DB_SQL = 'UPDATE sacoche_matiere ';
	$DB_SQL.= 'SET matiere_ref=:matiere_ref,matiere_nom=:matiere_nom ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_groupe ; on ne touche pas à 'groupe_type' ni à 'groupe_prof_id'
 * 
 * @param int    $groupe_id
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return void
 */

function DB_modifier_groupe($groupe_id,$groupe_ref,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'UPDATE sacoche_groupe ';
	$DB_SQL.= 'SET groupe_ref=:groupe_ref,groupe_nom=:groupe_nom,niveau_id=:niveau_id ';
	$DB_SQL.= 'WHERE groupe_id=:groupe_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':groupe_id'=>$groupe_id,':groupe_ref'=>$groupe_ref,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_ordre_item
 * 
 * @param int    $devoir_id
 * @param array  $tab_items   tableau des id des competences
 * @return void
 */

function DB_modifier_ordre_item($devoir_id,$tab_items)
{
	$DB_SQL = 'UPDATE sacoche_jointure_devoir_item SET jointure_ordre=:ordre ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id=:competence_id ';
	$DB_SQL.= 'LIMIT 1';
	$ordre = 1;
	foreach($tab_items as $competence_id)
	{
		$DB_VAR = array(':devoir_id'=>$devoir_id,':competence_id'=>$competence_id,':ordre'=>$ordre);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$ordre++;
	}
}

/**
 * DB_modifier_saisie
 * 
 * @param int    $eleve_id
 * @param int    $devoir_id
 * @param int    $competence_id
 * @param string $competence_note
 * @return void
 */

function DB_modifier_saisie($eleve_id,$devoir_id,$competence_id,$competence_note)
{
	$DB_SQL = 'UPDATE sacoche_saisie SET saisie_note=:competence_note ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:competence_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':devoir_id'=>$devoir_id,':competence_id'=>$competence_id,':competence_note'=>$competence_note);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_devoir
 * 
 * @param int    $devoir_id
 * @param int    $prof_id
 * @param string $date_mysql
 * @param string $info
 * @param array  $tab_items   tableau des id des competences
 * @return void
 */

function DB_modifier_devoir($devoir_id,$prof_id,$date_mysql,$info,$tab_items)
{
	// sacoche_devoir (maj)
	$DB_SQL = 'UPDATE sacoche_devoir ';
	$DB_SQL.= 'SET devoir_date=:date,devoir_info=:info ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND prof_id=:prof_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':date'=>$date_mysql,':info'=>$info,':devoir_id'=>$devoir_id,':prof_id'=>$prof_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// sacoche_saisie (retirer superflu)
	$chaine_id = implode(',',$tab_items);
	$DB_SQL = 'DELETE FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE prof_id=:prof_id AND devoir_id=:devoir_id AND item_id NOT IN('.$chaine_id.')';
	$DB_VAR = array(':prof_id'=>$prof_id,':devoir_id'=>$devoir_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// sacoche_saisie (maj)
	$DB_SQL = 'UPDATE sacoche_saisie ';
	$DB_SQL.= 'SET saisie_date=:date,saisie_info=:info ';
	$DB_SQL.= 'WHERE prof_id=:prof_id AND devoir_id=:devoir_id ';
	$DB_VAR = array(':prof_id'=>$prof_id,':devoir_id'=>$devoir_id,':date'=>$date_mysql,':info'=>$info);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_user_groupe
 * 
 * @param int    $user_id
 * @param string $user_profil   'eleve' ou 'professeur'
 * @param int    $groupe_id
 * @param string $groupe_type   'classe' ou 'groupe' ou 'besoin' MAIS PAS 'eval', géré par DB_modifier_liaison_devoir_user()
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_user_groupe($user_id,$user_profil,$groupe_id,$groupe_type,$etat)
{
	// Dans le cas d'un élève et d'une classe, ce n'est pas dans la table de jointure mais dans la table user que ça se passe
	if( ($user_profil=='eleve') && ($groupe_type=='classe') )
	{
		if(!$etat)
		{
			$groupe_id = 0; // normalement c'est déjà transmis à 0 mais bon...
		}
		$DB_SQL = 'UPDATE sacoche_user ';
		$DB_SQL.= 'SET eleve_classe_id=:groupe_id ';
		$DB_SQL.= 'WHERE user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
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
			$DB_SQL.= 'LIMIT 1';
		}
	}
	$DB_VAR = array(':user_id'=>$user_id,':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_professeur_coordonnateur
 * 
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_professeur_coordonnateur($user_id,$matiere_id,$etat)
{
	$coord = ($etat) ? 1 : 0 ;
	$DB_SQL = 'UPDATE sacoche_jointure_user_matiere SET jointure_coord=:coord ';
	$DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>$matiere_id,':coord'=>$coord);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_professeur_principal
 * 
 * @param int    $user_id
 * @param int    $groupe_id
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_professeur_principal($user_id,$groupe_id,$etat)
{
	$pp = ($etat) ? 1 : 0 ;
	$DB_SQL = 'UPDATE sacoche_jointure_user_groupe SET jointure_pp=:pp ';
	$DB_SQL.= 'WHERE user_id=:user_id AND groupe_id=:groupe_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':user_id'=>$user_id,':groupe_id'=>$groupe_id,':pp'=>$pp);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_professeur_matiere
 * 
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_professeur_matiere($user_id,$matiere_id,$etat)
{
	if($etat)
	{
		// On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd la valeur de jointure_coord.
		$DB_SQL = 'SELECT sacoche_structure_id FROM sacoche_jointure_user_matiere ';
		$DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
		$DB_SQL.= 'LIMIT 1';
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
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':user_id'=>$user_id,':matiere_id'=>$matiere_id);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * DB_modifier_liaison_devoir_competence
 * 
 * @param int    $devoir_id
 * @param array  $tab_items   tableau des id des competences
 * @param string $mode        'creer' ou 'dupliquer' pour un insert dans un nouveau devoir || 'substituer' pour une maj delete / insert || 'ajouter' pour maj insert uniquement
 * @param int    $devoir_ordonne_id   Dans le cas d'une duplication, id du devoir dont il faut récupérer l'ordre des items.
 * @return void
 */

function DB_modifier_liaison_devoir_competence($devoir_id,$tab_items,$mode,$devoir_ordonne_id=0)
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
		$DB_SQL.= 'VALUES(:devoir_id,:competence_id,:ordre)';
		foreach($tab_items as $competence_id)
		{
			$ordre = (isset($tab_ordre[$competence_id])) ? $tab_ordre[$competence_id] : 0 ;
			$DB_VAR = array(':devoir_id'=>$devoir_id,':competence_id'=>$competence_id,':ordre'=>$ordre);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	else
	{
		// On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd l'info sur l'ordre des items.
		// Alors on récupère la liste des items déjà présents, et on étudie les différences pour faire des DELETE et INSERT sélectifs
		// -> on récupère les items actuels
		$tab_old_items = array();
		$DB_SQL = 'SELECT item_id FROM sacoche_jointure_devoir_item ';
		$DB_SQL.= 'WHERE devoir_id=:devoir_id ';
		$DB_VAR = array(':devoir_id'=>$devoir_id);
		$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_old_items[] = $DB_ROW['item_id'];
		}
		// -> on supprime les anciens items non nouvellement sélectionnées
		if($mode!='ajouter')
		{
			$tab_items_supprimer = array_diff($tab_old_items,$tab_items);
			if(count($tab_items_supprimer))
			{
				$chaine_supprimer_id = implode(',',$tab_items_supprimer);
				$DB_SQL = 'DELETE FROM sacoche_jointure_devoir_item ';
				$DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id IN('.$chaine_supprimer_id.')';
				$DB_VAR = array(':devoir_id'=>$devoir_id);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
		// -> on ajoute les nouveaux items non anciennement présents
		$tab_items_ajouter = array_diff($tab_items,$tab_old_items);
		if(count($tab_items_ajouter))
		{
			foreach($tab_items_ajouter as $competence_id)
			{
				$DB_SQL = 'INSERT INTO sacoche_jointure_devoir_item(devoir_id,item_id) ';
				$DB_SQL.= 'VALUES(:devoir_id,:competence_id)';
				$DB_VAR = array(':devoir_id'=>$devoir_id,':competence_id'=>$competence_id);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
	}
}

/**
 * DB_modifier_liaison_devoir_user
 * Uniquement pour des évaluations de type 'eval' ; pour les autres, c'est géré par DB_modifier_liaison_user_groupe()
 * 
 * @param int    $groupe_id
 * @param array  $tab_eleves   tableau des id des élèves
 * @param string $mode         'creer' pour un insert dans un nouveau devoir || 'substituer' pour une maj delete / insert || 'ajouter' pour maj insert uniquement
 * @return void
 */

function DB_modifier_liaison_devoir_user($groupe_id,$tab_eleves,$mode)
{
	// -> on récupère la liste des élèves actuels déjà associés au groupe (pour la comparer à la liste transmise)
	if($mode!='creer')
	{
		// DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
		$DB_SQL = 'SELECT GROUP_CONCAT(user_id SEPARATOR " ") AS users_listing FROM sacoche_jointure_user_groupe ';
		$DB_SQL.= 'WHERE groupe_id=:groupe_id ';
		$DB_SQL.= 'GROUP BY groupe_id';
		$DB_VAR = array(':groupe_id'=>$groupe_id);
		$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$tab_eleves_avant = (count($DB_TAB)) ? explode(' ',$DB_TAB[0]['users_listing']) : array() ;
	}
	else
	{
		$tab_eleves_avant = array() ;
	}
	// -> on supprime si besoin les anciens élèves associés à ce groupe qui ne le sont plus dans la liste transmise
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
 * DB_modifier_liaison_groupe_periode
 * 
 * @param int    $groupe_id
 * @param int    $periode_id
 * @param bool   $etat               'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @param string $date_debut_mysql   date de début au format mysql (facultatif : obligatoire uniquement si $etat=true)
 * @param string $date_fin_mysql     date de fin au format mysql (facultatif : obligatoire uniquement si $etat=true)
 * @return void
 */

function DB_modifier_liaison_groupe_periode($groupe_id,$periode_id,$etat,$date_debut_mysql='',$date_fin_mysql='')
{
	if($etat)
	{
		$DB_SQL = 'REPLACE INTO sacoche_jointure_groupe_periode (groupe_id,periode_id,jointure_date_debut,jointure_date_fin) ';
		$DB_SQL.= 'VALUES(:groupe_id,:periode_id,:date_debut,:date_fin)';
		$DB_VAR = array(':groupe_id'=>$groupe_id,':periode_id'=>$periode_id,':date_debut'=>$date_debut_mysql,':date_fin'=>$date_fin_mysql);
	}
	else
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
		$DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':groupe_id'=>$groupe_id,':periode_id'=>$periode_id);
	}
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_periode
 * 
 * @param int    $periode_id
 * @param int    $periode_ordre
 * @param string $periode_nom
 * @return void
 */

function DB_modifier_periode($periode_id,$periode_ordre,$periode_nom)
{
	$DB_SQL = 'UPDATE sacoche_periode ';
	$DB_SQL.= 'SET periode_ordre=:periode_ordre,periode_nom=:periode_nom ';
	$DB_SQL.= 'WHERE periode_id=:periode_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':periode_id'=>$periode_id,':periode_ordre'=>$periode_ordre,':periode_nom'=>$periode_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_zone
 * 
 * @param int    $geo_id
 * @param int    $geo_ordre
 * @param string $geo_nom
 * @return void
 */

function DB_modifier_zone($geo_id,$geo_ordre,$geo_nom)
{
	$DB_SQL = 'UPDATE sacoche_geo ';
	$DB_SQL.= 'SET geo_ordre=:geo_ordre,geo_nom=:geo_nom ';
	$DB_SQL.= 'WHERE geo_id=:geo_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':geo_id'=>$geo_id,':geo_ordre'=>$geo_ordre,':geo_nom'=>$geo_nom);
	DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_statut_demandes
 * 
 * @param string $listing_demande_id   id des demandes séparées par des virgules
 * @param int    $nb_demandes          nb de demandes
 * @param string $statut               parmi 'prof' ou ...
 * @return void
 */

function DB_modifier_statut_demandes($listing_demande_id,$nb_demandes,$statut)
{
	$DB_SQL = 'UPDATE sacoche_demande SET demande_statut=:demande_statut ';
	$DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') ';
	$DB_SQL.= 'LIMIT '.$nb_demandes;
	$DB_VAR = array(':demande_statut'=>$statut);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_matiere_specifique
 * 
 * @param int $matiere_id
 * @return void
 */

function DB_supprimer_matiere_specifique($matiere_id)
{
	$DB_SQL = 'DELETE FROM sacoche_matiere ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les enseignants
	$DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les référentiels associés, et donc tous les scores associés (orphelins de la matière)
	DB_supprimer_referentiel_matiere_niveau($matiere_id);
}

/**
 * DB_supprimer_groupe
 * Par défaut, on supprime aussi les devoirs associés ($with_devoir=true), mais on conserve les notes, sui deviennent orphelines et non éditables ultérieurement.
 * Mais on peut aussi vouloir dans un second temps ($with_devoir=false) supprimer les devoirs associés avec leurs notes en utilisant DB_supprimer_devoir_et_saisies().
 * 
 * @param int    $groupe_id
 * @param string $groupe_type   valeur parmi 'classe' ; 'groupe' ; 'besoin' ; 'eval'
 * @param bool   $with_devoir
 * @return void
 */

function DB_supprimer_groupe($groupe_id,$groupe_type,$with_devoir=true)
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
 * DB_supprimer_devoir_et_saisies
 * 
 * @param int   $devoir_id
 * @param int   $prof_id   Seul un prof peut se supprimer une évaluation avec ses scores ; son id sert de sécurité.
 * @return void
 */

function DB_supprimer_devoir_et_saisies($devoir_id,$prof_id)
{
	// Il faut aussi supprimer les jointures du devoir avec les items
	$DB_SQL = 'DELETE FROM sacoche_devoir, sacoche_jointure_devoir_item, sacoche_saisie ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (devoir_id,prof_id) ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND prof_id=:prof_id ';
	$DB_VAR = array(':devoir_id'=>$devoir_id,':prof_id'=>$prof_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_saisie
 * 
 * @param int   $eleve_id
 * @param int   $devoir_id
 * @param int   $competence_id
 * @return void
 */

function DB_supprimer_saisie($eleve_id,$devoir_id,$competence_id)
{
	// Il faut aussi supprimer les jointures du devoir avec les items
	$DB_SQL = 'DELETE FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:competence_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':devoir_id'=>$devoir_id,':competence_id'=>$competence_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_periode
 * 
 * @param int $periode_id
 * @return void
 */

function DB_supprimer_periode($periode_id)
{
	$DB_SQL = 'DELETE FROM sacoche_periode ';
	$DB_SQL.= 'WHERE periode_id=:periode_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':periode_id'=>$periode_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les classes
	$DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'WHERE periode_id=:periode_id ';
	$DB_VAR = array(':periode_id'=>$periode_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_zone
 * 
 * @param int $geo_id
 * @return void
 */

function DB_supprimer_zone($geo_id)
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
}

/**
 * DB_supprimer_demande
 * On transmet soit l'id de la demande (1 paramètre) soit l'id de l'élève suivi de l'id de la compétence (2 paramètres).
 * 
 * @param int      $id1
 * @param bool|int $id2
 * @return void
 */

function DB_supprimer_demande($id1,$id2=false)
{
	$DB_SQL = 'DELETE FROM sacoche_demande ';
	if($id2)
	{
		$DB_SQL.= 'WHERE user_id=:eleve_id AND item_id=:competence_id ';
		$DB_VAR = array(':eleve_id'=>$id1,':competence_id'=>$id2);
	}
	else
	{
		$DB_SQL.= 'WHERE demande_id=:demande_id ';
		$DB_VAR = array(':demande_id'=>$id1);
	}
	$DB_SQL.= 'LIMIT 1';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_demandes
 * 
 * @param string $listing_demande_id   id des demandes séparées par des virgules
 * @param int    $nb_demandes          nb de demandes
 * @return void
 */

function DB_supprimer_demandes($listing_demande_id,$nb_demandes)
{
	$DB_SQL = 'DELETE FROM sacoche_demande ';
	$DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') ';
	$DB_SQL.= 'LIMIT '.$nb_demandes;
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
}

/**
 * DB_supprimer_utilisateur
 * 
 * @param int    $user_id
 * @param string $user_profil   'eleve' ou 'professeur' ou 'directeur' ou 'administrateur'
 * @return void
 */

function DB_supprimer_utilisateur($user_id,$user_profil)
{
	$DB_VAR = array(':user_id'=>$user_id);
	$DB_SQL = 'DELETE FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_SQL.= 'LIMIT 1';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if( ($user_profil=='eleve') || ($user_profil=='professeur') )
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
		$DB_SQL.= 'WHERE user_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	if($user_profil=='eleve')
	{
		$DB_SQL = 'DELETE FROM sacoche_saisie ';
		$DB_SQL.= 'WHERE eleve_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM sacoche_demande ';
		$DB_SQL.= 'WHERE user_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	if($user_profil=='professeur')
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
		$DB_SQL.= 'WHERE user_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE sacoche_jointure_devoir_item FROM sacoche_jointure_devoir_item ';
		$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
		$DB_SQL.= 'WHERE prof_id=:user_id';
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE sacoche_groupe FROM sacoche_groupe ';
		$DB_SQL.= 'LEFT JOIN sacoche_devoir ON sacoche_groupe.groupe_prof_id=sacoche_devoir.prof_id ';
		$DB_SQL.= 'WHERE groupe_prof_id=:user_id';
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
 * DB_supprimer_referentiel_matiere_niveau
 * 
 * @param int $matiere_id
 * @param int $niveau_id    facultatif : si non fourni, tous les niveaux seront concernés
 * @return void
 */

function DB_supprimer_referentiel_matiere_niveau($matiere_id,$niveau_id=false)
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
	if($niveau_id)
	{
		$DB_SQL.= 'AND niveau_id=:niveau_id ';
		$DB_VAR[':niveau_id'] = $niveau_id;
	}
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_structure
 * 
 * @param int    $BASE 
 * @return void
 */

function DB_supprimer_structure($BASE)
{
	// Dans le cas d'une installation de type multi-structures...
	if(HEBERGEUR_INSTALLATION=='multi-structures')
	{
		// Paramètres de connexion à la base de données
		$BD_name = 'sacoche_structure_'.$BASE;
		$BD_user = 'sql_user_'.$BASE; // Limité à 16 caractères
		// Supprimer la base associée à la structure
		DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP DATABASE '.$BD_name );
		// Retirer les droits et supprimer l'utilisateur pour la base de données de la structure
		DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'REVOKE ALL PRIVILEGES ON '.$BD_name.'.* FROM '.$BD_user );
		DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP USER '.$BD_user );
		// Supprimer le fichier de connexion
		unlink('./__mysql_config/serveur_sacoche_structure_'.$BASE.'.php');
		// Supprimer la structure dans la base du webmestre
		$DB_SQL = 'DELETE FROM sacoche_structure ';
		$DB_SQL.= 'WHERE sacoche_base=:base ';
		$DB_VAR = array(':base'=>$BASE);
		DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Dans le cas d'une installation de type mono-structure...
	else
	{
		// Supprimer les tables de la base (pas la base elle-même au cas où elle serait partagée avec autre chose)
		$tab_tables = array();
		$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"');
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_tables[] = $DB_ROW['Name'];
		}
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE '.implode(', ',$tab_tables) );
		// Supprimer le fichier de connexion
		unlink('./__mysql_config/serveur_sacoche_structure.php');
	}
}

/**
 * DB_creer_remplir_tables_structure
 * 
 * @param void
 * @return void
 */

function DB_creer_remplir_tables_structure()
{
	$dossier_requetes = './_sql/structure/';
	$tab_files = scandir($dossier_requetes);
	foreach($tab_files as $file)
	{
		$extension = pathinfo($file,PATHINFO_EXTENSION);
		if($extension=='sql')
		{
			$requetes = file_get_contents($dossier_requetes.$file);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
			/*
			La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
			La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
			*/
			DB::close(SACOCHE_STRUCTURE_BD_NAME);
		}
	}
}

/**
 * DB_creer_remplir_tables_webmestre
 * 
 * @param void
 * @return void
 */

function DB_creer_remplir_tables_webmestre()
{
	$dossier_requetes = './_sql/webmestre/';
	$tab_files = scandir($dossier_requetes);
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

?>