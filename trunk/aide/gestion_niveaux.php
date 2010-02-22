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
$TITRE = "Gestion des niveaux";
?>

<p>
	L'administrateur doit cocher les niveaux utilisés sur <em>SACoche</em> : seuls les niveaux sélectionnés sont affichés dans les menus déroulants.<br />
	<span class="astuce">Il faut l'indiquer avant d'importer les utilisateurs.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Niveaux]</em>.</li>
</ul>
<p>
	Environ 30 niveaux sont disponibles ; il est possible d'en ajouter (filières...) si besoin : <?php echo mailto('thomas.crespin@sesamath.net','Ajouter un niveau','contactez-moi'); ?> en m'indiquant son nom et son niveau de correspondance dans Sconet (l'idéal est de joindre le fichier <em>nomenclature.xml</em> issu de Sconet).
</p>
