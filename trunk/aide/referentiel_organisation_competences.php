<?php
/**
 * @version $Id: referentiel_organisation_competences.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Organisation d'un référentiel de compétences";
?>
<h2>Introduction</h2>
<p>
	<b>Les coordonnateurs</b>, en concertation avec leurs équipes, ont la charge d'établir pour chaque niveau le référentiel de compétences de leur discipline.
	<div class="hc"><img alt="organigramme_enseignements_et_socle_commun_extrait" src="./_img/aide/organigramme_enseignements_et_socle_commun_extrait.png" /></div>
</p>

<h2>Organisation et vocabulaire</h2>
<p>
	Chaque <img alt="niveau" src="./_img/folder/folder_m2.png" /> <b>niveau</b> contient des <img alt="domaine" src="./_img/folder/folder_n1.png" /> <b>domaines</b>.<br />
	Un domaine est constitué d'un nom, et d'un caractère (lettre ou chiffre) qui le référence.
</p>
<p>
	Chaque <img alt="domaine" src="./_img/folder/folder_n1.png" /> <b>domaine</b> contient des <img alt="thème" src="./_img/folder/folder_n2.png" /> <b>thèmes</b>.<br />
	Un thème est juste constitué d'un nom.<br />
	Dans chaque domaine, les thèmes sont implicitement numérotés à partir de 1.
</p>
<p>
	Chaque <img alt="thème" src="./_img/folder/folder_n2.png" /> <b>thème</b> contient des <img alt="item" src="./_img/folder/folder_n3.png" /> <b>items</b>.<br />
	Un item est constitué d'un nom.<br />
	Dans chaque thème, les items sont implicitement numérotés à partir de 0.<br />
	Chaque item peut être associé à un coefficient, relié au socle commun, et relié à une ressource de remédiation.
</p>
<p>
	Voici par exemple l'arborescence d'un référentiel, et la fiche de compétences associée :
	<div class="hc"><img alt="organisation_referentiel" src="./_img/aide/organisation_referentiel.png" /></div>
</p>
<p>
	<span class="danger">Attention : tout changement dans l'organisation ou le contenu d'un référentiel peut rendre obsolète une grille de compétences distribuée aux élèves.</span><br />
	Par exemple, si on insère l'item «&nbsp;Repasser les mouchoirs&nbsp;» au début du thème «&nbsp;M1&nbsp;–&nbsp;Repassage&nbsp;», alors il prendra la référence M10, et les suivantes deviendront M11, M12 et M13&nbsp;!<br />
	Ainsi, un référentiel de compétences, utilisé avec des grilles papier distribuées aux élèves, ne devrait pas être modifié en cours d'année.
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
	<li><span class="manuel">DOC : Organisation d'un référentiel de compétences.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_creer_importer_partager_detruire">DOC : Créer / importer / partager / détruire un référentiel.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_modifier_parametrer">DOC : Modifier / paramétrer un référentiel.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
</ul>
