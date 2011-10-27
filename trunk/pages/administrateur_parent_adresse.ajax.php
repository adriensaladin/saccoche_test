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

$action      = (isset($_POST['f_action']))      ? clean_texte($_POST['f_action'])       : '';
$user_id     = (isset($_POST['f_id']))          ? clean_entier($_POST['f_id'])          : 0;
$ligne1      = (isset($_POST['f_ligne1']))      ? clean_commune($_POST['f_ligne1'])     : '';
$ligne2      = (isset($_POST['f_ligne2']))      ? clean_commune($_POST['f_ligne2'])     : '';
$ligne3      = (isset($_POST['f_ligne3']))      ? clean_commune($_POST['f_ligne3'])     : '';
$ligne4      = (isset($_POST['f_ligne4']))      ? clean_commune($_POST['f_ligne4'])     : '';
$code_postal = (isset($_POST['f_code_postal'])) ? clean_entier($_POST['f_code_postal']) : 0;
$commune     = (isset($_POST['f_commune']))     ? clean_nom($_POST['f_commune'])        : '';
$pays        = (isset($_POST['f_pays']))        ? clean_nom($_POST['f_pays'])           : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter une nouvelle adresse
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $user_id )
{
	// Insérer l'enregistrement
	DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_adresse_parent( $user_id , array($ligne1,$ligne2,$ligne3,$ligne4,$code_postal,$commune,$pays) );
	// Afficher le retour
	echo'<td><span>'.html($ligne1).'</span> ; <span>'.html($ligne2).'</span> ; <span>'.html($ligne3).'</span> ; <span>'.html($ligne4).'</span></td>';
	echo'<td>'.html($code_postal).'</td>';
	echo'<td>'.html($commune).'</td>';
	echo'<td>'.html($pays).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce parent."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier une adresse existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='modifier') && $user_id )
{
	// Insérer l'enregistrement
	$user_id = DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_adresse_parent( $user_id , array($ligne1,$ligne2,$ligne3,$ligne4,$code_postal,$commune,$pays) );
	// Afficher le retour
	echo'<td><span>'.html($ligne1).'</span> ; <span>'.html($ligne2).'</span> ; <span>'.html($ligne3).'</span> ; <span>'.html($ligne4).'</span></td>';
	echo'<td>'.html($code_postal).'</td>';
	echo'<td>'.html($commune).'</td>';
	echo'<td>'.html($pays).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce parent."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else
{
	echo'Erreur avec les données transmises !';
}
?>
