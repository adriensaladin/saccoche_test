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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir    : affichage des données d'un élève | enregistrement/suppression d'une appréciation ou d'une note | recalculer une note
// Examiner  : recherche des saisies manquantes (notes et appréciations)
// Consulter : affichage des données d'un élève (HTML)
// Imprimer  : affichage de la liste des élèves | étape d'impression PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array( $section , array('officiel_saisir','officiel_examiner','officiel_consulter','officiel_imprimer') ) )
{
	require(CHEMIN_DOSSIER_INCLUDE.'code_'.$section.'.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Signaler une erreur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='signaler_erreur')
{
	$_POST['f_action']='ajouter';
	require(CHEMIN_DOSSIER_PAGES.'compte_message.ajax.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer une impression PDF des appréciations d'un prof ; que pour les bulletins actuellement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_appreciations')
{

	$BILAN_TYPE   = (isset($_POST['f_bilan_type']))   ? Clean::texte($_POST['f_bilan_type'])   : '';
	$periode_id   = (isset($_POST['f_periode']))      ? Clean::entier($_POST['f_periode'])     : 0;
	$classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])      : 0;
	$groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])      : 0;

	// On vérifie les paramètres principaux

	$tab_types = array
	(
		'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
		'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
		'palier1'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 1'  ) ,
		'palier2'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 2'  ) ,
		'palier3'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 3'  )
	);

	if( ($BILAN_TYPE!='bulletin') || (!$periode_id) || (!$classe_id) ) // (!isset($tab_types[$BILAN_TYPE]))
	{
		exit('Erreur avec les données transmises !');
	}

	// On vérifie que le bilan est bien accessible et on récupère les infos associées

	$DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$BILAN_TYPE);
	if(empty($DB_ROW))
	{
		exit('Association classe / période introuvable !');
	}
	$date_debut  = $DB_ROW['jointure_date_debut'];
	$date_fin    = $DB_ROW['jointure_date_fin'];
	$BILAN_ETAT  = $DB_ROW['officiel_'.$BILAN_TYPE];
	$periode_nom = $DB_ROW['periode_nom'];
	$classe_nom  = $DB_ROW['groupe_nom'];
	if(!$BILAN_ETAT)
	{
		exit('Bilan introuvable !');
	}

	// Récupérer la liste des élèves (on pourrait se faire transmettre les ids par l'envoi ajax, mais on a aussi besoin des noms-prénoms.

	$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;
	$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
	if(empty($DB_TAB))
	{
		exit('Aucun élève trouvé dans ce regroupement !');
	}
	$tab_eleve_id = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleve_id[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
	}
	$liste_eleve_id = implode(',',array_keys($tab_eleve_id));

	// Récupérer les saisies effectuées pour le bilan officiel concerné et pour le prof concerné

	$tab_saisie = array();	// [eleve_id][rubrique_id] => array(matiere_nom,appreciation,note);
	$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies( $BILAN_TYPE , $periode_id , $liste_eleve_id , $_SESSION['USER_ID'] ); // Restreindre au prof ? Récupérer nom matière ou palier ?
	if( (empty($DB_TAB)) || (!isset($DB_TAB[$_SESSION['USER_ID']])) )
	{
		exit('Aucune appréciation trouvée de votre part !');
	}
	// Les appréciations
	foreach($DB_TAB[$_SESSION['USER_ID']] as $DB_ROW)
	{
		$matiere_nom = ($DB_ROW['matiere_nom']!==NULL) ? $DB_ROW['matiere_nom'] : 'Appréciation générale' ;
		$tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']] = array( 'matiere_nom'=>$matiere_nom , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>NULL );
	}
	// Les notes qui vont avec (attention, la requêtes renvoie les notes de toutes les rubriques, il faut ne prendre que celles qui vont avec les appréciations).
	if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
	{
		foreach($DB_TAB[0] as $DB_ROW)
		{
			if(isset($tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]))
			{
				$tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['note'] = $DB_ROW['saisie_note'];
			}
		}
	}

	// Y a plus qu'à fabriquer le PDF

	$nb_appreciations = count($DB_TAB[$_SESSION['USER_ID']]);
	$nb_eleves = count($tab_saisie);
	$releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 10 /*marge_haut*/ , 10 /*marge_bas*/ , 'non' /*couleur*/ );
	$releve_PDF->tableau_appreciation_initialiser($nb_appreciations,$nb_eleves,$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']);
	$releve_PDF->tableau_appreciation_intitule('Relevé d\'appréciations - '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM'].' - '.$classe_nom.' - '.$periode_nom);
	// Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
	foreach($tab_eleve_id as $eleve_id => $eleve_nom_prenom)
	{
		if(isset($tab_saisie[$eleve_id]))
		{
			$releve_PDF->tableau_appreciation_interligne();
			foreach($tab_saisie[$eleve_id] as $rubrique_id => $tab)
			{
				extract($tab);	// $matiere_nom $appreciation $note
				$releve_PDF->tableau_appreciation_rubrique($eleve_nom_prenom,$matiere_nom,$appreciation,$note,$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']);
			}
		}
	}
	$fichier_export = 'releve_appreciations_'.Clean::fichier($periode_nom).'_'.Clean::fichier($classe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea().'.pdf';
	$releve_PDF->Output(CHEMIN_DOSSIER_EXPORT.$fichier_export,'F');
	exit('<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fichier_export.'"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li></ul>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
