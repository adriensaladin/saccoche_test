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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$id     = (isset($_POST['f_id']))     ? clean_entier($_POST['f_id'])    : 0;
$nom    = (isset($_POST['f_nom']))    ? clean_texte($_POST['f_nom'])    : '';
$ordre  = (isset($_POST['f_ordre']))  ? clean_entier($_POST['f_ordre']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter une nouvelle période / Dupliquer une pédiode existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( (($action=='ajouter')||($action=='dupliquer')) && $nom && $ordre )
{
	// Vérifier que le nom de la période est disponible
	if( DB_tester_periode_nom($_SESSION['STRUCTURE_ID'],$nom) )
	{
		exit('Erreur : nom de période déjà existant !');
	}
	// Insérer l'enregistrement
	$periode_id = DB_ajouter_periode($_SESSION['STRUCTURE_ID'],$nom,$ordre);
	// Afficher le retour
	echo'<tr id="id_'.$periode_id.'" class="new">';
	echo	'<td>'.$ordre.'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cette période."></q>';
	echo		'<q class="dupliquer" title="Dupliquer cette période."></q>';
	echo		'<q class="supprimer" title="Supprimer cette période."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier une période existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $nom && $ordre )
{
	// Vérifier que le nom de la période est disponible
	if( DB_tester_periode_nom($_SESSION['STRUCTURE_ID'],$nom,$id) )
	{
		exit('Erreur : nom de période déjà existant !');
	}
	// Mettre à jour l'enregistrement
	DB_modifier_periode($_SESSION['STRUCTURE_ID'],$id,$nom,$ordre);
	// Afficher le retour
	echo'<td>'.$ordre.'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cette période."></q>';
	echo	'<q class="dupliquer" title="Dupliquer cette période."></q>';
	echo	'<q class="supprimer" title="Supprimer cette période."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une période existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $id )
{
	// Effacer l'enregistrement
	DB_supprimer_periode($_SESSION['STRUCTURE_ID'],$id);
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
