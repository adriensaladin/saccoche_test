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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher un lien mailto en masquant l'adresse de courriel pour les robots
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function mailto($email,$sujet,$affichage)
{
	$mailto = 'mailto:'.$email.'?subject='.$sujet;
	$tab_latin   = array( ' ' ,  '#'  ,  '%'  ,  '&'  ,  '\'' ,  '-'  ,  '.'  ,  '0'  ,  '1'  ,  '2'  ,  '3'  ,  '4'  ,  '5'  ,  '6'  ,  '7'  ,  '8'  ,  '9'  ,  ':'  ,  ';'  ,  '='  ,  '?'  ,  '@'  ,  'A'  ,  'B'  ,  'C'  ,  'D'  ,  'E'  ,  'F'  ,  'G'  ,  'H'  ,  'I'  ,  'J'  ,  'K'  ,  'L'  ,  'M'  ,  'N'  ,  'O'  ,  'P'  ,  'Q'  ,  'R'  ,  'S'  ,  'T'  ,  'U'  ,  'V'  ,  'W'  ,  'X'  ,  'Y'  ,  'Z'  ,  '['  ,  ']'  ,  '_'  ,  'a'  ,  'b'  ,  'c'  ,   'd'  ,   'e'  ,   'f'  ,   'g'  ,   'h'  ,   'i'  ,   'j'  ,   'k'  ,   'l'  ,   'm'  ,   'n'  ,   'o'  ,   'p'  ,   'q'  ,   'r'  ,   's'  ,   't'  ,   'u'  ,   'v'  ,   'w'  ,   'x'  ,   'y'  ,   'à'  ,   'ç'  ,   'è'  ,   'é'  ,   'ù'  );
	$tab_unicode = array('%20','&#35;','&#37;','&#38;','&#39;','&#45;','&#46;','&#48;','&#49;','&#50;','&#51;','&#52;','&#53;','&#54;','&#55;','&#56;','&#57;','&#58;','&#59;','&#61;','&#63;','&#64;','&#65;','&#66;','&#67;','&#68;','&#69;','&#70;','&#71;','&#72;','&#73;','&#74;','&#75;','&#76;','&#77;','&#78;','&#79;','&#80;','&#81;','&#82;','&#83;','&#84;','&#85;','&#86;','&#87;','&#88;','&#89;','&#90;','&#91;','&#93;','&#95;','&#97;','&#98;','&#99;','&#100;','&#101;','&#102;','&#103;','&#104;','&#105;','&#106;','&#107;','&#108;','&#109;','&#110;','&#111;','&#112;','&#113;','&#114;','&#115;','&#116;','&#117;','&#118;','&#119;','&#120;','&#121;','&#224;','&#231;','&#232;','&#233;','&#249;');
	$imax = mb_strlen($mailto);
	$href = '';
	for($i=0;$i<$imax;$i++)
	{
		$href .= $tab_unicode[ array_search(mb_substr($mailto,$i,1),$tab_latin) ];
	}
	return '<a href="'.$href.'" class="lien_mail">'.$affichage.'</a>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Passer d'une date MySQL AAAA-MM-JJ à une date française JJ/MM/AAAA et inversement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function convert_date_mysql_to_french($date)
{
	list($annee,$mois,$jour) = explode('-',$date);	// date_mysql de la forme aaaa-mm-jj
	return $jour.'/'.$mois.'/'.$annee;	// date_française de la forme jj/mm/aaaa
}

function convert_date_french_to_mysql($date)
{
	list($jour,$mois,$annee) = explode('/',$date);	// date_française de la forme jj/mm/aaaa
	return $annee.'-'.$mois.'-'.$jour;	// date_mysql de la forme aaaa-mm-jj
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Convertir une date MySQL en un texte bien formaté pour l'infobulle (sortie HTML)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function affich_date($date)
{
	if(mb_strpos($date,'-'))
	{
		list($annee,$mois,$jour) = explode('-',$date);	// date_mysql de la forme aaaa-mm-jj
	}
	else
	{
		list($jour,$mois,$annee) = explode('/',$date);	// date_française de la forme jj/mm/aaaa
	}

	$tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
	return $jour.' '.$tab_mois[$mois].' '.$annee;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher une note Lomer pour une sortie HTML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_tri_note = array_flip(array('RR','R','V','VV','ABS','NN','DISP','REQ','-',''));	// sert pour le tri du tableau
function affich_note_html($note,$date,$info,$tri=false)
{
	global $tab_tri_note;
	$insert_tri = ($tri) ? '<i>'.$tab_tri_note[$note].'</i>' : '';
	$sous_dossier = (in_array($note,array('RR','R','V','VV'))) ? $_SESSION['NOTE_IMAGE_STYLE'].'/' : '';
	$title = ( ($date!='') || ($info!='') ) ? ' title="'.html($info).'<br />'.affich_date($date).'"' : '' ;
	return (in_array($note,array('REQ','-',''))) ? '&nbsp;' : $insert_tri.'<img'.$title.' alt="'.$note.'" src="./_img/note/'.$sous_dossier.$note.'.gif" />';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher un score bilan pour une sortie HTML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_tri_etat = array_flip(array('r','o','v'));	// sert pour le tri du tableau dans le cas d'un tri par état d'acquisition
function affich_score_html($score,$methode_tri,$pourcent='')
{
	global $tab_tri_etat;
	// $methode_tri vaut 'score' ou 'etat'
	if($score===false)
	{
		return '<td class="hc">-</td>';
	}
	elseif($score<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
	elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
	else                                                   {$etat = 'o';}
	$tri = ($methode_tri=='score') ? sprintf("%03u",$score) : $tab_tri_etat[$etat] ;	// le sprintf et le tab_tri_etat servent pour le tri du tableau
	return '<td class="hc '.$etat.'"><i>'.$tri.'</i>'.$score.$pourcent.'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher un pourcentage d'items acquis pour une sortie socle HTML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function affich_pourcentage_html($type_cellule,$tab_infos)
{
	// $tab_infos contient 'A' / 'VA' / 'NA' / 'nb' / '%'
	if($tab_infos['%']===false)
	{
		return '<'.$type_cellule.' class="hc">---</'.$type_cellule.'>';
	}
	elseif($tab_infos['%']<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
	elseif($tab_infos['%']>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
	else                                                   {$etat = 'o';}
	return '<'.$type_cellule.' class="hc '.$etat.'">'.$tab_infos['%'].'% acquis ('.$tab_infos['A'].'A '.$tab_infos['VA'].'VA '.$tab_infos['NA'].'NA)</'.$type_cellule.'>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher un état de validation pour une sortie socle HTML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function affich_validation_html($type_cellule,$tab_infos)
{
	// $tab_infos contient 'etat' / 'date' / 'info'
	$etat  = ($tab_infos['etat']==1) ? 'Validé' : 'Invalidé' ;
	$texte = ($tab_infos['etat']==2) ? '---' : $tab_infos['date'] ;
	$bulle = ($tab_infos['etat']==2) ? '' : ' title="'.$etat.' le '.$tab_infos['date'].' par '.html($tab_infos['info']).'"' ;
	return '<'.$type_cellule.' class="hc v'.$tab_infos['etat'].'"'.$bulle.'>'.$texte.'</'.$type_cellule.'>';
}

?>