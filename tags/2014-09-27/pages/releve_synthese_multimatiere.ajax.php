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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer une synthèse multi-matières
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$synthese_modele = 'multimatiere' ;
$groupe_id       = (isset($_POST['f_groupe']))             ? Clean::entier($_POST['f_groupe'])                : 0;
$groupe_nom      = (isset($_POST['f_groupe_nom']))         ? Clean::texte($_POST['f_groupe_nom'])             : '';
$periode_id      = (isset($_POST['f_periode']))            ? Clean::entier($_POST['f_periode'])               : 0;
$date_debut      = (isset($_POST['f_date_debut']))         ? Clean::date_fr($_POST['f_date_debut'])           : '';
$date_fin        = (isset($_POST['f_date_fin']))           ? Clean::date_fr($_POST['f_date_fin'])             : '';
$retroactif      = (isset($_POST['f_retroactif']))         ? Clean::calcul_retroactif($_POST['f_retroactif']) : '';
$niveau_id       = (isset($_POST['f_niveau']))             ? Clean::entier($_POST['f_niveau'])                : 0; // Niveau transmis uniquement si on restreint sur un niveau
$fusion_niveaux  = (isset($_POST['f_fusion_niveaux']))     ? 1                                                : 0;
$aff_coef        = (isset($_POST['f_coef']))               ? 1                                                : 0;
$aff_socle       = (isset($_POST['f_socle']))              ? 1                                                : 0;
$aff_lien        = (isset($_POST['f_lien']))               ? 1                                                : 0;
$aff_start       = (isset($_POST['f_start']))              ? 1                                                : 0;
$only_socle      = (isset($_POST['f_restriction_socle']))  ? 1                                                : 0;
$only_niveau     = (isset($_POST['f_restriction_niveau'])) ? $niveau_id                                       : 0;
$couleur         = (isset($_POST['f_couleur']))            ? Clean::texte($_POST['f_couleur'])                : '';
$legende         = (isset($_POST['f_legende']))            ? Clean::texte($_POST['f_legende'])                : '';
$marge_min       = (isset($_POST['f_marge_min']))          ? Clean::entier($_POST['f_marge_min'])             : 0;
// Normalement c'est un tableau qui est transmis, mais au cas où...
$tab_eleve = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_eleve = array_filter( Clean::map_entier($tab_eleve) , 'positif' );

$liste_eleve = implode(',',$tab_eleve);

if( !$groupe_id || !$groupe_nom || !count($tab_eleve) || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !$couleur || !$legende || !$marge_min )
{
  exit('Erreur avec les données transmises !');
}

Form::save_choix('synthese_multimatiere');

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

require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_synthese.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On retourne les résultats
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
