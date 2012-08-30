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

$action      = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])      : '';
$id          = (isset($_POST['f_id']))          ? Clean::entier($_POST['f_id'])         : 0;
$id_ent      = (isset($_POST['f_id_ent']))      ? Clean::texte($_POST['f_id_ent'])      : '';
$id_gepi     = (isset($_POST['f_id_gepi']))     ? Clean::texte($_POST['f_id_gepi'])     : '';
$sconet_id   = (isset($_POST['f_sconet_id']))   ? Clean::entier($_POST['f_sconet_id'])  : 0;
$reference   = (isset($_POST['f_reference']))   ? Clean::ref($_POST['f_reference'])     : '';
$nom         = (isset($_POST['f_nom']))         ? Clean::nom($_POST['f_nom'])           : '';
$prenom      = (isset($_POST['f_prenom']))      ? Clean::prenom($_POST['f_prenom'])     : '';
$login       = (isset($_POST['f_login']))       ? Clean::login($_POST['f_login'])       : '';
$password    = (isset($_POST['f_password']))    ? Clean::password($_POST['f_password']) : '' ;
$not_new_mdp = (isset($_POST['box_password']))  ? Clean::entier($_POST['box_password']) : 0;
$sortie_date = (isset($_POST['f_sortie_date'])) ? Clean::texte($_POST['f_sortie_date']) : '' ;
$not_exit    = (isset($_POST['box_date']))      ? Clean::entier($_POST['box_date'])     : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouveau professeur
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='ajouter') && $nom && $prenom && $password )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant sconet est disponible (parmi les professeurs de cet établissement)
	if($sconet_id)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('sconet_id',$sconet_id,NULL,'professeur') )
		{
			exit('Erreur : identifiant Sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les professeurs de cet établissement)
	if($reference)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('reference',$reference,NULL,'professeur') )
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Construire le login
	$login = fabriquer_login($prenom,$nom,'professeur');
	// Puis tester le login (parmi tout le personnel de l'établissement)
	if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
	{
		// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
		$login = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_login_disponible($login);
	}
	// Insérer l'enregistrement
	$user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur($sconet_id,$sconet_num=0,$reference,'professeur',$nom,$prenom,$login,crypter_mdp($password),0,$id_ent,$id_gepi);
	// Il peut (déjà !) falloir lui affecter une date de sortie...
	if($not_exit)
	{
		$sortie_date = '-' ;
		$sortie_date_mysql = SORTIE_DEFAUT_MYSQL;
	}
	else
	{
		$sortie_date_mysql = convert_date_french_to_mysql($sortie_date);
		DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $user_id , array(':sortie_date'=>$sortie_date_mysql) );
	}
	// Afficher le retour
	echo'<tr id="id_'.$user_id.'" class="new">';
	echo	'<td class="nu"><input type="checkbox" name="f_ids" value="'.$user_id.'" /></td>';
	echo	'<td class="label">'.html($id_ent).'</td>';
	echo	'<td class="label">'.html($id_gepi).'</td>';
	echo	'<td class="label">'.html($sconet_id).'</td>';
	echo	'<td class="label">'.html($reference).'</td>';
	echo	'<td class="label">'.html($nom).'</td>';
	echo	'<td class="label">'.html($prenom).'</td>';
	echo	'<td class="label new">'.html($login).' <img alt="" title="Pensez à relever le login généré !"  src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="label new">'.html($password).' <img alt="" title="Pensez à noter le mot de passe !" src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="label"><i>'.$sortie_date_mysql.'</i>'.$sortie_date.'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier ce professeur."></q>';
	echo	'</td>';
	echo'</tr>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un professeur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='modifier') && $id && $nom && $prenom && $login && ( $not_new_mdp || $password ) )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent,$id) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi,$id) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant sconet est disponible (parmi les professeurs de cet établissement)
	if($sconet_id)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('sconet_id',$sconet_id,$id,'professeur') )
		{
			exit('Erreur : identifiant Sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les professeurs de cet établissement)
	if($reference)
	{
		if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('reference',$reference,$id,'professeur') )
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Vérifier que le login du professeur est disponible (parmi tout le personnel de l'établissement)
	if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login,$id) )
	{
		exit('Erreur : login déjà existant !');
	}
	// Cas de la date de sortie
	if($not_exit)
	{
		$sortie_date = '-' ;
		$sortie_date_mysql = SORTIE_DEFAUT_MYSQL;
	}
	else
	{
		$sortie_date_mysql = convert_date_french_to_mysql($sortie_date);
	}
	// Mettre à jour l'enregistrement avec ou sans génération d'un nouveau mot de passe
	$tab_donnees = array(':sconet_id'=>$sconet_id,':reference'=>$reference,':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi,':sortie_date'=>$sortie_date_mysql);
	if(!$not_new_mdp)
	{
		$tab_donnees[':password'] = crypter_mdp($password);
	}
	DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id , $tab_donnees );
	// Afficher le retour
	echo'<td class="nu"><input type="checkbox" name="f_ids" value="'.$id.'" /></td>';
	echo'<td class="label">'.html($id_ent).'</td>';
	echo'<td class="label">'.html($id_gepi).'</td>';
	echo'<td class="label">'.html($sconet_id).'</td>';
	echo'<td class="label">'.html($reference).'</td>';
	echo'<td class="label">'.html($nom).'</td>';
	echo'<td class="label">'.html($prenom).'</td>';
	echo'<td class="label">'.html($login).'</td>';
	echo ($not_new_mdp) ? '<td class="label i">champ crypté</td>' : '<td class="label new">'.$password.' <img alt="" src="./_img/bulle_aide.png" title="Pensez à noter le mot de passe !" /></td>' ;
	echo'<td class="label"><i>'.$sortie_date_mysql.'</i>'.$sortie_date.'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce professeur."></q>';
	echo'</td>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là !
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
