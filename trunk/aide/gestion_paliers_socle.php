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
$TITRE = "Gestion des paliers du socle";
?>

<p>
	L'administrateur doit cocher les paliers du socle commun qui sont utilisés dans l'établissement : seuls les paliers sélectionnés sont affichés dans les menus déroulants.<br />
	Les paliers 1 et 2 sont destinés à l'école primaire, et le palier 3 est destiné au collège (le lycée n'est pas concerné).<br />
	<span class="astuce">Les items évalués dans les différentes disciplines peuvent alors être reliées à des items du socle commun par les professeurs coordonnateurs, afin d'établir un bilan de l'acquisition du socle.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Paliers du socle]</em>.</li>
</ul>
<p>
	Le contenu des 3 paliers du socle est déjà entièrement enregistré dans la base.<br />
	Cliquer sur <img alt="niveau" src="./_img/action/action_voir.png" /> permet d'en prendre connaissance.
</p>
