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
$TITRE = "Gestion du format des noms d'utilisateurs";
?>

<h2>Introduction</h2>
<p>
	Lors de l'ajout d'un nouvel utilisateur (import Sconet, import tableur, ou manuellement), <em>SACoche</em> génère un nom d'utilisateur selon le format choisi par l'administrateur (ce login demeure modifiable ensuite).<br />
	<span class="astuce">Il faut l'indiquer avant d'importer les utilisateurs.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Format des noms d'utilisateurs]</em>.</li>
</ul>

<h2>Contraintes sur le format</h2>
<ul class="puce">
	<li>Au maximum 20 caractères.</li>
	<li>Au moins une lettre du prénom et une lettre du nom (quel que soit l'ordre).</li>
	<li>Un caractère entre le prénom et le nom parmi "<b>.-_</b>", ou aucun.</li>
</ul>

<h2>Méthode employée</h2>
<p>
	Le modèle est indiqué à l'aide d'une suite de caractères.<br />
	Exemples pour un utilisateur se nommant <b>Jean Aimarre</b>.
</p>
<ul class="puce">
	<li>"<b>ppp.nnnnnnnn</b>" donnera "<b>jea.aimarre</b>"</li>
	<li>"<b>ppp-nnn</b>" donnera "<b>jea-aim</b>"</li>
	<li>"<b>p_nnnnnnnnnnn</b>" donnera "<b>j_aimarre</b>"</li>
	<li>"<b>pnnnnn</b>" donnera "<b>jaimar</b>"</li>
	<li>"<b>nnnnnnnnp</b>" donnera "<b>aimarrej</b>"</li>
	<li>"<b>n.ppp</b>" donnera "<b>a.jea</b>"</li>
</ul>
