<?php
/**
 * @version $Id: fonction_affichage.php 8 2009-10-30 20:56:02Z thomas $
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
//	Afficher les statistiques en page d'accueil
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function affichage_stats()
{
	// Stats date
	$nb_jours = floor( ( strtotime(date("Y-m-d H:i:s")) - date(strtotime('2009-08-31 17:09:00')) ) / 86400 );
	// Stats effectifs globaux : nb structures
	$DB_SQL = 'SELECT COUNT(livret_structure_id) AS nombre FROM livret_structure';
	$DB_TAB = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , null);
	$nb_total_structure = $DB_TAB['nombre'];
	// Stats effectifs globaux : nb professeurs, nb élèves
	$DB_SQL = 'SELECT livret_user_profil, COUNT(*) AS nombre FROM livret_user GROUP BY livret_user_profil';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , null , TRUE);
	$nb_total_professeur = (count($DB_TAB)) ? $DB_TAB['professeur'][0]['nombre'] : 0 ;
	$nb_total_eleve      = (count($DB_TAB)) ? $DB_TAB['eleve'][0]['nombre'] : 0 ;
	// Stats effectifs globaux : nb compétences
	$DB_SQL = 'SELECT COUNT(livret_competence_id) AS nombre FROM livret_competence_item';
	$DB_TAB = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , null);
	$nb_total_compet = $DB_TAB['nombre'];
	// Stats effectifs globaux : nb notes
	$DB_SQL = 'SELECT COUNT(*) AS nombre FROM livret_jointure_user_competence';
	$DB_TAB = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , null);
	$nb_total_note = $DB_TAB['nombre'];
	//Stats évals précises : nb structures, nb professeurs, nb élèves, nb compétences, nb notes
	$DB_SQL = 'SELECT COUNT( DISTINCT livret_prof_id ) AS nb_professeur, COUNT( DISTINCT livret_structure_id ) AS nb_structure, COUNT( * ) AS nb_note, COUNT( DISTINCT livret_eleve_id ) AS nb_eleve, COUNT( DISTINCT livret_competence_id ) AS nb_compet FROM livret_jointure_user_competence';
	$DB_TAB = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , null);
	$nb_professeur = $DB_TAB['nb_professeur'] ;
	$nb_structure  = $DB_TAB['nb_structure'] ;
	$nb_note       = $DB_TAB['nb_note'];
	$nb_eleve      = $DB_TAB['nb_eleve'] ;
	$nb_compet     = $DB_TAB['nb_compet'];
	// Retour de l'affichage
	$retour  = 'Logiciel expérimenté depuis '.$nb_jours.' jours.<br />';
	$retour .= 'Sont enregistrés : '.$nb_total_structure.' établissements, '.$nb_total_professeur.' professeurs, '.$nb_total_eleve.' élèves et '.$nb_total_compet.' items.<br />';
	$retour .= 'En pratique : dans '.$nb_structure.' établissements '.$nb_professeur.' professeurs ont évalué '.$nb_compet.' items pour '.$nb_eleve.' élèves avec '.$nb_note.' saisies.';
	return $retour;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher une note Lomer pour une sortie HTML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_tri_note = array_flip(array('RR','R','V','VV','ABS','NN','DISP'));	// sert pour le tri du tableau
function affich_note_html($note,$date,$info,$tri=false)
{
	global $tab_tri_note;
	$insert_tri = ($tri) ? '<i>'.$tab_tri_note[$note].'</i>' : '';
	return ($note=='-') ? '&nbsp;' : $insert_tri.'<img title="'.html($info).'<br />'.affich_date($date).'" alt="'.$note.'" src="./_img/note/note_'.$note.'.gif" />';
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
	elseif($score<$_SESSION['PARAM_CALCUL']['seuil']['R']) {$etat = 'r';}
	elseif($score>$_SESSION['PARAM_CALCUL']['seuil']['V']) {$etat = 'v';}
	else                                                   {$etat = 'o';}
	$tri = ($methode_tri=='score') ? sprintf("%03u",$score) : $tab_tri_etat[$etat] ;	// le sprintf et le tab_tri_etat servent pour le tri du tableau
	return '<td class="hc '.$etat.'"><i>'.$tri.'</i>'.$score.$pourcent.'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher un état d'acquisition du socle pour une sortie HTML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function affich_validation_html($type_cellule,$tab_infos)
{
	// $tab_infos contient 'A' / 'VA' / 'NA' / 'nb' / '%'
	if($tab_infos['%']===false)
	{
		return '<'.$type_cellule.' class="hc">-</'.$type_cellule.'>';
	}
	elseif($tab_infos['%']<$_SESSION['PARAM_CALCUL']['seuil']['R']) {$etat = 'r';}
	elseif($tab_infos['%']>$_SESSION['PARAM_CALCUL']['seuil']['V']) {$etat = 'v';}
	else                                                            {$etat = 'o';}
	return '<'.$type_cellule.' class="hc '.$etat.'">'.$tab_infos['%'].'% validé ('.$tab_infos['A'].'A '.$tab_infos['VA'].'VA '.$tab_infos['NA'].'NA)</'.$type_cellule.'>';
}

?>