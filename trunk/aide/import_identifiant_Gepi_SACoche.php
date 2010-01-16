<?php
/**
 * @version $Id: import_identifiant_Gepi_SACoche.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Import des identifiants de Gepi dans SACoche";
?>

<h2>Introduction</h2>
<p>
	Si vous utilisez le logiciel <a href="http://gepi.mutualibre.org/">GEPI</a> pour les bulletins trimestriels, <em>SACoche</em> permet d'y importer une note bilan chiffrée (sur 20) et un élément d'appréciation relatif à l'acquisition des compétences.<p />
	<b>Pour que le transfert fonctionne, <em>SACoche</em> doit connaître les noms d'utilisateurs de GEPI.</b>
</p>

<p class="hc"><img alt="gepi_logo" src="./_img/aide/gepi_logo.png" /></p>

<h2>Préalable</h2>
<p>
	<span class="danger">Cette procédure ne sert pas à se connecter avec les identifiants de Gepi !</span><br />
	<span class="danger">Cette procédure ne sert pas à inscrire des utilisateurs !</span>
</p>
<p>L'administrateur doit avoir importé les utilisateurs dans <em>SACoche</em></p>
<ul class="puce">
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer élèves & classes]</em></li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer professeurs & directeurs]</em></li>
</ul>

<h2>Cas de l'utilisation couplée avec un ENT</h2>
Si les identifiants de l'ENT ont déjà été importés dans SACoche, et si GEPI utilise les mêmes identifiants (académie de Bordeaux par exemple), la démarche est facilitée :
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer identifiant GEPI]</em>.</li>
	<li>Cliquer sur <em>[recopier l'identifiant de l'ENT déjà importé]</em>.</li>
</ul>
<p>Si ce n'est pas le cas (académie de Toulouse par exemple), il faut appliquer la démarche ci-après.</p>

<h2>Démarche de l'administrateur dans GEPI</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Depuis le menu principal, cliquer sur <em>[Gestion des bases]</em>, puis <em>[Gestion des élèves]</em>, puis <em>[Télécharger le fichier des élèves au format csv]</em>.</li>
	<li>Depuis le menu principal, cliquer sur <em>[Gestion des bases]</em>, puis <em>[Gestion des comptes d'accès des utilisateurs]</em>, puis <em>[Personnels de l'établissement]</em>, puis <em>[Télécharger le fichier des professeurs au format csv]</em>.</li>
</ul>
<p>On récupère ainsi 2 fichiers : <em>«&nbsp;base_eleves_gepi.csv&nbsp;»</em> et <em>«&nbsp;base_professeurs_gepi.csv&nbsp;»</em>.</p>

<h2>Démarche de l'administrateur dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer identifiant Gepi]</em>.</li>
	<li>Cliquer sur <em>[Parcourir...]</em> et indiquer successivement les 2 fichiers récupérés de GEPI.</li>
</ul>
