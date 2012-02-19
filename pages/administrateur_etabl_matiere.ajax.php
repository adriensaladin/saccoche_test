<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))   ? clean_texte($_POST['f_action'])    : '';
$famille_id = (isset($_POST['f_famille']))  ? clean_entier($_POST['f_famille'])  : 0 ;
$motclef    = (isset($_POST['f_motclef']))  ? clean_texte($_POST['f_motclef'])   : '' ;
$matiere_id = (isset($_POST['f_matiere']))  ? clean_entier($_POST['f_matiere'])  : 0 ;
$id_avant   = (isset($_POST['f_id_avant'])) ? clean_entier($_POST['f_id_avant']) : 0;
$id_apres   = (isset($_POST['f_id_apres'])) ? clean_entier($_POST['f_id_apres']) : 0;
$id         = (isset($_POST['f_id']))       ? clean_entier($_POST['f_id'])       : 0;
$ref        = (isset($_POST['f_ref']))      ? clean_ref($_POST['f_ref'])         : '';
$nom        = (isset($_POST['f_nom']))      ? clean_texte($_POST['f_nom'])       : '';

$tab_id = (isset($_POST['tab_id']))   ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
sort($tab_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher les matières partagées d'une famille donnée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='recherche_matiere_famille') && $famille_id )
{
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_famille($famille_id);
	foreach($DB_TAB as $DB_ROW)
	{
		$class = ($DB_ROW['matiere_active']) ? 'ajouter_non' : 'ajouter' ;
		$title = ($DB_ROW['matiere_active']) ? 'Matière déjà choisie.' : 'Ajouter cette matière.' ;
		echo'<li>'.html($DB_ROW['matiere_nom'].' ('.$DB_ROW['matiere_ref'].')').'<q id="add_'.$DB_ROW['matiere_id'].'" class="'.$class.'" title="'.$title.'"></q></li>';
	}
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher les matières partagées d'une recherche par mot clef
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='recherche_matiere_motclef') && $motclef )
{
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matiere_motclef($motclef);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$class = ($DB_ROW['matiere_active']) ? 'ajouter_non' : 'ajouter' ;
			$title = ($DB_ROW['matiere_active']) ? 'Matière déjà choisie.' : 'Ajouter cette matière.' ;
			echo'<li>['.round($DB_ROW['score']).'%] <i>'.html($DB_ROW['matiere_famille_nom']).'</i> || '.html($DB_ROW['matiere_nom'].' ('.$DB_ROW['matiere_ref'].')').'<q id="add_'.$DB_ROW['matiere_id'].'" class="'.$class.'" title="'.$title.'"></q></li>';
		}
	}
	else
	{
		echo'<li class="i">Recherche infructueuse...</li>';
	}
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un choix de matière partagée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='ajouter_partage') && $matiere_id )
{
	DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_matiere_partagee($matiere_id,1);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter une nouvelle matière spécifique
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='ajouter_perso') && $ref && $nom )
{
	// Vérifier que la référence de la matière est disponible
	if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_matiere_reference($ref) )
	{
		exit('Erreur : référence déjà existante !');
	}
	// Insérer l'enregistrement
	$id = DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_matiere_specifique($ref,$nom);
	// Afficher le retour
	exit(']¤['.$id.']¤['.html($ref).']¤['.html($nom));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier une matière spécifique existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='modifier') && $id && $ref && $nom )
{
	// Vérifier que la référence de la matière est disponible
	if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_matiere_reference($ref,$id) )
	{
		exit('Erreur : référence officielle déjà prise !');
	}
	// Mettre à jour l'enregistrement
	DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_matiere_specifique($id,$ref,$nom);
	// Afficher le retour
	exit(']¤['.$id.']¤['.html($ref).']¤['.html($nom));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retirer une matière partagée || Supprimer une matière spécifique existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function retirer_ou_supprimer_matiere($id)
{
	if($id>ID_MATIERE_PARTAGEE_MAX)
	{
		DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_matiere_specifique($id);
		// Log de l'action
		ajouter_log_SACoche('Suppression d\'une matière spécifique (n°'.$id.').');
		ajouter_log_SACoche('Suppression de référentiels (matière '.$id.').');
	}
	else
	{
		DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_matiere_partagee($id,0);
		// Log de l'action
		ajouter_log_SACoche('Retrait d\'une matière partagée (n°'.$id.').');
	}
}

if( ($action=='supprimer') && $id )
{
	retirer_ou_supprimer_matiere($id);
	// Afficher le retour
	exit(']¤['.$id);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Déplacer les référentiels d'une matière vers une autre
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='deplacer_referentiels') && $id_avant && $id_apres && ($id_avant!=$id_apres) )
{
	// Déplacement après vérification que c'est possible matière de destination vierge de données)
	// 
	$is_ok = DB_STRUCTURE_ADMINISTRATEUR::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
	if(!$is_ok)
	{
		exit('Erreur : la nouvelle matière contient déjà des données !');
	}
	// Log de l'action
	ajouter_log_SACoche('Déplacement des référentiels d\'une matière ('.$id_avant.' to '.$id_apres.').');
	// Retirer l'ancienne matière partagée || Supprimer l'ancienne matière spécifique existante
	retirer_ou_supprimer_matiere($id_avant);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');
?>
