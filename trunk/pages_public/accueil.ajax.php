<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

$structure_id = (isset($_POST['f_structure'])) ? intval($_POST['f_structure'])        : 0;
$login        = (isset($_POST['f_login']))     ? clean_login($_POST['f_login'])       : '';
$password     = (isset($_POST['f_password']))  ? clean_password($_POST['f_password']) : '';

// PASSWORD_WEBMESTRE : md5 du password du webmestre pour la version de SACoche hébergée sur un serveur Sésamath
// Ceci permet d'effectuer des tests à la place des utilisateurs (recherches d'erreurs)
$filename_webmestre = './__pages_webmestre/_inc/password.php';
if(is_file($filename_webmestre))
{
	include($filename_webmestre);
}
else
{
	define('PASSWORD_WEBMESTRE','sans objet');
}

$password_crypte    = crypter_mdp($password);
$god = ($password_crypte==PASSWORD_WEBMESTRE) ? true : false ;

if( $login && $password )
{
	if($_POST['f_login']=='admin-etabl-SACoche')
	{
		//	Demande de connexion comme administrateur
		connecter_admin($structure_id,$password);
	}
	else
	{
		//	Demande de connexion comme élève ou professeur ou directeur
		connecter_user($structure_id,$login,$password);
	}
	if($_SESSION['PROFIL']!='public')
	{
		// Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
		echo $_SESSION['PROFIL'];
	}
	else
	{
		echo html('L\'identification a échoué (ou le compte est désactivé) !');
	}
}
else
{
	echo'Erreur avec les données transmises !';
}
?>
