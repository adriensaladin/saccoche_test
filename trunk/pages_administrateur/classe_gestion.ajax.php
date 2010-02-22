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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action'])  : '';
$id     = (isset($_POST['f_id']))     ? clean_entier($_POST['f_id'])     : 0;
$niveau = (isset($_POST['f_niveau'])) ? clean_entier($_POST['f_niveau']) : 0;
$ref    = (isset($_POST['f_ref']))    ? clean_ref($_POST['f_ref'])       : '';
$nom    = (isset($_POST['f_nom']))    ? clean_texte($_POST['f_nom'])     : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter une nouvelle classe
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $niveau && $ref && $nom )
{
	// Vérifier que la référence de la classe est disponible
	if( DB_tester_classe_reference($_SESSION['STRUCTURE_ID'],$ref) )
	{
		exit('Erreur : référence déjà existante !');
	}
	// Insérer l'enregistrement
	$groupe_id = DB_ajouter_groupe($_SESSION['STRUCTURE_ID'],'classe',0,$ref,$nom,$niveau);
	// Afficher le retour
	echo'<tr id="id_'.$groupe_id.'" class="new">';
	echo	'<td>{{NIVEAU_NOM}}</td>';
	echo	'<td>'.html($ref).'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cette classe."></q>';
	echo		'<q class="supprimer" title="Supprimer cette classe."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier une classe existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $niveau && $ref && $nom )
{
	// Vérifier que la référence de la classe est disponible
	if( DB_tester_classe_reference($_SESSION['STRUCTURE_ID'],$ref,$id) )
	{
		exit('Erreur : référence déjà existante !');
	}
	// Mettre à jour l'enregistrement
	DB_modifier_groupe($_SESSION['STRUCTURE_ID'],$id,$ref,$nom,$niveau);
	// Afficher le retour
	echo'<td>{{NIVEAU_NOM}}</td>';
	echo'<td>'.html($ref).'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cette classe."></q>';
	echo	'<q class="supprimer" title="Supprimer cette classe."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une classe existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $id )
{
	// Effacer l'enregistrement
	DB_supprimer_groupe($_SESSION['STRUCTURE_ID'],$id,'classe');
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
