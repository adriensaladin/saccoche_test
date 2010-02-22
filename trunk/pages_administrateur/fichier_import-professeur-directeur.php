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
$TITRE = "Importer les professeurs et les directeurs";
?>

<?php
$nom_fin_fichier = (date('n')>7) ? date('Y') : date('Y')-1 ;
$nom_fin_fichier = $_SESSION['STRUCTURE_UAI'].'_'.$nom_fin_fichier;
?>

<p><span class="astuce">Si la procédure est utilisée en début d'année (initialisation), elle peut ensuite être renouvelée en cours d'année (mise à jour).</span></p>

<ul id="step">
	<li id="step1">Étape 1 - fichier Sconet / STS-Web ou tableur : récupération</li>
	<li id="step2">Étape 2 - fichier Sconet / STS-Web ou tableur : traitement</li>
	<li id="step3">Étape 3 - importation des professeurs et des directeurs : paramétrage</li>
	<li id="step4">Étape 4 - importation des professeurs et des directeurs : résultat</li>
	<li id="step5">Étape 5 - confirmation / impression</li>
</ul>

<hr />

<form action="">
	<div id="ajax">
		<h2>Première méthode : fichier issu de Sconet / STS-Web</h2>
		Cette méthode est fortement recommandée.<br />
		<span class="manuel"><a class="pop_up" href="./aide.php?fichier=import_professeurs_directeurs_Sconet">DOC : Import professeurs / directeurs depuis Sconet</a></span><br />
		Indiquez ci-dessous le fichier <b>sts_emp_<?php echo $nom_fin_fichier ?>.xml</b> (ou <b>sts_emp_<?php echo $nom_fin_fichier ?>.zip</b>) obtenu.
		<h2>Seconde méthode : fichier tableur</h2>
		Cette méthode n'est à utiliser que si l'établissement n'utilise pas SCONET (à l'étranger...).<br />
		<span class="manuel"><a class="pop_up" href="./aide.php?fichier=import_professeurs_directeurs_tableur">DOC : Import professeurs / directeurs avec un tableur</a></span><br />
		Indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu.
		<h2>Démarrer la procédure</h2>
		<label class="tab" for="f_submit_1">Fichier à importer :</label><input id="f_submit_1" type="button" value="Parcourir..." /><label id="ajax_msg">&nbsp;</label>
	</div>
</form>
