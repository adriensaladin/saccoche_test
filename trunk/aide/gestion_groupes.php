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
$TITRE = "Gestion des groupes";
?>

<h2>Introduction</h2>
<p>
	Seul l'administrateur gère les groupes et leurs affectations.<br />
	Les groupes sont pratiques pour un enseignement ne concernant que quelques élèves d'une classe, ou concernant des élèves issus de plusieurs classes.<br />
	Chaque groupe est rattaché à un niveau prédéfini.<br />
	<span class="astuce">Seules les classes sont mises à jour lors de l'import Sconet ou tableur.</span>
</p>

<h2>Créer / modifier / supprimer des groupes</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Groupes]</em> puis <em>[Groupes (gestion)]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer un nouveau groupe.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un groupe existant.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_supprimer.png" /> pour supprimer un groupe existant.</li>
</ul>
<p><span class="danger">Un groupe comportant des élèves ne devrait pas être supprimé (sinon les professeurs n'auront plus accès à certaines saisies) !</span></p>

<h2>Affecter les élèves aux groupes</h2>
<p>
	Un ou plusieurs élèves, issus de différentes classes ou différents groupes, peuvent être affectés à un ou plusieurs groupes.<br />
	<span class="astuce">Un élève peut être affecté à plusieurs groupes, mais à une seule classe.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Groupes]</em> menu <em>[Élèves &amp; groupes]</em>.</li>
	<li>Sélectionner les élèves, les groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="danger">La composition d'un groupe devrait rarement être modifiée en cours d'année (les professeurs n'auront plus accès à certaines saisies) !</span></p>

<h2>Affecter les professeurs aux groupes</h2>
<p>
	Un ou plusieurs professeurs peuvent être affectés à un ou plusieurs groupes.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Groupes]</em> menu <em>[Professeurs &amp; groupes]</em>.</li>
	<li>Sélectionner les professeurs, les groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="astuce">Les professeurs n'auront accès qu'aux élèves qui figurent dans les classes et groupes où ils sont affectés.</span></p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=gestion_classes">DOC : Gestion des classes.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=deplacer_eleve_durant_annee">DOC : Peut-on déplacer un élève en cours d'année ?</a></span></li>
</ul>
