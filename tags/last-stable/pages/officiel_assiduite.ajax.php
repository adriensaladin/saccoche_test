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
if( ($_SESSION['SESAMATH_ID']==ID_DEMO) && (!in_array($_POST['f_action'],array('import_siecle','afficher_formulaire_manuel'))) ) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))  ? $_POST['f_action']                 : '';
$periode_id = (isset($_POST['f_periode'])) ? Clean::entier($_POST['f_periode']) : 0;
$groupe_id  = (isset($_POST['f_groupe']))  ? Clean::entier($_POST['f_groupe'])  : 0;
$datas      = (isset($_POST['f_data']))    ? Clean::texte($_POST['f_data'])     : '';

$fichier_dest = 'import_siecle'.$_SESSION['BASE'].'_'.session_id().'.xml';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réception et analyse d'un fichier d'import
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import_siecle') && $periode_id )
{
	// Récupération du fichier (zip ou pas)
	$result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_dest /*fichier_nom*/ , array('zip','xml') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , 'SIECLE_exportAbsence.xml' /*filename_in_zip*/ );
	if($result!==TRUE)
	{
		exit('Erreur : '.$result);
	}
	// Vérification du fichier
	$xml = @simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
	if($xml===FALSE)
	{
		exit('Erreur : le fichier transmis n\'est pas un XML valide !');
	}
	$uai = (string)$xml->PARAMETRES->UAJ;
	if(!$uai)
	{
		exit('Erreur : le fichier transmis ne comporte pas de numéro UAI !');
	}
	if($uai!=$_SESSION['WEBMESTRE_UAI'])
	{
		exit('Erreur : le fichier transmis est issu de l\'établissement '.$uai.' et non '.$_SESSION['WEBMESTRE_UAI'].' !');
	}
	$annee_scolaire     = (string)$xml->PARAMETRES->ANNEE_SCOLAIRE;
	$date_export        = (string)$xml->PARAMETRES->DATE_EXPORT;
	$periode_libelle    = (string)$xml->PERIODE->LIBELLE;
	$periode_date_debut = (string)$xml->PERIODE->DATE_DEBUT;
	$periode_date_fin   = (string)$xml->PERIODE->DATE_FIN;
	if( !$annee_scolaire || !$date_export || !$periode_libelle || !$periode_date_debut || !$periode_date_fin )
	{
		exit('Erreur : informations manquantes (année scolaire, période...) !');
	}
	// On affiche la demande de confirmation
	exit('<p class="astuce">Ce fichier, généré le <b>'.html($date_export).'</b>, comporte les données de la période <b>'.html($periode_libelle).'</b>, allant du <b>'.html($periode_date_debut).'</b> au <b>'.html($periode_date_fin).'</b>.</p>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement d'un fichier d'import
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='traitement_siecle') && $periode_id )
{
	// Récupération des données de la base
	$tab_users_base = array();
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' , 2 /*actuels_et_anciens*/ , 'user_id,user_sconet_elenoet' /*liste_champs*/ , FALSE /*with_classe*/ , FALSE /*tri_statut*/ );
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base[(int)$DB_ROW['user_sconet_elenoet']] = (int)$DB_ROW['user_id'];
	}
	// Récupération des données du fichier, analyse et maj du contenu de la base
	if(!is_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest))
	{
		exit('Erreur : fichier non retrouvé !');
	}
	$lignes_ok = '';
	$lignes_ko = '';
	$xml = simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
	if($xml->eleve)
	{
		foreach ($xml->eleve as $eleve)
		{
			$eleve_elenoet   = Clean::entier($eleve->attributes()->elenoet);
			$eleve_nom       = Clean::nom(   $eleve->attributes()->nomEleve);
			$eleve_prenom    = Clean::prenom($eleve->attributes()->prenomEleve);
			$nb_absence      = Clean::entier($eleve->attributes()->nbAbs);
			$nb_non_justifie = Clean::entier($eleve->attributes()->nbNonJustif);
			$nb_retard       = Clean::entier($eleve->attributes()->nbRet);
			if(isset($tab_users_base[$eleve_elenoet]))
			{
				$user_id = $tab_users_base[$eleve_elenoet];
				DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_assiduite($periode_id,$user_id,$nb_absence,$nb_non_justifie,$nb_retard);
				$lignes_ok .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td><td>'.$nb_absence.'</td><td>'.$nb_non_justifie.'</td><td>'.$nb_retard.'</td></tr>';
			}
			else
			{
				$lignes_ko .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td><td colspan="3" class="r">Numéro Sconet ("ELENOET") '.$eleve_elenoet.' non trouvé dans la base.</td></tr>';
			}
		}
	}
	if( (!$lignes_ok) && (!$lignes_ko) )
	{
		exit('Erreur : aucun élève trouvé dans le fichier !');
	}
	// affichage du retour
	exit('<tbody>'.$lignes_ok.$lignes_ko.'</tbody>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le formulaire de saisie manuel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='afficher_formulaire_manuel') && $periode_id && $groupe_id )
{
	// liste des élèves
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $groupe_id );
	if(empty($DB_TAB))
	{
		exit('Aucun élève trouvé dans ce regroupement !');
	}
	$tab_eleves = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleves[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
	}
	// liste des saisies
	$tab_assiduite = array();
	$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_assiduite( $periode_id , array_keys($tab_eleves) );
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_assiduite[$DB_ROW['user_id']] = array(
			'absence'      => $DB_ROW['assiduite_absence'],
			'non_justifie' => $DB_ROW['assiduite_non_justifie'],
			'retard'       => $DB_ROW['assiduite_retard']
		);
	}
	// affichage du tableau
	$lignes = '';
	foreach($tab_eleves as $user_id => $user_nom_prenom)
	{
		if(isset($tab_assiduite[$user_id]))
		{
			$nb_absence      = is_null($tab_assiduite[$user_id]['absence'])      ? '' : (int)$tab_assiduite[$user_id]['absence'] ;
			$nb_non_justifie = is_null($tab_assiduite[$user_id]['non_justifie']) ? '' : (int)$tab_assiduite[$user_id]['non_justifie'] ;
			$nb_retard       = is_null($tab_assiduite[$user_id]['retard'])       ? '' : (int)$tab_assiduite[$user_id]['retard'] ;
		}
		else
		{
			$nb_absence = $nb_non_justifie = $nb_retard = '' ;
		}
		$lignes .= '<tr id="tr_'.$user_id.'"><td>'.html($user_nom_prenom).'</td><td><input type="text" size="3" maxlength="3" id="td1_'.$user_id.'" value="'.$nb_absence.'" /></td><td><input type="text" size="3" maxlength="3" id="td2_'.$user_id.'" value="'.$nb_non_justifie.'" /></td><td><input type="text" size="3" maxlength="3" id="td3_'.$user_id.'" value="'.$nb_retard.'" /></td></tr>';
	}
	exit('<tbody>'.$lignes.'</tbody>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement de saisies manuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='enregistrer_saisies') && $periode_id && $datas )
{
	// Récupération des données saisies
	$tab_eleves = explode('_',$datas);
	foreach($tab_eleves as $eleves_infos)
	{
		list($user_id,$nb_absence,$nb_non_justifie,$nb_retard) = explode('.',$eleves_infos);
		$user_id        = (int)$user_id;
		$nb_absence      = ($nb_absence==='')      ? NULL : (int)$nb_absence ;
		$nb_non_justifie = ($nb_non_justifie==='') ? NULL : (int)$nb_non_justifie ;
		$nb_retard       = ($nb_retard==='')       ? NULL : (int)$nb_retard ;
		DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_assiduite($periode_id,$user_id,$nb_absence,$nb_non_justifie,$nb_retard);
	}
	exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
