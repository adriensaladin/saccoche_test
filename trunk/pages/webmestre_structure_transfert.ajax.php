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

$action = (isset($_POST['f_action']))  ? clean_texte($_POST['f_action']) : '';	// "exporter" ou "importer_csv" ou "importer_zip" ou "importer"
$num    = (isset($_POST['num']))       ? (int)$_POST['num']              : 0 ;	// Numéro de l'étape en cours
$max    = (isset($_POST['max']))       ? (int)$_POST['max']              : 0 ;	// Nombre d'étapes à effectuer

if( ( ($action=='exporter') && $nb_bases ) || ($action=='importer_csv') || (!isset($_SESSION['alea'])) )
{
	$_SESSION['datetime'] = date('Y-m-d_H-i-s');
	$_SESSION['alea']     = mt_rand();
	$_SESSION['tab_info'] = array();
}

$dossier_dump     = './__tmp/dump-base/';
$dossier_temp_sql = './__tmp/dump-base/'.$_SESSION['alea'].'_sql/'; // Pour les sql d'une base
$dossier_temp_zip = './__tmp/dump-base/'.$_SESSION['alea'].'_zip/'; // Pour les zip des sql des bases (à l'export mais pas à l'import sinon ce dossier n'est pas vidé si l'opération n'arrive pas à son terme).
$dossier_export   = './__tmp/export/';
$dossier_import   = './__tmp/import/';
$fichier_csv_nom  = 'bases_dump_'.$_SESSION['datetime'].'_'.$_SESSION['alea'].'.csv';
$fichier_zip_nom  = 'bases_dump_'.$_SESSION['datetime'].'_'.$_SESSION['alea'].'.zip';

$separateur = ';';

require('./_inc/fonction_dump.php');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des structures avant export des bases
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='exporter') && $nb_bases )
{
	// Mémoriser dans un fichier les données des structures concernées par les stats
	$fichier_texte = 'Id_Export'.$separateur.'Id_Import'.$separateur.'Id_Zone'.$separateur.'Localisation'.$separateur.'Dénomination'.$separateur.'UAI'.$separateur.'Contact_Nom'.$separateur.'Contact_Prénom'.$separateur.'Contact_Courriel'.$separateur.'Date_Inscription'.$separateur.'Nom_fichier'."\r\n";
	$DB_TAB = DB_WEBMESTRE_lister_structures( implode(',',$tab_base_id) );
	foreach($DB_TAB as $DB_ROW)
	{
		$fichier_nom = 'dump_SACoche_'.$DB_ROW['sacoche_base'].'_'.$_SESSION['datetime'].'_'.mt_rand().'.zip';
		$fichier_texte .= $DB_ROW['sacoche_base'].$separateur.$separateur.$DB_ROW['geo_id'].$separateur.$DB_ROW['structure_localisation'].$separateur.$DB_ROW['structure_denomination'].$separateur.$DB_ROW['structure_uai'].$separateur.$DB_ROW['structure_contact_nom'].$separateur.$DB_ROW['structure_contact_prenom'].$separateur.$DB_ROW['structure_contact_courriel'].$separateur.$DB_ROW['structure_inscription_date'].$separateur.$fichier_nom."\r\n";
	}
	Ecrire_Fichier($dossier_export.$fichier_csv_nom,$fichier_texte);
	$max = $nb_bases + 1 ; // La dernière étape consiste à zipper les fichiers de sauvegarde et à faire le ménage.
	// Créer ou vider le dossier temporaire qui contiendra le zip des zip
	Creer_ou_Vider_Dossier($dossier_temp_zip);
	exit(']¤['.$max);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape d'export d'une base
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='exporter') && $num && $max && ($num<$max) )
{
	// Récupérer la ligne de données
	$fichier_texte = file_get_contents($dossier_export.$fichier_csv_nom);
	$tab_ligne = explode("\r\n",$fichier_texte);
	// Récupérer une série d'infos, sachant que seuls $export_id et $fichier_nom sont utiles
	list($export_id,$import_id,$geo_id,$localisation,$denomination,$uai,$contact_nom,$contact_prenom,$contact_courriel,$date,$fichier_nom) = explode($separateur,$tab_ligne[$num]);
	// Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
	charger_parametres_mysql_supplementaires($export_id);
	// Nombre d'enregistrements à récupérer par "SELECT * FROM table_nom" et donc ensuite inséré par "INSERT INTO table_nom VALUES (...),(...),(...)"
	$nb_lignes_maxi = determiner_nombre_lignes_maxi_par_paquet();
	// Créer ou vider le dossier temporaire des sql
	Creer_ou_Vider_Dossier($dossier_temp_sql);
	// Bloquer l'application
	bloquer_application('automate',$export_id,'Sauvegarde de la base en cours.');
	// Remplir le dossier temporaire avec les fichiers de svg des tables
	sauvegarder_tables_base_etablissement($dossier_temp_sql,$nb_lignes_maxi);
	// Débloquer l'application
	debloquer_application('automate',$export_id);
	// Zipper les fichiers de svg
	zipper_fichiers_sauvegarde($dossier_temp_sql,$dossier_temp_zip,$fichier_nom);
	// Appel suivant
	exit(']¤['.'ok');
}
elseif( ($action=='exporter') && $num && $max && ($num==$max) )
{
	// Supprimer le dossier temporaire des sql
	Supprimer_Dossier($dossier_temp_sql);
	// Zipper les zip de svg
	zipper_fichiers_sauvegarde($dossier_temp_zip,$dossier_dump,$fichier_zip_nom);
	// Supprimer le dossier temporaire des zip
	Supprimer_Dossier($dossier_temp_zip);
	// Game over
	unset($_SESSION['datetime'],$_SESSION['alea']);
	exit(']¤['.'ok'.']¤['.$dossier_export.$fichier_csv_nom.']¤['.$dossier_dump.$fichier_zip_nom);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Import d'un fichier CSV avec le listing des bases exportées
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
	$contenu = file_get_contents($dossier_import.$fichier_csv_nom);
	$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
	$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
	$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
	unset($tab_lignes[0]); // Supprimer la 1e ligne
	$tab_nouvel_uai = array();
	$tab_nouvel_id  = array();
	$nb_lignes_trouvees = 0;
	$tab_erreur = array(
		'info'    => array('nb'=>0,'txt'=>' manquant d\'informations !') ,
		'geo'     => array('nb'=>0,'txt'=>' avec identifiant géographique incorrect !') ,
		'uai'     => array('nb'=>0,'txt'=>' avec UAI déjà présent ou en double ou incorrect !') ,
		'mail'    => array('nb'=>0,'txt'=>' avec adresse de courriel incorrecte !') ,
		'date'    => array('nb'=>0,'txt'=>' avec date d\'inscription incorrecte !') ,
		'fichier' => array('nb'=>0,'txt'=>' avec nom de fichier de sauvegarde incorrect !') ,
		'id'      => array('nb'=>0,'txt'=>' avec identifiant de base déjà utilisé ou en double !')
	);
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		$tab_elements = array_slice($tab_elements,0,11);
		if(count($tab_elements)==11)
		{
			$nb_lignes_trouvees++;
			$tab_elements = array_map('clean_csv',$tab_elements);
			list($export_id,$import_id,$geo_id,$localisation,$denomination,$uai,$contact_nom,$contact_prenom,$contact_courriel,$date,$fichier_nom) = $tab_elements;
			$import_id        = clean_entier($import_id);
			$geo_id           = clean_entier($geo_id);
			$localisation     = $localisation; // Ne pas appliquer trim()
			$denomination     = clean_texte($denomination);
			$uai              = clean_uai($uai);
			$contact_nom      = clean_nom($contact_nom);
			$contact_prenom   = clean_prenom($contact_prenom);
			$contact_courriel = clean_courriel($contact_courriel);
			$_SESSION['tab_info'][$nb_lignes_trouvees] = array( 'import_id'=>$import_id , 'geo_id'=>$geo_id , 'localisation'=>$localisation , 'denomination'=>$denomination , 'uai'=>$uai , 'contact_nom'=>$contact_nom , 'contact_prenom'=>$contact_prenom , 'contact_courriel'=>$contact_courriel , 'date'=>$date , 'fichier_nom'=>$fichier_nom );
			// Vérifier la présence des informations
			if( !$geo_id || !$localisation || !$denomination || !$contact_nom || !$contact_prenom || !$contact_courriel || !$date || !$fichier_nom )
			{
				$tab_erreur['info']['nb']++;
			}
			// Vérifier que l'id géographique est correct
			if(!isset($tab_geo[$geo_id]))
			{
				$tab_erreur['geo']['nb']++;
			}
			// Vérifier que le n°UAI est disponible
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
			// Vérifier que la date est correcte
			if(!tester_date($date))
			{
				$tab_erreur['date']['nb']++;
			}
			// Vérifier que le nom de fichier est cohérent
			if( (substr($fichier_nom,0,13)!='dump_SACoche_') || (substr($fichier_nom,-4)!='.zip') )
			{
				$tab_erreur['fichier']['nb']++;
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
	// Nettoyer des restes d'upload de zip éventuels
	foreach($_SESSION['tab_info'] as $key => $tab_infos)
	{
		if(file_exists($dossier_dump.$tab_infos['fichier_nom']))
		{
			unlink($dossier_dump.$tab_infos['fichier_nom']);
		}
	}
	exit(']¤['.$info_lignes_trouvees);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Import d'un fichier ZIP avec le fichier des bases sauvegardées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='importer_zip')
{
	if(!count($_SESSION['tab_info']))
	{
		exit('Erreur : données du fichier CSV perdues !');
	}
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
	if($extension!='zip')
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_zip_nom))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Dezipper dans le dossier dump (pas dans un sous-dossier "temporaire" sinon ce dossier n'est pas vidé si l'opération n'arrive pas à son terme).
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_import.$fichier_zip_nom);
	if($result_open!==true)
	{
		require('./_inc/tableau_zip_error.php');
		exit('<li><label class="alerte">Erreur : votre archive ZIP n\'a pas pu être ouverte ('.$result_open.$tab_zip_error[$result_open].') !</label></li>');
	}
	$zip->extractTo($dossier_dump);
	$zip->close();
	unlink($dossier_import.$fichier_zip_nom);
	// Vérifier le contenu : noms des fichiers
	$tab_fichier = Lister_Contenu_Dossier($dossier_dump);
	$nb_fichiers_introuvables = 0;
	foreach($_SESSION['tab_info'] as $key => $tab_infos)
	{
		if(!in_array($tab_infos['fichier_nom'],$tab_fichier))
		{
			$nb_fichiers_introuvables++;
		}
	}
	if($nb_fichiers_introuvables)
	{
		$s = ($nb_fichiers_introuvables>1) ? 's' : '' ;
		exit('Erreur : '.$nb_fichiers_introuvables.' fichier'.$s.' référencé'.$s.' dans le CSV non trouvé'.$s.' dans le ZIP !');
	}
	// La dernière étape consiste seulement à faire le ménage.
	$max = count($_SESSION['tab_info']) + 1 ;
	exit(']¤['.$max);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape d'import d'une base
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='importer') && $num && $max && ($num<$max) )
{
	if(!count($_SESSION['tab_info']))
	{
		exit('Erreur : données du fichier CSV perdues !');
	}
	// Récupérer la série d'infos
	extract($_SESSION['tab_info'][$num]); // import_id / geo_id / localisation / denomination / uai / contact_nom / contact_prenom / contact_courriel / date / fichier_nom
	// Préparer le retour en cas de pb
	$retour_cellules_non = '<td class="nu"></td><td>---</td><td>'.html($localisation.' | '.$denomination).'</td><td>'.html($contact_nom.' '.$contact_prenom).'</td>';
	// Créer ou vider le dossier temporaire
	Creer_ou_Vider_Dossier($dossier_temp_sql);
	// Dezipper dans le dossier temporaire
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_dump.$fichier_nom);
	if($result_open!==true)
	{
		require('./_inc/tableau_zip_error.php');
		exit(']¤['.'<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : fichiers de '.html($fichier_nom).' impossible à extraire ('.$result_open.$tab_zip_error[$result_open].') !</label></td>'.'</tr>');
	}
	$zip->extractTo($dossier_temp_sql);
	$zip->close();
	// Vérifier le contenu : noms des fichiers
	$fichier_taille_maximale = verifier_dossier_decompression_sauvegarde($dossier_temp_sql);
	if(!$fichier_taille_maximale)
	{
		Supprimer_Dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit(']¤['.'<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : le contenu de '.html($fichier_nom).' ne semble pas être une sauvegarde de base !</label></td>'.'</tr>');
	}
	// Vérifier le contenu : taille des requêtes
	if( !verifier_taille_requetes($fichier_taille_maximale) )
	{
		Supprimer_Dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit(']¤['.'<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : '.html($fichier_nom).' contient au moins un fichier dont la taille dépasse la limitation <em>max_allowed_packet</em> de MySQL !</label></td>'.'</tr>');
	}
	// Vérifier le contenu : version de la base compatible avec la version logicielle
	if( version_base_fichier_svg($dossier_temp_sql) > VERSION_BASE )
	{
		Supprimer_Dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit(']¤['.'<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : '.html($fichier_nom).' contient une sauvegarde plus récente que celle supportée par cette installation ! Il faut mettre à jour SACoche.</label></td>'.'</tr>');
	}
	// Insérer l'enregistrement dans la base du webmestre
	// Créer le fichier de connexion de la base de données de la structure
	// Créer la base de données de la structure
	// Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
	$base_id = DB_WEBMESTRE_ajouter_structure($import_id,$geo_id,$uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel,$date);
	// Créer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires
	Creer_Dossier('./__tmp/badge/'.$base_id);
	Ecrire_Fichier('./__tmp/badge/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	Creer_Dossier('./__tmp/cookie/'.$base_id);
	Ecrire_Fichier('./__tmp/cookie/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	Creer_Dossier('./__tmp/rss/'.$base_id);
	Ecrire_Fichier('./__tmp/rss/'.$base_id.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	// Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
	charger_parametres_mysql_supplementaires($base_id);
	// Restaurer des fichiers de svg et mettre la base à jour si besoin.
	$texte_maj = restaurer_tables_base_etablissement($dossier_temp_sql);
	// Supprimer le dossier temporaire
	Supprimer_Dossier($dossier_temp_sql);
	// Retour du succès, appel suivant
	$retour_cellules_oui = '<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td><td class="label">'.$base_id.'</td><td class="label">'.html($localisation.' | '.$denomination.' ['.$uai.']').'</td><td class="label">'.html($contact_nom.' '.$contact_prenom.' ('.$contact_courriel.')').'</td>';
	exit(']¤['.'<tr>'.$retour_cellules_oui.'<td class="label"><label class="valide">Restauration de la base réalisée'.$texte_maj.' avec succès.</label></td>'.'</tr>');
}
elseif( ($action=='importer') && $num && $max && ($num==$max) )
{
	// Supprimer les fichiers zip des bases
	foreach($_SESSION['tab_info'] as $key => $tab_infos)
	{
		unlink($dossier_dump.$tab_infos['fichier_nom']);
	}
	// Game over
	unset($_SESSION['datetime'],$_SESSION['alea'],$_SESSION['tab_info']);
	exit(']¤['.'ok');
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
// On ne devrait pas en arriver là
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');
?>
