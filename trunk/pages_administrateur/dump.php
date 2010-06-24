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
$TITRE = "Sauvegarde / Restauration";

/*

Ce que j'ai programmé semble propre, efficace et assez rapide.
Néanmoins, plusieurs autres pistes peuvent toujours être explorées :

1. Utiliser "SELECT ... INTO OUTFILE" & "LOAD DATA INFILE"
	=> TB et très rapide sauf que le user MySQL doit avoir le droit de FILE sur le serveur, ce qui est rarement autorisé car pas bon pour la sécurité

2. Utiliser "LOCK TABLES table_nom WRITE" & "UNLOCK TABLES"
	=> Améliore un peu le process sauf que le user MySQL doit avoir le droit de LOCK TABLES sur le serveur, ce qui est n'est pas forcément le cas

3. Le nec plus ultra, et le plus rapide, est d'utiliser mysqldump en ligne de commande : voir http://dev.mysql.com/doc/refman/5.0/fr/mysqldump.html
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
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_dump">DOC : Sauvegarde et restauration de la base</a></span></p>
</p>

<hr />

<h2>Sauvegarder la base</h2>
<form id="form1" action=""><fieldset>
	<span class="tab"></span><input id="f_form1" type="button" value="Lancer la sauvegarde." /><label id="ajax_msg1">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Restaurer la base</h2>
<div class="danger">Restaurer une sauvegarde antérieure écrasera irrémédiablement les données actuelles !</div>
<form id="form2" action=""><fieldset>
	<label class="tab" for="f_form2">Uploader le fichier :</label><input id="f_form2" type="button" value="Parcourir..." /><label id="ajax_msg2">&nbsp;</label>
</fieldset></form>

<hr />

<ul class="puce" id="ajax_info">
</ul>
<p />
