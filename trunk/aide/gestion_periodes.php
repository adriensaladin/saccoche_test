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
$TITRE = "Gestion des périodes";
?>

<h2>Introduction</h2>
<p>Les périodes permettent :</p>
<ul class="puce">
<li>de proposer des dates par défaut pour l'édition de relevés ou de bilans.</li>
<li>de trier des évaluations (pour les professeurs).</li>
</ul>

<h2>Gestion des périodes</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Périodes]</em> puis <em>[Périodes (gestion)]</em>.</li>
</ul>
On créé une période en indiquant son nom et son ordre dans l'année.

<h2>Affecter des périodes aux classes / groupes</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Périodes]</em> puis <em>[Périodes &amp; classes / groupes]</em>.</li>
	<li>Pour ajouter une association, sélectionner les périodes, les classes / groupes, les dates de début et de fin, et choisir l'action correspondante.</li>
	<li>Pour retirer une association, sélectionner les périodes, les classes / groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	<span class="astuce">Le début d'une nouvelle période doit correspondre au jour suivant la fin de la période précédente.</span><br />
	Par exemple si le trimestre n°1 se termine le 27 novembre, alors le trimestre n°2 doit commencer le 28 novembre.
</p>
<p>
	<span class="astuce">Cliquer sur un logo <img alt="niveau" src="./_img/date_add.png" /> permet de recopier les dates de la cellule dans les champs du formulaire.
</p>
<p>
	<span class="astuce">Les barres vertes indiquent l'agencement successif des périodes pour chaque classe ; une barre rouge signale des périodes non consécutives ou non disjointes.
</p>
