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

$action = (isset($_POST['action'])) ? clean_texte($_POST['action']) : '';

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Format des noms d'utilisateurs
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='login')
{
	$tab_profils    = array('directeur','professeur','eleve','parent');
	$tab_parametres = array();
	foreach($tab_profils as $profil)
	{
		// Récupération du champ
		$champ = 'f_login_'.$profil;
		${$champ} = (isset($_POST[$champ])) ? clean_texte($_POST[$champ]) : '' ;
		if(!${$champ})
		{
			exit('Profil '.$profil.' non transmis !');
		}
		// Test du format du champ
		$test_profil = (preg_match("#^p+[._-]?n+$#", ${$champ})) ? 'prenom-puis-nom' : false ;
		$test_profil = (preg_match("#^n+[._-]?p+$#", ${$champ})) ? 'nom-puis-prenom' : $test_profil ;
		if(!$test_profil)
		{
			exit('Profil '.$profil.' mal formaté !');
		}
		$tab_parametres['modele_'.$profil] = ${$champ};
	}
	// Mettre à jour les paramètres dans la base
	DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
	// Mettre aussi à jour la session
	foreach($tab_parametres as $modele => $format)
	{
		$_SESSION[strtoupper($modele)] = $format;
	}
	exit('ok');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Longueur minimale d'un mot de passe
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='mdp_mini')
{
	$mdp_longueur_mini = (isset($_POST['f_mdp_mini'])) ? clean_entier($_POST['f_mdp_mini']) : 0 ;
	if(!$mdp_longueur_mini)
	{
		exit('Valeur non transmise !');
	}
	if( ($mdp_longueur_mini<4) || ($mdp_longueur_mini>8) )
	{
		exit('Valeur transmise incorrecte !');
	}
	// Mettre à jour le paramètre dans la base
	DB_STRUCTURE_COMMUN::DB_modifier_parametres( array('mdp_longueur_mini'=>$mdp_longueur_mini) );
	// Mettre aussi à jour la session
	$_SESSION['MDP_LONGUEUR_MINI'] = $mdp_longueur_mini;
	exit('ok');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	On ne devrait pas en arriver là !
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
