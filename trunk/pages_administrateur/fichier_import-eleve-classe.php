<?php
/**
 * @version $Id: fichier_import-eleve-classe.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Importer les élèves et les classes";
?>

<p><span class="astuce">Si la procédure est utilisée en début d'année (initialisation), elle peut ensuite être renouvelée en cours d'année (mise à jour).</span></p>

<ul id="step">
	<li id="step1">Étape 1 - fichier Sconet ou tableur : récupération</li>
	<li id="step2">Étape 2 - fichier Sconet ou tableur : traitement</li>
	<li id="step3">Étape 3 - importation des classes : paramétrage</li>
	<li id="step4">Étape 4 - importation des classes : résultat</li>
	<li id="step5">Étape 5 - importation des élèves : paramétrage</li>
	<li id="step6">Étape 6 - importation des élèves : résultat</li>
	<li id="step7">Étape 7 - confirmation / impression</li>
</ul>

<hr />

<form action="">
	<div id="ajax">
		<h2>Première méthode : fichier issu de Sconet</h2>
		Cette méthode est fortement recommandée.<br />
		<span class="manuel"><a class="pop_up" href="./aide.php?fichier=import_classes_eleves_Sconet">DOC : Import classes / élèves depuis Sconet</a></span><br />
		Indiquez ci-dessous le fichier <b>ExportXML_ElevesSansAdresses.zip</b> (ou <b>ElevesSansAdresses.xml</b>) obtenu.
		<h2>Seconde méthode : fichier tableur</h2>
		Cette méthode n'est à utiliser que si l'établissement n'utilise pas SCONET (à l'étranger...).<br />
		<span class="manuel"><a class="pop_up" href="./aide.php?fichier=import_classes_eleves_tableur">DOC : Import classes / élèves avec un tableur</a></span><br />
		Indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu.
		<h2>Démarrer la procédure</h2>
		<label class="tab" for="f_submit_1">Fichier à importer :</label><input id="f_submit_1" type="button" value="Parcourir..." /><label id="ajax_msg">&nbsp;</label>
	</div>
</form>
