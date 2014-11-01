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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$palier_id     = (isset($_POST['f_palier']))        ? Clean::entier($_POST['f_palier'])      : 0;
$palier_nom    = (isset($_POST['f_palier_nom']))    ? Clean::texte($_POST['f_palier_nom'])   : '';
$only_presence = (isset($_POST['f_only_presence'])) ? 1                                      : 0;
$aff_socle_PA  = (isset($_POST['f_socle_PA']))      ? 1                                      : 0;
$aff_socle_EV  = (isset($_POST['f_socle_EV']))      ? 1                                      : 0;
$groupe_id     = (isset($_POST['f_groupe']))        ? Clean::entier($_POST['f_groupe'])      : 0;
$groupe_nom    = (isset($_POST['f_groupe_nom']))    ? Clean::texte($_POST['f_groupe_nom'])   : '';
$groupe_type   = (isset($_POST['f_groupe_type']))   ? Clean::texte($_POST['f_groupe_type'])  : '';
$mode          = (isset($_POST['f_mode']))          ? Clean::texte($_POST['f_mode'])         : '';
$aff_coef      = (isset($_POST['f_coef']))          ? 1                                      : 0;
$aff_socle     = (isset($_POST['f_socle']))         ? 1                                      : 0;
$aff_lien      = (isset($_POST['f_lien']))          ? 1                                      : 0;
$aff_start     = (isset($_POST['f_start']))         ? 1                                      : 0;
$couleur       = (isset($_POST['f_couleur']))       ? Clean::texte($_POST['f_couleur'])      : '';
$legende       = (isset($_POST['f_legende']))       ? Clean::texte($_POST['f_legende'])      : '';
$marge_min     = (isset($_POST['f_marge_min']))     ? Clean::entier($_POST['f_marge_min'])   : 0;
$eleves_ordre  = (isset($_POST['f_eleves_ordre']))  ? Clean::texte($_POST['f_eleves_ordre']) : '';
// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_pilier_id  = (isset($_POST['f_pilier']))  ? ( (is_array($_POST['f_pilier']))  ? $_POST['f_pilier']  : explode(',',$_POST['f_pilier'])  ) : array() ;
$tab_eleve_id   = (isset($_POST['f_eleve']))   ? ( (is_array($_POST['f_eleve']))   ? $_POST['f_eleve']   : explode(',',$_POST['f_eleve'])   ) : array() ;
$tab_matiere_id = (isset($_POST['f_matiere'])) ? ( (is_array($_POST['f_matiere'])) ? $_POST['f_matiere'] : explode(',',$_POST['f_matiere']) ) : array() ;
$tab_pilier_id  = array_filter( Clean::map_entier($tab_pilier_id)  , 'positif' );
$tab_eleve_id   = array_filter( Clean::map_entier($tab_eleve_id)   , 'positif' );
$tab_matiere_id = array_filter( Clean::map_entier($tab_matiere_id) , 'positif' );

// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  $aff_socle_PA = test_user_droit_specifique($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS']) ? $aff_socle_PA : 0 ;
  $aff_socle_EV = test_user_droit_specifique($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'])    ? $aff_socle_EV : 0 ;
  $only_presence = 0;
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $groupe_id    = $_SESSION['ELEVE_CLASSE_ID'];
  $tab_eleve_id = array($_SESSION['USER_ID']);
}

if( !$palier_id || !$palier_nom || ( $groupe_id && ( !$groupe_nom || !$groupe_type) ) || !count($tab_pilier_id) || !in_array($mode,array('auto','manuel')) || !$couleur || !$legende || !$marge_min || !$eleves_ordre )
{
  exit('Erreur avec les données transmises !');
}

Form::save_choix('releve_socle');

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

require(CHEMIN_DOSSIER_INCLUDE.'noyau_socle_releve.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($affichage_direct)
{
  echo'<hr />'.NL;
  echo'<ul class="puce">'.NL;
  echo  '<li><a target="_blank" href="'.URL_DIR_EXPORT.$fichier_nom.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  echo'</ul>'.NL;
  echo $releve_HTML;
}
else
{
  echo'<ul class="puce">'.NL;
  echo  '<li><a target="_blank" href="'.URL_DIR_EXPORT.$fichier_nom.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  echo  '<li><a target="_blank" href="./releve_html.php?fichier='.$fichier_nom.'"><span class="file file_htm">Explorer / Détailler (format <em>html</em>).</span></a></li>'.NL;
  echo'</ul>'.NL;
}

?>
