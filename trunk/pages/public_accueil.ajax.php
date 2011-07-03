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

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Afficher un formulaire d'identification
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

function afficher_formulaire_etablissement($BASE,$profil)
{
	$options_structures = afficher_select(DB_WEBMESTRE_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection=$BASE , $optgroup='oui');
	echo'<label class="tab" for="f_base">Établissement :</label><select id="f_base" name="f_base" tabindex="1" >'.$options_structures.'</select><br />'."\r\n";
	echo'<span class="tab"></span><button id="f_choisir" type="button" tabindex="2"><img alt="" src="./_img/bouton/valider.png" /> Choisir cet établissement.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	echo'<input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" />'."\r\n";
}

function afficher_nom_etablissement($BASE,$denomination)
{
	$changer = (HEBERGEUR_INSTALLATION=='multi-structures') ? '&nbsp;&nbsp;&nbsp;<a id="f_changer" href="#"><img alt="" src="./_img/bouton/retourner.png" /> Changer</a>' : '' ;
	echo'<label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="'.$BASE.'" /><em>'.html($denomination).'</em>'.$changer.'<br />'."\r\n";
}

function afficher_formulaire_identification($profil,$mode)
{
	if($profil=='webmestre')
	{
		echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" /><br />'."\r\n";
		echo'<span class="tab"></span><input id="f_login" name="f_login" type="hidden" value="webmestre" /><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="webmestre" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4"><img alt="" src="./_img/bouton/mdp_perso.png" /> Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	}
	elseif($mode=='normal')
	{
		echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" type="text" value="" tabindex="2" /><br />'."\r\n";
		echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" /><br />'."\r\n";
		echo'<span class="tab"></span><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="normal" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4"><img alt="" src="./_img/bouton/mdp_perso.png" /> Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	}
	else
	{
		echo'<label class="tab" for="f_mode">Mode de connexion :</label><label for="f_mode_'.$mode.'"><input type="radio" id="f_mode_'.$mode.'" name="f_mode" value="'.$mode.'" checked /> formulaire <em>ENT</em></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_normal"><input type="radio" id="f_mode_normal" name="f_mode" value="normal" /> formulaire <em>SACoche</em></label><br />'."\r\n";
		echo'<fieldset id="fieldset_normal" class="hide">'."\r\n";
		echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" type="text" value="" tabindex="2" /><br />'."\r\n";
		echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" /><br />'."\r\n";
		echo'</fieldset>'."\r\n";
		echo'<span class="tab"></span><input id="f_profil" name="f_profil" type="hidden" value="normal" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4"><img alt="" src="./_img/bouton/mdp_perso.png" /> Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
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
	// Nettoyer le dossier des vignettes si nécessaire
	effacer_fichiers_temporaires('./__tmp/badge/'.'0' , 525600); // Nettoyer ce dossier des fichiers antérieurs à 1 an
	// Requête pour récupérer la dénomination et le mode de connexion
	$DB_TAB = DB_STRUCTURE_lister_parametres('"denomination","connexion_mode"');
	foreach($DB_TAB as $DB_ROW)
	{
		${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
	}
	if(isset($denomination,$connexion_mode)==false)
	{
		exit('Erreur : base de l\'établissement incomplète !');
	}
	exit( afficher_nom_etablissement($BASE=0,$denomination) . afficher_formulaire_identification($profil,$connexion_mode) );
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
	$DB_ROW = DB_WEBMESTRE_recuperer_structure($BASE);
	if(!count($DB_ROW))
	{
		// Sans doute un établissement supprimé, mais le cookie est encore là
		setcookie(COOKIE_STRUCTURE,'',time()-42000,'/');
		exit('Erreur : établissement non trouvé dans la base d\'administration !');
	}
	afficher_nom_etablissement($BASE,$DB_ROW['structure_denomination']);
	// Mettre à jour la base si nécessaire
	charger_parametres_mysql_supplementaires($BASE);
	maj_base_si_besoin($BASE);
	// Nettoyer le dossier des vignettes si nécessaire
	effacer_fichiers_temporaires('./__tmp/badge/'.$BASE , 525600); // Nettoyer ce dossier des fichiers antérieurs à 1 an
	// Une deuxième requête sur SACOCHE_STRUCTURE_BD_NAME pour savoir si le mode de connexion est SSO ou pas
	$DB_ROW = DB_STRUCTURE_lister_parametres('"connexion_mode"');
	if(!count($DB_ROW))
	{
		exit('Erreur : base de l\'établissement incomplète !');
	}
	exit( afficher_formulaire_identification($profil,$DB_ROW['parametre_valeur']) );
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Traiter une demande d'identification
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

// Pour le webmestre d'un serveur

if( ($action=='identifier') && ($profil=='webmestre') && ($login=='webmestre') && ($password!='') )
{
	exit( connecter_webmestre($password) );
}

// Pour un utilisateur normal, y compris un administrateur

if( ($action=='identifier') && ($profil=='normal') && ($login!='') && ($password!='') )
{
	exit( connecter_user($BASE,$login,$password,$mode_connection='normal') );
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// On ne devrait pas en arriver là...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

exit('Erreur avec les données transmises !');
?>
