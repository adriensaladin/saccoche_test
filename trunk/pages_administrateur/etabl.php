<?php
/**
 * @version $Id: etabl.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gestion de l'établissement";
?>

<div class="hc">
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=matiere">Matières.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=palier">Paliers du socle.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=niveau">Niveaux.</a>	<br />
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=login">Format des noms d'utilisateurs.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=connexion">Mode d'identification.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=duree-inactivite">Délai avant déconnexion.</a>	<br />
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=demandes-eval">Demandes d'évaluations.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=options-eleve">Options de l'environnement élève.</a>
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
	echo'<p><span class="astuce">Choisissez une rubrique ci-dessus...</span></p>';
}
?>
