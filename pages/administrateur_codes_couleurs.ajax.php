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

$jeu_codes = (isset($_POST['jeu_codes'])) ? clean_texte($_POST['jeu_codes']) : '' ;
$color_NA  = (isset($_POST['color_NA']))  ? clean_texte($_POST['color_NA'])  : '' ;
$color_VA  = (isset($_POST['color_VA']))  ? clean_texte($_POST['color_VA'])  : '' ;
$color_A   = (isset($_POST['color_A']))   ? clean_texte($_POST['color_A'])   : '' ;

$longueur_NA = mb_strlen($color_NA);
$longueur_VA = mb_strlen($color_VA);
$longueur_A  = mb_strlen($color_A);
$test_jeu = (preg_match("/^[0-9a-z_-]+$/i", $jeu_codes)) && (is_dir('./_img/note/'.$jeu_codes)) ;
$test_NA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $color_NA)) && ($longueur_NA!=5) && ($longueur_NA!=6) ;
$test_VA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $color_VA)) && ($longueur_VA!=5) && ($longueur_VA!=6) ;
$test_A   = (preg_match("/^\#[0-9a-f]{3,6}$/i", $color_A))  && ($longueur_A !=5) && ($longueur_A !=6) ;

if( $test_jeu && $test_NA && $test_VA && $test_A )
{
	// Passer si besoin d'un code hexadécimal à 3 caractères vers un code hexadécimal à 6 caractères.
	if($longueur_NA==4) {$color_NA = '#'.$color_NA{1}.$color_NA{1}.$color_NA{2}.$color_NA{2}.$color_NA{3}.$color_NA{3};}
	if($longueur_VA==4) {$color_VA = '#'.$color_VA{1}.$color_VA{1}.$color_VA{2}.$color_VA{2}.$color_VA{3}.$color_VA{3};}
	if($longueur_A ==4) {$color_A  = '#'.$color_A{1} .$color_A{1} .$color_A{2} .$color_A{2} .$color_A{3} .$color_A{3} ;}
	// Mettre à jour la session + le css perso + la base
	$_SESSION['CSS_BACKGROUND-COLOR']['NA'] = $color_NA;
	$_SESSION['CSS_BACKGROUND-COLOR']['VA'] = $color_VA;
	$_SESSION['CSS_BACKGROUND-COLOR']['A']  = $color_A;
	$_SESSION['CSS_NOTE_STYLE']             = $jeu_codes;
	actualiser_style_session();
	DB_STRUCTURE_modifier_parametres( array('css_background-color_NA'=>$color_NA,'css_background-color_VA'=>$color_VA,'css_background-color_A'=>$color_A,'css_note_style'=>$jeu_codes) );
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
