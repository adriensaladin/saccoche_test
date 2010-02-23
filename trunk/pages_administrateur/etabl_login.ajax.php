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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$f_login_professeur = (isset($_POST['f_login_professeur'])) ? clean_texte($_POST['f_login_professeur']) : '';
$f_login_eleve      = (isset($_POST['f_login_eleve']))      ? clean_texte($_POST['f_login_eleve'])      : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Format des noms d'utilisateurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $f_login_professeur && $f_login_eleve )
{
	$test_professeur = (preg_match("#^p+[._-]?n+$#", $f_login_professeur)) ? 'prenom-puis-nom' : false ;
	$test_professeur = (preg_match("#^n+[._-]?p+$#", $f_login_professeur)) ? 'nom-puis-prenom' : $test_professeur ;
	$test_eleve      = (preg_match("#^p+[._-]?n+$#", $f_login_eleve))      ? 'prenom-puis-nom' : false ;
	$test_eleve      = (preg_match("#^n+[._-]?p+$#", $f_login_eleve))      ? 'nom-puis-prenom' : $test_eleve ;
	if( $test_professeur && $test_eleve )
	{
		DB_modifier_format_login($_SESSION['STRUCTURE_ID'],$f_login_professeur,$f_login_eleve);
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['MODELE_PROF']  = $f_login_professeur;
		$_SESSION['MODELE_ELEVE'] = $f_login_eleve;
		echo'ok';
	}
	else
	{
		echo'Erreur avec les données transmises !';
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
