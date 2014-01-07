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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Reporter des notes -> redirection vers la page pour le traiter
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (isset($_POST['f_action'])) && ($_POST['f_action']=='reporter_notes') )
{
  require(CHEMIN_DOSSIER_INCLUDE.'code_report_notes_releve_to_bulletin.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Autres cas
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$format                 = 'selection';
$aff_etat_acquisition   = (isset($_POST['f_etat_acquisition']))   ? 1                                                : 0;
$aff_moyenne_scores     = (isset($_POST['f_moyenne_scores']))     ? 1                                                : 0;
$aff_pourcentage_acquis = (isset($_POST['f_pourcentage_acquis'])) ? 1                                                : 0;
$conversion_sur_20      = (isset($_POST['f_conversion_sur_20']))  ? 1                                                : 0;
$tableau_tri_objet      = (isset($_POST['f_tri_objet']))          ? Clean::texte($_POST['f_tri_objet'])              : '';
$tableau_tri_mode       = (isset($_POST['f_tri_mode']))           ? Clean::texte($_POST['f_tri_mode'])               : '';
$with_coef              = (isset($_POST['f_with_coef']))          ? 1                                                : 0;
$groupe_id              = (isset($_POST['f_groupe']))             ? Clean::entier($_POST['f_groupe'])                : 0;
$groupe_nom             = (isset($_POST['f_groupe_nom']))         ? Clean::texte($_POST['f_groupe_nom'])             : '';
$matiere_id             = TRUE;
$matiere_nom            = '';
$periode_id             = (isset($_POST['f_periode']))            ? Clean::entier($_POST['f_periode'])               : 0;
$date_debut             = (isset($_POST['f_date_debut']))         ? Clean::date_fr($_POST['f_date_debut'])           : '';
$date_fin               = (isset($_POST['f_date_fin']))           ? Clean::date_fr($_POST['f_date_fin'])             : '';
$retroactif             = (isset($_POST['f_retroactif']))         ? Clean::calcul_retroactif($_POST['f_retroactif']) : '';
$only_socle             = 0;
$aff_coef               = (isset($_POST['f_coef']))               ? 1                                                : 0;
$aff_socle              = (isset($_POST['f_socle']))              ? 1                                                : 0;
$aff_lien               = (isset($_POST['f_lien']))               ? 1                                                : 0;
$aff_domaine            = (isset($_POST['f_domaine']))            ? 1                                                : 0;
$aff_theme              = (isset($_POST['f_theme']))              ? 1                                                : 0;
$orientation            = (isset($_POST['f_orientation']))        ? Clean::texte($_POST['f_orientation'])            : '';
$couleur                = (isset($_POST['f_couleur']))            ? Clean::texte($_POST['f_couleur'])                : '';
$legende                = (isset($_POST['f_legende']))            ? Clean::texte($_POST['f_legende'])                : '';
$marge_min              = (isset($_POST['f_marge_min']))          ? Clean::entier($_POST['f_marge_min'])             : 0;
$pages_nb               = (isset($_POST['f_pages_nb']))           ? Clean::texte($_POST['f_pages_nb'])               : '';
$cases_nb               = (isset($_POST['f_cases_nb']))           ? Clean::entier($_POST['f_cases_nb'])              : -1;
$cases_largeur          = (isset($_POST['f_cases_larg']))         ? Clean::entier($_POST['f_cases_larg'])            : 0;

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_eleve = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_type  = (isset($_POST['f_type']))  ? ( (is_array($_POST['f_type']))  ? $_POST['f_type']  : explode(',',$_POST['f_type'])  ) : array() ;
$tab_eleve = array_filter( Clean::map_entier($tab_eleve) , 'positif' );
$tab_type  = Clean::map_texte($tab_type);

// Ci-après sans objet car cette page n'est proposée qu'aux professeurs.
// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
/*
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  $aff_moyenne_scores     = test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? $aff_moyenne_scores     : 0 ;
  $aff_pourcentage_acquis = test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? $aff_pourcentage_acquis : 0 ;
  $conversion_sur_20      = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? $conversion_sur_20      : 0 ;
  $tab_type               = array('individuel');
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $groupe_id  = $_SESSION['ELEVE_CLASSE_ID'];
  $groupe_nom = $_SESSION['ELEVE_CLASSE_NOM'];
  $tab_eleve  = array($_SESSION['USER_ID']);
}
*/

$type_individuel = (in_array('individuel',$tab_type)) ? 1 : 0 ;
$type_synthese   = (in_array('synthese',$tab_type))   ? 1 : 0 ;
$type_bulletin   = (in_array('bulletin',$tab_type))   ? 1 : 0 ;

$liste_eleve = implode(',',$tab_eleve);

if( !$orientation || !$couleur || !$legende || !$marge_min || !$pages_nb || ($cases_nb<0) || !$cases_largeur || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !$matiere_id || !$groupe_id || !$groupe_nom || !count($tab_eleve) || !count($tab_type) )
{
  exit('Erreur avec les données transmises !');
}

Form::save_choix('items_selection');

$marge_gauche = $marge_droite = $marge_haut = $marge_bas = $marge_min ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_officiel = FALSE;
$make_brevet   = FALSE;
$make_action   = '';
$make_html     = TRUE;
$make_pdf      = TRUE;
$make_graph    = FALSE;

require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_releve.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On retourne les résultats
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($affichage_direct)
{
  echo'<hr />'.NL;
  echo'<ul class="puce">'.NL;
  echo  '<li><a class="lien_ext" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  echo'</ul>'.NL;
  echo $releve_HTML_individuel;
}
else
{
  if($type_individuel)
  {
    echo'<h2>Relevé individuel</h2>'.NL;
    echo'<ul class="puce">'.NL;
    echo  '<li><a class="lien_ext" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    echo  '<li><a class="lien_ext" href="./releve_html.php?fichier='.str_replace('<REPLACE>','individuel',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    echo'</ul>'.NL;
  }
  if($type_synthese)
  {
    echo'<h2>Synthèse collective</h2>'.NL;
    echo'<ul class="puce">'.NL;
    echo  '<li><a class="lien_ext" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    echo  '<li><a class="lien_ext" href="./releve_html.php?fichier='.str_replace('<REPLACE>','synthese',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    echo'</ul>'.NL;
  }
  if($type_bulletin)
  {
    echo'<h2>Bulletin SACoche</h2>'.NL;
    echo'<ul class="puce">'.NL;
    echo  '<li><a class="lien_ext" href="./releve_html.php?fichier='.str_replace('<REPLACE>','bulletin',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    echo $bulletin_form;
    echo'</ul>'.NL;
    echo $bulletin_alerte;
    echo'<h2>Bulletin Gepi</h2>'.NL;
    echo'<ul class="puce">'.NL;
    echo  '<li><a class="lien_ext" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_note_appreciation',$fichier_nom).'.csv"><span class="file file_txt">Récupérer notes et appréciations à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
    echo  '<li><a class="lien_ext" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_note',$fichier_nom).'.csv"><span class="file file_txt">Récupérer les notes à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
    echo  '<li><a class="lien_ext" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_appreciation',$fichier_nom).'.csv"><span class="file file_txt">Récupérer les appréciations à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
    echo'</ul>'.NL;
  }
}

?>
