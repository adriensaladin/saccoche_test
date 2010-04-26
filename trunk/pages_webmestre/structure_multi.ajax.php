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

$action           = (isset($_POST['f_action']))           ? clean_texte($_POST['f_action'])              : '';
$base_id          = (isset($_POST['f_base_id']))          ? clean_entier($_POST['f_base_id'])            : 0;
$geo_id           = (isset($_POST['f_geo']))              ? clean_entier($_POST['f_geo'])                : 0;
$localisation     = (isset($_POST['f_localisation']))     ? clean_texte($_POST['f_localisation'])        : '';
$denomination     = (isset($_POST['f_denomination']))     ? clean_texte($_POST['f_denomination'])        : '';
$structure_uai    = (isset($_POST['f_structure_uai']))    ? clean_uai($_POST['f_structure_uai'])         : '';
$contact_nom      = (isset($_POST['f_contact_nom']))      ? clean_nom($_POST['f_contact_nom'])           : '';
$contact_prenom   = (isset($_POST['f_contact_prenom']))   ? clean_prenom($_POST['f_contact_prenom'])     : '';
$contact_courriel = (isset($_POST['f_contact_courriel'])) ? clean_courriel($_POST['f_contact_courriel']) : '';

// On récupère les zones géographiques pour 2 raisons :
// => vérifier que l'identifiant transmis est cohérent
// => pouvoir retourner la cellule correspondante du tableau
if($action!='supprimer')
{
	$DB_TAB = DB_lister_zones();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_geo[$DB_ROW['geo_id']] = array( 'ordre'=>$DB_ROW['geo_nom'] , 'nom'=>$DB_ROW['geo_nom'] );
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouvel établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && isset($tab_geo[$geo_id]) && $localisation && $denomination && $contact_nom && $contact_prenom && $contact_courriel )
{
	if($structure_uai)
	{
		// Vérifier que le n°UAI est disponible
		if( DB_tester_structure_UAI($structure_uai) )
		{
			exit('Erreur : numéro UAI déjà utilisé !');
		}
	}
	// Insérer l'enregistrement dans la base du webmestre
	// Créer le fichier de connexion de la base de données de la structure
	// Créer la base de données de la structure
	// Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
	$base_id = DB_ajouter_structure($geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel);
	// Lancer les requêtes pour créer et remplir les tables
	charger_parametres_mysql_supplementaires($base_id);
	DB_creer_remplir_tables_structure();
	// Personnaliser certains paramètres de la structure
	$tab_parametres = array();
	$tab_parametres['structure_uai'] = $structure_uai;
	$tab_parametres['denomination']  = $denomination;
	DB_modifier_parametres($tab_parametres);
	// Insérer le compte administrateur dans la base de cette structure
	$password = fabriquer_mdp();
	$user_id = DB_ajouter_utilisateur($num_sconet=0,$reference='','administrateur',$contact_nom,$contact_prenom,$login='admin',$password,$classe_id=0,$id_ent='',$id_gepi='');
	// Et lui envoyer un courriel
	$texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
	$texte.= 'Je viens de créer une base SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par '.HEBERGEUR_DENOMINATION.'. Pour accéder au site sans avoir besoin de sélectionner votre établissement, utilisez le lien suivant :'."\r\n".SERVEUR_ADRESSE.'?id='.$base_id."\r\n\r\n";
	$texte.= 'Vous êtes maintenant inscrit comme administrateur de cet établissement sur SACoche. Pour vous connecter comme administrateur, utilisez le lien'."\r\n".SERVEUR_ADRESSE.'?id='.$base_id.'&admin'."\r\n".'et entrez les identifiants'."\r\n".'nom d\'utilisateur " admin "'."\r\n".'mot de passe " '.$password.' "'."\r\n".'(vous pouvez changer ce mot de passe depuis votre espace d\'administration).'."\r\n\r\n";
	$texte.= 'Ce logiciel est mis à votre disposition gratuitement, mais sans aucune garantie, conformément à la licence libre GNU GPL3.'."\r\n".'De plus les administrateurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
	$texte.= 'N\'hésitez pas à consulter la documentation disponible depuis le site du projet :'."\r\n".'http://competences.sesamath.net'."\r\n".'Tout retour quand à votre utilisation sera le bienvenu.'."\r\n\r\n";
	$texte.= 'Cordialement'."\r\n";
	$texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
	$courriel_bilan = envoyer_webmestre_courriel($contact_courriel,'Création compte',$texte,false);
	if(!$courriel_bilan)
	{
		exit('Erreur lors de l\'envoi du courriel !');
	}
	// On affiche le retour
	echo'<tr id="id_'.$base_id.'" class="new">';
	echo	'<td>'.$base_id.'</td>';
	echo	'<td><i>'.sprintf("%02u",$tab_geo[$geo_id]['ordre']).'</i>'.html($tab_geo[$geo_id]['nom']).'</td>';
	echo	'<td>'.html($localisation).'</td>';
	echo	'<td>'.html($denomination).'</td>';
	echo	'<td>'.html($structure_uai).'</td>';
	echo	'<td>'.html($contact_nom).'</td>';
	echo	'<td>'.html($contact_prenom).'</td>';
	echo	'<td>'.html($contact_courriel).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cet établissement."></q>';
	echo		'<q class="supprimer" title="Supprimer cet établissement."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un établissement existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $base_id && isset($tab_geo[$geo_id]) && $localisation && $denomination && $contact_nom && $contact_prenom && $contact_courriel )
{
		// Vérifier que le n°UAI est disponible
	if($structure_uai)
	{
		if( DB_tester_structure_UAI($structure_uai,$base_id) )
		{
			exit('Erreur : numéro UAI déjà utilisé !');
		}
	}
	// On met à jour l'enregistrement dans la base du webmestre
	// Remarque : on laisse les administrateurs maîtres de leur numéro UAI en ne répercutant pas un éventuel changement
	DB_modifier_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel);
	// On affiche le retour
	echo'<td>'.$base_id.'</td>';
	echo'<td><i>'.sprintf("%02u",$tab_geo[$geo_id]['ordre']).'</i>'.html($tab_geo[$geo_id]['nom']).'</td>';
	echo'<td>'.html($localisation).'</td>';
	echo'<td>'.html($denomination).'</td>';
	echo'<td>'.html($structure_uai).'</td>';
	echo'<td>'.html($contact_nom).'</td>';
	echo'<td>'.html($contact_prenom).'</td>';
	echo'<td>'.html($contact_courriel).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cet établissement."></q>';
	echo	'<q class="supprimer" title="Supprimer cet établissement."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une structure existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $base_id )
{
	DB_supprimer_structure($base_id);
	echo'<ok>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
