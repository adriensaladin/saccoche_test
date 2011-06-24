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

$action     = (isset($_POST['f_action']))     ? clean_texte($_POST['f_action'])      : '';
$id         = (isset($_POST['f_id']))         ? clean_entier($_POST['f_id'])         : 0;
$id_ent     = (isset($_POST['f_id_ent']))     ? clean_texte($_POST['f_id_ent'])      : '';
$id_gepi    = (isset($_POST['f_id_gepi']))    ? clean_texte($_POST['f_id_gepi'])     : '';
$nom        = (isset($_POST['f_nom']))        ? clean_nom($_POST['f_nom'])           : '';
$prenom     = (isset($_POST['f_prenom']))     ? clean_prenom($_POST['f_prenom'])     : '';
$login      = (isset($_POST['f_login']))      ? clean_login($_POST['f_login'])       : '';
$password   = (isset($_POST['f_password']))   ? clean_entier($_POST['f_password'])   : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouvel administrateur
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $nom && $prenom && $login )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		if( DB_STRUCTURE_tester_utilisateur_idENT($id_ent) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_STRUCTURE_tester_utilisateur_idGepi($id_gepi) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le login de l'administrateur est disponible (parmi tout le personnel de l'établissement)
	if( DB_STRUCTURE_tester_login($login) )
	{
		exit('Erreur : login déjà existant !');
	}
	// Construire le password
	$password = fabriquer_mdp();
	// Insérer l'enregistrement
	$user_id = DB_STRUCTURE_ajouter_utilisateur($user_sconet_id=0,$user_sconet_elenoet=0,$reference='','administrateur',$nom,$prenom,$login,$password,$classe_id=0,$id_ent,$id_gepi);
	// Afficher le retour
	echo'<tr id="id_'.$user_id.'" class="new">';
	echo	'<td>'.html($id_ent).'</td>';
	echo	'<td>'.html($id_gepi).'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td>'.html($prenom).'</td>';
	echo	'<td class="new">'.html($login).' <img alt="" title="Pensez à relever le login généré !"  src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="new">'.html($password).' <img alt="" title="Pensez à relever le mot de passe !" src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cet administrateur."></q>';
	echo		'<q class="supprimer" title="Retirer cet administrateur."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un administrateur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $nom && $prenom && $login )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		if( DB_STRUCTURE_tester_utilisateur_idENT($id_ent,$id) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_STRUCTURE_tester_utilisateur_idGepi($id_gepi,$id) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le login de l'administrateur est disponible (parmi tout le personnel de l'établissement)
	if( DB_STRUCTURE_tester_login($login,$id) )
	{
		exit('Erreur : login déjà existant !');
	}
	// Mettre à jour l'enregistrement avec ou sans génération d'un nouveau mot de passe
	$tab_donnees = array(':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi);
	if($password)
	{
		$tab_donnees[':password'] = fabriquer_mdp() ;
	}
	DB_STRUCTURE_modifier_utilisateur( $id , $tab_donnees );
	// Mettre à jour aussi éventuellement la session
	if($id==$_SESSION['USER_ID'])
	{
		$_SESSION['USER_NOM']    = $nom ;
		$_SESSION['USER_PRENOM'] = $prenom ;
	}
	// Afficher le retour
	echo'<td>'.html($id_ent).'</td>';
	echo'<td>'.html($id_gepi).'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td>'.html($prenom).'</td>';
	echo'<td>'.html($login).'</td>';
	echo (!$password) ? '<td class="i">champ crypté</td>' : '<td class="new">'.html($tab_donnees[':password']).' <img alt="" src="./_img/bulle_aide.png" title="Pensez à relever le mot de passe !" /></td>' ;
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce administrateur."></q>';
	echo	($id!=$_SESSION['USER_ID']) ? '<q class="supprimer" title="Retirer cet administrateur."></q>' : '<q class="supprimer_non" title="Un administrateur ne peut pas supprimer son propre compte."></q>' ;
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retirer un administrateur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $id )
{
	if($id==$_SESSION['USER_ID'])
	{
		exit('Erreur : un administrateur ne peut pas supprimer son propre compte !');
	}
	// Supprimer l'enregistrement
	DB_STRUCTURE_supprimer_utilisateur( $id , 'administrateur' );
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
