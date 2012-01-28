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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action             = (isset($_POST['action']))               ? $_POST['action']                            : '';

$tab_coordonnees    = (isset($_POST['f_coordonnees']))        ? $_POST['f_coordonnees']                     : array();
$infos_responsables = (isset($_POST['f_infos_responsables'])) ? clean_texte($_POST['f_infos_responsables']) : '';
$horizontal_gauche  = (isset($_POST['f_horizontal_gauche']))  ? clean_entier($_POST['f_horizontal_gauche']) : 0;
$horizontal_milieu  = (isset($_POST['f_horizontal_milieu']))  ? clean_entier($_POST['f_horizontal_milieu']) : 0;
$horizontal_droite  = (isset($_POST['f_horizontal_droite']))  ? clean_entier($_POST['f_horizontal_droite']) : 0;
$vertical_haut      = (isset($_POST['f_vertical_haut']))      ? clean_entier($_POST['f_vertical_haut'])     : 0;
$vertical_milieu    = (isset($_POST['f_vertical_milieu']))    ? clean_entier($_POST['f_vertical_milieu'])   : 0;
$vertical_bas       = (isset($_POST['f_vertical_bas']))       ? clean_entier($_POST['f_vertical_bas'])      : 0;
$nombre_exemplaires = (isset($_POST['f_nombre_exemplaires'])) ? clean_texte($_POST['f_nombre_exemplaires']) : '';
$marge_gauche       = (isset($_POST['f_marge_gauche']))       ? clean_entier($_POST['f_marge_gauche'])      : 0;
$marge_droite       = (isset($_POST['f_marge_droite']))       ? clean_entier($_POST['f_marge_droite'])      : 0;
$marge_haut         = (isset($_POST['f_marge_haut']))         ? clean_entier($_POST['f_marge_haut'])        : 0;
$marge_bas          = (isset($_POST['f_marge_bas']))          ? clean_entier($_POST['f_marge_bas'])         : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire form_mise_en_page
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='mise_en_page') && $infos_responsables && $horizontal_gauche && $horizontal_milieu && $horizontal_droite && $vertical_haut && $vertical_milieu && $vertical_bas && $nombre_exemplaires && $marge_gauche && $marge_droite && $marge_haut && $marge_bas )
{
	$tab_parametres = array();
	$tab_parametres['bulletin_infos_etablissement'] = implode(',',$tab_coordonnees);
	$tab_parametres['bulletin_infos_responsables']  = $infos_responsables;
	$tab_parametres['bulletin_nombre_exemplaires']  = $nombre_exemplaires;
	$tab_parametres['bulletin_marge_gauche']        = $marge_gauche;
	$tab_parametres['bulletin_marge_droite']        = $marge_droite;
	$tab_parametres['bulletin_marge_haut']          = $marge_haut;
	$tab_parametres['bulletin_marge_bas']           = $marge_bas;
	if($infos_responsables=='oui_force')
	{
		$tab_parametres['enveloppe_horizontal_gauche'] = $horizontal_gauche;
		$tab_parametres['enveloppe_horizontal_milieu'] = $horizontal_milieu;
		$tab_parametres['enveloppe_horizontal_droite'] = $horizontal_droite;
		$tab_parametres['enveloppe_vertical_haut']     = $vertical_haut;
		$tab_parametres['enveloppe_vertical_milieu']   = $vertical_milieu;
		$tab_parametres['enveloppe_vertical_bas']      = $vertical_bas;
	}
	DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['BULLETIN']['INFOS_ETABLISSEMENT'] = implode(',',$tab_coordonnees) ;
	$_SESSION['BULLETIN']['INFOS_RESPONSABLES']  = $infos_responsables ;
	$_SESSION['BULLETIN']['NOMBRE_EXEMPLAIRES']  = $nombre_exemplaires ;
	$_SESSION['BULLETIN']['MARGE_GAUCHE']        = $marge_gauche ;
	$_SESSION['BULLETIN']['MARGE_DROITE']        = $marge_droite ;
	$_SESSION['BULLETIN']['MARGE_HAUT']          = $marge_haut ;
	$_SESSION['BULLETIN']['MARGE_BAS']           = $marge_bas ;
	if($infos_responsables=='oui_force')
	{
		$_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] = $horizontal_gauche ;
		$_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] = $horizontal_milieu ;
		$_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] = $horizontal_droite ;
		$_SESSION['ENVELOPPE']['VERTICAL_HAUT']     = $vertical_haut ;
		$_SESSION['ENVELOPPE']['VERTICAL_MILIEU']   = $vertical_milieu ;
		$_SESSION['ENVELOPPE']['VERTICAL_BAS']      = $vertical_bas ;
	}
	exit('ok');
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	On ne devrait pas en arriver là...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

else
{
	echo'Erreur avec les données transmises !';
}
?>
