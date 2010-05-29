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
$TITRE = "Importer identifiant ENT";
?>

<?php
if($_SESSION['SSO']=='normal')
{
	echo'<p><span class="astuce">Vous devez commencer par sélectionner votre ENT depuis la page "<a href="./index.php?dossier=administrateur&amp;fichier=etabl&amp;section=connexion">Mode d\'identification</a>".</span></p>';
}
else
{
	require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
	echo'<p><span class="astuce">SSO en lien avec l\''.$tab_sso[$_SESSION['SSO']]['txt'].' <a href="./index.php?dossier=administrateur&amp;fichier=etabl&amp;section=connexion">Changer de mode d\'identification.</a></span></p>';
	echo'<hr />';
	echo'<form action="">';
	echo	'<ul class="puce">';
	echo		'<li>Importer l\'identifiant avec le fichier <b>csv</b> provenant de l\'ENT (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_mode_identification__integration_ENT_'.$tab_sso[$_SESSION['SSO']]['doc'].'">documentation</a></span>) : <input id="import_ent" type="button" value="Parcourir..." /></li>';
	echo		'<li>Prendre et <input id="copy_id_Gepi" type="button" value="recopier l\'identifiant de Gepi déjà importé" /> comme identifiant de l\'ENT pour tous les utilisateurs.</li>';
	echo		'<li>Prendre et <input id="copy_login_SACoche" type="button" value="recopier le login de SACoche" /> comme identifiant de l\'ENT pour tous les utilisateurs.</li>';
	echo		'<li>Pour un traitement individuel, on peut aussi utiliser la page "<a href="./index.php?dossier=administrateur&amp;fichier=eleve&amp;section=gestion">Gérer les élèves</a>" ou "<a href="./index.php?dossier=administrateur&amp;fichier=professeur&amp;section=gestion">Gérer les professeurs</a>" ou "<a href="./index.php?dossier=administrateur&amp;fichier=directeur&amp;section=gestion">Gérer les directeurs</a>".</li>';
	echo	'</ul>';
	echo'</form>';
	echo'<hr />';
	echo'<p class="hc"><label id="ajax_msg">&nbsp;</label></p>';
	echo'<div id="ajax_retour" class="hc"></div>';
}
?>
