<?php
/**
 * @version $Id: referentiel_creer_importer_partager_detruire.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Créer / importer / partager / détruire un référentiel";
?>
<h2>Introduction</h2>
<p>
	Seuls <b>les professeurs coordonnateurs</b> ont accès à cette gestion.<br />
	Les autres professeurs peuvent uniquement consulter les référentiels de leurs disciplines.<br />
	Les personnels de direction peuvent consulter les référentiels de toutes les disciplines ; un tableau récapitulatif leur indique les absences de référentiels, ou les disciplines sans coordonnateur attribué.
</p>

<h2>Créer / importer un référentiel</h2>
<p>
	Si un référentiel de compétence est manquant, il est possible soit de créer un référentiel vierge, soit de partir d'un référentiel existant qu'un autre établissement aurait accepté de partager (c'est à dire de le rendre visible par les professeurs des autres établissements).
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Référentiels de compétences]</em> menu <em>[Créer / importer / partager / détruire]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> (si un référentiel est manquant).</li>
	<li><em>SACoche</em> va afficher la liste des référentiels partagés disponibles pour la matière et le niveau donnés.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_voir.png" /> pour voir le détail d'un référentiel.</li>
	<li>Il est possible de chercher un référentiel partagé parmi un autre niveau ou une autre matière.</li>
	<li>Sélectionner le référentiel de votre choix, ou un référentiel vierge, et valider.</li>
</ul>

<h2>Partager un référentiel</h2>
<p>
	On peut choisir de rendre publique ou non les référentiels de compétences en cliquant sur <img alt="partager" src="./_img/action/action_partager.png" />. Il y a plusieurs statuts possibles :
</p>
<ul class="puce">
	<li><img alt="niveau" src="./_img/partage1.gif" /> référentiel partagé : à utiliser pour tout référentiel novateur que l'on souhaite rendre visible.</li>
	<li><img alt="niveau" src="./_img/partage0.gif" /> référentiel non partagé car sans intérêt : à utiliser si on s'est inspiré d'un référentiel existant, peu ou pas modifié.</li>
	<li><img alt="niveau" src="./_img/partage0.gif" /> référentiel non partagé : soit par choix, soit parce qu'on ne le considère pas abouti.</li>
</ul>
<p>
	Seuls les référentiels d'une matière 'générale' peuvent être partagés. Dans le cas de la création d'une matière spécifique à l'établissement, la notion de partage est hors-sujet et ne peut pas être modifiée (image <img alt="partager" src="./_img/action/action_partager_non.png" />).
</p>

<h2>Détruire un référentiel</h2>
<p>
	On peut supprimer un référentiel existant.<br />
	<span class="danger">Cette action supprime tout son contenu, ainsi que les résultats associés de tous les élèves. Un référentiel en cours d'utilisation ne devrait donc jamais être effacé.</span>
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_organisation_competences">DOC : Organisation d'un référentiel de compétences.</a></span></li>
	<li><span class="manuel">DOC : Créer / importer / partager / détruire un référentiel.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_modifier_parametrer">DOC : Modifier / paramétrer un référentiel.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
</ul>
