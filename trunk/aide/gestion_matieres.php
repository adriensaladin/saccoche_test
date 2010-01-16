<?php
/**
 * @version $Id: gestion_matieres.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gestion des matières";
?>

<h2>Introduction</h2>
<p>
	L'administrateur doit cocher les matières utilisées sur <em>SACoche</em> : seules les matières sélectionnées sont affichées dans les menus déroulants.<br />
	<span class="astuce">Il faut l'indiquer avant d'affecter les professeurs aux matières.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Matières]</em>.</li>
</ul>

<h2>Matières partagées</h2>
<p>
	Ce sont des matières communes que l'on retrouve fréquemment.<br />
	Plus de 30 matières sont disponibles ; il est possible d'en ajouter (langues, options...) si besoin : <?php echo mailto('thomas.crespin@sesamath.net','Ajouter une matière','contactez-moi'); ?> en m'indiquant son nom et sa référence dans Sconet (l'idéal est de joindre le fichier <em>nomenclature.xml</em> issu de Sconet).
</p>

<h2>Matières spécifiques</h2>
<p>
	Il est possible d'ajouter des matières, spécifiques à un établissement, dont on aurait l'utilité (projets de classe, parcours...).<br />
	Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour ajouter une matière spécifique.<br />
	<span class="astuce">Les référentiels de compétences associés ne pourront pas être partagés avec la communauté d'utilisateurs.</span>
</p>
