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
$TITRE = "Réintégrer / supprimer professeurs &amp; directeurs";
?>

<?php
// Fabrication des éléments select du formulaire
$select_professeurs_directeurs = afficher_select(DB_OPT_professeurs_directeurs_etabl($_SESSION['STRUCTURE_ID'],$statut=0) , $select_nom=false , $option_first='non' , $selection=false , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_statuts">DOC : Statuts : désactiver / réintégrer / supprimer</a></span></li>
	<li><span class="danger">Supprimer un compte professeur ou directeur est une action irréversible !</span></li>
</ul>

<hr />

<form action="">
	<table><tr>
		<td class="nu" style="width:25em">
			<b>Liste des professeurs et directeurs :</b><br />
			<select id="select_professeurs_directeurs" name="select_professeurs_directeurs[]" multiple="multiple" size="10"><?php echo $select_professeurs_directeurs; ?></select>
		</td>
		<td class="nu" style="width:25em">
			<p><span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>"<br />pour une sélection multiple.</span></p>
			<p><input id="reintegrer" type="button" value="Réintégrer" /> ces professeurs / directeurs.</p>
			<p><input id="supprimer" type="button" value="Supprimer définitivement" /> ces comptes professeurs / directeurs.</p>
		</td>
	</tr></table>
</form>
<hr />
<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>
