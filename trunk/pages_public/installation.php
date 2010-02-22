<?php
/**
 * @version $Id: installation.php 7 2009-10-30 20:50:17Z thomas $
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
$TITRE = "Procédure d'installation";
?>

<ul id="step">
	<li id="step1">Étape 1 - vérification / création des dossiers supplémentaires et de leurs droits</li>
	<li id="step2">Étape 2 - vérification / remplissage de ces dossiers avec le contenu approprié</li>
	<li id="step3">Étape 3 - vérification / indication des paramètres de connexion MySQL</li>
	<li id="step4">Étape 4 - vérification / installation de la base de données</li>
</ul>

<hr />

<form action="">
	<div id="ajax">
		<div>
			<span class="astuce">Ce logiciel en phase de développement est mis à disposition à titre expérimental.</span><br />
			Une distribution sous licence libre de ce logiciel est prévue (échance à déterminer).
		</div>
		<p><span class="tab"><a href="#" class="step1">Passer à l'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>
	</div>
</form>
