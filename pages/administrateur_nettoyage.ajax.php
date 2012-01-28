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

$top_depart = microtime(TRUE);

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Recherche et correction de numérotations anormales
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='numeroter')
{
	// Bloquer l'application
	bloquer_application('automate',$_SESSION['BASE'],'Recherche et correction de numérotations anormales en cours.');
	// Rechercher et corriger les anomalies
	$tab_bilan = DB_STRUCTURE_ADMINISTRATEUR::DB_corriger_numerotations();
	// Débloquer l'application
	debloquer_application('automate',$_SESSION['BASE']);
	// Afficher le retour
	echo'<li>'.implode('</li><li>',$tab_bilan).'</li>';
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Recherche et correction de numérotations anormales réalisée en '.$duree.'s.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Recherche et suppression de correspondances anormales
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='nettoyer')
{
	// Bloquer l'application
	bloquer_application('automate',$_SESSION['BASE'],'Recherche et suppression de données orphelines en cours.');
	// Rechercher et corriger les anomalies
	$tab_bilan = DB_STRUCTURE_ADMINISTRATEUR::DB_corriger_anomalies();
	// Débloquer l'application
	debloquer_application('automate',$_SESSION['BASE']);
	// Afficher le retour
	echo'<li>'.implode('</li><li>',$tab_bilan).'</li>';
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Recherche et suppression de données orphelines réalisée en '.$duree.'s.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Initialisation annuelle des données
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='purger')
{

	// Bloquer l'application
	bloquer_application('automate',$_SESSION['BASE'],'Purge annuelle de la base en cours.');
	// Supprimer tous les devoirs associés aux classes, mais pas les saisies associées
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_devoirs_sans_saisies();
	ajouter_log_SACoche('Suppression de tous les devoirs sans les saisies associées.');
	// Supprimer tous les types de groupes, sauf les classes (donc 'groupe' ; 'besoin' ; 'eval'), ainsi que les jointures avec les périodes.
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_groupes_sauf_classes();
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_groupe_par_admin( $DB_ROW['groupe_id'] , $DB_ROW['groupe_type'] , FALSE /*with_devoir*/ );
		}
	}
	ajouter_log_SACoche('Suppression de tous les groupes, hors classes, sans les devoirs associés.');
	// Supprimer les jointures classes/périodes
	DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_groupe_periode($groupe_id=true,$periode_id=true,$etat=false,$date_debut_mysql='',$date_fin_mysql='');
	// Supprimer les bulletins
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_bulletins();
	// Supprimer les comptes utilisateurs désactivés depuis plus de 3 ans
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_desactives_obsoletes();
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$param_profil = ($DB_ROW['user_profil']=='eleve') ? 'eleve' : 'professeur' ; // On transmet 'professeur' y compris pour les directeurs.
			DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur($DB_ROW['user_id'],$param_profil);
			// Log de l'action
			ajouter_log_SACoche('Suppression d\'un utilisateur ('.$param_profil.' '.$DB_ROW['user_id'].').');
		}
	}
	// Supprimer les demandes d'évaluations, ainsi que les reliquats de notes 'REQ'
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_demandes_evaluation();
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_saisies_REQ();
	// En profiter pour optimiser les tables (1 fois par an, ça ne peut pas faire de mal)
	DB_STRUCTURE_ADMINISTRATEUR::DB_optimiser_tables_structure();
	// Débloquer l'application
	debloquer_application('automate',$_SESSION['BASE']);
	// Afficher le retour
	echo'<li><label class="valide">Évaluations supprimées (saisies associées conservées).</label></li>';
	echo'<li><label class="valide">Groupes supprimés (avec leurs associations).</label></li>';
	echo'<li><label class="valide">Jointures classes / périodes supprimées.</label></li>';
	echo'<li><label class="valide">Comptes utilisateurs obsolètes supprimés.</label></li>';
	echo'<li><label class="valide">Demandes d\'évaluations supprimées.</label></li>';
	echo'<li><label class="valide">Tables optimisées par MySQL (équivalent d\'un défragmentage).</label></li>';
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Initialisation annuelle de la base réalisée en '.$duree.'s.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Suppression des notes et des validations
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='supprimer')
{
	// Bloquer l'application
	bloquer_application('automate',$_SESSION['BASE'],'Suppression des notes et des validations en cours.');
	// Supprimer toutes les saisies aux évaluations
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_saisies();
	// Supprimer toutes les validations du socle
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_validations();
	// Débloquer l'application
	debloquer_application('automate',$_SESSION['BASE']);
	// Afficher le retour
	echo'<li><label class="valide">Notes saisies aux évaluations supprimées.</label></li>';
	echo'<li><label class="valide">Validations des items et des compétences du socle supprimées.</label></li>';
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Suppression des notes et des validations réalisée en '.$duree.'s.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Effacement des étiquettes nom & prénom
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='effacer')
{
	effacer_fichiers_temporaires('./__tmp/badge/'.$_SESSION['BASE'] , 0);
	// Afficher le retour
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Suppression des étiquettes nom &amp; prénom réalisée en '.$duree.'s.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// On ne devrait pas en arriver là...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
exit('Erreur avec les données transmises !');

?>
