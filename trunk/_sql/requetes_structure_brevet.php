<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 *
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 *
 * Ce fichier est une partie de SACoche.
 *
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 *
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 *
 */
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ciblent essentiellement les tables "sacoche_brevet_serie" ; "sacoche_brevet_epreuve" ; "sacoche_brevet_saisie".

class DB_STRUCTURE_BREVET extends DB
{

/**
 * Retourner un tableau [valeur texte] des séries du brevet
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_brevet_series()
{
  $DB_SQL = 'SELECT brevet_serie_ref AS valeur, brevet_serie_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_brevet_serie ';
  $DB_SQL.= 'ORDER BY brevet_serie_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_brevet_series_etablissement
 *
 * @param void
 * @return array
 */
public static function DB_lister_brevet_series_etablissement()
{
  $DB_SQL = 'SELECT brevet_serie_ref, brevet_serie_nom, COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_serie ON sacoche_user.eleve_brevet_serie=sacoche_brevet_serie.brevet_serie_ref ';
  $DB_SQL.= 'WHERE eleve_brevet_serie!="X" AND user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY brevet_serie_ref ';
  $DB_SQL.= 'ORDER BY brevet_serie_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_brevet_series_etablissement
 *
 * @param void
 * @return array
 */
public static function DB_lister_brevet_series_etablissement_non_configurees()
{
  $DB_SQL = 'SELECT brevet_serie_ref, brevet_serie_nom ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_serie ON sacoche_user.eleve_brevet_serie=sacoche_brevet_serie.brevet_serie_ref ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_epreuve USING(brevet_serie_ref)  ';
  $DB_SQL.= 'WHERE eleve_brevet_serie!="X" AND user_sortie_date>NOW() AND ( brevet_epreuve_choix_recherche IS NULL OR brevet_epreuve_choix_moyenne IS NULL OR brevet_epreuve_choix_matieres IS NULL) ';
  $DB_SQL.= 'GROUP BY brevet_serie_ref ';
  $DB_SQL.= 'ORDER BY brevet_serie_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_brevet_epreuves
 *
 * @param string   $serie_ref
 * @param bool     $with_serie_nom
 * @return array
 */
public static function DB_lister_brevet_epreuves($serie_ref,$with_serie_nom=FALSE)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= ($with_serie_nom) ? ', brevet_serie_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_brevet_epreuve ';
  $DB_SQL.= ($with_serie_nom) ? 'LEFT JOIN sacoche_brevet_serie USING(brevet_serie_ref) ' : '' ;
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref ';
  $DB_SQL.= 'ORDER BY brevet_epreuve_code ASC ';
  $DB_VAR = array(':serie_ref'=>$serie_ref);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_brevet_eleves_avec_serie_et_total
 *
 * @param void
 * @return array
 */
public static function DB_lister_brevet_eleves_avec_serie_et_total()
{
  $DB_SQL = 'SELECT user_id,user_nom,user_prenom,eleve_classe_id,eleve_brevet_serie,saisie_note ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_saisie ON ( sacoche_user.user_id=sacoche_brevet_saisie.eleve_ou_classe_id AND sacoche_user.eleve_brevet_serie=sacoche_brevet_saisie.brevet_serie_ref ) ';
  $DB_SQL.= 'WHERE sacoche_user.eleve_brevet_serie!="X" AND user_sortie_date>NOW() ';
  $DB_SQL.= 'AND (brevet_epreuve_code='.CODE_BREVET_EPREUVE_TOTAL.' OR brevet_epreuve_code IS NULL) AND (saisie_type="eleve" OR saisie_type IS NULL) ';
  $DB_SQL.= 'ORDER BY user_nom ASC,user_prenom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_brevet_notes_eleve
 *
 * @param string $serie_ref
 * @param int    $user_id
 * @return array
 */
public static function DB_lister_brevet_notes_eleve($serie_ref,$user_id)
{
  $DB_SQL = 'SELECT brevet_epreuve_code, prof_id, matieres_id, saisie_note, saisie_appreciation ';
  $DB_SQL.= 'FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref AND eleve_ou_classe_id=:user_id AND saisie_type=:saisie_type ';
  $DB_SQL.= 'ORDER BY brevet_epreuve_code ASC ';
  $DB_VAR = array(
    ':serie_ref'   => $serie_ref,
    ':user_id'     => $user_id,
    ':saisie_type' => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_brevet_notes_epreuves_classe
 *
 * @param string $serie_ref
 * @param string $listing_epreuves_code
 * @param int    $classe_id
 * @return array
 */
public static function DB_lister_brevet_notes_epreuves_classe($serie_ref,$listing_epreuves_code,$classe_id)
{
  $DB_SQL = 'SELECT brevet_epreuve_code, saisie_type, saisie_note ';
  $DB_SQL.= 'FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON ( sacoche_brevet_saisie.eleve_ou_classe_id=sacoche_user.user_id AND sacoche_brevet_saisie.brevet_serie_ref=sacoche_user.eleve_brevet_serie ) ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref AND brevet_epreuve_code IN('.$listing_epreuves_code.') AND ( ( eleve_classe_id=:classe_id AND saisie_type="eleve" ) OR ( eleve_ou_classe_id=:classe_id AND saisie_type="classe" ) ) ';
  $DB_VAR = array(
    ':serie_ref'   => $serie_ref,
    ':classe_id'   => $classe_id,
    ':saisie_type' => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_brevet_notes_eleve
 *
 * @param int    $listing_eleve_id
 * @return array
 */
public static function DB_lister_brevet_notes_eleves($listing_eleve_id)
{
  $DB_SQL = 'SELECT eleve_ou_classe_id AS eleve_id, brevet_epreuve_code, saisie_note, brevet_epreuve_point_sup_10, brevet_epreuve_coefficient, brevet_epreuve_note_comptee ';
  $DB_SQL.= 'FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_epreuve USING(brevet_serie_ref,brevet_epreuve_code) ';
  $DB_SQL.= 'WHERE eleve_ou_classe_id IN('.$listing_eleve_id.') AND saisie_type=:saisie_type ';
  $DB_SQL.= 'ORDER BY brevet_epreuve_code ASC ';
  $DB_VAR = array(':saisie_type'=>'eleve');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les élèves (parmi les id transmis) ayant un INE
 *
 * @param string   $listing_eleve_id   id des élèves séparés par des virgules
 * @return array
 */
public static function DB_lister_eleves_cibles_actuels_avec_INE($listing_eleve_id)
{
  $DB_SQL = 'SELECT user_id , user_reference ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') AND user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $DB_SQL.= 'AND (user_reference REGEXP "^[0-9]{10}[A-Z]{1}$") ';
  $DB_SQL.= 'ORDER BY user_reference ASC';
  $DB_VAR = array(':profil_type'=>'eleve');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_brevet_fichiers
 * @param string   $listing_eleve_id   id des élèves séparés par des virgules
 * @return array   array( [eleve_id] => array( 0 => array( 'fichier_date' ) ) )
 */
public static function DB_lister_brevet_fichiers($listing_eleve_id)
{
  $DB_SQL = 'SELECT user_id , fichier_date ';
  $DB_SQL.= 'FROM sacoche_brevet_fichier ';
  $DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * Modifier la série du DNB pour une liste d'élèves
 *
 * @param string $listing_user_id
 * @param int    $serie_ref
 * @return void
 */
public static function DB_modifier_user_brevet_serie($listing_user_id,$serie_ref)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET eleve_brevet_serie=:serie_ref ';
  $DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
  $DB_VAR = array(
    ':serie_ref' => $serie_ref
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $DB_SQL = 'DELETE FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'WHERE brevet_serie_ref!=:serie_ref AND eleve_ou_classe_id IN('.$listing_user_id.') AND saisie_type=:saisie_type ';
  $DB_VAR = array(
    ':serie_ref'   => $serie_ref,
    ':saisie_type' => 'eleve',
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Il faudrait aussi recalculer pour chaque épreuve les moyennes des classes dont les élèves sont issus...
}

/**
 * modifier_epreuve_choix
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param int     $choix_recherche
 * @param int     $choix_moyenne
 * @param string  $choix_matieres
 * @return void
 */
public static function DB_modifier_epreuve_choix($serie_ref , $epreuve_code , $choix_recherche , $choix_moyenne , $choix_matieres)
{
  $DB_SQL = 'UPDATE sacoche_brevet_epreuve ';
  $DB_SQL.= 'SET brevet_epreuve_choix_recherche=:choix_recherche, brevet_epreuve_choix_moyenne=:choix_moyenne , brevet_epreuve_choix_matieres=:choix_matieres ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:brevet_serie_ref AND brevet_epreuve_code=:brevet_epreuve_code ';
  $DB_VAR = array(
    ':brevet_serie_ref'    => $serie_ref,
    ':brevet_epreuve_code' => $epreuve_code,
    ':choix_recherche'     => $choix_recherche,
    ':choix_moyenne'       => $choix_moyenne,
    ':choix_matieres'      => $choix_matieres,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_brevet_classe_etat
 *
 * @param int     $classe_id
 * @param string  $new_etat
 * @return void
 */
public static function DB_modifier_brevet_classe_etat($classe_id , $new_etat)
{
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET fiche_brevet=:fiche_brevet ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(
    ':groupe_id'    => $classe_id,
    ':fiche_brevet' => $new_etat,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_brevet_fichier
 *
 * @param int   $user_id
 * @return void
 */
public static function DB_modifier_brevet_fichier($user_id)
{
  $DB_SQL = 'REPLACE INTO sacoche_brevet_fichier (user_id, fichier_date) ';
  $DB_SQL.= 'VALUES(:user_id, NOW() ) ';
  $DB_VAR = array(':user_id'=>$user_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_brevet_note
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param string  $saisie_type
 * @param int     $eleve_ou_classe_id
 * @param string  $matieres_id
 * @param string  $saisie_note
 * @return void
 */
public static function DB_ajouter_brevet_note($serie_ref , $epreuve_code , $saisie_type , $eleve_ou_classe_id , $matieres_id , $saisie_note)
{
  $DB_SQL = 'INSERT INTO sacoche_brevet_saisie( brevet_serie_ref, brevet_epreuve_code, eleve_ou_classe_id, saisie_type, prof_id, matieres_id, saisie_note, saisie_appreciation) ';
  $DB_SQL.= 'VALUES                           (:brevet_serie_ref,:brevet_epreuve_code,:eleve_ou_classe_id,:saisie_type,:prof_id,:matieres_id,:saisie_note,:saisie_appreciation)';
  $DB_VAR = array(
    ':brevet_serie_ref'    => $serie_ref,
    ':brevet_epreuve_code' => $epreuve_code,
    ':eleve_ou_classe_id'  => $eleve_ou_classe_id,
    ':saisie_type'         => $saisie_type,
    ':prof_id'             => 0,
    ':matieres_id'         => $matieres_id,
    ':saisie_note'         => $saisie_note,
    ':saisie_appreciation' => '',
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_brevet_note
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param string  $saisie_type
 * @param int     $eleve_ou_classe_id
 * @param string  $matieres_id
 * @param string  $saisie_note
 * @return void
 */
public static function DB_modifier_brevet_note($serie_ref , $epreuve_code , $saisie_type , $eleve_ou_classe_id , $matieres_id , $saisie_note)
{
  $DB_SQL = 'UPDATE sacoche_brevet_saisie ';
  $DB_SQL.= 'SET matieres_id=:matieres_id, saisie_note=:saisie_note ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:brevet_serie_ref AND brevet_epreuve_code=:brevet_epreuve_code AND eleve_ou_classe_id=:eleve_ou_classe_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(
    ':brevet_serie_ref'    => $serie_ref,
    ':brevet_epreuve_code' => $epreuve_code,
    ':eleve_ou_classe_id'  => $eleve_ou_classe_id,
    ':saisie_type'         => $saisie_type,
    ':matieres_id'         => $matieres_id,
    ':saisie_note'         => $saisie_note,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_brevet_appreciation
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param int     $eleve_id
 * @param int     $prof_id
 * @param string  $saisie_appreciation
 * @return void
 */
public static function DB_modifier_brevet_appreciation($serie_ref , $epreuve_code , $eleve_id , $prof_id , $saisie_appreciation)
{
  $DB_SQL = 'UPDATE sacoche_brevet_saisie ';
  $DB_SQL.= 'SET prof_id=:prof_id, saisie_appreciation=:saisie_appreciation ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:brevet_serie_ref AND brevet_epreuve_code=:brevet_epreuve_code AND eleve_ou_classe_id=:eleve_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(
    ':brevet_serie_ref'    => $serie_ref,
    ':brevet_epreuve_code' => $epreuve_code,
    ':eleve_id'            => $eleve_id,
    ':saisie_type'         => 'eleve',
    ':prof_id'             => $prof_id,
    ':saisie_appreciation' => $saisie_appreciation,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_brevet_saisie
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param string  $saisie_type
 * @param int     $eleve_ou_classe_id
 * @return void
 */
public static function DB_supprimer_brevet_saisie($serie_ref , $epreuve_code , $saisie_type , $eleve_ou_classe_id)
{
  $DB_SQL = 'DELETE FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:brevet_serie_ref AND brevet_epreuve_code=:brevet_epreuve_code AND eleve_ou_classe_id=:eleve_ou_classe_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(
    ':brevet_serie_ref'    => $serie_ref,
    ':brevet_epreuve_code' => $epreuve_code,
    ':eleve_ou_classe_id'  => $eleve_ou_classe_id,
    ':saisie_type'         => $saisie_type,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter les élèves n'ayant pas d'identifiant national renseigné
 *
 * @param void
 * @return int
 */
public static function DB_compter_eleves_actuels_sans_INE()
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() AND (user_reference NOT REGEXP "^[0-9]{10}[A-Z]{1}$") ';
  $DB_VAR = array(':profil_type'=>'eleve');
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_brevet_classe_infos
 *
 * @param int     $classe_id
 * @return string
 */
public static function DB_recuperer_brevet_classe_infos($classe_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT fiche_brevet, groupe_nom, GROUP_CONCAT(user_id SEPARATOR ",") AS listing_user_id ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_groupe.groupe_id = sacoche_user.eleve_classe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_saisie ON ( sacoche_user.user_id=sacoche_brevet_saisie.eleve_ou_classe_id AND sacoche_user.eleve_brevet_serie=sacoche_brevet_saisie.brevet_serie_ref ) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND user_profil_type=:profil_type AND user_sortie_date>NOW() AND sacoche_user.eleve_brevet_serie!="X" AND brevet_epreuve_code='.CODE_BREVET_EPREUVE_TOTAL.' AND saisie_type="eleve" ';
  $DB_VAR = array(
    ':groupe_id'   => $classe_id,
    ':profil_type' => 'eleve',
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_brevet_saisies_eleves
 *
 * @param string $liste_eleve_id
 * @param int    $prof_id           Pour restreindre aux saisies d'un prof.
 * @param bool   $with_epreuve_nom On récupère aussi le nom de l'épreuve correspondante.
 * @param bool   $only_total        Pour restreindre au total des point / à l'avis de synthèse.
 * @return array
 */
public static function DB_recuperer_brevet_saisies_eleves($liste_eleve_id,$prof_id,$with_epreuve_nom,$only_total)
{
  $DB_SQL = 'SELECT prof_id, eleve_ou_classe_id AS eleve_id, brevet_serie_ref, brevet_epreuve_code, matieres_id, saisie_note, saisie_appreciation, user_genre, user_nom, user_prenom ';
  $DB_SQL.= ($with_epreuve_nom) ? ', brevet_epreuve_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_brevet_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= ($with_epreuve_nom) ? 'LEFT JOIN sacoche_brevet_epreuve USING(brevet_serie_ref,brevet_epreuve_code) ' : '' ;
  $DB_SQL.= 'WHERE eleve_ou_classe_id IN('.$liste_eleve_id.') AND saisie_type=:saisie_type ';
  $DB_SQL.= ($prof_id) ? 'AND prof_id IN(:prof_id,0) ' : '' ;
  $DB_SQL.= ($only_total) ? 'AND brevet_epreuve_code='.CODE_BREVET_EPREUVE_TOTAL.' ' : '' ;
  $DB_SQL.= 'ORDER BY brevet_epreuve_code ASC ';
  $DB_VAR = array(
    ':prof_id'     => $prof_id,
    ':saisie_type' => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_brevet_saisies_classe
 *
 * @param int    $classe_id
 * @param int    $prof_id          Pour restreindre aux saisies d'un prof.
 * @param bool   $with_epreuve_nom On récupère aussi le nom de l'épreuve correspondante.
 * @param bool   $only_total       Pour restreindre au total des point / à l'avis de synthèse.
 * @return array
 */
public static function DB_recuperer_brevet_saisies_classe($classe_id,$prof_id,$with_epreuve_nom,$only_total)
{
  $DB_SQL = 'SELECT prof_id, 0 AS eleve_id, brevet_serie_ref, brevet_epreuve_code, saisie_note, saisie_appreciation, CONCAT(user_nom," ",SUBSTRING(user_prenom,1,1),".") AS prof_info ';
  $DB_SQL.= ($with_epreuve_nom) ? ', brevet_epreuve_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_brevet_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= ($with_epreuve_nom) ? 'LEFT JOIN sacoche_brevet_epreuve USING(brevet_serie_ref,brevet_epreuve_code) ' : '' ;
  $DB_SQL.= 'WHERE eleve_ou_classe_id=:classe_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($prof_id) ? 'AND prof_id IN(:prof_id,0) ' : '' ;
  $DB_SQL.= ($only_total) ? 'AND brevet_epreuve_code='.CODE_BREVET_EPREUVE_TOTAL.' ' : '' ;
  $DB_SQL.= 'ORDER BY brevet_epreuve_code ASC ';
  $DB_VAR = array(
    ':classe_id'   => $classe_id,
    ':prof_id'     => $prof_id,
    ':saisie_type' => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_brevet_listing_classes_editables
 *
 * @param void
 * @return string
 */
public static function DB_recuperer_brevet_listing_classes_editables()
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT eleve_classe_id SEPARATOR ",") AS listing_classes ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_saisie ON ( sacoche_user.user_id=sacoche_brevet_saisie.eleve_ou_classe_id AND sacoche_user.eleve_brevet_serie=sacoche_brevet_saisie.brevet_serie_ref ) ';
  $DB_SQL.= 'WHERE sacoche_user.eleve_brevet_serie!="X" AND user_sortie_date>NOW() AND eleve_classe_id>0 ';
  $DB_SQL.= 'AND brevet_epreuve_code='.CODE_BREVET_EPREUVE_TOTAL.' AND saisie_type="eleve" ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_departement_academie
 *
 * @param string   $uai
 * @return array
 */
public static function DB_recuperer_departement_academie($uai)
{
  $geo_departement_id = (int)substr($uai,0,3);
  $DB_SQL = 'SELECT geo_departement_nom, geo_academie_nom ';
  $DB_SQL.= 'FROM sacoche_geo_departement ';
  $DB_SQL.= 'LEFT JOIN sacoche_geo_academie USING(geo_academie_id) ';
  $DB_SQL.= 'WHERE geo_departement_id=:geo_departement_id ';
  $DB_VAR = array(':geo_departement_id'=>$geo_departement_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_brevet_classes_editables_etat
 * Au passage, on tague les classes concernées.
 *
 * @param string   $listing_classes
 * @return array
 */
public static function DB_lister_brevet_classes_editables_etat($listing_classes)
{
  // Marquer les classes concernées
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET fiche_brevet="1vide" ';
  $DB_SQL.= 'WHERE groupe_id IN('.$listing_classes.') AND fiche_brevet="" ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET fiche_brevet="" ';
  $DB_SQL.= 'WHERE groupe_id NOT IN('.$listing_classes.') ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Retourner les états
  $DB_SQL = 'SELECT groupe_id , fiche_brevet ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_id IN('.$listing_classes.') ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

}
?>