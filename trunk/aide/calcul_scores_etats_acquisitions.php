<?php
/**
 * @version $Id: calcul_scores_etats_acquisitions.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Calcul des scores et des états d'acquisitions";
?>

<h2>Introduction</h2>
<p>
	Pour un item donné, évalué une ou plusieurs fois, le logiciel permet de paramétrer un calcul automatique de son état d'acquisition.<br />
	<b>Seul l'administrateur peut modifier ces réglages ; afin de le comprendre, les professeurs et directeurs y ont un accès en consultation et simulation.</b>
</p>

<h2>Valeur d'un code</h2>
<p>A chaque évaluation d'un item est associée une valeur sur 100. Les valeurs par défaut sont :</p>
<ul class="puce">
	<li><img alt="" src="./_img/note/note_RR.gif" /> = 0</li>
	<li><img alt="" src="./_img/note/note_R.gif" /> = 33</li>
	<li><img alt="" src="./_img/note/note_V.gif" /> = 67</li>
	<li><img alt="" src="./_img/note/note_VV.gif" /> = 100</li>
</ul>
<p>Ceci correspond à une répartition régulière (0/3 ; 1/3 ; 2/3 ; 3/3). Une évaluation codée «&nbsp;absent&nbsp;» ou «&nbsp;non&nbsp;noté&nbsp;» ou «&nbsp;dispensé&nbsp;» n'est pas comptabilisée.</p>

<h2>Pondération des devoirs</h2>
<p>Quand un item est évalué plusieurs fois, la dernière évaluation est celle qui a le plus d'importance. On estime ainsi qu'un élève a le droit à l'erreur en cours d'apprentissage, qu'il peut mettre du temps pour acquérir certains items, l'essentiel étant de valoriser l'acquisition finale (mais inversement, un élève qui régresse sera davantage pénalisé). Les valeurs par défaut, dont la somme doit valoir 1, sont :</p>
<ul class="puce">
	<li>pour 2 devoirs : 0,25 ; 0,75</li>
	<li>pour 3 devoirs : 0,2 ; 0,3 ; 0,5</li>
	<li>pour 4 devoirs : 0,1 ; 0,2 ; 0,3 ; 0,4</li>
</ul>
<p>Pour plus de 4 devoirs, seules les 4 dernières notes sont prises en compte.</p>
<p>Par rapport à un système classique où toutes les évaluations auraient le même poids dans le temps, le dernier devoir est ainsi sensiblement valorisé dans les mêmes proportions :</p>
<ul class="puce">
	<li>pour 2 devoirs le dernier compte coefficient 0,75 au lieu de 1/2, soit +50%</li>
	<li>pour 3 devoirs le dernier compte coefficient 0,5 au lieu de 1/3, soit +50%</li>
	<li>pour 4 devoirs le dernier compte coefficient 0,4 au lieu de 1/4, soit +60%</li>
</ul>
<p>Pour que seule la dernière évaluation soit prise en compte, il faudrait mettre le coefficient 1 au dernier devoir et 0 aux autres.</p>

<h2>Seuil d'acquisition</h2>
<p>Enfin, une fois le score calculé pour un item donné, on détermine dans quelle tranche il est considéré comme acquis ou non. Les valeurs par défaut (sur 100) sont :</p>
<ul class="puce">
	<li><span class="r">&lt; 40 : non acquis</span></li>
	<li><span class="v">&gt; 60 : acquis</span></li>
	<li><span class="o">entre ces valeurs : partiellement acquis</span></li>
</ul>
<p>En prenant des valeurs égales on supprime (ou presque) le niveau intermédiaire.</p>

<h2>Avertissement</h2>
<p>Déterminer si les items sont acquis ou non met en jeu un nombre important de paramètres, qu'il est impossible de tous contrôler et unifier :</p>
<ul class="puce">
	<li>le nombre d'évaluations proposées et le nombre d'évaluations prises en compte</li>
	<li>le poids donné à chacun des items ou à chacune des évaluations</li>
	<li>la difficulté des évaluations proposées, le degré d'exigence du professeur</li>
	<li>etc.</li>
</ul>
<p>De plus, des coefficients peuvent être associés aux items : ils sont alors utilisés lors de l'élaboration de bilans sur une matière donnée.</p>
