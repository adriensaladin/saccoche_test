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

/**
 * lien_mailto
 * Afficher un lien mailto en masquant l'adresse de courriel pour les robots.
 *
 * @param string $mail_adresse
 * @param string $mail_sujet
 * @param string $texte_lien
 * @param string $mail_contenu
 * @param string $mail_copy
 * @return string
 */

function lien_mailto($mail_adresse,$mail_sujet,$texte_lien,$mail_contenu='',$mail_copy='')
{
	$mailto = 'mailto:'.$mail_adresse.'?subject='.$mail_sujet;
	$mailto.= ($mail_copy) ? '&cc='.$mail_copy : '' ;
	$mailto.= ($mail_contenu) ? '&body='.$mail_contenu : '' ;
	$tab_unicode_valeurs = utf8ToUnicode($mailto);
	$href = '&#'.implode(';'.'&#',$tab_unicode_valeurs).';';
	return '<a href="'.$href.'" class="lien_mail">'.$texte_lien.'</a>';
}

/**
 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
 * Astral planes are supported ie. the ints in the output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates are not allowed.
 * Returns false if the input string isn't a valid UTF-8 octet sequence.
 * 
 * Licence : NPL 1.1/GPL 2.0/LGPL 2.1
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is Netscape Communications Corporation.
 * Contributor(s): Henri Sivonen, hsivonen@iki.fi
 * The latest version of this file can be obtained from http://iki.fi/hsivonen/php-utf8/
 * Version 1.0, 2003-05-30
 */

function utf8ToUnicode(&$str)
{
	$mState = 0;     // cached expected number of octets after the current octet until the beginning of the next UTF8 character sequence
	$mUcs4  = 0;     // cached Unicode character
	$mBytes = 1;     // cached expected number of octets in the current sequence
	$len = strlen($str);
	for($i = 0; $i < $len; $i++)
	{
		$in = ord($str{$i});
		if (0 == $mState)
		{
			// When mState is zero we expect either a US-ASCII character or a
			// multi-octet sequence.
			if (0 == (0x80 & ($in)))
			{
				// US-ASCII, pass straight through.
				$out[] = $in;
				$mBytes = 1;
			}
			else if (0xC0 == (0xE0 & ($in)))
			{
				// First octet of 2 octet sequence
				$mUcs4 = ($in);
				$mUcs4 = ($mUcs4 & 0x1F) << 6;
				$mState = 1;
				$mBytes = 2;
			}
			else if (0xE0 == (0xF0 & ($in))) 
			{
				// First octet of 3 octet sequence
				$mUcs4 = ($in);
				$mUcs4 = ($mUcs4 & 0x0F) << 12;
				$mState = 2;
				$mBytes = 3;
			}
			else if (0xF0 == (0xF8 & ($in)))
			{
				// First octet of 4 octet sequence
				$mUcs4 = ($in);
				$mUcs4 = ($mUcs4 & 0x07) << 18;
				$mState = 3;
				$mBytes = 4;
			}
			else if (0xF8 == (0xFC & ($in)))
			{
				/* First octet of 5 octet sequence.
				 *
				 * This is illegal because the encoded codepoint must be either
				 * (a) not the shortest form or
				 * (b) outside the Unicode range of 0-0x10FFFF.
				 * Rather than trying to resynchronize, we will carry on until the end
				 * of the sequence and let the later error handling code catch it.
				 */
				$mUcs4 = ($in);
				$mUcs4 = ($mUcs4 & 0x03) << 24;
				$mState = 4;
				$mBytes = 5;
			}
			else if (0xFC == (0xFE & ($in)))
			{
				// First octet of 6 octet sequence, see comments for 5 octet sequence.
				$mUcs4 = ($in);
				$mUcs4 = ($mUcs4 & 1) << 30;
				$mState = 5;
				$mBytes = 6;
			}
			else 
			{
				/* Current octet is neither in the US-ASCII range nor a legal first
				 * octet of a multi-octet sequence.
				 */
				return false;
			}
		}
		else 
		{
			// When mState is non-zero, we expect a continuation of the multi-octet
			// sequence
			if (0x80 == (0xC0 & ($in)))
			{
				// Legal continuation.
				$shift = ($mState - 1) * 6;
				$tmp = $in;
				$tmp = ($tmp & 0x0000003F) << $shift;
				$mUcs4 |= $tmp;
				if (0 == --$mState) 
				{
					/* End of the multi-octet sequence. mUcs4 now contains the final
					 * Unicode codepoint to be output
					 *
					 * Check for illegal sequences and codepoints.
					 */
					// From Unicode 3.1, non-shortest form is illegal
					if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
							((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
							((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
							(4 < $mBytes) ||
							// From Unicode 3.2, surrogate characters are illegal
							(($mUcs4 & 0xFFFFF800) == 0xD800) ||
							// Codepoints outside the Unicode range are illegal
							($mUcs4 > 0x10FFFF))
					{
						return false;
					}
					if (0xFEFF != $mUcs4)
					{
						// BOM is legal but we don't want to output it
						$out[] = $mUcs4;
					}
					//initialize UTF8 cache
					$mState = 0;
					$mUcs4  = 0;
					$mBytes = 1;
				}
			} 
			else 
			{
				/* ((0xC0 & (*in) != 0x80) && (mState != 0))
				 * 
				 * Incomplete multi-octet sequence.
				 */
				return false;
			}
		}
	}
	return $out;
}

/**
 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
 * Astral planes are supported ie. the ints in the input can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates are not allowed.
 * Returns false if the input array contains ints that represent surrogates or are outside the Unicode range.
 * 
 * Licence : NPL 1.1/GPL 2.0/LGPL 2.1
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is Netscape Communications Corporation.
 * Contributor(s): Henri Sivonen, hsivonen@iki.fi
 * The latest version of this file can be obtained from http://iki.fi/hsivonen/php-utf8/
 * Version 1.0, 2003-05-30
 */

function unicodeToUtf8(&$arr)
{
	$dest = '';
	foreach ($arr as $src)
	{
		if($src < 0) 
		{
			return false;
		}
		else if ( $src <= 0x007f) 
		{
			$dest .= chr($src);
		}
		else if ($src <= 0x07ff) 
		{
			$dest .= chr(0xc0 | ($src >> 6));
			$dest .= chr(0x80 | ($src & 0x003f));
		} 
		else if($src == 0xFEFF) 
		{
			// nop -- zap the BOM
		}
		else if ($src >= 0xD800 && $src <= 0xDFFF) 
		{
			// found a surrogate
			return false;
		}
		else if ($src <= 0xffff) 
		{
			$dest .= chr(0xe0 | ($src >> 12));
			$dest .= chr(0x80 | (($src >> 6) & 0x003f));
			$dest .= chr(0x80 | ($src & 0x003f));
		}
		else if ($src <= 0x10ffff) 
		{
			$dest .= chr(0xf0 | ($src >> 18));
			$dest .= chr(0x80 | (($src >> 12) & 0x3f));
			$dest .= chr(0x80 | (($src >> 6) & 0x3f));
			$dest .= chr(0x80 | ($src & 0x3f));
		} else 
		{
			// out of range
			return false;
		}
	}
	return $dest;
}

/**
 * convert_date_mysql_to_french
 * Passer d'une date MySQL AAAA-MM-JJ à une date française JJ/MM/AAAA.
 *
 * @param string $date   AAAA-MM-JJ
 * @return string        JJ/MM/AAAA
 */

function convert_date_mysql_to_french($date)
{
	list($annee,$mois,$jour) = explode('-',$date);
	return $jour.'/'.$mois.'/'.$annee;
}

/**
 * convert_date_french_to_mysql
 * Passer d'une date française JJ/MM/AAAA à une date MySQL AAAA-MM-JJ.
 *
 * @param string $date   JJ/MM/AAAA
 * @return string        AAAA-MM-JJ
 */

function convert_date_french_to_mysql($date)
{
	list($jour,$mois,$annee) = explode('/',$date);
	return $annee.'-'.$mois.'-'.$jour;
}

/**
 * affich_date
 * Convertir une date MySQL ou française en un texte bien formaté pour l'infobulle (sortie HTML).
 *
 * @param string $date   AAAA-MM-JJ ou JJ/MM/AAAA
 * @return string        JJ nom_du mois AAAA
 */

function affich_date($date)
{
	if(mb_strpos($date,'-')) { list($annee,$mois,$jour) = explode('-',$date); }
	else                     { list($jour,$mois,$annee) = explode('/',$date); }
	$tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
	return $jour.' '.$tab_mois[$mois].' '.$annee;
}

/**
 * affich_note_html
 * Afficher une note Lomer pour une sortie HTML.
 *
 * @param string $note
 * @param string $date
 * @param string $info
 * @param bool   $tri
 * @return string
 */

$tab_tri_note = array_flip(array('RR','R','V','VV','ABS','NN','DISP','REQ','-',''));	// sert pour le tri du tableau

function affich_note_html($note,$date,$info,$tri=false)
{
	global $tab_tri_note;
	$insert_tri = ($tri) ? '<i>'.$tab_tri_note[$note].'</i>' : '';
	$dossier = (in_array($note,array('RR','R','V','VV'))) ? $_SESSION['NOTE_DOSSIER'].'/h/' : 'commun/h/';
	$title = ( ($date!='') || ($info!='') ) ? ' title="'.html($info).'<br />'.affich_date($date).'"' : '' ;
	return (in_array($note,array('REQ','-',''))) ? '&nbsp;' : $insert_tri.'<img'.$title.' alt="'.$note.'" src="./_img/note/'.$dossier.$note.'.gif" />';
}

/**
 * affich_score_html
 * Afficher un score bilan pour une sortie HTML.
 *
 * @param int|FALSE $score
 * @param string    $methode_tri   'score' | 'etat'
 * @param string    $pourcent      '%' | ''
 * @return string
 */

$tab_tri_etat = array_flip(array('r','o','v'));	// sert pour le tri du tableau dans le cas d'un tri par état d'acquisition

function affich_score_html($score,$methode_tri,$pourcent='')
{
	global $tab_tri_etat;
	if($score===false)
	{
		$score_affiche = (strpos($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])!==false) ? '-' : '' ;
		return '<td class="hc">'.$score_affiche.'</td>';
	}
	elseif($score<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
	elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
	else                                                   {$etat = 'o';}
	$score_affiche = (strpos($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])!==false) ? $score.$pourcent : '' ;
	$tri = ($methode_tri=='score') ? sprintf("%03u",$score) : $tab_tri_etat[$etat] ;	// le sprintf et le tab_tri_etat servent pour le tri du tableau
	return '<td class="hc '.$etat.'"><i>'.$tri.'</i>'.$score_affiche.'</td>';
}

/**
 * affich_barre_synthese_html
 * Afficher une barre colorée de synthèse NA VA A pour une sortie HTML.
 *
 * @param int     $td_width
 * @param array   $tab_infos   array( 'A' , 'VA' , 'NA' )
 * @param int     $total
 * @return string
 */

function affich_barre_synthese_html($td_width,$tab_infos,$total)
{
	$tab_couleur = array('NA'=>'r','VA'=>'o','A'=>'v');
	$span = '';
	foreach($tab_infos as $etat => $nb)
	{
		$span_width = $td_width * $nb / $total ;
		$texte = ($span_width>30) ? $nb.' '.$_SESSION['ACQUIS_TEXTE'][$etat] : $nb ;
		$span .= '<span class="'.$tab_couleur[$etat].'" style="display:inline-block;width:'.$span_width.'px">'.$texte.'</span>';
	}
	return '<td style="padding:0;width:'.$td_width.'px" class="hc">'.$span.'</td>';
}

/**
 * affich_pourcentage_html
 * Afficher un pourcentage d'items acquis pour une sortie socle HTML.
 *
 * @param string   $type_cellule   'td' | 'th'
 * @param array    $tab_infos      array( 'A' , 'VA' , 'NA' , 'nb' , '%' )
 * @param bool     $detail
 * @return string
 */

function affich_pourcentage_html($type_cellule,$tab_infos,$detail)
{
	if($tab_infos['%']===false)
	{
		$texte = ($detail) ? '---' : '-' ; // Mettre qq chose sinon en mode daltonien le gris de la case se confond avec les autres couleurs.
		return '<'.$type_cellule.' class="hc">'.$texte.'</'.$type_cellule.'>' ;
	}
	elseif($tab_infos['%']<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
	elseif($tab_infos['%']>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
	else                                                   {$etat = 'o';}
	$texte = html($tab_infos['%'].'% acquis ('.$tab_infos['A'].$_SESSION['ACQUIS_TEXTE']['A'].' '.$tab_infos['VA'].$_SESSION['ACQUIS_TEXTE']['VA'].' '.$tab_infos['NA'].$_SESSION['ACQUIS_TEXTE']['NA'].')');
	return ($detail) ? '<'.$type_cellule.' class="hc '.$etat.'">'.$texte.'</'.$type_cellule.'>' : '<'.$type_cellule.' class="'.$etat.'" title="'.$texte.'"></'.$type_cellule.'>';
}

/**
 * affich_validation_html
 * Afficher un état de validation pour une sortie socle HTML.
 *
 * @param string   $type_cellule   'td' | 'th'
 * @param array    $tab_infos      array( 'etat' , 'date' , 'info' )
 * @param bool     $detail
 * @param int      $etat_pilier    0 | 1
 * @param bool     $colspan
 * @return string
 */

function affich_validation_html($type_cellule,$tab_infos,$detail,$etat_pilier=false,$colspan=false)
{
	$etat  = ($tab_infos['etat']==1) ? 'Validé' : 'Invalidé' ;
	$bulle = ($tab_infos['etat']==2) ? '' : ' title="'.$etat.' le '.$tab_infos['date'].' par '.html($tab_infos['info']).'"' ;
	if($detail)
	{
		$texte = ($tab_infos['etat']==2) ? '---' : $tab_infos['date'] ;
		return '<'.$type_cellule.' class="hc v'.$tab_infos['etat'].'"'.$bulle.'>'.$texte.'</'.$type_cellule.'>';
	}
	else
	{
		if($colspan)
		{
			// État de validation d'un pilier dans un colspan
			$colspan_et_classe = ' colspan="'.$colspan.'" class="v'.$tab_infos['etat'].'"' ;
		}
		else
		{
			// État de validation d'un item à indiquer comme inutile si le pilier est validé
			$colspan_et_classe = ( ($etat_pilier==1) && ($tab_infos['etat']==2) && (!$_SESSION['USER_DALTONISME']) ) ? '' : ' class="v'.$tab_infos['etat'].'"' ;
		}
		return '<'.$type_cellule.$colspan_et_classe.$bulle.'></'.$type_cellule.'>';
	}
}

?>