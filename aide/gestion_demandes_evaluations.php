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
$TITRE = "Demandes d'évaluations";
?>

<h2>Introduction</h2>
<p>
	On peut permettre à l'élève de solliciter envers ses professeurs des demandes d'évaluations sur un certains nombre d'items.
</p>

<h2>Espace administrateur</h2>
<p>
	L'administrateur peut activer cette fonctionnalité, et dans ce cas paramétrer le nombre maximal de demandes simultanées autorisées par matières.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Demandes d'évaluations]</em>.</li>
</ul>

<h2>Espace élève</h2>
<p>
	L'élève peut formuler des souhaits d'évaluations, consulter ses demandes en cours et en supprimer.
</p>
<ul class="puce">
	<li>Se connecter avec son compte élève.</li>
	<li>Pour formuler une demande, consulter <em>[Bilan sur une matière]</em> ou <em>[Attestation de maîtrise du socle]</em> (si autorisé). Cliquer sur l'icône <img alt="demander" src="./_img/action/action_demander_add.png" /> à côté d'un item pour l'ajouter à la liste des demandes.</li>
	<li>Pour consulter ses demandes en cours ou en supprimer, se rendre dans le menu <em>[Demandes d'évaluations]</em>.</li>
</ul>

<h2>Espace professeur</h2>
<p>
	Le professeur peut consulter les souhaits d'évaluations des élèves, et créer des évaluations à partir de ces demandes.
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Évaluations &amp; Saisie des résultats]</em> menu <em>[Demandes d'évaluations]</em>.</li>
</ul>
<p>
	On choisit une matière et un groupe, puis on affiche les demandes. Dans le tableau obtenu sont indiqués quelques aides à la décision :
</p>
<ul class="puce">
	<li>la popularité correspond au nombre d'élèves du groupe qui ont choisi l'item</li>
	<li>le score correspond au score de l'élève pour cet item avant qu'il ne formule sa demande</li>
</ul>
<p>
	Le professeur dispose alors de plusieurs actions possibles pour les items sélectionnés :
</p>
<ul class="puce">
	<li>créer une nouvelle évaluation, pour tous les élèves du groupe ou pour les seuls élèves concernés</li>
	<li>intégrer les élèves et les items choisis à une évaluation déjà existante</li>
	<li>changer le statut des demandes pour "évaluation en préparation"</li>
	<li>retirer les demandes de la liste</li>
</ul>
<p>
	<span class="astuce">Lorsqu'un item est évalué pour un élève, la demande associée, si elle existe, est automatiquement retirée.</span><br />
	<span class="danger">Le statut de la demande est seulement indicatif ; si un professeur supprime ou modifie une évaluation prévue, ce n'est pas répercuté sur les statuts des demandes (qui ne sont pas directement liées à une évaluation donnée).</span><br />
</p>

