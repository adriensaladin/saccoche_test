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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))     ? clean_texte($_POST['f_action'])      : '';
$id         = (isset($_POST['f_id']))         ? clean_entier($_POST['f_id'])         : 0;
$id_ent     = (isset($_POST['f_id_ent']))     ? clean_texte($_POST['f_id_ent'])      : '';
$id_gepi    = (isset($_POST['f_id_gepi']))    ? clean_texte($_POST['f_id_gepi'])     : '';
$num_sconet = (isset($_POST['f_num_sconet'])) ? clean_entier($_POST['f_num_sconet']) : 0;
$reference  = (isset($_POST['f_reference']))  ? clean_ref($_POST['f_reference'])     : '';
$nom        = (isset($_POST['f_nom']))        ? clean_nom($_POST['f_nom'])           : '';
$prenom     = (isset($_POST['f_prenom']))     ? clean_prenom($_POST['f_prenom'])     : '';
$login      = (isset($_POST['f_login']))      ? clean_login($_POST['f_login'])       : '';
$password   = (isset($_POST['f_password']))   ? clean_entier($_POST['f_password'])   : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouveau professeur
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $nom && $prenom )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		if( DB_tester_utilisateur_idENT($id_ent) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_tester_utilisateur_idGepi($id_gepi) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le n° sconet est disponible (parmi les professeurs de cet établissement)
	if($num_sconet)
	{
		if( DB_tester_utilisateur_numSconet($num_sconet,'professeur') )
		{
			exit('Erreur : n° sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les professeurs de cet établissement)
	if($reference)
	{
		if( DB_tester_utilisateur_reference($reference,'professeur') )
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Construire le login
	$login = fabriquer_login($prenom,$nom,'professeur');
	// Puis tester le login (parmi tout le personnel de l'établissement)
	if( DB_tester_login($login) )
	{
		// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
		$login = DB_rechercher_login_disponible($login);
	}
	// Construire le password
	$password = fabriquer_mdp();
	// Insérer l'enregistrement
	$user_id = DB_ajouter_utilisateur($num_sconet,$reference,'professeur',$nom,$prenom,$login,$password,0,$id_ent,$id_gepi);
	// Afficher le retour
	echo'<tr id="id_'.$user_id.'" class="new">';
	echo	'<td>'.html($id_ent).'</td>';
	echo	'<td>'.html($id_gepi).'</td>';
	echo	'<td>'.html($num_sconet).'</td>';
	echo	'<td>'.html($reference).'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td>'.html($prenom).'</td>';
	echo	'<td class="new">'.html($login).' <img alt="" title="Pensez à relever le login généré !"  src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="new">'.html($password).' <img alt="" title="Pensez à relever le mot de passe !" src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier ce professeur."></q>';
	echo		'<q class="desactiver" title="Enlever ce professeur."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un professeur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $nom && $prenom && $login )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		if( DB_tester_utilisateur_idENT($id_ent,$id) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_tester_utilisateur_idGepi($id_gepi,$id) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le n° sconet est disponible (parmi les professeurs de cet établissement)
	if($num_sconet)
	{
		if( DB_tester_utilisateur_numSconet($num_sconet,'professeur',$id) )
		{
			exit('Erreur : n° sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les professeurs de cet établissement)
	if($reference)
	{
		if( DB_tester_utilisateur_reference($reference,'professeur',$id) )
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Vérifier que le login du professeur est disponible (parmi tout le personnel de l'établissement)
	if( DB_tester_login($login,$id) )
	{
		exit('Erreur : login déjà existant !');
	}
	// Mettre à jour l'enregistrement avec ou sans génération d'un nouveau mot de passe
	$tab_donnees = array(':num_sconet'=>$num_sconet,':reference'=>$reference,':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi);
	if($password)
	{
		$tab_donnees[':password'] = fabriquer_mdp() ;
	}
	DB_modifier_utilisateur( $id , $tab_donnees );
	// Afficher le retour
	echo'<td>'.html($id_ent).'</td>';
	echo'<td>'.html($id_gepi).'</td>';
	echo'<td>'.html($num_sconet).'</td>';
	echo'<td>'.html($reference).'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td>'.html($prenom).'</td>';
	echo'<td>'.html($login).'</td>';
	echo (!$password) ? '<td class="i">champ crypté</td>' : '<td class="new">'.html($tab_donnees[':password']).' <img alt="" src="./_img/bulle_aide.png" title="Pensez à relever le mot de passe !" /></td>' ;
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce professeur."></q>';
	echo	'<q class="desactiver" title="Enlever ce professeur."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Désactiver un professeur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='desactiver') && $id )
{
	// Mettre à jour l'enregistrement
	DB_modifier_utilisateur( $id , array(':statut'=>0) );
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
