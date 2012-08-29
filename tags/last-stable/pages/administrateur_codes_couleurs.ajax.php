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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$objet             = (isset($_POST['objet']))             ? clean_texte($_POST['objet'])             : '' ;

$note_image_style  = (isset($_POST['note_image_style']))  ? clean_texte($_POST['note_image_style'])  : '' ;

$note_texte_RR     = (isset($_POST['note_texte_RR']))     ? mb_substr(clean_texte($_POST['note_texte_RR']),0,40)     : '' ;
$note_texte_R      = (isset($_POST['note_texte_R']))      ? mb_substr(clean_texte($_POST['note_texte_R']),0,40)      : '' ;
$note_texte_V      = (isset($_POST['note_texte_V']))      ? mb_substr(clean_texte($_POST['note_texte_V']),0,40)      : '' ;
$note_texte_VV     = (isset($_POST['note_texte_VV']))     ? mb_substr(clean_texte($_POST['note_texte_VV']),0,40)     : '' ;

$note_legende_RR   = (isset($_POST['note_legende_RR']))   ? mb_substr(clean_texte($_POST['note_legende_RR']),0,40)   : '' ;
$note_legende_R    = (isset($_POST['note_legende_R']))    ? mb_substr(clean_texte($_POST['note_legende_R']),0,40)    : '' ;
$note_legende_V    = (isset($_POST['note_legende_V']))    ? mb_substr(clean_texte($_POST['note_legende_V']),0,40)    : '' ;
$note_legende_VV   = (isset($_POST['note_legende_VV']))   ? mb_substr(clean_texte($_POST['note_legende_VV']),0,40)   : '' ;

$acquis_texte_NA   = (isset($_POST['acquis_texte_NA']))   ? mb_substr(clean_texte($_POST['acquis_texte_NA']),0,40)   : '' ;
$acquis_texte_VA   = (isset($_POST['acquis_texte_VA']))   ? mb_substr(clean_texte($_POST['acquis_texte_VA']),0,40)   : '' ;
$acquis_texte_A    = (isset($_POST['acquis_texte_A']))    ? mb_substr(clean_texte($_POST['acquis_texte_A']),0,40)    : '' ;

$acquis_legende_NA = (isset($_POST['acquis_legende_NA'])) ? mb_substr(clean_texte($_POST['acquis_legende_NA']),0,40) : '' ;
$acquis_legende_VA = (isset($_POST['acquis_legende_VA'])) ? mb_substr(clean_texte($_POST['acquis_legende_VA']),0,40) : '' ;
$acquis_legende_A  = (isset($_POST['acquis_legende_A']))  ? mb_substr(clean_texte($_POST['acquis_legende_A']),0,40)  : '' ;

$acquis_color_NA   = (isset($_POST['acquis_color_NA']))   ? clean_texte($_POST['acquis_color_NA'])   : '' ;
$acquis_color_VA   = (isset($_POST['acquis_color_VA']))   ? clean_texte($_POST['acquis_color_VA'])   : '' ;
$acquis_color_A    = (isset($_POST['acquis_color_A']))    ? clean_texte($_POST['acquis_color_A'])    : '' ;

$longueur_NA = mb_strlen($acquis_color_NA);
$longueur_VA = mb_strlen($acquis_color_VA);
$longueur_A  = mb_strlen($acquis_color_A);
$test_jeu = (preg_match("/^[0-9a-z_-]+$/i", $note_image_style)) && (is_dir('./_img/note/'.$note_image_style)) ;
$test_color_NA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $acquis_color_NA)) && ($longueur_NA!=5) && ($longueur_NA!=6) ;
$test_color_VA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $acquis_color_VA)) && ($longueur_VA!=5) && ($longueur_VA!=6) ;
$test_color_A   = (preg_match("/^\#[0-9a-f]{3,6}$/i", $acquis_color_A))  && ($longueur_A !=5) && ($longueur_A !=6) ;

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Notes aux évaluations : symboles colorés, équivalents textes, légende
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($objet=='notes') && $test_jeu && $note_texte_RR && $note_texte_R && $note_texte_V && $note_texte_VV && $note_legende_RR && $note_legende_R && $note_legende_V && $note_legende_VV )
{
	// Mettre à jour la session + la base + le css perso
	$tab_parametres = array();
	$_SESSION['NOTE_IMAGE_STYLE']           = $note_image_style;  $tab_parametres['note_image_style']               = $note_image_style;
	$_SESSION['NOTE_TEXTE']['RR']           = $note_texte_RR;     $tab_parametres['note_texte_RR']                  = $note_texte_RR;
	$_SESSION['NOTE_TEXTE']['R']            = $note_texte_R;      $tab_parametres['note_texte_R']                   = $note_texte_R;
	$_SESSION['NOTE_TEXTE']['V']            = $note_texte_V;      $tab_parametres['note_texte_V']                   = $note_texte_V;
	$_SESSION['NOTE_TEXTE']['VV']           = $note_texte_VV;     $tab_parametres['note_texte_VV']                  = $note_texte_VV;
	$_SESSION['NOTE_LEGENDE']['RR']         = $note_legende_RR;   $tab_parametres['note_legende_RR']                = $note_legende_RR;
	$_SESSION['NOTE_LEGENDE']['R']          = $note_legende_R;    $tab_parametres['note_legende_R']                 = $note_legende_R;
	$_SESSION['NOTE_LEGENDE']['V']          = $note_legende_V;    $tab_parametres['note_legende_V']                 = $note_legende_V;
	$_SESSION['NOTE_LEGENDE']['VV']         = $note_legende_VV;   $tab_parametres['note_legende_VV']                = $note_legende_VV;
	DB_STRUCTURE_COMMUN::DB_modifier_parametres( $tab_parametres );
	// Enregistrer en session le CSS personnalisé
	adapter_session_daltonisme();
	actualiser_style_session();
	exit('ok');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Degrés d'acquisitions calculés : couleurs de fond, équivalents textes, légende
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
	adapter_session_daltonisme();
	actualiser_style_session();
	exit('ok');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	On ne devrait pas en arriver là !
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
