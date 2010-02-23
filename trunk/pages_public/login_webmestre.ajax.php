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

// Connexion du webmestre pour accéder à la gestion des établissements de SACoche (serveur Sésamath uniquement).

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$password  = (isset($_POST['f_password']))  ? clean_password($_POST['f_password']) : '';

// PASSWORD_WEBMESTRE : md5 du password du webmestre pour la version de SACoche hébergée sur un serveur Sésamath
$filename_webmestre = './__pages_webmestre/_inc/password.php';
include($filename_webmestre);

$password_crypte = crypter_mdp($password);
$god = ($password_crypte==PASSWORD_WEBMESTRE) ? true : false ;

if($god)
{
	$_SESSION['GOD']              = $god;
	$_SESSION['PROFIL']           = 'webmestre';
	$_SESSION['STRUCTURE_ID']     = 0;
	$_SESSION['STRUCTURE']        = 'Administration du site';
	$_SESSION['USER_ID']          = 0;
	$_SESSION['USER_NOM']         = 'CRESPIN';
	$_SESSION['USER_PRENOM']      = 'Thomas';
	$_SESSION['USER_DESCR']       = '[webmestre] Thomas CRESPIN';
	$_SESSION['SSO']              = 'normal';
	$_SESSION['DUREE_INACTIVITE'] = 30;
	echo'webmestre';
}
else
{
	echo'Mot de passe incorrect !';
}
?>
