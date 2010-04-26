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
$TITRE = "Gestion des matières";
?>

<h2>Introduction</h2>
<p>
	L'administrateur doit cocher les matières utilisées sur <em>SACoche</em> : seules les matières sélectionnées sont affichées dans les menus déroulants.<br />
	<span class="astuce">Il faut l'indiquer avant d'affecter les professeurs aux matières.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Matières]</em>.</li>
</ul>

<h2>Matières partagées</h2>
<p>
	Ce sont des matières communes que l'on retrouve fréquemment.<br />
	Plus de 30 matières sont disponibles ; il est possible d'en ajouter (langues, options...) si besoin : <?php echo mailto('thomas.crespin@sesamath.net','Ajouter une matière','contactez-moi'); ?> en m'indiquant son nom et sa référence dans Sconet (l'idéal est de joindre le fichier <em>nomenclature.xml</em> issu de Sconet).
</p>

<h2>Matières spécifiques</h2>
<p>
	Il est possible d'ajouter des matières, spécifiques à un établissement, dont on aurait l'utilité (projets de classe, parcours...).<br />
	Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour ajouter une matière spécifique.<br />
	<span class="astuce">Les référentiels de compétences associés ne pourront pas être partagés avec la communauté d'utilisateurs.</span>
</p>
