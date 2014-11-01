<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$classe_id = (isset($_POST['f_classe'])) ? Clean::entier($_POST['f_classe']) : 0;
$groupe_id = (isset($_POST['f_groupe'])) ? Clean::entier($_POST['f_groupe']) : 0;
// Autres chaines spécifiques...
$listing_rubriques = (isset($_POST['f_listing_rubriques'])) ? $_POST['f_listing_rubriques'] : '' ;
$tab_rubrique      = explode(',',$listing_rubriques);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

// On vérifie les paramètres

if( !$classe_id || (!count($tab_rubrique)) )
{
  exit('Erreur avec les données transmises !');
}

// On vérifie que la fiche brevet est bien accessible en modification et on récupère les infos associées (nom de la classe, id des élèves concernés avec lesquels l'intersection est faite ultérieurement).

$DB_ROW = DB_STRUCTURE_BREVET::DB_recuperer_brevet_classe_infos($classe_id);
if(empty($DB_ROW))
{
  exit('Classe sans élèves concernés !');
}
$BILAN_ETAT = $DB_ROW['fiche_brevet'];
$classe_nom = $DB_ROW['groupe_nom'];
$tab_id_eleves_avec_notes = explode(',',$DB_ROW['listing_user_id']);

if(!$BILAN_ETAT)
{
  exit('Fiche brevet introuvable !');
}
if(!in_array($BILAN_ETAT,array('2rubrique','3synthese')))
{
  exit('Fiche brevet interdite d\'accès pour cette action !');
}
if(!$DB_ROW['listing_user_id'])
{
  exit('Aucun élève concerné dans cette classe !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister les élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
if(empty($DB_TAB))
{
  exit('Aucun élève trouvé dans ce regroupement !');
}
$tab_eleve_id = array();
foreach($DB_TAB as $DB_ROW)
{
  if(in_array($DB_ROW['user_id'],$tab_id_eleves_avec_notes))
  {
    $tab_eleve_id[] = $DB_ROW['user_id'];
  }
}
if(empty($tab_eleve_id))
{
  exit('Aucun élève concerné dans ce regroupement !');
}
$liste_eleve_id = implode(',',$tab_eleve_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de l'identité des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve_id , 'alpha' /*eleves_ordre*/ , FALSE /*with_gepi*/ , FALSE /*with_langue*/ , TRUE /*with_brevet_serie*/ );

if(!is_array($tab_eleve_infos))
{
  exit('Aucun élève trouvé correspondant aux identifiants transmis !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des séries de brevet (probablement une seule)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_brevet_serie = array();
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  $tab_brevet_serie[$tab_eleve['eleve_brevet_serie']] = $tab_eleve['eleve_brevet_serie']; // Sera remplacé par le nom de la série après
}
if( !count($tab_brevet_serie) || isset($tab_brevet_serie['X']) )
{
  exit('Élève(s) trouvé(s) sans association avec une série de brevet !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des noms des épreuves par série de brevet (probablement une seule)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_brevet_epreuve = array();
foreach($tab_brevet_serie as $serie_ref)
{
  $tab_brevet_epreuve[$serie_ref] = array();
  $DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves( $serie_ref , TRUE /*with_serie_nom*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_brevet_serie[$serie_ref] = $DB_ROW['brevet_serie_nom'];
    $tab_brevet_epreuve[$serie_ref][$DB_ROW['brevet_epreuve_code']] = $DB_ROW['brevet_epreuve_nom'];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des saisies déjà effectuées pour le bilan officiel concerné
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Pour la recherche de saisies manquantes, on peut simplement récupérer ce qui est présent dans la table sacoche_brevet_saisie ; en effet :
// - on ne regarde que les élèves dont les notes sont enregistrées
// - les notes sont toutes enregistrées à la fois, il ne peut pas y avoir de notes manquantes
// - seules les appréciations sont donc à étudier, et elles sont retournées avec les notes, il est donc facile de lister les manques

$tab_resultat_examen = array();
$DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_eleves( $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , FALSE /*only_total*/ );
foreach($DB_TAB as $DB_ROW)
{
  if( (in_array($DB_ROW['brevet_serie_ref'].'_'.$DB_ROW['brevet_epreuve_code'],$tab_rubrique)) && (!$DB_ROW['saisie_appreciation']) && ($tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_brevet_serie']==$DB_ROW['brevet_serie_ref']) )
  {
    $tab_resultat_examen[$tab_brevet_serie[$DB_ROW['brevet_serie_ref']].' - '.$tab_brevet_epreuve[$DB_ROW['brevet_serie_ref']][$DB_ROW['brevet_epreuve_code']]][] = 'Absence d\'appréciation pour '.html($tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_nom'].' '.$tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_prenom']);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat de l'analyse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_pb_rubriques = count($tab_resultat_examen);
if(!$nb_pb_rubriques)
{
  exit('<p class="ti"><label class="valide">Aucune saisie manquante trouvée.</label></p>');
}
else
{
  $nb_pb_saisies = count($tab_resultat_examen,COUNT_RECURSIVE) - $nb_pb_rubriques ;
  $sr = ($nb_pb_rubriques>1) ? 's' : '' ;
  $ss = ($nb_pb_saisies>1)   ? 's' : '' ;
  echo'<p class="ti"><label class="danger">'.$nb_pb_saisies.' saisie'.$ss.' manquante'.$ss.' répartie'.$ss.' parmi '.$nb_pb_rubriques.' rubrique'.$sr.' !</label></p>';
  foreach($tab_resultat_examen as $rubrique_nom => $tab)
  {
    echo'<h3>'.html($rubrique_nom).'</h3>';
    echo'<ul class="puce"><li>'.implode('</li><li>',$tab).'</li></ul>';
  }
  exit();
}

?>
