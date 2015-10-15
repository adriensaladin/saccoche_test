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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$objet             = (isset($_POST['objet']))             ?    Clean::texte($_POST['objet'])             : '' ;

$note_image_RR     = (isset($_POST['note_image_RR']))     ? Clean::txt_note($_POST['note_image_RR'])     : '' ;
$note_image_R      = (isset($_POST['note_image_R']))      ? Clean::txt_note($_POST['note_image_R'])      : '' ;
$note_image_V      = (isset($_POST['note_image_V']))      ? Clean::txt_note($_POST['note_image_V'])      : '' ;
$note_image_VV     = (isset($_POST['note_image_VV']))     ? Clean::txt_note($_POST['note_image_VV'])     : '' ;

$note_texte_RR     = (isset($_POST['note_texte_RR']))     ? Clean::txt_note($_POST['note_texte_RR'])     : '' ;
$note_texte_R      = (isset($_POST['note_texte_R']))      ? Clean::txt_note($_POST['note_texte_R'])      : '' ;
$note_texte_V      = (isset($_POST['note_texte_V']))      ? Clean::txt_note($_POST['note_texte_V'])      : '' ;
$note_texte_VV     = (isset($_POST['note_texte_VV']))     ? Clean::txt_note($_POST['note_texte_VV'])     : '' ;

$note_legende_RR   = (isset($_POST['note_legende_RR']))   ? Clean::txt_note($_POST['note_legende_RR'])   : '' ;
$note_legende_R    = (isset($_POST['note_legende_R']))    ? Clean::txt_note($_POST['note_legende_R'])    : '' ;
$note_legende_V    = (isset($_POST['note_legende_V']))    ? Clean::txt_note($_POST['note_legende_V'])    : '' ;
$note_legende_VV   = (isset($_POST['note_legende_VV']))   ? Clean::txt_note($_POST['note_legende_VV'])   : '' ;

$acquis_texte_NA   = (isset($_POST['acquis_texte_NA']))   ? Clean::txt_note($_POST['acquis_texte_NA'])   : '' ;
$acquis_texte_VA   = (isset($_POST['acquis_texte_VA']))   ? Clean::txt_note($_POST['acquis_texte_VA'])   : '' ;
$acquis_texte_A    = (isset($_POST['acquis_texte_A']))    ? Clean::txt_note($_POST['acquis_texte_A'])    : '' ;

$acquis_legende_NA = (isset($_POST['acquis_legende_NA'])) ? Clean::txt_note($_POST['acquis_legende_NA']) : '' ;
$acquis_legende_VA = (isset($_POST['acquis_legende_VA'])) ? Clean::txt_note($_POST['acquis_legende_VA']) : '' ;
$acquis_legende_A  = (isset($_POST['acquis_legende_A']))  ? Clean::txt_note($_POST['acquis_legende_A'])  : '' ;

$acquis_color_NA   = (isset($_POST['acquis_color_NA']))   ?    Clean::texte($_POST['acquis_color_NA'])   : '' ;
$acquis_color_VA   = (isset($_POST['acquis_color_VA']))   ?    Clean::texte($_POST['acquis_color_VA'])   : '' ;
$acquis_color_A    = (isset($_POST['acquis_color_A']))    ?    Clean::texte($_POST['acquis_color_A'])    : '' ;

$chemin_dossier = CHEMIN_DOSSIER_IMG.'note'.DS.'choix'.DS.'h'.DS;
$test_image_RR = (preg_match("/^[0-9a-z_-]+$/i", $note_image_RR)) && (is_file($chemin_dossier.$note_image_RR.'.gif')) ;
$test_image_R  = (preg_match("/^[0-9a-z_-]+$/i", $note_image_R )) && (is_file($chemin_dossier.$note_image_R .'.gif')) ;
$test_image_V  = (preg_match("/^[0-9a-z_-]+$/i", $note_image_V )) && (is_file($chemin_dossier.$note_image_V .'.gif')) ;
$test_image_VV = (preg_match("/^[0-9a-z_-]+$/i", $note_image_VV)) && (is_file($chemin_dossier.$note_image_VV.'.gif')) ;

$longueur_NA = mb_strlen($acquis_color_NA);
$longueur_VA = mb_strlen($acquis_color_VA);
$longueur_A  = mb_strlen($acquis_color_A);
$test_color_NA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $acquis_color_NA)) && ($longueur_NA!=5) && ($longueur_NA!=6) ;
$test_color_VA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $acquis_color_VA)) && ($longueur_VA!=5) && ($longueur_VA!=6) ;
$test_color_A   = (preg_match("/^\#[0-9a-f]{3,6}$/i", $acquis_color_A))  && ($longueur_A !=5) && ($longueur_A !=6) ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Notes aux évaluations : symboles colorés, équivalents textes, légende
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($objet=='notes') && $test_image_RR && $test_image_R && $test_image_R && $test_image_VV && ($note_texte_RR!='') && ($note_texte_R!='') && ($note_texte_V!='') && ($note_texte_VV!='') && $note_legende_RR && $note_legende_R && $note_legende_V && $note_legende_VV )
{
  // Mettre à jour la session + la base + le css perso
  $tab_parametres = array();
  $_SESSION['NOTE_IMAGE']['RR']   = $note_image_RR;   $tab_parametres['note_image_RR']   = $note_image_RR;
  $_SESSION['NOTE_IMAGE']['R']    = $note_image_R;    $tab_parametres['note_image_R']    = $note_image_R;
  $_SESSION['NOTE_IMAGE']['V']    = $note_image_V;    $tab_parametres['note_image_V']    = $note_image_V;
  $_SESSION['NOTE_IMAGE']['VV']   = $note_image_VV;   $tab_parametres['note_image_VV']   = $note_image_VV;
  $_SESSION['NOTE_TEXTE']['RR']   = $note_texte_RR;   $tab_parametres['note_texte_RR']   = $note_texte_RR;
  $_SESSION['NOTE_TEXTE']['R']    = $note_texte_R;    $tab_parametres['note_texte_R']    = $note_texte_R;
  $_SESSION['NOTE_TEXTE']['V']    = $note_texte_V;    $tab_parametres['note_texte_V']    = $note_texte_V;
  $_SESSION['NOTE_TEXTE']['VV']   = $note_texte_VV;   $tab_parametres['note_texte_VV']   = $note_texte_VV;
  $_SESSION['NOTE_LEGENDE']['RR'] = $note_legende_RR; $tab_parametres['note_legende_RR'] = $note_legende_RR;
  $_SESSION['NOTE_LEGENDE']['R']  = $note_legende_R;  $tab_parametres['note_legende_R']  = $note_legende_R;
  $_SESSION['NOTE_LEGENDE']['V']  = $note_legende_V;  $tab_parametres['note_legende_V']  = $note_legende_V;
  $_SESSION['NOTE_LEGENDE']['VV'] = $note_legende_VV; $tab_parametres['note_legende_VV'] = $note_legende_VV;
  DB_STRUCTURE_COMMUN::DB_modifier_parametres( $tab_parametres );
  // Enregistrer en session le CSS personnalisé
  SessionUser::adapter_daltonisme();
  SessionUser::actualiser_style();
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Degrés d'acquisitions calculés : couleurs de fond, équivalents textes, légende
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($objet=='acquis') && $test_color_NA && $test_color_VA && $test_color_A && $acquis_texte_NA && $acquis_texte_VA && $acquis_texte_A && $acquis_legende_NA && $acquis_legende_VA && $acquis_legende_A )
{
  // Passer si besoin d'un code hexadécimal à 3 caractères vers un code hexadécimal à 6 caractères.
  if($longueur_NA==4) {$acquis_color_NA = '#'.$acquis_color_NA{1}.$acquis_color_NA{1}.$acquis_color_NA{2}.$acquis_color_NA{2}.$acquis_color_NA{3}.$acquis_color_NA{3};}
  if($longueur_VA==4) {$acquis_color_VA = '#'.$acquis_color_VA{1}.$acquis_color_VA{1}.$acquis_color_VA{2}.$acquis_color_VA{2}.$acquis_color_VA{3}.$acquis_color_VA{3};}
  if($longueur_A ==4) {$acquis_color_A  = '#'.$acquis_color_A{1} .$acquis_color_A{1} .$acquis_color_A{2} .$acquis_color_A{2} .$acquis_color_A{3} .$acquis_color_A{3} ;}
  // Mettre à jour la session + la base + le css perso
  $tab_parametres = array();
  $_SESSION['ACQUIS_TEXTE']['NA']         = $acquis_texte_NA;   $tab_parametres['acquis_texte_NA']         = $acquis_texte_NA;
  $_SESSION['ACQUIS_TEXTE']['VA']         = $acquis_texte_VA;   $tab_parametres['acquis_texte_VA']         = $acquis_texte_VA;
  $_SESSION['ACQUIS_TEXTE']['A']          = $acquis_texte_A;    $tab_parametres['acquis_texte_A']          = $acquis_texte_A;
  $_SESSION['ACQUIS_LEGENDE']['NA']       = $acquis_legende_NA; $tab_parametres['acquis_legende_NA']       = $acquis_legende_NA;
  $_SESSION['ACQUIS_LEGENDE']['VA']       = $acquis_legende_VA; $tab_parametres['acquis_legende_VA']       = $acquis_legende_VA;
  $_SESSION['ACQUIS_LEGENDE']['A']        = $acquis_legende_A;  $tab_parametres['acquis_legende_A']        = $acquis_legende_A;
  $_SESSION['CSS_BACKGROUND-COLOR']['NA'] = $acquis_color_NA;   $tab_parametres['css_background-color_A']  = $acquis_color_A;
  $_SESSION['CSS_BACKGROUND-COLOR']['VA'] = $acquis_color_VA;   $tab_parametres['css_background-color_VA'] = $acquis_color_VA;
  $_SESSION['CSS_BACKGROUND-COLOR']['A']  = $acquis_color_A;    $tab_parametres['css_background-color_NA'] = $acquis_color_NA;
  DB_STRUCTURE_COMMUN::DB_modifier_parametres( $tab_parametres );
  // Enregistrer en session le CSS personnalisé
  SessionUser::adapter_daltonisme();
  SessionUser::actualiser_style();
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
