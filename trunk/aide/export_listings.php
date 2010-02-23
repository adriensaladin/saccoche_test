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
$TITRE = "Export listings";
?>

<h2>Introduction</h2>
<p>
	Certaines extractions de données ont été demandées par les professeurs, pour avoir une vue d'ensemble ou pour une utilisation dans le cadre de leurs préparations de cours.<br />
	Ces exports sont effectués en HTML et au format CSV (pour tableur).
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Menu <em>[Export listings]</em>.</li>
</ul>

<h2>Listes des élèves par classe</h2>
<p>
	Pour récupérer id / login / nom / prénom / groupe des élèves.<br />
	L'identifiant doit être utilisé dans le cadre d'une saisie déportée d'une évaluation (prochainement).
</p>

<h2>Listes des items par matiere</h2>
<p>
	Pour récupérer id / matière / référence / nom des items des matières.<br />
	L'identifiant doit être utilisé dans le cadre d'une saisie déportée d'une évaluation (prochainement).
</p>

<h2>Arborescence des items par matière</h2>
<p>
	Pour récupérer l'arborescence niveau / domaine / thème / item d'une matière.
</p>

<h2>Arborescence des items du socle</h2>
<p>
	Pour récupérer l'arborescence palier / pilier / section / item du socle.
</p>

<h2>Liens socle &amp; matieres</h2>
<p>
	Pour récupérer la liste des items de chaque matière associé à chaque item du socle (présenté en suivant l'arborescence du socle).
</p>
