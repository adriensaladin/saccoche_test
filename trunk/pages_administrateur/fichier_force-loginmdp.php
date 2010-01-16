<?php
/**
 * @version $Id: fichier_force-loginmdp.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Imposer identifiants SACoche";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=force_login_mdp_tableur">DOC : Imposer identifiants SACoche avec un tableur</a></span></p>

<form action="">
	<div>
		Pour commencer, vous pouvez <input id="user_export" type="button" value="récupérer un fichier csv avec les noms / prénoms / logins actuels" /> (le mot de passe, crypté, ne peut être restitué).<p />
		Indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu que vous souhaitez importer<br />
		<label class="tab" for="user_import">Fichier à importer :</label><input id="user_import" type="button" value="Parcourir..." />
	</div>
</form>

<p><span class="astuce">Pour un traitement individuel, on peut aussi utiliser la page "<a href="./index.php?dossier=administrateur&amp;fichier=eleve&amp;section=gestion">Gérer les élèves</a>" ou "<a href="./index.php?dossier=administrateur&amp;fichier=professeur&amp;section=gestion">Gérer les professeurs</a>" ou "<a href="./index.php?dossier=administrateur&amp;fichier=directeur&amp;section=gestion">Gérer les directeurs</a>".</span></p>

<hr />
<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>
