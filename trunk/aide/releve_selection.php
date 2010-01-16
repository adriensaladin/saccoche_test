<?php
/**
 * @version $Id: releve_selection.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Bilans sur une sélection d'items";
?>
<h2>Introduction</h2>
<p>
	Les professeurs peuvent générer des bilans de compétences, individuels ou collectifs, sur une sélection d'items.
</p>
<ul class="puce">
	<li>Se connecter avec son compte.</li>
	<li>Dans <em>[Relevés de compétences]</em> menu <em>[Bilans sur une sélection d'items]</em>.</li>
</ul>

<h2>Réglages</h2>
<p>Voici différents paramètres accessibles :</p>
<ul class="puce">
	<li>relevé individuel et/ou synthèse collective</li>
	<li>le ou les items concernés</li>
	<li>le ou les élèves concernés</li>
</ul>
<p>Choix supplémentaires pour le relevé individuel :</p>
<ul class="puce">
	<li>coefficients associés aux items (affichage ou pas)</li>
	<li>appartenance au socle commun des items (affichage ou pas)</li>
	<li>liens de remédiation associés aux items (affichage ou pas)</li>
	<li>ligne de synthèse avec la moyenne des scores (affichage ou pas)</li>
	<li>ligne de synthèse avec le pourcentage d'acquisitions validées (affichage ou pas)</li>
	<li>proposition de note sur 20 (dans les lignes de synthèses)</li>
</ul>
<p>Il existe des options de mise en page supplémentaires pour la sortie <em>pdf</em> :</p>
<ul class="puce">
	<li>format portrait ou paysage</li>
	<li>impression en couleur ou en noir et blanc</li>
	<li>marges minimales de 5mm / 10mm / 15mm</li>
	<li>nombre de cases pour y reporter les évaluations (de 1 à 10)</li>
	<li>largeur des cases (de 4mm à 16mm)</li>
	<li>hauteur des lignes (de 4mm à 16mm)</li>
</ul>
<p>
	<span class="astuce">Les évaluations prises en compte ne sont pas celles affichées mais celles qui satisfont aux critères de sélection. Par exemple si on choisit de n'afficher qu'une seule case mais qu'il y a trois évaluations concernées, les trois évaluations comptent pour le calcul du bilan.</span>
</p>

<h2>Relevés individuels générés</h2>
Pour chaque élève, pour chaque item choisi, un score sur 100 est calculé, et un état d'acquisition est associé (acquis, non acquis, ou intermédiaire).
<ul class="puce">
	<li>Le format <em>HTML</em> permet de pouvoir cliquer sur les liens de remédiation, apporte des informations supplémentaires au survol d'un résultat avec la souris (nom et date de l'évaluation), et permet de trier les colonnes (cliquer sur les flèches).</li>
	<li>Le format <em>PDF</em> permet d'obtenir un fichier adapté à l'impression, proprement mis en page.</li>
</ul>

<h2>Synthèse collective générée</h2>
Un tableau à double entrée (élèves &amp; items) reprend les scores et les états d'acquisitions. En bout de lignes et de colonnes figurent des indicateurs de réussite : moyenne des scores et pourcentages d'items validés.
<ul class="puce">
	<li>Le format <em>HTML</em> permet de trier les colonnes (cliquer sur les flèches).</li>
	<li>Le format <em>PDF</em> permet d'obtenir un fichier adapté à l'impression, tenant sur une seule page.</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></li>
</ul>

