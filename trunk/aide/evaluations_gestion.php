<?php
/**
 * @version $Id: evaluations_gestion.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
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
