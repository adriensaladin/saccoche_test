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
$TITRE = "Gestion des périodes";
?>

<h2>Introduction</h2>
<p>Les périodes permettent :</p>
<ul class="puce">
<li>de proposer des dates par défaut pour l'édition de relevés ou de bilans.</li>
<li>de trier des évaluations (pour les professeurs).</li>
</ul>

<h2>Gestion des périodes</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Périodes]</em> puis <em>[Périodes (gestion)]</em>.</li>
</ul>
On créé une période en indiquant son nom et son ordre dans l'année.

<h2>Affecter des périodes aux classes / groupes</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Périodes]</em> puis <em>[Périodes &amp; classes / groupes]</em>.</li>
	<li>Pour ajouter une association, sélectionner les périodes, les classes / groupes, les dates de début et de fin, et choisir l'action correspondante.</li>
	<li>Pour retirer une association, sélectionner les périodes, les classes / groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	<span class="astuce">Le début d'une nouvelle période doit correspondre au jour suivant la fin de la période précédente.</span><br />
	Par exemple si le trimestre n°1 se termine le 27 novembre, alors le trimestre n°2 doit commencer le 28 novembre.
</p>
<p>
	<span class="astuce">Cliquer sur un logo <img alt="niveau" src="./_img/date_add.png" /> permet de recopier les dates de la cellule dans les champs du formulaire.
</p>
<p>
	<span class="astuce">Les barres vertes indiquent l'agencement successif des périodes pour chaque classe ; une barre rouge signale des périodes non consécutives ou non disjointes.
</p>
