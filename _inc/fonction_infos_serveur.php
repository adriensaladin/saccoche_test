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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Commentaires
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_commentaires = array();
$tab_commentaires['version_php']          = 'Version 5.1 ou ultérieure requise.<br \>Avec la version 5.3 des bugs peuvent subsister.';
$tab_commentaires['version_mysql']        = 'Version 5.1 ou ultérieure conseillée.<br \>Version 4.1 ou ultérieure requise.';
$tab_commentaires['max_execution_time']   = 'Par défaut 30 secondes.<br />Une valeur trop faible pourrait gêner les sauvegardes / restaurations de grosses bases.';
$tab_commentaires['memory_limit']         = 'Par défaut 128Mo (bien suffisant).<br />Doit être plus grand que post_max_size (ci-dessous).';
$tab_commentaires['post_max_size']        = 'Par défaut 8Mo.<br />Doit être plus grand que upload_max_filesize (ci-dessous).';
$tab_commentaires['upload_max_filesize']  = 'Par défaut 2Mo.<br />A augmenter si on doit envoyer un fichier d\'une taille supérieure.';
$tab_commentaires['max_allowed_packet']   = 'Par défaut 1Mo (1 048 576 octets).<br />Pour restaurer une sauvegarde, les fichiers contenus dans le zip ne doivent pas dépasser cette taille.';
$tab_commentaires['max_user_connections'] = 'Une valeur inférieure à 5 est susceptible, suivant la charge, de poser problème.';
$tab_commentaires['group_concat_max_len'] = 'Par défaut 1024 octets.<br />Une telle valeur devrait suffire.';
$tab_commentaires['modules_PHP']          = 'Les modules \'dom\' \'gd\' \'mbstring\' \'mysql\' \'PDO\' \'pdo_mysql\' \'session\' \'zip\' \'zlib\' sont requis.<br />Le module \'curl\' est aussi nécessaire pour une identification utilisant le protocole CAS.';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Fonctions de base
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function version_php()
{
	/*
	Retourne le numéro de la version courante de PHP.
	La vérification de la version de PHP est effectuée à chaque appel de SACoche.
	Voir http://fr.php.net/phpversion
	*/
	return phpversion();
}

function version_mysql()
{
	/*
	Retourne une chaîne indiquant la version courante du serveur MySQL.
	Voir http://dev.mysql.com/doc/refman/5.0/fr/information-functions.html
	*/
	$HOST = defined('SACOCHE_STRUCTURE_BD_NAME') ? SACOCHE_STRUCTURE_BD_NAME : SACOCHE_WEBMESTRE_BD_NAME ;
	// Avec une connexion classique style mysql_connect() on peut utiliser mysql_get_server_info() .
	$DB_ROW = DB::queryRow($HOST , 'SELECT VERSION() AS version');
	$version = $DB_ROW['version'];
	$fin = strpos($version,'-');
	return ($fin) ? substr($version,0,$fin) : $version;
}

function max_execution_time()
{
	/*
	Fixe le temps maximal d'exécution d'un script, en secondes.
	Cela permet d'éviter que des scripts en boucles infinies saturent le serveur.
	Lorsque PHP fonctionne depuis la ligne de commande, la valeur par défaut est 0.
	Voir http://fr.php.net/manual/fr/info.configuration.php#ini.max-execution-time
	*/
	$val = ini_get('max_execution_time');
	return ($val) ? $val.'s' : '<b>&infin;</b>' ;
}

function memory_limit()
{
	/*
	Cette option détermine la mémoire limite, en octets, qu'un script est autorisé à allouer.
	Cela permet de prévenir l'utilisation de toute la mémoire par un script mal codé.
	Notez que pour n'avoir aucune limite, vous devez définir cette directive à -1.
	Voir http://fr.php.net/manual/fr/ini.core.php#ini.memory-limit
	*/
	$val = ini_get('memory_limit');
	return ($val!=-1) ? $val : '<b>&infin;</b>' ;
}

function post_max_size()
{
	/*
	Définit la taille maximale (en octets) des données reçues par la méthode POST.
	Cette option affecte également les fichiers chargés.
	Pour charger de gros fichiers, cette valeur doit être plus grande que la valeur de upload_max_filesize.
	Si la limitation de mémoire est activée par votre script de configuration, memory_limit affectera également les fichiers chargés.
	De façon générale, memory_limit doit être plus grand que post_max_size.
	Voir http://fr.php.net/manual/fr/ini.core.php#post_max_size
	*/
	return ini_get('post_max_size');
}

function upload_max_filesize()
{
	/*
	La taille maximale en octets d'un fichier à charger.
	Voir http://fr.php.net/manual/fr/ini.core.php#ini.upload-max-filesize
	*/
	return ini_get('upload_max_filesize');
}

function max_allowed_packet()
{
	/*
	La taille maximale d'un paquet envoyé à MySQL.
	Quand on fait un INSERT multiple, il ne faut pas balancer trop d'enregistrements car si la chaîne dépasse cette limitation (1Mo) alors la requête ne passe pas.
	Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
	*/
	$HOST = defined('SACOCHE_STRUCTURE_BD_NAME') ? SACOCHE_STRUCTURE_BD_NAME : SACOCHE_WEBMESTRE_BD_NAME ;
	$DB_ROW = DB::queryRow($HOST , 'SHOW VARIABLES LIKE "max_allowed_packet"');
	$val = $DB_ROW['Value'];
	return number_format($val,0,'',' ');
}

function max_user_connections()
{
	/*
	Le nombre maximum de connexions actives à MySQL pour un utilisateur particulier (0 = pas de limite).
	Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
	*/
	$HOST = defined('SACOCHE_STRUCTURE_BD_NAME') ? SACOCHE_STRUCTURE_BD_NAME : SACOCHE_WEBMESTRE_BD_NAME ;
	$DB_ROW = DB::queryRow($HOST , 'SHOW VARIABLES LIKE "max_user_connections"');
	$val = $DB_ROW['Value'];
	return ($val) ? $val : '<b>&infin;</b>' ;
}

function group_concat_max_len()
{
	/*
	La taille maximale de la chaîne résultat de GROUP_CONCAT().
	Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
	Pour lever cette limitation on peut effectuer la pré-requête DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...');
	*/
	$HOST = defined('SACOCHE_STRUCTURE_BD_NAME') ? SACOCHE_STRUCTURE_BD_NAME : SACOCHE_WEBMESTRE_BD_NAME ;
	$DB_ROW = DB::queryRow($HOST , 'SHOW VARIABLES LIKE "group_concat_max_len"');
	$val = $DB_ROW['Value'];
	return number_format($val,0,'',' ');
}

function modules_php()
{
	/*
	Liste de tous les modules compilés et chargés.
	La présence des modules requis est effectuée à chaque appel de SACoche.
	Voir http://fr.php.net/get_loaded_extensions
	*/
	$tab_modules = get_loaded_extensions();
	natcasesort($tab_modules);
	return array_values($tab_modules);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Fonctions assemblant les résultats dans un tableau
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function tableau_versions_logicielles()
{
	global $tab_commentaires;
	return'
		<table>
			<thead>
				<tr><th colspan="2">Versions logicielles</th></tr>
			</thead>
			<tbody>
				<tr><td><img alt="" title="'.$tab_commentaires['version_php'].'" src="./_img/bulle_aide.png" /> PHP</td><td class="hc">'.version_php().'</td></tr>
				<tr><td><img alt="" title="'.$tab_commentaires['version_mysql'].'" src="./_img/bulle_aide.png" /> MySQL</td><td class="hc">'.version_mysql().'</td></tr>
			</tbody>
		</table>
	';
}

function tableau_limitations_PHP()
{
	global $tab_commentaires;
	return'
		<table>
			<thead>
				<tr><th colspan="2">Réglage des limitations PHP</th></tr>
			</thead>
			<tbody>
				<tr><td><img alt="" title="'.$tab_commentaires['max_execution_time'].'" src="./_img/bulle_aide.png" /> max execution time</td><td class="hc">'.max_execution_time().'</td></tr>
				<tr><td><img alt="" title="'.$tab_commentaires['memory_limit'].'" src="./_img/bulle_aide.png" /> memory limit</td><td class="hc">'.memory_limit().'</td></tr>
				<tr><td><img alt="" title="'.$tab_commentaires['post_max_size'].'" src="./_img/bulle_aide.png" /> post max size</td><td class="hc">'.post_max_size().'</td></tr>
				<tr><td><img alt="" title="'.$tab_commentaires['upload_max_filesize'].'" src="./_img/bulle_aide.png" /> upload max filesize</td><td class="hc">'.upload_max_filesize().'</td></tr>
			</tbody>
		</table>
	';
}

function tableau_limitations_MySQL()
{
	global $tab_commentaires;
	return'
		<table>
			<thead>
				<tr><th colspan="2">Réglage des limitations MySQL</th></tr>
			</thead>
			<tbody>
				<tr><td><img alt="" title="'.$tab_commentaires['max_allowed_packet'].'" src="./_img/bulle_aide.png" /> max allowed packet</td><td class="hc">'.max_allowed_packet().'</td></tr>
				<tr><td><img alt="" title="'.$tab_commentaires['max_user_connections'].'" src="./_img/bulle_aide.png" /> max user connections</td><td class="hc">'.max_user_connections().'</td></tr>
				<tr><td><img alt="" title="'.$tab_commentaires['group_concat_max_len'].'" src="./_img/bulle_aide.png" /> group concat max len</td><td class="hc">'.group_concat_max_len().'</td></tr>
			</tbody>
		</table>
	';
}

function tableau_modules_PHP($nb_colonnes=3)
{
	global $tab_commentaires;
	$lignes = '';
	$tab_modules = modules_php();
	$nb_modules = count($tab_modules);
	$nb_lignes = ceil($nb_modules/$nb_colonnes);
	for($numero_ligne=0 ; $numero_ligne<$nb_lignes ; $numero_ligne++)
	{
		$lignes .= '<tr>';
		for($numero_colonne=0 ; $numero_colonne<$nb_colonnes ; $numero_colonne++)
		{
			$indice = $numero_colonne*$nb_lignes + $numero_ligne ;
			$lignes .= ($indice<$nb_modules) ? '<td>'.$tab_modules[$indice].'</td>' : '<td></td>' ;
		}
		$lignes .= '</tr>';
	}
	return'
		<table>
			<thead>
				<tr><th colspan="'.$nb_colonnes.'">Modules PHP compilés et chargés <img alt="" title="'.$tab_commentaires['modules_PHP'].'" src="./_img/bulle_aide.png" /></th></tr>
			</thead>
			<tbody>
				'.$lignes.'
			</tbody>
		</table>
	';
}

function tableau_serveur_et_client()
{
	return'
		<table>
			<tbody>
				<tr><th>Identification du serveur</th><td class="hc">'.$_SERVER['SERVER_SOFTWARE'].' &laquo; '.SERVEUR_ADRESSE.' &raquo;</td></tr>
				<tr><th>Identification du client</th><td class="hc">'.$_SERVER['HTTP_USER_AGENT'].'</td></tr>
			</tbody>
		</table>
	';
}

?>