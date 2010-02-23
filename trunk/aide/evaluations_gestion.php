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
$TITRE = "Gestion des évaluations";
?>
<h2>Introduction</h2>
<p>
	Le professeur peut gérer des évaluations portant sur un regroupement de type «&nbsp;classe&nbsp;» / «&nbsp;groupe&nbsp;» / «&nbsp;groupe&nbsp;de&nbsp;besoin&nbsp;», ou sur un ensemble d'élèves à cocher.
</p>

<h2>Gestion des évaluations</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Évaluations et saisie des résultats]</em> menu <em>[Évaluer une classe ou un groupe]</em> ou <em>[Évaluer des élèves sélectionnés]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour ajouter une nouvelle évaluation : on renseigne la date, le regroupement ou les élèves concernés (parmi ceux affectés au professeur), une brève description et la liste des items concernés par l'évaluation (à cocher dans une liste comprenant ceux enregistrés dans les matières affectées au professeur).</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ordonner.png" /> pour modifier l'ordre des items d'une évaluation affiché lors de la saisie, de l'impression d'un cartouche, et de la visualisation (par défaut c'est l'ordre du référentiel).</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier une évaluation existante.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_dupliquer.png" /> pour dupliquer une évaluation existante (pour d'autres élèves).</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_supprimer.png" /> pour supprimer une évaluation existante.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_imprimer.png" /> pour imprimer un cartouche d'une évaluation.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_saisir.png" /> pour saisir les résultats d'une évaluation.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_voir.png" /> pour voir les résultats d'une évaluation.</li>
</ul>
<p>
	<span class="danger">La liste des items évalués ou des élèves concernés ne devrait pas être modifiée à partir du moment où la saisie des résultats de l'évaluation a commencé (sinon, des résultats peuvent être perdus ou devenir inaccessibles).</span><br />
	<span class="danger">Supprimer une évaluation entraîne la suppression de tous les résultats qui y sont associés !</span>
</p>

<h2>Cartouche</h2>
<p>Un cartouche peut être imprimé avant ou après la saisie des résultats.</p>
<ul class="puce">
	<li>On peut choisir un cartouche vierge (avant l'évaluation), ou comportant les résultats obtenus par les élèves (si saisis).</li>
	<li>On peut choisir un cartouche avec la dénomination complète de chaque item (un item par ligne), ou un cartouche minimal avec uniquement les références des items (un item par colonne).</li>
</ul>

<h2>Saisie des résultats</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=evaluations_saisie_resultats">DOC : Saisie des résultats.</a></span></li>
</ul>
