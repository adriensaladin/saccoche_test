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
$TITRE = "Gestion du format des noms d'utilisateurs";
?>

<h2>Introduction</h2>
<p>
	Lors de l'ajout d'un nouvel utilisateur (import Sconet, import tableur, ou manuellement), <em>SACoche</em> génère un nom d'utilisateur selon le format choisi par l'administrateur (ce login demeure modifiable ensuite).<br />
	<span class="astuce">Il faut l'indiquer avant d'importer les utilisateurs.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Format des noms d'utilisateurs]</em>.</li>
</ul>

<h2>Contraintes sur le format</h2>
<ul class="puce">
	<li>Au maximum 20 caractères.</li>
	<li>Au moins une lettre du prénom et une lettre du nom (quel que soit l'ordre).</li>
	<li>Un caractère entre le prénom et le nom parmi "<b>.-_</b>", ou aucun.</li>
</ul>

<h2>Méthode employée</h2>
<p>
	Le modèle est indiqué à l'aide d'une suite de caractères.<br />
	Exemples pour un utilisateur se nommant <b>Jean Aimarre</b>.
</p>
<ul class="puce">
	<li>"<b>ppp.nnnnnnnn</b>" donnera "<b>jea.aimarre</b>"</li>
	<li>"<b>ppp-nnn</b>" donnera "<b>jea-aim</b>"</li>
	<li>"<b>p_nnnnnnnnnnn</b>" donnera "<b>j_aimarre</b>"</li>
	<li>"<b>pnnnnn</b>" donnera "<b>jaimar</b>"</li>
	<li>"<b>nnnnnnnnp</b>" donnera "<b>aimarrej</b>"</li>
	<li>"<b>n.ppp</b>" donnera "<b>a.jea</b>"</li>
</ul>
