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

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( array_map( 'clean_entier' , explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$num    = (isset($_POST['num']))      ? clean_entier($_POST['num'])     : 0 ;	// Numéro de l'étape en cours
$max    = (isset($_POST['max']))      ? clean_entier($_POST['max'])     : 0 ;	// Nombre d'étapes à effectuer

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des structures avant recherche des stats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='calculer') && $nb_bases )
{
	// Pour mémoriser les totaux
	$_SESSION['tmp']['totaux'] = array( 'prof_nb'=>0 , 'prof_use'=>0 , 'eleve_nb'=>0 , 'eleve_use'=>0 , 'score_nb'=>0 );
	// Mémoriser les données des structures concernées par les stats
	$_SESSION['tmp']['infos'] = array();
	$DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures( implode(',',$tab_base_id) );
	foreach($DB_TAB as $DB_ROW)
	{
		$_SESSION['tmp']['infos'][] = array(
			'base_id'                => $DB_ROW['sacoche_base'] ,
			'structure_denomination' => $DB_ROW['structure_denomination'] ,
			'contact'                => $DB_ROW['structure_contact_nom'].' '.$DB_ROW['structure_contact_prenom'] ,
			'inscription_date'       => $DB_ROW['structure_inscription_date']
		);
	}
	// Retour
	$max = $nb_bases + 1 ; // La dernière étape consistera à vider la session temporaire et à renvoyer les totaux
	exit('ok-'.$max);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape de récupération des stats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='calculer') && $num && $max && ($num<$max) )
{
	// Récupérer les infos ($base_id $structure_denomination $contact $inscription_date)
	extract($_SESSION['tmp']['infos'][$num-1]);
	// Récupérer une série de stats
	charger_parametres_mysql_supplementaires($base_id);
	list($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb) = DB_STRUCTURE_WEBMESTRE::DB_recuperer_statistiques();
	// maj les totaux
	$_SESSION['tmp']['totaux']['prof_nb']   += $prof_nb;
	$_SESSION['tmp']['totaux']['prof_use']  += $prof_use;
	$_SESSION['tmp']['totaux']['eleve_nb']  += $eleve_nb;
	$_SESSION['tmp']['totaux']['eleve_use'] += $eleve_use;
	$_SESSION['tmp']['totaux']['score_nb']  += $score_nb;
	// Retour
	exit('ok-<tr><td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td><td class="label">'.$base_id.'</td><td class="label">'.html($structure_denomination).'</td><td class="label">'.html($contact).'</td><td class="label">'.$inscription_date.'</td><td class="label">'.$prof_nb.'</td><td class="label">'.$prof_use.'</td><td class="label">'.$eleve_nb.'</td><td class="label">'.$eleve_use.'</td><td class="label"><i>'.sprintf("%07u",$score_nb).'</i>'.number_format($score_nb,0,'',' ').'</td></tr>');
}
if( ($action=='calculer') && $num && $max && ($num==$max) )
{
	$ligne_total = '<tr><td class="nu"></td><th colspan="4" class="hc">Total</th><th class="hc">'.number_format($_SESSION['tmp']['totaux']['prof_nb'],0,'',' ').'</th><th class="hc">'.number_format($_SESSION['tmp']['totaux']['prof_use'],0,'',' ').'</th><th class="hc">'.number_format($_SESSION['tmp']['totaux']['eleve_nb'],0,'',' ').'</th><th class="hc">'.number_format($_SESSION['tmp']['totaux']['eleve_use'],0,'',' ').'</th><th class="hc">'.number_format($_SESSION['tmp']['totaux']['score_nb'],0,'',' ').'</th></tr>';
	unset($_SESSION['tmp']);
	exit('ok-'.$ligne_total);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer plusieurs structures existantes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='supprimer') && $nb_bases )
{
	foreach($tab_base_id as $base_id)
	{
		supprimer_multi_structure($base_id);
	}
	exit('<ok>');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
