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

$action           = (isset($_POST['f_action']))           ? clean_texte($_POST['f_action'])              : '';
$base_id          = (isset($_POST['f_base_id']))          ? clean_entier($_POST['f_base_id'])            : 0;
$listing_base_id  = (isset($_POST['f_listing_id']))       ? $_POST['f_listing_id']                       : '';
$geo_id           = (isset($_POST['f_geo']))              ? clean_entier($_POST['f_geo'])                : 0;
$localisation     = (isset($_POST['f_localisation']))     ? $_POST['f_localisation']                     : ''; // Ne pas appliquer trim()
$denomination     = (isset($_POST['f_denomination']))     ? clean_texte($_POST['f_denomination'])        : '';
$uai              = (isset($_POST['f_uai']))              ? clean_uai($_POST['f_uai'])                   : '';
$contact_nom      = (isset($_POST['f_contact_nom']))      ? clean_nom($_POST['f_contact_nom'])           : '';
$contact_prenom   = (isset($_POST['f_contact_prenom']))   ? clean_prenom($_POST['f_contact_prenom'])     : '';
$contact_courriel = (isset($_POST['f_contact_courriel'])) ? clean_courriel($_POST['f_contact_courriel']) : '';
$courriel_envoi   = (isset($_POST['f_courriel_envoi']))   ? clean_entier($_POST['f_courriel_envoi'])     : 0;
$admin_id         = (isset($_POST['f_admin_id']))         ? clean_entier($_POST['f_admin_id'])           : 0;

// On récupère les zones géographiques pour 2 raisons :
// => vérifier que l'identifiant transmis est cohérent
// => pouvoir retourner la cellule correspondante du tableau
if( ($action!='supprimer') && ($action!='lister_admin') && ($action!='initialiser_mdp') )
{
	$DB_TAB = DB_WEBMESTRE_lister_zones();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_geo[$DB_ROW['geo_id']] = array( 'ordre'=>$DB_ROW['geo_ordre'] , 'nom'=>$DB_ROW['geo_nom'] );
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouvel établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='ajouter') && isset($tab_geo[$geo_id]) && $localisation && $denomination && $contact_nom && $contact_prenom && $contact_courriel )
{
	// Vérifier que le n°UAI est disponible
	if($uai)
	{
		if( DB_WEBMESTRE_tester_structure_UAI($uai) )
		{
			exit('Erreur : numéro UAI déjà utilisé !');
		}
	}
	// Insérer l'enregistrement dans la base du webmestre
	// Créer le fichier de connexion de la base de données de la structure
	// Créer la base de données de la structure
	// Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
	$base_id = DB_WEBMESTRE_ajouter_structure($base_id=0,$geo_id,$uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel);
	// Créer un dossier pour accueillir les vignettes verticales avec l'identité des élèves
	Creer_Dossier('./__tmp/badge/'.$base_id);
	Ecrire_Fichier('./__tmp/badge/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	// Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
	charger_parametres_mysql_supplementaires($base_id);
	// Lancer les requêtes pour créer et remplir les tables
	DB_STRUCTURE_creer_remplir_tables_structure('./_sql/structure/');
	// Il est arrivé que la fonction DB_STRUCTURE_modifier_parametres() retourne une erreur disant que la table n'existe pas.
	// Comme si les requêtes de DB_STRUCTURE_creer_remplir_tables_structure() étaient en cache, et pas encore toutes passées (parcequ'au final, quand on va voir la base, toutes les tables sont bien là).
	// Est-ce que c'est possible au vu du fonctionnement de la classe de connexion ? Et, bien sûr, y a-t-il quelque chose à faire pour éviter ce problème ?
	// En attendant une réponse de SebR, j'ai mis ce sleep(1)... sans trop savoir si cela pouvait aider...
	@sleep(1);
	// Personnaliser certains paramètres de la structure
	$tab_parametres = array();
	$tab_parametres['version_base'] = VERSION_BASE;
	$tab_parametres['uai']          = $uai;
	$tab_parametres['denomination'] = $denomination;
	DB_STRUCTURE_modifier_parametres($tab_parametres);
	// Insérer le compte administrateur dans la base de cette structure
	$password = fabriquer_mdp();
	$user_id = DB_STRUCTURE_ajouter_utilisateur($user_sconet_id=0,$user_sconet_elenoet=0,$reference='','administrateur',$contact_nom,$contact_prenom,$login='admin',$password,$classe_id=0,$id_ent='',$id_gepi='');
	// Et lui envoyer un courriel
	if($courriel_envoi)
	{
		$texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
		$texte.= 'Je viens de créer une base SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'". Pour accéder au site sans avoir besoin de sélectionner votre établissement, utilisez le lien suivant :'."\r\n".SERVEUR_ADRESSE.'?id='.$base_id."\r\n\r\n";
		$texte.= 'Vous êtes maintenant le contact de votre établissement pour cette installation de SACoche.'."\r\n".'Pour modifier l\'identité de la personne référente, il suffit de me communiquer ses coordonnées.'."\r\n\r\n";
		$texte.= 'Un premier compte administrateur a été créé. Pour se connecter comme administrateur, utiliser le lien'."\r\n".SERVEUR_ADRESSE.'?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   admin'."\r\n".'mot de passe :   '.$password."\r\n\r\n";
		$texte.= 'Ces identifiants sont modifiables depuis l\'espace d\'administration.'."\r\n".'Un administrateur peut déléguer son rôle en créant d\'autres administrateurs.'."\r\n\r\n";
		$texte.= 'Ce logiciel est mis à votre disposition gratuitement, mais sans garantie, conformément à la licence libre GNU GPL3.'."\r\n".'Les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
		$texte.= 'Merci de consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n\r\n";
		$texte.= 'Cordialement'."\r\n";
		$texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
		$courriel_bilan = envoyer_webmestre_courriel($contact_courriel,'Création compte',$texte,false);
		if(!$courriel_bilan)
		{
			exit('Erreur lors de l\'envoi du courriel !');
		}
	}
	// On affiche le retour
	echo'<tr id="id_'.$base_id.'" class="new">';
	echo	'<td class="nu"><a href="#id_0"><img class="bloquer" src="./_img/blocage_non.png" title="Bloquer cet établissement." /></a></td>';
	echo	'<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td>';
	echo	'<td class="label">'.$base_id.'</td>';
	echo	'<td class="label"><i>'.sprintf("%02u",$tab_geo[$geo_id]['ordre']).'</i>'.html($tab_geo[$geo_id]['nom']).'</td>';
	echo	'<td class="label">'.html($localisation).'<br />'.html($denomination).'</td>';
	echo	'<td class="label">'.html($uai).'</td>';
	echo	'<td class="label">'.html($contact_nom).'<br />'.html($contact_prenom).'</td>';
	echo	'<td class="label">'.html($contact_courriel).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cet établissement."></q>';
	echo		'<q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q>';
	echo		'<q class="supprimer" title="Supprimer cet établissement."></q>';
	echo	'</td>';
	echo'</tr>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un établissement existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='modifier') && $base_id && isset($tab_geo[$geo_id]) && $localisation && $denomination && $contact_nom && $contact_prenom && $contact_courriel )
{
		// Vérifier que le n°UAI est disponible
	if($uai)
	{
		if( DB_WEBMESTRE_tester_structure_UAI($uai,$base_id) )
		{
			exit('Erreur : numéro UAI déjà utilisé !');
		}
	}
	// On met à jour l'enregistrement dans la base du webmestre
	DB_WEBMESTRE_modifier_structure($base_id,$geo_id,$uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel);
	// On met à jour l'enregistrement dans la base de la structure
	charger_parametres_mysql_supplementaires($base_id);
	$tab_parametres = array();
	$tab_parametres['uai']          = $uai;
	$tab_parametres['denomination'] = $denomination;
	DB_STRUCTURE_modifier_parametres($tab_parametres);
	// On affiche le retour
	$img = (!is_file($CHEMIN_CONFIG.'blocage_webmestre_'.$base_id.'.txt')) ? '<img class="bloquer" src="./_img/blocage_non.png" title="Bloquer cet établissement." />' : '<img class="debloquer" src="./_img/blocage_oui.png" title="Débloquer cet établissement." />' ;
	echo'<td class="nu"><a href="#id_0">'.$img.'</a></td>';
	echo'<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td>';
	echo'<td class="label">'.$base_id.'</td>';
	echo'<td class="label"><i>'.sprintf("%02u",$tab_geo[$geo_id]['ordre']).'</i>'.html($tab_geo[$geo_id]['nom']).'</td>';
	echo'<td class="label">'.html($localisation).'<br />'.html($denomination).'</td>';
	echo'<td class="label">'.html($uai).'</td>';
	echo'<td class="label">'.html($contact_nom).'<br />'.html($contact_prenom).'</td>';
	echo'<td class="label">'.html($contact_courriel).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cet établissement."></q>';
	echo	'<q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q>';
	echo	'<q class="supprimer" title="Supprimer cet établissement."></q>';
	echo'</td>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger la liste des administrateurs d'un établissement pour remplir un select (liste d'options)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='lister_admin') && $base_id )
{
	charger_parametres_mysql_supplementaires($base_id);
	exit( afficher_select(DB_STRUCTURE_OPT_administrateurs_etabl() , $select_nom=false , $option_first='non' , $selection=false , $optgroup='non') );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier le mdp d'un administrateur et envoyer les identifiants par courriel au contact
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='initialiser_mdp') && $base_id && $admin_id )
{
	charger_parametres_mysql_supplementaires($base_id);
	// Informations sur la structure, notamment coordonnées du contact.
	$DB_ROW = DB_WEBMESTRE_recuperer_structure($base_id);
	if(!count($DB_ROW))
	{
		exit('Erreur : structure introuvable !');
	}
	$denomination     = $DB_ROW['structure_denomination'];
	$contact_nom      = $DB_ROW['structure_contact_nom'];
	$contact_prenom   = $DB_ROW['structure_contact_prenom'];
	$contact_courriel = $DB_ROW['structure_contact_courriel'];
	// Informations sur l'admin : nom / prénom / login.
	$DB_TAB = DB_STRUCTURE_lister_users_cibles($admin_id,$info_classe=false);
	if(!count($DB_TAB))
	{
		exit('Erreur : administrateur introuvable !');
	}
	$admin_nom    = $DB_TAB[0]['user_nom'];
	$admin_prenom = $DB_TAB[0]['user_prenom'];
	$admin_login  = $DB_TAB[0]['user_login'];
	// Générer un nouveau mdp de l'admin
	$admin_password = fabriquer_mdp();
	DB_STRUCTURE_modifier_utilisateur($admin_id, array(':password'=>$admin_password) );
	// Envoyer un courriel au contact
	$texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
	$texte.= 'Je viens de réinitialiser le mot de passe de '.$admin_prenom.' '.$admin_nom.', administrateur de SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'".'."\r\n\r\n";
	$texte.= 'Pour se connecter, cet administrateur doit utiliser le lien'."\r\n".SERVEUR_ADRESSE.'?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   '.$admin_login."\r\n".'mot de passe :   '.$admin_password."\r\n\r\n";
	$texte.= 'Ces identifiants sont modifiables depuis l\'espace d\'administration.'."\r\n".'Un administrateur peut déléguer son rôle en créant d\'autres administrateurs.'."\r\n\r\n";
	$texte.= 'Rappel : ce logiciel est mis à votre disposition gratuitement, mais sans garantie, conformément à la licence libre GNU GPL3.'."\r\n".'Les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
	$texte.= 'Merci de consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n\r\n";
	$texte.= 'Cordialement'."\r\n";
	$texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
	$courriel_bilan = envoyer_webmestre_courriel($contact_courriel,'Modification mdp administrateur',$texte,false);
	if(!$courriel_bilan)
	{
		exit('Erreur lors de l\'envoi du courriel !');
	}
	// On affiche le retour
	echo'<ok>';
	echo'Le mot de passe de '.html($admin_prenom).' '.html($admin_nom).',<BR />administrateur de l\'établissement '.html($denomination).',<BR />vient d\'être réinitialisé.<BR /><BR />';
	echo'Les nouveaux identifiants ont été envoyés au contact '.html($contact_prenom).' '.html($contact_nom).',<BR />à son adresse de courriel '.html($contact_courriel).'.';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une structure existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='supprimer') && $base_id )
{
	DB_WEBMESTRE_supprimer_multi_structure($base_id);
	exit('<ok>');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer plusieurs structures existantes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='supprimer') && $listing_base_id )
{
	$tab_base_id = array_filter( array_map( 'clean_entier' , explode(',',$listing_base_id) ) , 'positif' );
	foreach($tab_base_id as $base_id)
	{
		DB_WEBMESTRE_supprimer_multi_structure($base_id);
	}
	exit('<ok>');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Bloquer les accès à une structure
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='bloquer') && $base_id )
{
	bloquer_application($_SESSION['USER_PROFIL'],$base_id,'Action ciblée ; contacter le webmestre pour obtenir des précisions.');
	exit('<img class="debloquer" src="./_img/blocage_oui.png" title="Débloquer cet établissement." />');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Débloquer les accès à une structure
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='debloquer') && $base_id )
{
	debloquer_application($_SESSION['USER_PROFIL'],$base_id);
	exit('<img class="bloquer" src="./_img/blocage_non.png" title="Bloquer cet établissement." />');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
