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
?>

<form action="./index.php?page=webmestre_database_test" method="post">
	<p class="ti"><input id="lancer_test" name="lancer_test" type="hidden" value="go" /><button id="bouton_newsletter" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Lancer le test.</button></p>
</form>

<p class="astuce">Cette page de test est encore en développement...</p>

<hr />

<style type="text/css">
	pre.b{color:blue}
	pre.r{color:red}
	pre.v{color:green}
</style>

<?php

if(isset($_POST['lancer_test']))
{
	error_reporting(E_ALL);
	define('SACOCHE_STRUCTURE_BD_HOST',SACOCHE_WEBMESTRE_BD_HOST);
	define('SACOCHE_STRUCTURE_BD_PORT',SACOCHE_WEBMESTRE_BD_PORT);
	define('SACOCHE_STRUCTURE_BD_NAME','sac_base_0');
	define('SACOCHE_STRUCTURE_BD_USER','sac_user_0');
	define('SACOCHE_STRUCTURE_BD_PASS','password_0');

	echo'<h2>0.1 Paramètres de connexion du webmestre</h2>';
	echo'<pre class="b">BD_HOST : '.SACOCHE_WEBMESTRE_BD_HOST.'</pre>';
	echo'<pre class="b">BD_PORT : '.SACOCHE_WEBMESTRE_BD_PORT.'</pre>';
	echo'<pre class="b">BD_NAME : '.SACOCHE_WEBMESTRE_BD_NAME.'</pre>';
	echo'<pre class="b">BD_USER : '.SACOCHE_WEBMESTRE_BD_USER.'</pre>';
	echo'<pre class="b">BD_PASS : '.'--masqué--'.'</pre>';

	echo'<h2>0.2 Paramètres de connexion établissement</h2>';
	echo'<pre class="b">BD_HOST : '.SACOCHE_STRUCTURE_BD_HOST.'</pre>';
	echo'<pre class="b">BD_PORT : '.SACOCHE_STRUCTURE_BD_PORT.'</pre>';
	echo'<pre class="b">BD_NAME : '.SACOCHE_STRUCTURE_BD_NAME.'</pre>';
	echo'<pre class="b">BD_USER : '.SACOCHE_STRUCTURE_BD_USER.'</pre>';
	echo'<pre class="b">BD_PASS : '.SACOCHE_STRUCTURE_BD_PASS.'</pre>';

	echo'<hr />';

	echo'<h2>1.11 Se connecter à mysql comme webmestre</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT.','.SACOCHE_WEBMESTRE_BD_USER.','.'--masqué--'.')</pre>';
	$BDlink = mysql_connect(SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT,SACOCHE_WEBMESTRE_BD_USER,SACOCHE_WEBMESTRE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.12 Voir les droits du webmestre</h2>';
	$query = 'SHOW GRANTS FOR CURRENT_USER()';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	$affichage = ($BDres) ? print_r(mysql_fetch_row($BDres),true) : mysql_error();
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo'<h2>1.13 Créer une base "sac_base_0"</h2>';
	$query = 'CREATE DATABASE '.SACOCHE_STRUCTURE_BD_NAME;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.14 Créer un user "sac_user_0"</h2>';
	$query = 'CREATE USER '.SACOCHE_STRUCTURE_BD_USER.' IDENTIFIED BY "'.SACOCHE_STRUCTURE_BD_PASS.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.15 Attribuer des droits à ce user</h2>';
	$query = 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.SACOCHE_STRUCTURE_BD_NAME.'.* TO '.SACOCHE_STRUCTURE_BD_USER;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.16 Vérifier les droits du user : base mysql.user</h2>';
	$query = 'SELECT host, user, Select_priv FROM mysql.user WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo'<pre class="v">';while($row=mysql_fetch_row($BDres)){print_r($row);}echo'</pre>';

	echo'<h2>1.17 Vérifier les droits du user : base mysql.db</h2>';
	$query = 'SELECT host, user, Select_priv FROM mysql.db WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo'<pre class="v">';while($row=mysql_fetch_row($BDres)){print_r($row);}echo'</pre>';

	echo'<h2>1.18 Fermer la connexion webmestre</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>1.21 Se connecter à mysql avec le user "sac_user_0"</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_STRUCTURE_BD_HOST.':'.SACOCHE_STRUCTURE_BD_PORT.','.SACOCHE_STRUCTURE_BD_USER.','.SACOCHE_STRUCTURE_BD_PASS.')</pre>';
	$BDlink = mysql_connect(SACOCHE_STRUCTURE_BD_HOST.':'.SACOCHE_STRUCTURE_BD_PORT,SACOCHE_STRUCTURE_BD_USER,SACOCHE_STRUCTURE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.22 Sélectionner la base "sac_base_0" avec le user "sac_user_0"</h2>';
	echo'<pre class="b">mysql_select_db()</pre>';
	$BDres = mysql_select_db(SACOCHE_STRUCTURE_BD_NAME,$BDlink);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.23 Créer une table dans la base "sac_base_0" avec le user "sac_user_0"</h2>';
	$query = 'CREATE TABLE IF NOT EXISTS sacoche_test (test_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (test_id) )';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.24 Fermer la connexion sac_user_0</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>1.31 Se connecter à mysql comme webmestre</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT.','.SACOCHE_WEBMESTRE_BD_USER.','.'--masqué--'.')</pre>';
	$BDlink = mysql_connect(SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT,SACOCHE_WEBMESTRE_BD_USER,SACOCHE_WEBMESTRE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.32 Supprimer la base "sac_base_0"</h2>';
	$query = 'DROP DATABASE '.SACOCHE_STRUCTURE_BD_NAME;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.33 Retirer les droits de "sac_user_0"</h2>';
	$query = 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.SACOCHE_STRUCTURE_BD_USER;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.34 Supprimer le user "sac_user_0"</h2>';
	$query = 'DROP USER '.SACOCHE_STRUCTURE_BD_USER;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'DELETE FROM mysql.user WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';	// Car ça ne suffit pas toujours, même sur des Mysql 5 !!!
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'FLUSH PRIVILEGES';	// Forcer le rechargement des droits après une modification manuelle de la table
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>1.35 Fermer la connexion webmestre</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>2.11 Se connecter à mysql comme webmestre</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT.','.SACOCHE_WEBMESTRE_BD_USER.','.'--masqué--'.')</pre>';
	$BDlink = mysql_connect(SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT,SACOCHE_WEBMESTRE_BD_USER,SACOCHE_WEBMESTRE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.12 Créer une base "sac_base_0"</h2>';
	$query = 'CREATE DATABASE '.SACOCHE_STRUCTURE_BD_NAME;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.13 Créer un user "sac_user_0" EN SPÉCIFIANT EXPLICITEMENT LE HOST "'.SACOCHE_WEBMESTRE_BD_HOST.'"</h2>';
	$query = 'CREATE USER '.SACOCHE_STRUCTURE_BD_USER.'@"'.SACOCHE_WEBMESTRE_BD_HOST.'" IDENTIFIED BY "'.SACOCHE_STRUCTURE_BD_PASS.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.14 Attribuer des droits à ce user</h2>';
	$query = 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.SACOCHE_STRUCTURE_BD_NAME.'.* TO '.SACOCHE_STRUCTURE_BD_USER;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.15 Vérifier les droits du user : base mysql.user</h2>';
	$query = 'SELECT host, user, Select_priv FROM mysql.user WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo'<pre class="v">';while($row=mysql_fetch_row($BDres)){print_r($row);}echo'</pre>';

	echo'<h2>2.16 Vérifier les droits du user : base mysql.db</h2>';
	$query = 'SELECT host, user, Select_priv FROM mysql.db WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo'<pre class="v">';while($row=mysql_fetch_row($BDres)){print_r($row);}echo'</pre>';

	echo'<h2>2.17 Fermer la connexion webmestre</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>2.21 Se connecter à mysql avec le user "sac_user_0"</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_STRUCTURE_BD_HOST.':'.SACOCHE_STRUCTURE_BD_PORT.','.SACOCHE_STRUCTURE_BD_USER.','.SACOCHE_STRUCTURE_BD_PASS.')</pre>';
	$BDlink = mysql_connect(SACOCHE_STRUCTURE_BD_HOST.':'.SACOCHE_STRUCTURE_BD_PORT,SACOCHE_STRUCTURE_BD_USER,SACOCHE_STRUCTURE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.22 Sélectionner la base "sac_base_0" avec le user "sac_user_0"</h2>';
	echo'<pre class="b">mysql_select_db()</pre>';
	$BDres = mysql_select_db(SACOCHE_STRUCTURE_BD_NAME,$BDlink);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.23 Créer une table dans la base "sac_base_0" avec le user "sac_user_0"</h2>';
	$query = 'CREATE TABLE IF NOT EXISTS sacoche_test (test_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (test_id) )';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.24 Fermer la connexion sac_user_0</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>2.31 Se connecter à mysql comme webmestre</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT.','.SACOCHE_WEBMESTRE_BD_USER.','.'--masqué--'.')</pre>';
	$BDlink = mysql_connect(SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT,SACOCHE_WEBMESTRE_BD_USER,SACOCHE_WEBMESTRE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.32 Supprimer la base "sac_base_0"</h2>';
	$query = 'DROP DATABASE '.SACOCHE_STRUCTURE_BD_NAME;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.33 Retirer les droits de "sac_user_0"</h2>';
	$query = 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.SACOCHE_STRUCTURE_BD_USER;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.34 Supprimer le user "sac_user_0"</h2>';
	$query = 'DROP USER '.SACOCHE_STRUCTURE_BD_USER;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'DELETE FROM mysql.user WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';	// Car ça ne suffit pas toujours, même sur des Mysql 5 !!!
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'FLUSH PRIVILEGES';	// Forcer le rechargement des droits après une modification manuelle de la table
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.35 Fermer la connexion webmestre</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

}

?>
