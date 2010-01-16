<?php
/**
 * @version $Id: gestion_paliers_socle.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gestion des paliers du socle";
?>

<p>
	L'administrateur doit cocher les paliers du socle commun qui sont utilisés dans l'établissement : seuls les paliers sélectionnés sont affichés dans les menus déroulants.<br />
	Les paliers 1 et 2 sont destinés à l'école primaire, et le palier 3 est destiné au collège (le lycée n'est pas concerné).<br />
	<span class="astuce">Les items évalués dans les différentes disciplines peuvent alors être reliées à des items du socle commun par les professeurs coordonnateurs, afin d'établir un bilan de l'acquisition du socle.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Paliers du socle]</em>.</li>
</ul>
<p>
	Le contenu des 3 paliers du socle est déjà entièrement enregistré dans la base.<br />
	Cliquer sur <img alt="niveau" src="./_img/action/action_voir.png" /> permet d'en prendre connaissance.
</p>
