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

$action   = (isset($_POST['f_action']))   ? clean_texte($_POST['f_action'])      : '';
$BASE     = (isset($_POST['f_base']))     ? clean_entier($_POST['f_base'])       : 0;
$profil   = (isset($_POST['f_profil']))   ? clean_texte($_POST['f_profil'])      : '';	// normal | webmestre
$login    = (isset($_POST['f_login']))    ? clean_login($_POST['f_login'])       : '';
$password = (isset($_POST['f_password'])) ? clean_password($_POST['f_password']) : '';

/*
 * Afficher le formulaire de choix des établissements (installation multi-structures)
 */
function afficher_formulaire_etablissement($BASE,$profil)
{
	$options_structures = Formulaire::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection=$BASE , $optgroup='oui');
	echo'<label class="tab" for="f_base">Établissement :</label><select id="f_base" name="f_base" tabindex="1" >'.$options_structures.'</select><br />'."\r\n";
	echo'<span class="tab"></span><button id="f_choisir" type="button" tabindex="2" class="valider">Choisir cet établissement.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	echo'<input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" />'."\r\n";
}

/*
 * Afficher le nom d'un établissement, avec l'icône pour changer de structure si installation multi-structures
 */
function afficher_nom_etablissement($BASE,$denomination)
{
	$changer = (HEBERGEUR_INSTALLATION=='multi-structures') ? ' - <a id="f_changer" href="#">changer</a>' : '' ;
	echo'<label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="'.$BASE.'" /><em>'.html($denomination).'</em>'.$changer.'<br />'."\r\n";
}

/*
 * Afficher la partie du formulaire spécialement dédiée à l'identification :
 * - pour le webmestre -> seulement la saisie du mot de passe pour le webmestre
 * - si le mode de connexion est normal -> saisie login & mot de passe SACoche
 * - si l'établissement est configuré pour un autre mode de connexion -> au choix [ saisie login & mot de passe SACoche ] ou [ utilisation de l'authentification externe ]
 */
function afficher_formulaire_identification($profil,$mode='normal',$nom='')
{
	if($profil=='webmestre')
	{
		echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" autocomplete="off" /><br />'."\r\n";
		echo'<span class="tab"></span><input id="f_login" name="f_login" type="hidden" value="webmestre" /><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="webmestre" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	}
	elseif($mode=='normal')
	{
		echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" type="text" value="" tabindex="2" autocomplete="off" /><br />'."\r\n";
		echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" autocomplete="off" /><br />'."\r\n";
		echo'<span class="tab"></span><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="normal" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	}
	else
	{
		echo'<label class="tab">Mode de connexion :</label>';
		echo	'<label for="f_mode_normal"><input type="radio" id="f_mode_normal" name="f_mode" value="normal" /> formulaire <em>SACoche</em></label>&nbsp;&nbsp;&nbsp;';
		echo	'<label for="f_mode_'.$mode.'"><input type="radio" id="f_mode_'.$mode.'" name="f_mode" value="'.$mode.'" checked /> authentification extérieure <em>'.html($mode.'-'.$nom).'</em></label><br />'."\r\n";
		echo'<fieldset id="fieldset_normal" class="hide">'."\r\n";
		echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" type="text" value="" tabindex="2" autocomplete="off" /><br />'."\r\n";
		echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" autocomplete="off" /><br />'."\r\n";
		echo'</fieldset>'."\r\n";
		echo'<span class="tab"></span><input id="f_profil" name="f_profil" type="hidden" value="normal" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	}
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Rechercher la dernière version disponible
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='tester_version')
{
	exit( recuperer_numero_derniere_version() );
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Charger un formulaire d'identification
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

// Charger le formulaire pour le webmestre d'un serveur

if( ($action=='initialiser') && ($profil=='webmestre') )
{
	exit( afficher_formulaire_identification($profil,'normal') );
}

// Charger le formulaire pour un établissement donné (installation mono-structure)

if( ($action=='initialiser') && (HEBERGEUR_INSTALLATION=='mono-structure') && $profil )
{
	// Mettre à jour la base si nécessaire
	maj_base_si_besoin($BASE);
	// Requête pour récupérer la dénomination et le mode de connexion
	$DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"denomination","connexion_mode","connexion_nom"');
	foreach($DB_TAB as $DB_ROW)
	{
		${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
	}
	if(isset($denomination,$connexion_mode,$connexion_nom)==false)
	{
		exit('Erreur : base de l\'établissement incomplète !');
	}
	exit( afficher_nom_etablissement($BASE=0,$denomination) . afficher_formulaire_identification($profil,$connexion_mode,$connexion_nom) );
}

// Charger le formulaire de choix des établissements (installation multi-structures)

if( ( ($action=='initialiser') && ($BASE==0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='choisir') && $profil )
{
	exit( afficher_formulaire_etablissement($BASE,$profil) );
}

// Charger le formulaire pour un établissement donné (installation multi-structures)

if( ( ($action=='initialiser') && ($BASE>0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='charger') && $profil )
{
	// Une première requête sur SACOCHE_WEBMESTRE_BD_NAME pour vérifier que la structure est référencée
	$structure_denomination = DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_nom_for_Id($BASE);
	if($structure_denomination===NULL)
	{
		// Sans doute un établissement supprimé, mais le cookie est encore là
		setcookie(COOKIE_STRUCTURE,'',time()-42000,'');
		exit('Erreur : établissement non trouvé dans la base d\'administration !');
	}
	afficher_nom_etablissement($BASE,$structure_denomination);
	// Mettre à jour la base si nécessaire
	charger_parametres_mysql_supplementaires($BASE);
	maj_base_si_besoin($BASE);
	// Une deuxième requête sur SACOCHE_STRUCTURE_BD_NAME pour savoir si le mode de connexion est SSO ou pas
	$DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"connexion_mode","connexion_nom"');
	foreach($DB_TAB as $DB_ROW)
	{
		${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
	}
	if(isset($connexion_mode,$connexion_nom)==false)
	{
		exit('Erreur : base de l\'établissement incomplète !');
	}
	exit( afficher_formulaire_identification($profil,$connexion_mode,$connexion_nom) );
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Traiter une demande d'identification
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

// On en profite pour effacer les fichiers temporaires (pas mis en page d'accueil sinon c'est appelé trop souvent)
if($action=='identifier')
{
	// On essaye de faire en sorte que plusieurs nettoyages ne se lancent pas simultanément (sinon on trouve des warning php dans les logs)
	$fichier_lock = './__tmp/lock.txt';
	if(!file_exists($fichier_lock))
	{
		Ecrire_Fichier($fichier_lock,'');
		effacer_fichiers_temporaires('./__tmp/login-mdp'     ,     10); // Nettoyer ce dossier des fichiers antérieurs à 10 minutes
		effacer_fichiers_temporaires('./__tmp/export'        ,     60); // Nettoyer ce dossier des fichiers antérieurs à 1 heure
		effacer_fichiers_temporaires('./__tmp/dump-base'     ,     60); // Nettoyer ce dossier des fichiers antérieurs à 1 heure
		effacer_fichiers_temporaires('./__tmp/import'        ,  10080); // Nettoyer ce dossier des fichiers antérieurs à 1 semaine
		effacer_fichiers_temporaires('./__tmp/rss/'.$BASE    ,  43800); // Nettoyer ce dossier des fichiers antérieurs à 1 mois
		effacer_fichiers_temporaires('./__tmp/badge/'.$BASE  , 525600); // Nettoyer ce dossier des fichiers antérieurs à 1 an
		effacer_fichiers_temporaires('./__tmp/cookie/'.$BASE , 525600); // Nettoyer ce dossier des fichiers antérieurs à 1 an
		unlink($fichier_lock);
	}
	// Si le fichier témoin du nettoyage existe, on vérifie que sa présence n'est pas anormale (cela s'est déjà produit...)
	else
	{
		if( time() - filemtime($fichier_lock) > 30 )
		{
			unlink($fichier_lock);
		}
	}
}

// Pour le webmestre d'un serveur

if( ($action=='identifier') && ($profil=='webmestre') && ($login=='webmestre') && ($password!='') )
{
	$auth_resultat = tester_authentification_webmestre($password);
	if($auth_resultat=='ok')
	{
		enregistrer_session_webmestre();
	}
	exit($auth_resultat);
}

// Pour un utilisateur normal, y compris un administrateur

if( ($action=='identifier') && ($profil=='normal') && ($login!='') && ($password!='') )
{
	list($auth_resultat,$auth_DB_ROW) = tester_authentification_user($BASE,$login,$password,$mode_connection='normal');
	if($auth_resultat=='ok')
	{
		enregistrer_session_user($BASE,$auth_DB_ROW);
	}
	exit($auth_resultat);
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// On ne devrait pas en arriver là...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

exit('Erreur avec les données transmises !');
?>
