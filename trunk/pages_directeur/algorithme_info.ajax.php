<?php
/**
 * @version $Id: algorithme_info.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_POST['action']!='calculer')){exit('Action désactivée pour la démo...');}

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
$methode = (isset($_POST['f_methode'])) ? clean_entier($_POST['f_methode']) : 1 ;
$limite  = (isset($_POST['f_limite']))  ? clean_entier($_POST['f_limite'])  : 0 ;

if($action=='calculer')
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
			$coef = 0;
			$somme_coef = 0;
			$masque = sprintf('%0'.$nb_devoirs.'u',base_convert($cas,10,4));
			$codes = str_replace($tab_bad,$tab_bon,$masque);
			$tab_codes = explode(' ',$codes);
			for($num_devoir=1;$num_devoir<=$nb_devoirs;$num_devoir++)
			{
				$code = $tab_codes[$num_devoir];
				$tab_lignes[$cas] .= '<td><img alt="" src="./_img/note/note_'.$code.'.gif" /></td>';
				// Si on prend ce devoir en compte
				if( ($limite==0) || ($nb_devoirs-$num_devoir<$limite) )
				{
					$coef = $methode ? $coef+1 : 1 ;
					$somme_coef += $coef;
					$note += $valeur[$code]*$coef;
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
