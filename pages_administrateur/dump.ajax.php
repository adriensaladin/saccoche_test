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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';

$top_depart = microtime(TRUE);

$dossier_dump   = './__tmp/dump-base/';
$dossier_temp   = './__tmp/dump-base/'.$_SESSION['BASE'].'/';
$dossier_import = './__tmp/import/';

function verifier_dossier($dossier)
{
	$taille_maximale = 0;
	$tab_fichier = scandir($dossier);
	unset($tab_fichier[0],$tab_fichier[1]);	// fichiers '.' et '..'
	foreach($tab_fichier as $fichier_nom)
	{
		$chemin_fichier = $dossier.'/'.$fichier_nom;
		$prefixe   = substr($chemin_fichier,0,13);
		$extension = pathinfo($chemin_fichier,PATHINFO_EXTENSION);
		if( ($prefixe!='dump_sacoche_') && ($extension!='sql') )
		{
			return false;
		}
		$taille_maximale = max( $taille_maximale , filesize($chemin_fichier) );
	}
	return $taille_maximale;
}
function formater_guillemets($val)
{
	return '"'.str_replace('"','\"',$val).'"';
}
function version_base_fichier_svg()
{
	global $dossier_temp;
	$fichier_contenu = file_get_contents($dossier_temp.'dump_sacoche_parametre_0.sql');
	$position_debut = mb_strpos($fichier_contenu,'"version_base"') + 16;
	return mb_substr($fichier_contenu,$position_debut,10);
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Sauvegarder la base
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='sauvegarder')
{
	/*
		Attention ! Augmenter le paramètre suivant peut poser de multiples problèmes !
		D'une part, pour une restauration de secours via phpmyadmin, on risque de dépasser le temps d'exécution autorisé par PHP.
		D'autre part, si la chaine dépasse 1Mo, soit environ 1 million de caractères, lors de l'INSERT multiple ça ne passe plus, même en utilisant mysql_query().
		-> C'est dû au "max_allowed_packet = 1M" présent dans le my.ini
	*/
	// Nombre d'enregistrements à récupérer par "SELECT * FROM table_nom" et donc ensuite inséré par "INSERT INTO table_nom VALUES (...),(...),(...)"
	$limit = 10000;
	// Créer ou vider le dossier temporaire
	if(!is_dir($dossier_temp))
	{
		Creer_Dossier($dossier_temp);
	}
	else
	{
		Vider_Dossier($dossier_temp);
	}
	// Bloquer l'application
	bloquer_application($_SESSION['USER_PROFIL'],'Sauvegarde de la base en cours.');
	// Lister les tables présentes et leur nombre d'enregistrement (pour déterminer le nombre de boucles à effectuer afin de récupérer les données des grosses tables)
	$tab_tables_info = array();
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"');
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_tables_info[] = array( 'Nom'=>$DB_ROW['Name'] , 'Nombre'=>ceil($DB_ROW['Rows']/$limit) );
	}
	// Créer les fichiers sql table par table...
	foreach($tab_tables_info as $tab_table_info)
	{
		$imax = max($tab_table_info['Nombre'],1); // Parcourir au moins une fois la boucle pour une table sans enregistrement
		for($i=0 ; $i<$imax ; $i++)
		{
			$fichier_contenu = '';
			if($i==0)
			{
				// ... la structure
				$fichier_contenu .= 'DROP TABLE IF EXISTS '.$tab_table_info['Nom'].';'."\r\n";
				$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SHOW CREATE TABLE '.$tab_table_info['Nom'] );
				$fichier_contenu .= str_replace('`','',$DB_ROW['Create Table']).';'."\r\n";
			}
			// ... les données
			$tab_ligne_insert = array();
			$from = $i*$limit;
			$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM '.$tab_table_info['Nom'].' LIMIT '.$from.','.$limit );
			if(count($DB_TAB))
			{
				$fichier_contenu .= 'ALTER TABLE '.$tab_table_info['Nom'].' DISABLE KEYS;'."\r\n";
				foreach($DB_TAB as $DB_ROW)
				{
					$DB_ROW = array_map('formater_guillemets',$DB_ROW);
					$tab_ligne_insert[] = '('.implode(',',$DB_ROW).')';
				}
				$fichier_contenu .= 'INSERT INTO '.$tab_table_info['Nom'].' VALUES '."\r\n".implode(','."\r\n",$tab_ligne_insert).';'."\r\n";
				$fichier_contenu .= 'ALTER TABLE '.$tab_table_info['Nom'].' ENABLE KEYS;'."\r\n";
			}
			// Enregistrer le fichier
			$fichier_sql_nom = 'dump_'.$tab_table_info['Nom'].'_'.$i.'.sql';
			file_put_contents($dossier_temp.$fichier_sql_nom,$fichier_contenu);
		}
	}
	// Débloquer l'application
	debloquer_application($_SESSION['USER_PROFIL']);
	// Zipper les fichiers
	$zip = new ZipArchive();
	$fichier_zip_nom = 'dump_SACoche_'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.zip';
	$zip->open($dossier_dump.$fichier_zip_nom, ZIPARCHIVE::CREATE);
	$tab_fichier = scandir($dossier_temp);
	unset($tab_fichier[0],$tab_fichier[1]);	// fichiers '.' et '..'
	foreach($tab_fichier as $fichier_sql_nom)
	{
		$zip->addFile($dossier_temp.$fichier_sql_nom,$fichier_sql_nom);
	}
	$zip->close();
	// Supprimer le dossier temporaire
	Supprimer_Dossier($dossier_temp);
	// Afficher le retour
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Sauvegarde de la base réalisée en '.$duree.'s.</label></li>';
	echo'<li><a class="lien_ext" href="'.$dossier_dump.$fichier_zip_nom.'">Récupérez le fichier de sauvegarde au format ZIP.</a></li>';
	echo'<li><label class="alerte">Attention : pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Uploader et dezipper / vérifier un fichier à restaurer
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

elseif($action=='uploader')
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		exit('<li><label class="alerte">Erreur : problème avec le fichier transmis (taille dépassant probablement post_max_size ) !</label></li>');
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if($extension!='zip')
	{
		exit('<li><label class="alerte">Erreur : l\'extension du fichier transmis est incorrecte !</label></li>');
	}
	$fichier_upload_nom = 'dump_'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.zip';
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_upload_nom))
	{
		exit('<li><label class="alerte">Erreur : le fichier n\'a pas pu être enregistré sur le serveur.</label></li>');
	}
	// Créer ou vider le dossier temporaire
	if(!is_dir($dossier_temp))
	{
		Creer_Dossier($dossier_temp);
	}
	else
	{
		Vider_Dossier($dossier_temp);
	}
	// Dezipper dans le dossier temporaire
	$zip = new ZipArchive();
	if($zip->open($dossier_import.$fichier_upload_nom)!==true)
	{
		exit('<li><label class="alerte">Erreur : votre archive ZIP n\'a pas pu être ouverte !</label></li>');
	}
	$zip->extractTo($dossier_temp);
	$zip->close();
	// Vérifier le contenu : noms des fichiers
	$taille_maximale = verifier_dossier($dossier_temp);
	if(!$taille_maximale)
	{
		Supprimer_Dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP ne semble pas contenir les fichiers d\'une sauvegarde de la base effectuée par SACoche !</label></li>');
	}
	// Vérifier le contenu : taille des requêtes
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SHOW VARIABLES LIKE "max_allowed_packet"');
	$val = $DB_ROW['Value'];
	if($DB_ROW['Value'] < $taille_maximale)
	{
		exit('<li><label class="alerte">Erreur : votre archive ZIP contient au moins un fichier dont la taille dépasse la limitation <em>max_allowed_packet</em> de MySQL !</label></li>');
	}
	// Vérifier le contenu : version de la base compatible avec la version logicielle
	if( version_base_fichier_svg() > VERSION_BASE )
	{
		exit('<li><label class="alerte">Erreur : votre archive ZIP contient une sauvegarde plus récente que celle supportée par cette installation ! Le webmestre doit préalablement mettre à jour le programme...</label></li>');
	}
	// Afficher le retour
	echo'<li><label class="valide">Contenu du fichier récupéré avec succès.</label></li>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Restaurer la base
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif($action=='restaurer')
{
	// Bloquer l'application
	bloquer_application($_SESSION['USER_PROFIL'],'Restauration de la base en cours.');
	// Pour chaque fichier...
	$tab_fichier = scandir($dossier_temp);
	unset($tab_fichier[0],$tab_fichier[1]);	// fichiers '.' et '..'
	natsort($tab_fichier); // Si plusieurs fichiers correspondent à une table, il faut que la requête de création soit lancée avant les requêtes d'insertion
	foreach($tab_fichier as $fichier_nom)
	{
		// ... lancer les requêtes
		$requetes = file_get_contents($dossier_temp.$fichier_nom);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
		/*
		La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
		La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
		*/
		DB::close(SACOCHE_STRUCTURE_BD_NAME);
	}
	// Tester si la base nécessite une mise à jour, et si oui alors la lancer
	$texte_maj = '';
	$version_base_restauree = version_base_fichier_svg();
	if( $version_base_restauree < VERSION_BASE )
	{
		require_once('./_inc/fonction_maj_base.php');
		maj_base($version_base_restauree);
		$texte_maj = ', et base mise à jour,';
	}
	// Débloquer l'application
	debloquer_application($_SESSION['USER_PROFIL']);
	// Supprimer le dossier temporaire
	Supprimer_Dossier($dossier_temp);
	// Afficher le retour
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Restauration de la base réalisée '.$texte_maj.' en '.$duree.'s.</label></li>';
	exit();
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
