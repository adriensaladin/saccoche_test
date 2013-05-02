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
 * @return array
 */
public static function DB_lister_brevet_epreuves($serie_ref)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_brevet_epreuve ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref ';
  $DB_SQL.= 'ORDER BY brevet_epreuve_code ASC ';
  $DB_VAR = array(':serie_ref'=>$serie_ref);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_brevet_eleves_avec_serie_et_total
 *
 * @param string   $serie_ref
 * @return array
 */
public static function DB_lister_brevet_eleves_avec_serie_et_total()
{
  $DB_SQL = 'SELECT user_id,user_nom,user_prenom,eleve_classe_id,eleve_brevet_serie,saisie_note ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_brevet_saisie ON ( sacoche_user.user_id=sacoche_brevet_saisie.eleve_ou_classe_id AND sacoche_user.eleve_brevet_serie=sacoche_brevet_saisie.brevet_serie_ref ) ';
  $DB_SQL.= 'WHERE sacoche_user.eleve_brevet_serie!="X" AND user_sortie_date>NOW() ';
  $DB_SQL.= 'AND (brevet_epreuve_code=255 OR brevet_epreuve_code IS NULL) AND (saisie_type="eleve" OR saisie_type IS NULL) ';
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
  $DB_VAR = array(':serie_ref'=>$serie_ref,':user_id'=>$user_id,':saisie_type'=>'eleve',':epreuve_code'=>'TOT');
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
  $DB_SQL = 'SELECT eleve_ou_classe_id AS eleve_id, brevet_epreuve_code, saisie_note ';
  $DB_SQL.= 'FROM sacoche_brevet_saisie ';
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
  $DB_VAR = array(':serie_ref'=>$serie_ref);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $DB_SQL = 'DELETE FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'WHERE brevet_serie_ref!=:serie_ref AND eleve_ou_classe_id IN('.$listing_user_id.') AND saisie_type=:saisie_type ';
  $DB_VAR = array(':serie_ref'=>$serie_ref,':saisie_type'=>'eleve');
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
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
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref AND brevet_epreuve_code=:epreuve_code ';
  $DB_VAR = array(':serie_ref'=>$serie_ref,':epreuve_code'=>$epreuve_code,':choix_recherche'=>$choix_recherche,':choix_moyenne'=>$choix_moyenne,':choix_matieres'=>$choix_matieres);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_brevet_note
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param int     $eleve_id
 * @param string  $matieres_id
 * @param string  $saisie_note
 * @return void
 */
public static function DB_ajouter_brevet_note($serie_ref , $epreuve_code , $eleve_id , $matieres_id , $saisie_note)
{
  $DB_SQL = 'INSERT INTO sacoche_brevet_saisie(brevet_serie_ref,brevet_epreuve_code,eleve_ou_classe_id,saisie_type,prof_id,matieres_id,saisie_note,saisie_appreciation) ';
  $DB_SQL.= 'VALUES(:serie_ref,:epreuve_code,:eleve_ou_classe_id,:saisie_type,:prof_id,:matieres_id,:saisie_note,:saisie_appreciation)';
  $DB_VAR = array(':serie_ref'=>$serie_ref,':epreuve_code'=>$epreuve_code,':eleve_ou_classe_id'=>$eleve_id,':saisie_type'=>'eleve',':prof_id'=>0,':matieres_id'=>$matieres_id,':saisie_note'=>$saisie_note,':saisie_appreciation'=>'');
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_brevet_note
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param int     $eleve_id
 * @param string  $matieres_id
 * @param string  $saisie_note
 * @return void
 */
public static function DB_modifier_brevet_note($serie_ref , $epreuve_code , $eleve_id , $matieres_id , $saisie_note)
{
  $DB_SQL = 'UPDATE sacoche_brevet_saisie ';
  $DB_SQL.= 'SET matieres_id=:matieres_id, saisie_note=:saisie_note ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref AND brevet_epreuve_code=:epreuve_code AND eleve_ou_classe_id=:eleve_ou_classe_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(':serie_ref'=>$serie_ref,':epreuve_code'=>$epreuve_code,':eleve_ou_classe_id'=>$eleve_id,':saisie_type'=>'eleve',':matieres_id'=>$matieres_id,':saisie_note'=>$saisie_note);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_brevet_saisie
 *
 * @param string  $serie_ref
 * @param int     $epreuve_code
 * @param int     $eleve_id
 * @return void
 */
public static function DB_supprimer_brevet_saisie($serie_ref , $epreuve_code , $eleve_id)
{
  $DB_SQL = 'DELETE FROM sacoche_brevet_saisie ';
  $DB_SQL.= 'WHERE brevet_serie_ref=:serie_ref AND brevet_epreuve_code=:epreuve_code AND eleve_ou_classe_id=:eleve_ou_classe_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(':serie_ref'=>$serie_ref,':epreuve_code'=>$epreuve_code,':eleve_ou_classe_id'=>$eleve_id,':saisie_type'=>'eleve');
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

}
?>