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

$action   = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$base_id  = (isset($_POST['f_base']))   ? clean_entier($_POST['f_base'])  : 0;
$admin_id = (isset($_POST['f_admin']))  ? clean_entier($_POST['f_admin']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger la liste des administrateurs d'un établissement pour remplir un select (liste d'options)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='maj_admin') && $base_id )
{
	charger_parametres_mysql_supplementaires($base_id);
	exit( afficher_select(DB_OPT_administrateurs_etabl() , $select_nom=false , $option_first='non' , $selection=false , $optgroup='non') );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier le mdp d'un administrateur et envoyer les identifiants par courriel au contact
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='init-mdp-admin') && $base_id && $admin_id )
{
	charger_parametres_mysql_supplementaires($base_id);
	// Informations sur la structure, notamment coordonnées du contact.
	$DB_ROW = DB_recuperer_structure($base_id);
	if(!count($DB_ROW))
	{
		exit('Erreur : structure introuvable !');
	}
	$denomination     = $DB_ROW['structure_denomination'];
	$contact_nom      = $DB_ROW['structure_contact_nom'];
	$contact_prenom   = $DB_ROW['structure_contact_prenom'];
	$contact_courriel = $DB_ROW['structure_contact_courriel'];
	// Informations sur l'admin : nom / prénom / login.
	$DB_TAB = DB_lister_users_cibles($admin_id,$info_classe=false);
	if(!count($DB_TAB))
	{
		exit('Erreur : administrateur introuvable !');
	}
	$admin_nom    = $DB_TAB[0]['user_nom'];
	$admin_prenom = $DB_TAB[0]['user_prenom'];
	$admin_login  = $DB_TAB[0]['user_login'];
	// Initialiser le mdp de l'admin
	$admin_password = fabriquer_mdp();
	DB_modifier_utilisateur($admin_id, array(':password'=>$admin_password) );
	// Envoyer un courriel au contact
	$texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
	$texte.= 'Je viens de réinitialiser le mot de passe de '.$admin_prenom.' '.$admin_nom.', administrateur de SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par '.HEBERGEUR_DENOMINATION.'.'."\r\n\r\n";
	$texte.= 'Pour se connecter comme administrateur, utilisez le lien'."\r\n".SERVEUR_ADRESSE.'?id='.$base_id.'&admin'."\r\n".'et entrez les identifiants'."\r\n".'nom d\'utilisateur " admin "'."\r\n".'mot de passe " '.$password.' "'."\r\n\r\n";
	$texte.= 'On peut changer ce mot de passe depuis l\'espace d\'administration.'."\r\n".'On peut déléguer ce rôle d\'administration, ou créer d\'autres administrateurs.'."\r\n\r\n";
	$texte.= 'Rappel : ce logiciel est mis à votre disposition gratuitement, mais sans aucune garantie, conformément à la licence libre GNU GPL3.'."\r\n".'De plus les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
	$texte.= 'N\'hésitez pas à consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n".'Tout retour quand à votre utilisation sera le bienvenu.'."\r\n\r\n";
	$texte.= 'Cordialement'."\r\n";
	$texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
	$courriel_bilan = envoyer_webmestre_courriel($contact_courriel,'Création compte',$texte,false);
	if(!$courriel_bilan)
	{
		exit('Erreur lors de l\'envoi du courriel !');
	}
	// On affiche le retour
	echo'<ul class="puce">';
	echo'<li>Le mot de passe de <em>'.html($admin_prenom).' '.html($admin_nom).'</em>, administrateur de l\'établissement <em>'.html($denomination).'</em>, vient d\'être réinitialisé.</li>';
	echo'<li>Les nouveaux identifiants ont été envoyés au contact <em>'.html($contact_prenom).' '.html($contact_nom).'</em>, à son adresse de courriel <em>'.html($contact_courriel).'</em>.</li>';
	echo'</ul><p />';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
