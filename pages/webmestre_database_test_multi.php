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

<p class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__droits_mysql">DOC : Droits MySQL requis (multi-structures)</a></p>

<form action="./index.php?page=webmestre_database_test" method="post">
	<p class="ti"><input id="lancer_test" name="lancer_test" type="hidden" value="go" /><button id="bouton_newsletter" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Lancer le test.</button></p>
</form>

<hr />

<style type="text/css">
	pre.b{color:blue}
	pre.r{color:red}
	pre.v{color:green}
</style>

<?php

if(isset($_POST['lancer_test']))
{
	define('SACOCHE_STRUCTURE_BD_HOST',SACOCHE_WEBMESTRE_BD_HOST);
	define('SACOCHE_STRUCTURE_BD_PORT',SACOCHE_WEBMESTRE_BD_PORT);
	define('SACOCHE_STRUCTURE_BD_NAME','sac_base_0');
	define('SACOCHE_STRUCTURE_BD_USER','sac_user_0');
	define('SACOCHE_STRUCTURE_BD_PASS','sac_pass_0');

	echo'<h2>1.1 Paramètres de connexion du webmestre</h2>';
	echo'<pre class="b">BD_HOST : '.SACOCHE_WEBMESTRE_BD_HOST.'</pre>';
	echo'<pre class="b">BD_PORT : '.SACOCHE_WEBMESTRE_BD_PORT.'</pre>';
	echo'<pre class="b">BD_NAME : '.SACOCHE_WEBMESTRE_BD_NAME.'</pre>';
	echo'<pre class="b">BD_USER : '.SACOCHE_WEBMESTRE_BD_USER.'</pre>';
	echo'<pre class="b">BD_PASS : '.'--masqué--'.'</pre>';

	echo'<h2>1.2 Paramètres de connexion établissement</h2>';
	echo'<pre class="b">BD_HOST : '.SACOCHE_STRUCTURE_BD_HOST.'</pre>';
	echo'<pre class="b">BD_PORT : '.SACOCHE_STRUCTURE_BD_PORT.'</pre>';
	echo'<pre class="b">BD_NAME : '.SACOCHE_STRUCTURE_BD_NAME.' (pour ce test uniquement)</pre>';
	echo'<pre class="b">BD_USER : '.SACOCHE_STRUCTURE_BD_USER.' (pour ce test uniquement)</pre>';
	echo'<pre class="b">BD_PASS : '.SACOCHE_STRUCTURE_BD_PASS.' (pour ce test uniquement)</pre>';

	echo'<hr />';

	echo'<h2>2.1 Se connecter à mysql comme webmestre</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT.','.SACOCHE_WEBMESTRE_BD_USER.','.'--masqué--'.')</pre>';
	$BDlink = mysql_connect(SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT,SACOCHE_WEBMESTRE_BD_USER,SACOCHE_WEBMESTRE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.2 Voir les droits du webmestre</h2>';
	$query = 'SHOW GRANTS FOR CURRENT_USER()';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	$affichage = ($BDres) ? mysql_fetch_row($BDres) : mysql_error();
	echo ($BDres) ? '<pre class="v">'.str_replace(', ','<br />',$affichage[0]).'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo'<h2>2.3 Créer une base "sac_base_0"</h2>';
	$query = 'CREATE DATABASE '.SACOCHE_STRUCTURE_BD_NAME;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.4 Créer un user "sac_user_0" sur localhost et sur % ()</h2>';
	$query = 'CREATE USER '.SACOCHE_STRUCTURE_BD_USER.'@"localhost" IDENTIFIED BY "'.SACOCHE_STRUCTURE_BD_PASS.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'CREATE USER '.SACOCHE_STRUCTURE_BD_USER.'@"%" IDENTIFIED BY "'.SACOCHE_STRUCTURE_BD_PASS.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.5 Attribuer des droits à ce user</h2>';
	$query = 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.SACOCHE_STRUCTURE_BD_NAME.'.* TO '.SACOCHE_STRUCTURE_BD_USER.'@"localhost"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.SACOCHE_STRUCTURE_BD_NAME.'.* TO '.SACOCHE_STRUCTURE_BD_USER.'@"%"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>2.6 Vérifier les droits du user : base mysql.user</h2>';
	$query = 'SELECT host, user, Select_priv FROM mysql.user WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo'<pre class="v">';while($row=mysql_fetch_row($BDres)){print_r($row);}echo'</pre>';

	echo'<h2>2.7 Vérifier les droits du user : base mysql.db</h2>';
	$query = 'SELECT host, user, Select_priv FROM mysql.db WHERE user="'.SACOCHE_STRUCTURE_BD_USER.'"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo'<pre class="v">';while($row=mysql_fetch_row($BDres)){print_r($row);}echo'</pre>';

	echo'<h2>2.8 Fermer la connexion webmestre</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>3.1 Se connecter à mysql avec le user "sac_user_0"</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_STRUCTURE_BD_HOST.':'.SACOCHE_STRUCTURE_BD_PORT.','.SACOCHE_STRUCTURE_BD_USER.','.SACOCHE_STRUCTURE_BD_PASS.')</pre>';
	$BDlink = mysql_connect(SACOCHE_STRUCTURE_BD_HOST.':'.SACOCHE_STRUCTURE_BD_PORT,SACOCHE_STRUCTURE_BD_USER,SACOCHE_STRUCTURE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>3.2 Sélectionner la base "sac_base_0" avec le user "sac_user_0"</h2>';
	echo'<pre class="b">mysql_select_db()</pre>';
	$BDres = mysql_select_db(SACOCHE_STRUCTURE_BD_NAME,$BDlink);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>3.3 Créer une table dans la base "sac_base_0" avec le user "sac_user_0"</h2>';
	$query = 'CREATE TABLE IF NOT EXISTS sacoche_test (test_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (test_id) )';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>3.4 Fermer la connexion sac_user_0</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

	echo'<h2>4.1 Se connecter à mysql comme webmestre</h2>';
	echo'<pre class="b">mysql_connect('.SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT.','.SACOCHE_WEBMESTRE_BD_USER.','.'--masqué--'.')</pre>';
	$BDlink = mysql_connect(SACOCHE_WEBMESTRE_BD_HOST.':'.SACOCHE_WEBMESTRE_BD_PORT,SACOCHE_WEBMESTRE_BD_USER,SACOCHE_WEBMESTRE_BD_PASS);
	echo ($BDlink) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>4.2 Supprimer la base "sac_base_0"</h2>';
	$query = 'DROP DATABASE '.SACOCHE_STRUCTURE_BD_NAME;
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>4.3 Retirer les droits de "sac_user_0"</h2>';
	$query = 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.SACOCHE_STRUCTURE_BD_USER.'@"localhost"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.SACOCHE_STRUCTURE_BD_USER.'@"%"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>4.4 Supprimer le user "sac_user_0"</h2>';
	$query = 'DROP USER '.SACOCHE_STRUCTURE_BD_USER.'@"localhost"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';
	$query = 'DROP USER '.SACOCHE_STRUCTURE_BD_USER.'@"%"';
	echo'<pre class="b">'.$query.'</pre>';
	$BDres = mysql_query($query);
	echo ($BDres) ? '<pre class="v">OK</pre>' : '<pre class="r">'.mysql_error().'</pre>';

	echo'<h2>4.5 Fermer la connexion webmestre</h2>';
	echo'<pre class="b">mysql_close()</pre>';
	$BDres = mysql_close($BDlink);
	$affichage = var_export($BDres,true);
	echo ($BDres) ? '<pre class="v">'.$affichage.'</pre>' : '<pre class="r">'.$affichage.'</pre>';

	echo('<hr />');

}

?>
