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

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( array_map( 'clean_entier' , explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$action         = (isset($_POST['f_action']))       ? clean_texte($_POST['f_action'])        : '';
$num            = (isset($_POST['num']))            ? (int)$_POST['num']                     : 0 ;	// Numéro de l'étape en cours
$max            = (isset($_POST['max']))            ? (int)$_POST['max']                     : 0 ;	// Nombre d'étapes à effectuer
$courriel_envoi = (isset($_POST['courriel_envoi'])) ? clean_entier($_POST['courriel_envoi']) : 0;

$dossier_import   = './__tmp/import/';
$fichier_csv_nom  = 'ajout_structures_'.mt_rand().'.csv';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Import d'un fichier CSV avec le listing des structures
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='importer_csv')
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur  = $tab_file['tmp_name'];
	$ftaille       = $tab_file['size'];
	$ferreur       = $tab_file['error'];
	// Récupération du fichier
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('Erreur : problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload());
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if(!in_array($extension,array('txt','csv')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_csv_nom))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// On récupère les zones géographiques pour vérifier que l'identifiant transmis est cohérent
	$tab_geo = array();
	$DB_TAB = DB_WEBMESTRE_lister_zones();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_geo[$DB_ROW['geo_id']] = TRUE;
	}
	// Tester si le contenu est correct, et mémoriser les infos en session
	$_SESSION['tab_info'] = array();
	$contenu = file_get_contents($dossier_import.$fichier_csv_nom);
	$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
	$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
	$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
	unset($tab_lignes[0]); // Supprimer la 1e ligne
	$tab_nouvel_uai = array();
	$tab_nouvel_id  = array();
	$nb_lignes_trouvees = 0;
	$tab_erreur = array(
		'info' => array('nb'=>0,'txt'=>' manquant d\'informations !') ,
		'geo'  => array('nb'=>0,'txt'=>' avec identifiant géographique incorrect !') ,
		'uai'  => array('nb'=>0,'txt'=>' avec UAI déjà présent ou en double ou incorrect !') ,
		'mail' => array('nb'=>0,'txt'=>' avec adresse de courriel incorrecte !') ,
		'id'   => array('nb'=>0,'txt'=>' avec identifiant de base déjà utilisé ou en double !')
	);
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		$tab_elements = array_slice($tab_elements,0,8);
		if(count($tab_elements)==8)
		{
			$nb_lignes_trouvees++;
			$tab_elements = array_map('clean_csv',$tab_elements);
			list($import_id,$geo_id,$localisation,$denomination,$uai,$contact_nom,$contact_prenom,$contact_courriel) = $tab_elements;
			$import_id        = clean_entier($import_id);
			$geo_id           = clean_entier($geo_id);
			$localisation     = $localisation; // Ne pas appliquer trim()
			$denomination     = clean_texte($denomination);
			$uai              = clean_uai($uai);
			$contact_nom      = clean_nom($contact_nom);
			$contact_prenom   = clean_prenom($contact_prenom);
			$contact_courriel = clean_courriel($contact_courriel);
			$_SESSION['tab_info'][$nb_lignes_trouvees] = array( 'import_id'=>$import_id , 'geo_id'=>$geo_id , 'localisation'=>$localisation , 'denomination'=>$denomination , 'uai'=>$uai , 'contact_nom'=>$contact_nom , 'contact_prenom'=>$contact_prenom , 'contact_courriel'=>$contact_courriel );
			// Vérifier la présence des informations
			if( !$geo_id || !$localisation || !$denomination || !$contact_nom || !$contact_prenom || !$contact_courriel )
			{
				$tab_erreur['info']['nb']++;
			}
			// Vérifier que l'id géographique est correct
			if(!isset($tab_geo[$geo_id]))
			{
				$tab_erreur['geo']['nb']++;
			}
			// Vérifier que le n°UAI est disponible et correct
			if($uai)
			{
				if( (!tester_UAI($uai)) || (isset($tab_nouvel_uai[$uai])) || DB_WEBMESTRE_tester_structure_UAI($uai) )
				{
					$tab_erreur['uai']['nb']++;
				}
				$tab_nouvel_uai[$uai] = TRUE;
			}
			// Vérifier que l'adresse de courriel est correcte
			if(!tester_courriel($contact_courriel))
			{
				$tab_erreur['mail']['nb']++;
			}
			// Vérifier que l'identifiant est disponible
			if($import_id)
			{
				if((isset($tab_nouvel_id[$import_id])) || count(DB_WEBMESTRE_recuperer_structure($import_id)) )
				{
					$tab_erreur['id']['nb']++;
				}
				$tab_nouvel_id[$import_id] = TRUE;
			}
		}
	}
	unlink($dossier_import.$fichier_csv_nom);
	if(!$nb_lignes_trouvees)
	{
		exit('Erreur : aucune ligne du fichier ne semble correcte !');
	}
	$info_lignes_trouvees = ($nb_lignes_trouvees>1) ? $nb_lignes_trouvees.' lignes trouvées' : '1 ligne trouvée' ;
	foreach($tab_erreur as $key => $tab)
	{
		if($tab['nb'])
		{
			$s = ($tab['nb']>1) ? 's' : '' ;
			exit('Erreur : '.$info_lignes_trouvees.' mais '.$tab['nb'].' ligne'.$s.$tab['txt']);
		}
	}
	exit(']¤['.$info_lignes_trouvees);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape d'ajout d'un nouvel établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='ajouter') && $num && $max )
{
	if(!count($_SESSION['tab_info']))
	{
		exit('Erreur : données du fichier CSV perdues !');
	}
	require('./_inc/fonction_dump.php');
	// Récupérer la série d'infos
	extract($_SESSION['tab_info'][$num]); // import_id / geo_id / localisation / denomination / uai / nom / prenom / courriel
	// Insérer l'enregistrement dans la base du webmestre
	// Créer le fichier de connexion de la base de données de la structure
	// Créer la base de données de la structure
	// Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
	$base_id = DB_WEBMESTRE_ajouter_structure($import_id,$geo_id,$uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel);
	// Créer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires
	Creer_Dossier('./__tmp/badge/'.$base_id);
	Ecrire_Fichier('./__tmp/badge/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	Creer_Dossier('./__tmp/cookie/'.$base_id);
	Ecrire_Fichier('./__tmp/cookie/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	Creer_Dossier('./__tmp/rss/'.$base_id);
	Ecrire_Fichier('./__tmp/rss/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	// Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
	charger_parametres_mysql_supplementaires($base_id);
	// Lancer les requêtes pour créer et remplir les tables
	charger_parametres_mysql_supplementaires($base_id);
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
		$texte.= 'Je viens de créer une base SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'". Pour accéder au site sans avoir besoin de sélectionner votre établissement, utilisez le lien suivant :'."\r\n".SERVEUR_ADRESSE.'/?id='.$base_id."\r\n\r\n";
		$texte.= 'Vous êtes maintenant le contact de votre établissement pour cette installation de SACoche.'."\r\n".'Pour modifier l\'identité de la personne référente, il suffit de me communiquer ses coordonnées.'."\r\n\r\n";
		$texte.= 'Un premier compte administrateur a été créé. Pour se connecter comme administrateur, utiliser le lien'."\r\n".SERVEUR_ADRESSE.'/?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   admin'."\r\n".'mot de passe :   '.$password."\r\n\r\n";
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
	// Mini-ménage si dernier appel
	if($num==$max)
	{
		unset($_SESSION['tab_info']);
	}
	// Retour de l'affichage, appel suivant
	exit(']¤['.'<tr><td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td><td class="label">'.$base_id.'</td><td class="label">'.html($localisation.' | '.$denomination.' ['.$uai.']').'</td><td class="label">'.html($contact_nom.' '.$contact_prenom.' ('.$contact_courriel.')').'</td></tr>');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer plusieurs structures existantes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='supprimer') && $nb_bases )
{
	foreach($tab_base_id as $base_id)
	{
		DB_WEBMESTRE_supprimer_multi_structure($base_id);
	}
	exit('<ok>');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
