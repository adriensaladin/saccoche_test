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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$palier_id    = (isset($_POST['f_palier']))     ? clean_entier($_POST['f_palier'])    : 0;
$palier_nom   = (isset($_POST['f_palier_nom'])) ? clean_texte($_POST['f_palier_nom']) : '';
$groupe_id    = (isset($_POST['f_groupe']))     ? clean_entier($_POST['f_groupe'])    : 0;
$groupe_nom   = (isset($_POST['f_groupe_nom'])) ? clean_texte($_POST['f_groupe_nom']) : '';
$tab_eleve_id = (isset($_POST['eleves']))       ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;

$tab_eleve_id = array_filter($tab_eleve_id,'positif');
$liste_eleve  = implode(',',$tab_eleve_id);

if( (!$palier_id) || (!$palier_nom) || (!$groupe_id) || (!$groupe_nom) || (!count($tab_eleve_id)) )
{
	exit('Erreur avec les données transmises !');
}

$tab_pilier       = array();	// [pilier_id] => array(pilier_ref,pilier_nom,pilier_nb_entrees);
$tab_socle        = array();	// [pilier_id][socle_id] => array(section_nom,socle_nom);
$tab_liste_entree = array();	// [i] => entree_id
$tab_eleve        = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
$tab_user_entree  = array();	// [eleve_id][entree_id] => array(etat,date,info);
$tab_user_pilier  = array();	// [eleve_id][pilier_id] => array(etat,date,info);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des items du socle pour le palier sélectionné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$DB_TAB = DB_STRUCTURE_recuperer_arborescence_palier($palier_id);
if(!count($DB_TAB))
{
	exit('Aucun item référencé pour ce palier du socle commun !');
}
$pilier_id  = 0;
$socle_id   = 0;
foreach($DB_TAB as $DB_ROW)
{
	if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
	{
		$pilier_id  = $DB_ROW['pilier_id'];
		$tab_pilier[$pilier_id] = array('pilier_ref'=>$DB_ROW['pilier_ref'],'pilier_nom'=>$DB_ROW['pilier_nom'],'pilier_nb_entrees'=>0);
	}
	if(!is_null($DB_ROW['section_id']))
	{
		$section_nom = $DB_ROW['section_nom'];
	}
	if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
	{
		$socle_id = $DB_ROW['entree_id'];
		$tab_socle[$pilier_id][$socle_id] = $section_nom.' » '.$DB_ROW['entree_nom'];
		$tab_pilier[$pilier_id]['pilier_nb_entrees']++;
		$tab_liste_entree[] = $socle_id;
	}
}
$listing_entree_id = implode(',',$tab_liste_entree);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_eleve = DB_STRUCTURE_lister_eleves_cibles($liste_eleve);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des validations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// On commence par remplir tout le tableau des items pour ne pas avoir ensuite à tester tout le temps si le champ existe
foreach($tab_eleve_id as $eleve_id)
{
	foreach($tab_liste_entree as $entree_id)
	{
		$tab_user_entree[$eleve_id][$entree_id] = array('etat'=>2,'date'=>'','info'=>'');
	}
}
//Maintenant on complète avec les valeurs de la base
$DB_TAB = DB_STRUCTURE_lister_jointure_user_entree($liste_eleve,$listing_entree_id,$domaine_id=0,$pilier_id=0,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les entrées
foreach($DB_TAB as $DB_ROW)
{
	$tab_user_entree[$DB_ROW['user_id']][$DB_ROW['entree_id']] = array('etat'=>$DB_ROW['validation_entree_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_entree_date']),'info'=>$DB_ROW['validation_entree_info']);
}
// On commence par remplir tout le tableau des piliers pour ne pas avoir ensuite à tester tout le temps si le champ existe
foreach($tab_eleve_id as $eleve_id)
{
	foreach($tab_pilier as $pilier_id => $tab)
	{
		$tab_user_pilier[$eleve_id][$pilier_id] = array('etat'=>2,'date'=>'','info'=>'');
	}
}
//Maintenant on complète avec les valeurs de la base
$listing_pilier_id = implode(',',array_keys($tab_pilier));
$DB_TAB = DB_STRUCTURE_lister_jointure_user_pilier($liste_eleve,$listing_pilier_id,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les piliers
foreach($DB_TAB as $DB_ROW)
{
	$tab_user_pilier[$DB_ROW['user_id']][$DB_ROW['pilier_id']] = array('etat'=>$DB_ROW['validation_pilier_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_pilier_date']),'info'=>$DB_ROW['validation_pilier_info']);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Elaboration du bilan relatif au socle, en HTML et PDF => Production et mise en page
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$eleves_nb   = count($tab_eleve_id);
$items_nb    = count($tab_liste_entree);
$cellules_nb = $items_nb+1;
$releve_html  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
$releve_html .= '<style type="text/css">th{text-align:center}tbody td{width:8px;height:8px}</style>';
$releve_html .= '<h1>Synthèse des validations du socle</h1>';
$releve_html .= '<h2>'.html($groupe_nom).' - '.html($palier_nom).'</h2>';
// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
require('./_fpdf/fpdf.php');
require('./_inc/class.PDF.php');
$releve_pdf = new PDF($orientation='landscape',$marge_min=7.5,$couleur='oui');
$releve_pdf->releve_synthese_socle_initialiser($groupe_nom,$palier_nom,$eleves_nb,$items_nb);
// - - - - - - - - - -
// Lignes d'en-tête
// - - - - - - - - - -
$releve_html_head = '<tr><td class="nu" rowspan="2"></td>';
foreach($tab_pilier as $tab)
{
	extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
	$texte = ($pilier_nb_entrees>10) ? 'Compétence ' : 'Comp. ' ;
	$releve_html_head .= '<th colspan="'.$pilier_nb_entrees.'" title="'.html($pilier_nom).'">'.$texte.$pilier_ref.'</th>';
}
$releve_html_head .= '</tr><tr>';
foreach($tab_socle as $tab)
{
	foreach($tab as $socle_nom)
	{
		$releve_html_head .= '<th class="info" title="'.html($socle_nom).'"></th>';
	}
}
$releve_html_head .= '</tr></thead>';
$releve_pdf->releve_synthese_socle_entete($tab_pilier);
// - - - - - - - - - -
// Lignes suivantes
// - - - - - - - - - -
$releve_html_body = '';
// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
	$releve_html_body .= '<tr><td class="nu" colspan="'.$cellules_nb.'" style="height:4px"></td></tr>';
	$releve_html_body .= '<tr><td rowspan="2">'.html($eleve_nom).' '.html($eleve_prenom).'</td>';
	// - - - - -
	// Validation des compétences
	// - - - - -
	// Pour chaque pilier...
	foreach($tab_pilier as $pilier_id => $tab)
	{
		extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
		$etat = $tab_user_pilier[$eleve_id][$pilier_id]['etat'];
		$title = '';
		if($etat!=2)
		{
			$nature = ($etat) ? 'Validé' : 'Invalidé' ;
			$title  = ' title="'.$nature.' le '.$tab_user_pilier[$eleve_id][$pilier_id]['date'].' par '.html($tab_user_pilier[$eleve_id][$pilier_id]['info']).'"';
		}
		$releve_html_body .= '<td colspan="'.$pilier_nb_entrees.'" class="v'.$etat.'"'.$title.'></td>';
	}
	$releve_html_body .= '</tr><tr>';
	// - - - - -
	// Validation des items
	// - - - - -
	// Pour chaque entrée du socle...
	foreach($tab_socle as $pilier_id => $tab)
	{
		foreach($tab as $socle_id => $socle_nom)
		{
			$etat = $tab_user_entree[$eleve_id][$socle_id]['etat'];
			$title = '';
			if($etat!=2)
			{
				$nature = ($etat) ? 'Validé' : 'Invalidé' ;
				$title  = ' title="'.$nature.' le '.$tab_user_entree[$eleve_id][$socle_id]['date'].' par '.html($tab_user_entree[$eleve_id][$socle_id]['info']).'"';
			}
			$classe = ( ($tab_user_pilier[$eleve_id][$pilier_id]['etat']==1) && ($etat==2) && (!$_SESSION['USER_DALTONISME']) ) ? '' : ' class="v'.$etat.'"' ;
			$releve_html_body .= '<td '.$classe.$title.'></td>';
		}
	}
	$releve_html_body .= '</tr>';
	$releve_pdf->releve_synthese_socle_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_user_pilier,$tab_user_entree,$tab_pilier,$tab_socle);
}
$releve_html .= '<table class="bilan"><thead>'.$releve_html_head.'</thead><tbody>'.$releve_html_body.'</tbody></table><p />';

// Chemins d'enregistrement
$dossier      = './__tmp/export/';
$fichier_lien = 'releve_synthese_socle_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();
// On enregistre les sorties HTML et PDF
Ecrire_Fichier($dossier.$fichier_lien.'.html',$releve_html);
$releve_pdf->Output($dossier.$fichier_lien.'.pdf','F');
// Affichage du résultat
echo'<ul class="puce">';
echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Archiver / Imprimer (format <em>pdf</em>).</a></li>';
echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'">Explorer / Détailler (format <em>html</em>).</a></li>';
echo'</ul><p />';

?>
