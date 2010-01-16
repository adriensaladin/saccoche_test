<?php
/**
 * @version $Id: gestion_statuts.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Statuts : désactiver / réintégrer / supprimer";
?>

<h2>Introduction</h2>
<p>
	Par sécurité, un utilisateur n'est jamais supprimé directement, il est tout d'abord désactivé (il n'apparait alors plus dans les menus, et il ne peut plus se connecter).
</p>

<h2>Désactiver comptes élèves / professeurs &amp; directeurs</h2>
Une désactivation peut se faire :
<ul class="puce">
	<li>lors de l'import d'un fichier Sconet ou tableur</li>
	<li>sur les différentes pages de gestion de l'administrateur (traitement individuel)</li>
	<li>depuis le menu <em>[Statuts]</em> de l'administrateur (traitement collectif)</li>
</ul>

<h2>Réintégrer / supprimer comptes élèves / professeurs &amp; directeurs</h2>
<p>Une fois un utilisateur désactivé on peut :</p>
<ul class="puce">
	<li>soit le réintégrer dans l'établissement</li>
	<li>soit le supprimer définitivement</li>
</ul>
<p>Pour cela :</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Statuts]</em>.</li>
</ul>
<p>
	<span class="astuce">Un élève quittant l'établissement en cours de trimestre ne devrait pas être désactivé afin la fin de celui-ci : sinon le professeur ne pourra plus consulter son bilan.</span><br />
	<span class="astuce">Un élève quittant l'établissement ne devrait pas être supprimé avant plusieurs années, pour pouvoir récupérer ses résultats s'il revient.</span>
</p>
<p>
	<span class="danger">Supprimer définitivement un élève entraîne la suppression de tous les résultats qui y sont associés.</span><br />
	<span class="danger">Supprimer définitivement un professeur entraîne la suppression de toutes les données qui y sont associées, dont les évaluations (mais pas les résultats des élèves déjà enregistrés lors de ces évaluations).</span>
</p>
