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

$f_entree_options = (isset($_POST['f_entree'])) ? clean_texte($_POST['f_entree']) : 'erreur';
$f_pilier_options = (isset($_POST['f_pilier'])) ? clean_texte($_POST['f_pilier']) : 'erreur';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Options de l'environnement élève
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// f_entree_options et f_pilier_options ne peuvent être vides, et doivent contenir la chaine 'prof'
$nettoyage = str_replace( array('directeur','aucunprof','profprincipal','professeur') , '*' , $f_entree_options.','.$f_pilier_options );
$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
$test_options = ( ($nettoyage=='') && (strpos($f_entree_options,'prof')!==false) && (strpos($f_pilier_options,'prof')!==false) ) ? true : false ;

if($test_options)
{
	DB_STRUCTURE_modifier_parametres( array('profil_validation_entree'=>$f_entree_options,'profil_validation_pilier'=>$f_pilier_options) );
	// ne pas oublier de mettre aussi à jour la session
	$_SESSION['PROFIL_VALIDATION_ENTREE'] = $f_entree_options;
	$_SESSION['PROFIL_VALIDATION_PILIER'] = $f_pilier_options;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
