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
$TITRE = "Transfert du bulletin de SACoche dans Gepi";
?>

<h2>Introduction</h2>
<p>
	Si vous utilisez le logiciel <a href="http://gepi.mutualibre.org/">GEPI</a> pour les bulletins trimestriels, <em>SACoche</em> permet d'y importer une note bilan chiffrée (sur 20) et un élément d'appréciation relatif à l'acquisition des compétences.<p />
	<b>L'administrateur doit avoir fait le nécessaire pour que les noms d'utilisateurs de GEPI coïncident avec ceux de <em>SACoche</em>.</b>
</p>

<p class="hc"><img alt="gepi_logo" src="./_img/aide/gepi_logo.png" /></p>

<h2>Démarche du professeur dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Menu <em>[Bilans sur une matière]</em>.</li>
	<li>Cocher <em>[Bulletin (moyenne & appréciation)]</em> et régler les autres paramètres.</li>
	<li>Cliquer sur <em>[Valider]</em>.</li>
	<li>Cliquer sur <em>[Bulletin au format CSV importable dans GEPI]</em>.</li>
</ul>
<p>On récupère un fichier avec l'extension <em>«&nbsp;csv&nbsp;»</em>.</p>

<h2>Démarche du professeur dans GEPI</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li><span class="u">Depuis un menu simplifié</span>, cliquer sur<img alt="gepi_bulletin" width="16" height="16" src="./_img/aide/gepi_bulletin.png" /> pour accéder aux moyennes d'un bulletin, puis <em>[Import/Export notes et appréciations]</em>.</li>
	<li><span class="u">Depuis un menu complet</span>, accéder aux bulletins, puis choisir son enseignement.</li>
	<li>Cliquer sur<img alt="gepi_import_notes_app" width="16" height="16" src="./_img/aide/gepi_import_notes_app.png" /> pour une importation d'un fichier CSV.</li>
	<li>Cliquer sur <em>[Parcourir...]</em>, indiquer le fichier récupéré de SACoche, puis cliquer sur <em>[Ouvrir]</em>.</li>
	<li>Si tout s'est bien passé, cliquer en bas du tableau sur <em>[Enregistrer les données]</em>.</li>
	<li>Si GEPI signale un ou plusieurs noms d'utilisateurs non concordants, signalez-le à l'administrateur (ou corrigez manuellement dans le fichier).</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=releve_matiere">DOC : Bilans sur une matière.</a></span></li>
</ul>
