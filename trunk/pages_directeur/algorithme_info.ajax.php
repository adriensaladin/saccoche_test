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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['action']!='calculer')){exit('Action désactivée pour la démo...');}

$action = (isset($_POST['action'])) ? $_POST['action'] : '';
// Valeur d'un code (sur 100)
$valeur = array();
$valeur['RR'] = (isset($_POST['valeurRR'])) ? clean_entier($_POST['valeurRR']) :   0 ;
$valeur['R']  = (isset($_POST['valeurR']))  ? clean_entier($_POST['valeurR'])  :  33 ;
$valeur['V']  = (isset($_POST['valeurV']))  ? clean_entier($_POST['valeurV'])  :  67 ;
$valeur['VV'] = (isset($_POST['valeurVV'])) ? clean_entier($_POST['valeurVV']) : 100 ;
// Seuil d'aquisition (sur 100) 
$seuil = array();
$seuil['R'] = (isset($_POST['seuilR'])) ? clean_entier($_POST['seuilR']) : 40 ;
$seuil['V'] = (isset($_POST['seuilV'])) ? clean_entier($_POST['seuilV']) : 60 ;
// Méthode de calcul
$methode = (isset($_POST['f_methode'])) ? clean_texte($_POST['f_methode']) : '' ;
$limite  = (isset($_POST['f_limite']))  ? clean_entier($_POST['f_limite']) : 0 ;

$tab_methodes = array('geometrique','arithmetique','classique');
$tab_limites['geometrique']  = array(1,2,3,4,5);
$tab_limites['arithmetique'] = array(1,2,3,4,5,6,7,8,9);
$tab_limites['classique']  = array(0,1,2,3,4,5,6,7,8,9,10,15,20,30,40,50);

if( ($action=='calculer') && in_array($methode,$tab_methodes) && in_array($limite,$tab_limites[$methode]) )
{
	$tab_bad = array('0','1','2','3');
	$tab_bon = array(' RR',' R',' V',' VV');
	$tab_lignes = array();
	$tab_lignes[1] = '';
	$tab_lignes = array_pad($tab_lignes,256,'');
	for($nb_devoirs=1;$nb_devoirs<=4;$nb_devoirs++)
	{
		$nb_cas = pow(4,$nb_devoirs);
		for($cas=0;$cas<$nb_cas;$cas++)
		{
			$note = 0;
			$coef = 1;
			$somme_coef = 0;
			$masque = sprintf('%0'.$nb_devoirs.'u',base_convert($cas,10,4));
			$codes = str_replace($tab_bad,$tab_bon,$masque);
			$tab_codes = explode(' ',$codes);
			for($num_devoir=1;$num_devoir<=$nb_devoirs;$num_devoir++)
			{
				$code = $tab_codes[$num_devoir];
				$tab_lignes[$cas] .= '<td><img alt="" src="./_img/note/'.$_SESSION['CSS_NOTE_STYLE'].'/'.$code.'.gif" /></td>';
				// Si on prend ce devoir en compte
				if( ($limite==0) || ($nb_devoirs-$num_devoir<$limite) )
				{
					$note += $valeur[$code]*$coef;
					$somme_coef += $coef;
					// Calcul du coef de l'éventuel devoir suivant
					$coef = ($methode=='geometrique') ? $coef*2 : ( ($methode=='arithmetique') ? $coef+1 : 1 ) ;
				}
			}
			$note = round($note/$somme_coef,0);
			    if($note<$seuil['R']) {$bg = 'r';}
			elseif($note>$seuil['V']) {$bg = 'v';}
			else                      {$bg = 'o';}
			$tab_lignes[$cas] .= '<td class="'.$bg.'">'.$note.'</td>';
			if( ($cas==0) && ($nb_devoirs!=4) )
			{
				$tab_lignes[$cas] .= '<td rowspan="256"></td>';
			}
		}
	}

	foreach($tab_lignes as $cas => $ligne)
	{
		$nb_td_manquant = 14 - substr_count($ligne,'<td');
		echo'<tr>';
		if($nb_td_manquant>0)
		{
			if($cas>63)     {$nb_td_manquant+=2;}
			elseif($cas>15) {$nb_td_manquant+=1;}
			echo'<td colspan="'.$nb_td_manquant.'"></td>';
		}
		echo $ligne;
		echo'</tr>';
	}
}
else
{
	echo'Erreur avec les données transmises !';
}
?>
