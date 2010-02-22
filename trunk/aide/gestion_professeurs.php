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
$TITRE = "Gestion des professeurs";
?>

<h2>Introduction</h2>
<p>
	Seul l'administrateur gère les professeurs et leurs affectations.<br />
	<span class="astuce">L'ajout d'un professeur peut se faire depuis la procédure d'importation Sconet / tableur ou manuellement.</span>
</p>

<h2>Créer / modifier / enlever des professeurs</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Professeurs]</em> puis <em>[Professeurs (gestion)]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer un nouveau professeur.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un professeur présent.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_desactiver.png" /> pour enlever un professeur présent.</li>
</ul>
<p>Remarques sur les différents champs :</p>
<ul class="puce">
	<li>Les champs "<b>Id&nbsp;ENT</b>" et "<b>Id&nbsp;GEPI</b>" servent à réaliser des interconnexions (voir documentations correspondantes).</li>
	<li>Il est déconseillé de modifier les champs "<b>n°&nbsp;Sconet</b>" et "<b>Référence</b>", sauf en connaissance de cause (lors d'une inscription manuelle, ces champs peuvent être ignorés).</li>
	<li>Lors de l'ajout d'un nouveau professeur, un nom d'utilisateur et un mot de passe sont générés automatiquement : ne pas oublier de les noter.</li>
	<li>Un nom d'utilisateur peut être modifié sous réserve de disponibilité.</li>
	<li>Retirer un professeur ne le supprime pas de la base : son compte est simplement désactivé.</li>
</ul>

<h2>Affecter les professeurs aux classes</h2>
<p>
	Un ou plusieurs professeurs peuvent être affectés à une ou plusieurs classes.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Professeurs]</em> menu <em>[Professeurs &amp; classes]</em>.</li>
	<li>Sélectionner les professeurs, les classes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="astuce">Les professeurs n'auront accès qu'aux élèves qui figurent dans les classes et groupes où ils sont affectés.</span></p>

<h2>Affecter les professeurs aux groupes</h2>
<p>
	Un ou plusieurs professeurs peuvent être affectés à un ou plusieurs groupes.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Professeurs]</em> menu <em>[Professeurs &amp; groupes]</em>.</li>
	<li>Sélectionner les professeurs, les groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="astuce">Les professeurs n'auront accès qu'aux élèves qui figurent dans les classes et groupes où ils sont affectés.</span></p>

<h2>Affecter les professeurs aux matières</h2>
<p>
	Un ou plusieurs professeurs peuvent être affectés à une ou plusieurs matières.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Professeurs]</em> menu <em>[Professeurs &amp; matières]</em>.</li>
	<li>Sélectionner les professeurs, les matières, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="astuce">Les professeurs n'auront accès qu'aux référentiels des matières qui leurs sont affectées.</span></p>

<h2>Affecter les professeurs principaux</h2>
<p>
	Un ou plusieurs professeurs peuvent être professeurs principaux d'une ou plusieurs classes.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Professeurs]</em> menu <em>[Professeurs principaux]</em>.</li>
	<li>Cocher les professeurs principaux (il faut que les professeurs soient déjà affectés aux classes).</li>
</ul>
<p><span class="astuce">Les professeurs principaux ont accès à des bilans multi-disciplinaires supplémentaires.</span></p>

<h2>Affecter les professeurs coordonnateurs</h2>
<p>
	Un ou plusieurs professeurs peuvent être professeurs coordonnateurs d'une ou plusieurs matières.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Professeurs]</em> menu <em>[Professeurs coordonnateurs]</em>.</li>
	<li>Cocher les professeurs coordonnateurs (il faut que les professeurs soient déjà affectés aux matières).</li>
</ul>
<p><span class="astuce">Les professeurs coordonnateurs gérent les référentiels de compétences de leurs disciplines.</span></p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=import_professeurs_directeurs_Sconet">DOC : Import professeurs / directeurs depuis Sconet</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=import_professeurs_directeurs_tableur">DOC : Import professeurs / directeurs avec un tableur</a></span></li>
</ul>
