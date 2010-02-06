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
	Pour un item donné, évalué une ou plusieurs fois, le logiciel permet de paramétrer un calcul automatique de son état d'acquisition.
</p>

<h2>Valeur d'un code</h2>
<p>
	A chaque évaluation d'un item on associe une valeur sur 100. Les valeurs par défaut sont :
</p>
<ul class="puce">
	<li><img alt="" src="./_img/note/note_RR.gif" /> = 0</li>
	<li><img alt="" src="./_img/note/note_R.gif" /> = 33</li>
	<li><img alt="" src="./_img/note/note_V.gif" /> = 67</li>
	<li><img alt="" src="./_img/note/note_VV.gif" /> = 100</li>
</ul>
<p>
	Ceci correspond à une répartition régulière (0/3 ; 1/3 ; 2/3 ; 3/3). Une évaluation codée «&nbsp;absent&nbsp;» ou «&nbsp;non&nbsp;noté&nbsp;» ou «&nbsp;dispensé&nbsp;» n'est pas comptabilisée.
</p>
<p>
	<b>Seul l'administrateur peut modifier ce réglage ; afin de le comprendre, les autres statuts y ont un accès en consultation et simulation.</b>
</p>

<h2>Coefficients des évaluations</h2>
<p>
	Par défaut, quand un item est évalué plusieurs fois, les évaluations les plus récentes sont celles qui ont le plus d'importance. On autorise ainsi le droit à l'erreur en cours d'apprentissage, l'essentiel étant de valoriser l'acquisition finale (mais inversement, un élève qui régresse sera davantage pénalisé).<br />
	Les coefficients suivent alors une progression arithmétique : le coefficient du devoir suivant est augmenté de 1. Ainsi, dans le cas de 4 devoirs, le très ancien est coefficient 1, l'ancien est coefficient 2, le récent est coefficient 3, le très récent est coefficient 4.
</p>
<p>
	On peut aussi choisir de compter autant chaque évaluation (moyenne classique).
</p>
<p>
	<b>L'administrateur fixe un réglage par défaut ; celui-ci peut être modifié pour chaque référentiel par les coordonnateurs.</b>
</p>

<h2>Nombre d'évaluations prises en compte</h2>
<p>
	Par défaut, toutes les évaluations sont comptabilisées. Mais on peut aussi se restreindre à la dernière évaluation, ou aux 2 ; 3 ; 4 ; 5 ; 6 ; 7 ; 8 ; 9 ; 10 ; 15 ; 20 ; 30 ; 40 ; 50 dernières évaluations.
</p>
<p>
	<b>L'administrateur fixe un réglage par défaut ; celui-ci peut être modifié pour chaque référentiel par les coordonnateurs.</b>
</p>

<h2>Seuil d'acquisition</h2>
<p>
	Enfin, une fois le score calculé pour un item donné, on détermine dans quelle tranche il est considéré comme acquis ou non. Les valeurs par défaut (sur 100) sont :
</p>
<ul class="puce">
	<li><span class="r">&lt; 40 : non acquis</span></li>
	<li><span class="v">&gt; 60 : acquis</span></li>
	<li><span class="o">entre ces valeurs : partiellement acquis</span></li>
</ul>
<p>
	En prenant des valeurs égales on supprime (ou presque) le niveau intermédiaire.
</p>
<p>
	<b>Seul l'administrateur peut modifier ce réglage ; afin de le comprendre, les autres statuts y ont un accès en consultation et simulation.</b>
</p>

<h2>Avertissement</h2>
<p>
	Déterminer si les items sont acquis ou non met en jeu un nombre important de paramètres, qu'il est impossible de tous contrôler et unifier :
</p>
<ul class="puce">
	<li>le nombre d'évaluations proposées et le nombre d'évaluations prises en compte</li>
	<li>le poids donné à chacun des items ou à chacune des évaluations</li>
	<li>la difficulté des évaluations proposées, le degré d'exigence du professeur</li>
	<li>etc.</li>
</ul>
<p>
	De plus, des coefficients peuvent être associés aux items : ils sont alors utilisés lors de l'élaboration de bilans sur une matière donnée.
</p>
