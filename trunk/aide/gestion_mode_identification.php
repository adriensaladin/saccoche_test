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
$TITRE = "Gestion du mode d'identification";
?>

<h2>Introduction</h2>
<p>
	L'administrateur peut choisir le mode de connexion à <em>SACoche</em> :
</p>
<ul class="puce">
	<li>Une connexion normale avec les identifiants enregistrés dans <em>SACoche</em>.</li>
	<li>Une connexion SSO en lien avec un ENT.</li>
</ul>
<p>
	Pour cela :
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Mode d'identification]</em>.</li>
</ul>
<p>
	Pour une connexion SSO, il faut ensuite importer l'identifiant de l'ENT pour que <em>SACoche</em> puisse lier les applications.<br />
	<span class="astuce">Dans ce cas, l'administrateur est le seul utilisateur à se connecter de façon classique.</span>
</p>

<h2>Passerelles disponibles</h2>
<ul class="puce">
<?php
require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
unset($tab_sso['normal']);
foreach($tab_sso as $value => $tab_infos)
{
	$documentation = ($tab_infos['doc']) ? ' <span class="manuel"><a href="./aide.php?fichier=integration_ENT_'.$tab_infos['doc'].'">DOC</a></span>' : '' ;
	echo'<li>'.$tab_infos['txt'].' '.$documentation.'</li>'."\r\n";
}
?>
</ul>
<p>
	<span class="astuce">Pour tenter d'établir d'autres passerelles, <?php echo mailto('thomas.crespin@sesamath.net','Ajouter une passerelle','contactez-moi'); ?>.</span>
</p>
