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
$TITRE = "Gestion du mode d'identification";
?>

<h2>Introduction</h2>
<p>
	L'administrateur peut choisir le mode de connexion à <em>SACoche</em> :
</p>
<ul class="puce">
	<li>Une connexion normale avec les identifiants enregistrés dans <em>SACoche</em>.</li>
	<li>Une connexion SSO en lien avec un ENT.</li>
</ul>
<p>
	Pour cela :
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Mode d'identification]</em>.</li>
</ul>
<p>
	Pour une connexion SSO, il faut ensuite importer l'identifiant de l'ENT pour que <em>SACoche</em> puisse lier les applications.<br />
	<span class="astuce">Dans ce cas, l'administrateur est le seul utilisateur à se connecter de façon classique.</span>
</p>

<h2>Passerelles disponibles</h2>
<ul class="puce">
<?php
require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
unset($tab_sso['normal']);
foreach($tab_sso as $value => $tab_infos)
{
	$documentation = ($tab_infos['doc']) ? ' <span class="manuel"><a href="./aide.php?fichier=integration_ENT_'.$tab_infos['doc'].'">DOC</a></span>' : '' ;
	echo'<li>'.$tab_infos['txt'].' '.$documentation.'</li>'."\r\n";
}
?>
</ul>
<p>
	<span class="astuce">Pour tenter d'établir d'autres passerelles, <?php echo mailto('thomas.crespin@sesamath.net','Ajouter une passerelle','contactez-moi'); ?>.</span>
</p>
