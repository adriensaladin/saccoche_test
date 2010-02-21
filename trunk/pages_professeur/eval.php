<?php
/**
 * @version $Id: eval.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gestion des évaluations et saisie des acquisitions";
?>

<div class="hc">
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=demande">Demandes des élèves.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=groupe">Évaluer une classe ou un groupe.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=select">Évaluer des élèves sélectionnés.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=releve">Accéder aux bilans.</a>
</div>

<hr />

<?php
// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = './pages_'.$DOSSIER.'/'.$FICHIER.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
	include($fichier_section);
	$FICHIER = $FICHIER.'_'.$SECTION ;
}
else
{
	echo'
		<p>Vous pouvez soit :</p>
		<ul class="puce">
			<li>évaluer une classe, un groupe, ou un groupe de besoin que vous auriez créé.</li>
			<li>évaluer ponctuellement un ou plusieurs élèves (en dehors du cadre d\'une classe ou d\'un groupe).</li>
		</ul>
	';
}
?>
