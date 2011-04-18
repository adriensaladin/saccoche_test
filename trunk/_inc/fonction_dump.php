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

/*

Ces fonctions ont été isolées dans un fichier car elles servent à 2 endroits : administrateur_dump_ajax et webmestre_transfert_bases_ajax

Ce que j'ai programmé semble propre, efficace et assez rapide.
Néanmoins, plusieurs autres pistes peuvent toujours être explorées :

1. Utiliser "SELECT ... INTO OUTFILE" & "LOAD DATA INFILE"
	=> TB et très rapide sauf que le user MySQL doit avoir le droit de FILE sur le serveur, ce qui est rarement autorisé car pas bon pour la sécurité

2. Utiliser "LOCK TABLES table_nom WRITE" & "UNLOCK TABLES"
	=> Améliore un peu le process sauf que le user MySQL doit avoir le droit de LOCK TABLES sur le serveur, ce qui est n'est pas forcément le cas

3. Sans doute le mieux, et le plus rapide, serait d'utiliser mysqldump en ligne de commande : voir http://dev.mysql.com/doc/refman/5.0/fr/mysqldump.html
	=> Sauf qu'il faut avoir le droit d'utiliser exec() ou shell_exec() ou system() ou passthru()
	=> Sauf que la commande "mysqldump" n'est pas forcément accessible (si j'ai bien compris il faut que le chemin figure dans $_SERVER["PATH"])
	=> Voici un exemple :
		// Pour la sauvegarde
		$commande  = 'mysqldump'; (normalement)
		$commande  = 'M:\WEB\bin\mysql\mysql5.1.36\bin\mysqldump.exe';	// exemple de chamin complet avec wampserver sous windows (http://forum.topflood.com/hebergement/mysql-dump-2711.html)
		$commande .= ' --quick';
		$commande .= ' --add-drop-table';
		$commande .= ' --skip-comments';
		$commande .= ' --disable-keys';
		$commande .= ' --extended-insert';
		$commande .= ' --host='.SACOCHE_STRUCTURE_BD_HOST;
		$commande .= ' --user='.SACOCHE_STRUCTURE_BD_USER;
		$commande .= ' --password='.SACOCHE_STRUCTURE_BD_PASS;
		$commande .= ' '.SACOCHE_STRUCTURE_BD_NAME;
		$commande .= ' '.implode(' ',$tab_tables_base);
		$commande .= ' > '.$dossier.$fichier;
		$exec = exec($commande);
		// Pour la restauration :
		$commande  = 'mysql';
		$commande .= ' '.SACOCHE_STRUCTURE_BD_NAME;
		$commande .= ' < '.$dossier.$fichier;
		$exec = exec($commande);

*/


/**
 * determiner_nombre_lignes_maxi_par_paquet
 * Nombre d'enregistrements à récupérer par "SELECT * FROM table_nom" et donc ensuite inséré par "INSERT INTO table_nom VALUES (...),(...),(...)"
 *
 * Attention ! Augmenter ce paramètre peut poser de multiples problèmes !
 * D'une part, pour une restauration de secours via phpmyadmin, on risque de dépasser le temps d'exécution autorisé par PHP.
 * D'autre part, si la chaine dépasse 1Mo, soit environ 1 million de caractères, lors de l'INSERT multiple ça ne passe plus, même en utilisant mysql_query().
 * -> C'est dû au "max_allowed_packet = 1M" présent dans le my.ini
 * D'où le plafond à 10000 lignes.
 * Enfin, les tests ont montrés qu'il fallait aussi adapter la valeur en fonction de la variable serveur memory_limit.
 * -> pour 16Mo ça coince à partir de 7000 lignes
 * -> pour  8Mo ça coince à partir de 2800 lignes
 *
 * @param void
 * @return int
 */

function determiner_nombre_lignes_maxi_par_paquet()
{
	$memory_limit = (int)ini_get('memory_limit'); // Par exemple 32M => 32 ; -1 si illimité.
	return ($memory_limit==-1) ? 10000 : min(10000,$memory_limit*250) ; // Ainsi 8 => 2000 ; 16 => 4000 ; 32 => 8000 ; -1 => 10000
}

/**
 * formater_guillemets
 * Fonction utilisée avec array_map() pour ajouter des guillemets autour des valeurs et échapper ceux éventuellement présents.
 *
 * @param string $val
 * @return string
 */

function formater_guillemets($val)
{
	return '"'.str_replace('"','\"',$val).'"';
}

/**
 * sauvegarder_tables_base_etablissement
 * Remplir le dossier temporaire avec les fichiers de svg des tables
 *
 * @param string $dossier
 * @param int    $nb_lignes_maxi
 * @return void
 */

function sauvegarder_tables_base_etablissement($dossier_temp,$nb_lignes_maxi)
{
	// Lister les tables présentes et le nombre de boucles à effectuer afin de récupérer les données des grosses tables (nombre d'enregistrements / nb_lignes_maxi)
	$tab_tables_info = array();
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"');
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_tables_info[] = array( 'Nom'=>$DB_ROW['Name'] , 'Nombre'=>ceil($DB_ROW['Rows']/$nb_lignes_maxi) );
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
			$from = $i*$nb_lignes_maxi;
			$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM '.$tab_table_info['Nom'].' LIMIT '.$from.','.$nb_lignes_maxi );
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
			Ecrire_Fichier($dossier_temp.$fichier_sql_nom,$fichier_contenu);
		}
	}
}

/**
 * zipper_fichiers_sauvegarde
 * Zipper les fichiers de svg
 *
 * @param string $dossier_temp
 * @param string $dossier_dump
 * @param string $fichier_zip_nom
 * @return void
 */

function zipper_fichiers_sauvegarde($dossier_temp,$dossier_dump,$fichier_zip_nom)
{
	$zip = new ZipArchive();
	$zip->open($dossier_dump.$fichier_zip_nom, ZIPARCHIVE::CREATE);
	$tab_fichier = Lister_Contenu_Dossier($dossier_temp);
	foreach($tab_fichier as $fichier_sql_nom)
	{
		$zip->addFile($dossier_temp.$fichier_sql_nom,$fichier_sql_nom);
	}
	$zip->close();
}

/**
 * verifier_dossier_decompression_sauvegarde
 * Vérifie les noms des fichiers et renvoie la taille maximale d'un fichier.
 *
 * @param string $dossier
 * @return int
 */

function verifier_dossier_decompression_sauvegarde($dossier)
{
	$fichier_taille_maximale = 0;
	$tab_fichier = Lister_Contenu_Dossier($dossier);
	foreach($tab_fichier as $fichier_nom)
	{
		$prefixe   = substr($fichier_nom,0,13);
		$extension = substr($fichier_nom,-3);
		if( ($prefixe!='dump_sacoche_') || ($extension!='sql') )
		{
			return false;
		}
		$fichier_taille_maximale = max( $fichier_taille_maximale , filesize($dossier.'/'.$fichier_nom) );
	}
	return $fichier_taille_maximale;
}

/**
 * verifier_taille_requetes
 *
 * @param int $fichier_taille_maximale
 * @return bool
 */

function verifier_taille_requetes($fichier_taille_maximale)
{
	$HOST = defined('SACOCHE_STRUCTURE_BD_NAME') ? SACOCHE_STRUCTURE_BD_NAME : SACOCHE_WEBMESTRE_BD_NAME ;
	$DB_ROW = DB::queryRow($HOST , 'SHOW VARIABLES LIKE "max_allowed_packet"');
	return ($fichier_taille_maximale < $DB_ROW['Value']) ? TRUE : FALSE ;
}

/**
 * version_base_fichier_svg
 * Récupère la version de la base sauvegardée dans le fichier (si elle est supérieure à la version logicielle lors de la restauration, ce n'est pas compatible).
 *
 * @param string $dossier
 * @return string
 */

function version_base_fichier_svg($dossier)
{
	$fichier_contenu = file_get_contents($dossier.'dump_sacoche_parametre_0.sql');
	$position_debut = mb_strpos($fichier_contenu,'"version_base"') + 16;
	return mb_substr($fichier_contenu,$position_debut,10);
}

/**
 * restaurer_tables_base_etablissement
 * Restaurer des fichiers de svg et mettre la base à jour si besoin.
 *
 * @param string $dossier_temp
 * @return string                chaine vide ou " et base mise à jour"
 */

function restaurer_tables_base_etablissement($dossier_temp)
{
	// Pour chaque fichier...
	$tab_fichier = Lister_Contenu_Dossier($dossier_temp);
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
	$version_base_restauree = version_base_fichier_svg($dossier_temp);
	if( $version_base_restauree < VERSION_BASE )
	{
		require_once('./_inc/fonction_maj_base.php');
		maj_base($version_base_restauree);
		return' et base mise à jour';
	}
	return'';
}

?>