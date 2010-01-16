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
$TITRE = "Export listings";
?>

<h2>Introduction</h2>
<p>
	Certaines extractions de données ont été demandées par les professeurs, pour avoir une vue d'ensemble ou pour une utilisation dans le cadre de leurs préparations de cours.<br />
	Ces exports sont effectués en HTML et au format CSV (pour tableur).
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Menu <em>[Export listings]</em>.</li>
</ul>

<h2>Listes des élèves par classe</h2>
<p>
	Pour récupérer id / login / nom / prénom / groupe des élèves.<br />
	L'identifiant doit être utilisé dans le cadre d'une saisie déportée d'une évaluation (prochainement).
</p>

<h2>Listes des items par matiere</h2>
<p>
	Pour récupérer id / matière / référence / nom des items des matières.<br />
	L'identifiant doit être utilisé dans le cadre d'une saisie déportée d'une évaluation (prochainement).
</p>

<h2>Arborescence des items par matière</h2>
<p>
	Pour récupérer l'arborescence niveau / domaine / thème / item d'une matière.
</p>

<h2>Arborescence des items du socle</h2>
<p>
	Pour récupérer l'arborescence palier / pilier / section / item du socle.
</p>

<h2>Liens socle &amp; matieres</h2>
<p>
	Pour récupérer la liste des items de chaque matière associé à chaque item du socle (présenté en suivant l'arborescence du socle).
</p>
