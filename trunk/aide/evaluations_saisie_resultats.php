<?php
/**
 * @version $Id$
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
$TITRE = "Saisie des résultats";
?>
<h2>Introduction</h2>
<p>
	La saisie des résultats est un travail qui peut se révéler fastidieux ; elle se fait dans un tableau particulièrement travaillé afin d'obtenir la plus grande ergonomie possible, en ayant tout sous les yeux, avec une saisie assistée au maximum.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Évaluations et saisie des résultats]</em> menu <em>[Évaluer une classe ou un groupe]</em> ou <em>[Évaluer des élèves sélectionnés]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_saisir.png" /> pour saisir les résultats d'une évaluation.</li>
</ul>

<h2>Saisie au clavier</h2>
	C'est le mode de saisie par défaut. On sélectionne une cellule avec la souris, ou en se déplaçant avec les touches fléchées. Voici les autres touches à utiliser :
<ul class="puce">
	<li>Pour <img alt="" src="./_img/note/note_RR.gif" /> utiliser la touche <b>«&nbsp;1&nbsp;»</b>.</li>
	<li>Pour <img alt="" src="./_img/note/note_R.gif" /> utiliser la touche <b>«&nbsp;2&nbsp;»</b>.</li>
	<li>Pour <img alt="" src="./_img/note/note_V.gif" /> utiliser la touche <b>«&nbsp;3&nbsp;»</b>.</li>
	<li>Pour <img alt="" src="./_img/note/note_VV.gif" /> utiliser la touche <b>«&nbsp;4&nbsp;»</b>.</li>
	<li>Pour <img alt="" src="./_img/note/note_ABS.gif" /> (absent) utiliser la touche <b>«&nbsp;a&nbsp;»</b>.</li>
	<li>Pour <img alt="" src="./_img/note/note_NN.gif" /> (non noté) utiliser la touche <b>«&nbsp;n&nbsp;»</b>.</li>
	<li>Pour <img alt="" src="./_img/note/note_DISP.gif" /> (dispensé) utiliser la touche <b>«&nbsp;d&nbsp;»</b>.</li>
	<li>Pour <b>«&nbsp;non saisi&nbsp;»</b>, utiliser la touche <b>«&nbsp;suppr&nbsp;»</b>.</li>
	<li>Pour enregistrer, utiliser la touche «&nbsp;entrée&nbsp;».</li>
</ul>
La cellule suivante est alors automatiquement sélectionnée jusqu'à atteindre la fin du tableau.

<h2>Saisie à la souris</h2>
Dans ce mode, au survol d'une cellule celle-ci se transforme automatiquement en une palette de 8 options dont on peut sélectionner une valeur.

<h2>Enregistrement</h2>
	Des fonds colorés permettent de distinguer les différents états :
<ul class="puce">
	<li><span style="background-color:#EEF">item non saisi</span></li>
	<li><span style="background-color:#AAF">item déjà enregistré</span></li>
	<li><span style="background-color:#F6D">item modifié mais non enregistré</span></li>
	<li><span style="background-color:#AAF">item modifié et enregistré</span></li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></li>
</ul>
