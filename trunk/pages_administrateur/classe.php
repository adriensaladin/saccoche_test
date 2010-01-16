<?php
/**
 * @version $Id: classe.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Classes";
?>

<div class="hc">
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=gestion">Classes (gestion).</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=classe-groupe">Périodes &amp; classes / groupes.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=eleve">Élèves &amp; classes.</a>	||
	<a href="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=professeur">Professeurs &amp; classes.</a>
</div>

<hr />

<?php
if(($SECTION=='eleve')||($SECTION=='professeur'))
{
	// échanger $FICHIER et $SECTION pour piocher le bon fichier sans avoir besoin de le dupliquer, tout en gardant ce menu
	$FICHIER = $SECTION;
	$SECTION = 'classe';
}
elseif($SECTION=='classe-groupe')
{
	// remplcer $FICHIER pour piocher le bon fichier sans avoir besoin de le dupliquer, tout en gardant ce menu
	$FICHIER = 'periode';
}
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
	echo'<p><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_classes">DOC : Gestion des classes</a></span></p>';
}
?>
