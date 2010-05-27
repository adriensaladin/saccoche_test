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

$action   = (isset($_POST['f_action']))   ? clean_texte($_POST['f_action'])      : '';
$BASE     = (isset($_POST['f_base']))     ? clean_entier($_POST['f_base'])       : 0;
$profil   = (isset($_POST['f_profil']))   ? clean_texte($_POST['f_profil'])      : '';	// normal / administrateur / webmestre
$login    = (isset($_POST['f_login']))    ? clean_login($_POST['f_login'])       : '';
$password = (isset($_POST['f_password'])) ? clean_password($_POST['f_password']) : '';

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Afficher un formulaire d'identification
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

function afficher_formulaire_etablissement($BASE,$profil)
{
	$options_structures = afficher_select(DB_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection=$BASE , $optgroup='oui');
	echo'<label class="tab" for="f_base">Établissement :</label><select id="f_base" name="f_base" tabindex="1" >'.$options_structures.'</select><br />'."\r\n";
	echo'<span class="tab"></span><input id="f_choisir" type="button" value="Choisir cet établissement." tabindex="2" /><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
	echo'<input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" />'."\r\n";
}

function afficher_nom_etablissement($BASE,$denomination)
{
	$changer = (HEBERGEUR_INSTALLATION=='multi-structures') ? '&nbsp;&nbsp;&nbsp;<a href="#" id="structure_changer"><img src="./_img/action_retourner.png" alt="Serveur" /> Changer</a>' : '' ;
	echo'<label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="'.$BASE.'" /><em>'.html($denomination).'</em>'.$changer.'<br />'."\r\n";
}

function afficher_formulaire_identification_webmestre()
{
	echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" /><br />'."\r\n";
	echo'<span class="tab"></span><input id="f_login" name="f_login" type="hidden" value="webmestre" /><input id="f_sso" name="f_sso" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="webmestre" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><input id="f_submit" type="submit" value="Accéder à son espace." tabindex="4" /><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
}

function afficher_formulaire_identification($profil,$sso)
{
	$input_login    = (($sso=='normal')||($profil=='administrateur')) ? 'type="text" value=""' : 'type="text" value="connexion ENT" disabled="disabled"' ;
	$input_password = (($sso=='normal')||($profil=='administrateur')) ? 'type="password" value=""' : 'type="text" value="connexion ENT" disabled="disabled"' ;
	echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" '.$input_login.' tabindex="2" /><br />'."\r\n";
	echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" '.$input_password.' tabindex="3" /><br />'."\r\n";
	echo'<span class="tab"></span><input id="f_sso" name="f_sso" type="hidden" value="'.$sso.'" /><input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><input id="f_submit" type="submit" value="Accéder à son espace." tabindex="4" /><label id="ajax_msg">&nbsp;</label><br />'."\r\n";
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

elseif( ($action=='initialiser') && ($profil=='webmestre') )
{
	afficher_formulaire_identification_webmestre();
}

// Charger le formulaire pour un établissement donné (installation mono-structure)

elseif( ($action=='initialiser') && (HEBERGEUR_INSTALLATION=='mono-structure') && $profil )
{
	$DB_SQL = 'SELECT parametre_nom,parametre_valeur FROM sacoche_parametre ';
	$DB_SQL.= 'WHERE parametre_nom IN("denomination","sso") ';
	$DB_SQL.= 'LIMIT 2';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
	foreach($DB_TAB as $DB_ROW)
	{
		${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
	}
	if(isset($denomination,$sso))
	{
		afficher_nom_etablissement($BASE=0,$denomination);
		afficher_formulaire_identification($profil,$sso);
	}
	else
	{
		exit('Erreur : base de l\'établissement incomplète !');
	}
}

// Charger le formulaire de choix des établissements (installation multi-structures)

elseif( ( ($action=='initialiser') && ($BASE==0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='choisir') && $profil )
{
	afficher_formulaire_etablissement($BASE,$profil);
}

// Charger le formulaire pour un établissement donné (installation multi-structures)

elseif( ( ($action=='initialiser') && ($BASE>0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='charger') && $profil )
{
	// Une première requête sur SACOCHE_WEBMESTRE_BD_NAME pour vérifier que la structure est référencée
	$DB_SQL = 'SELECT structure_denomination FROM sacoche_structure ';
	$DB_SQL.= 'WHERE sacoche_base=:sacoche_base ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':sacoche_base'=>$BASE);
	$DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_ROW))
	{
		// Sans doute un établissement supprimé, mais le cookie est encore là
		setcookie('SACoche-etablissement','',time()-42000,'/');
		exit('Erreur : établissement non trouvé dans la base d\'administration !');
	}
	afficher_nom_etablissement($BASE,$DB_ROW['structure_denomination']);
	// Une deuxième requête sur SACOCHE_STRUCTURE_BD_NAME pour savoir si le mode de connexion est SSO ou pas
	charger_parametres_mysql_supplementaires($BASE);
	$DB_SQL = 'SELECT parametre_nom,parametre_valeur FROM sacoche_parametre ';
	$DB_SQL.= 'WHERE parametre_nom="sso" ';
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
	if(!count($DB_ROW))
	{
		exit('Erreur : base de l\'établissement incomplète !');
	}
	afficher_formulaire_identification($profil,$DB_ROW['parametre_valeur']);
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Traiter une demande d'identification
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

// Pour le webmestre d'un serveur

elseif( ($action=='identifier') && ($profil=='webmestre') && ($login=='webmestre') && $password )
{
	$connexion = connecter_webmestre($password);
	echo ($connexion=='ok') ? $_SESSION['USER_PROFIL'] : $connexion ;
}

// Pour un utilisateur normal, y compris un administrateur

elseif( ($action=='identifier') && ($profil!='webmestre') && $login && $password )
{
	$connexion = connecter_user($BASE,$profil,$login,$password,$sso=false);
	echo ($connexion=='ok') ? $_SESSION['USER_PROFIL'] : $connexion ;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
