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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action         = (isset($_POST['f_action']))     ? $_POST['f_action']                 : '';
$eleve_id       = (isset($_POST['f_eleve_id']))   ? clean_entier($_POST['f_eleve_id']) : 0 ;
$tab_parents_id = (isset($_POST['f_parents_id'])) ? array_map('clean_entier',explode(',','0,'.$_POST['f_parents_id'])) : array() ; // On ajoute "0," pour que les ids soient indexés sur 1;2;3;4 ($resp_legal_num)

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier la liste des parents d'un élève
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='enregistrer_parents') && $eleve_id && (count($tab_parents_id)==5) )
{
	$tab_parents_id = array_filter($tab_parents_id,'non_zero');
	// supprimer les liens de responsabilité de l'élève concerné (il est plus simple de réinitialiser que de traiter les resp un par un puis de vérifier s'il n'en reste pas à supprimer...)
	DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_jointures_parents_for_eleves($eleve_id);
	// modifier les liens de responsabilité
	foreach($tab_parents_id as $resp_legal_num => $parent_id)
	{
		DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_jointure_parent_eleve($parent_id,$eleve_id,$resp_legal_num);
	}
	// On enbraye sur l'affichage actualisé des parents de l'élève
	$action = 'afficher_parents';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Charger la liste des parents d'un élève
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='afficher_parents') && $eleve_id )
{
	$tab_parents = array_fill(1,4,'<table><tbody><tr><th class="vu" style="width:6em">$TITRE$</th><td>---</td><th class="nu"><q class="ajouter" title="Ajouter un responsable."></q></th></tr></tbody></table>');
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_actifs_avec_infos_for_eleve($eleve_id);
	foreach($DB_TAB AS $key => $DB_ROW)
	{
		$identite        = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
		$tab_adresse     = array( $DB_ROW['adresse_ligne1'] , $DB_ROW['adresse_ligne2'] , $DB_ROW['adresse_ligne3'] , $DB_ROW['adresse_ligne4'] , $DB_ROW['adresse_postal_code'] , $DB_ROW['adresse_postal_libelle'] , $DB_ROW['adresse_pays_nom'] );
		$adresse         = html(implode(' ; ',array_filter($tab_adresse,'non_vide')));
		$responsabilites = html($DB_ROW['enfants_liste']);
		$tab_parents[$DB_ROW['resp_legal_num']] = '<table id="parent_'.$DB_ROW['parent_id'].'"><tbody><tr><th class="vu" style="width:6em">$TITRE$</th><td><em>'.$identite.'</em><hr /><img alt="" src="./_img/home.png" /> '.$adresse.'<br /><img alt="" src="./_img/groupe.png" /> '.$responsabilites.'</td><th class="nu"><q class="modifier" title="Changer ce responsable."></q><q class="supprimer" title="Retirer ce responsable."></q></th></tr></tbody></table>';
	}
	foreach($tab_parents AS $resp_legal_num => $affichage)
	{
		$tab_parents[$resp_legal_num] = str_replace( '$TITRE$' , 'Resp légal '.$resp_legal_num , $affichage );
	}
	exit(implode('<div class="ti"><input type="image" src="./_img/action_ordonner.png" title="Echanger ces responsables" /></div>',$tab_parents));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
