<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

class Html
{

  // //////////////////////////////////////////////////
  // Tableaux prédéfinis
  // //////////////////////////////////////////////////

  // sert pour le tri d'un tableau de notes Lomer
  // correspond à $tab_tri_note = array_flip(array('RR','R','V','VV','ABS','NE','NF','NN','NR','DISP','REQ','-',''));
  private static $tab_tri_note = array('RR'=>0,'R'=>1,'V'=>2,'VV'=>3,'ABS'=>4,'NE'=>5,'NF'=>6,'NN'=>7,'NR'=>8,'DISP'=>9,'REQ'=>10,'-'=>11,''=>12);

  // sert pour le tri du tableau de scores bilans dans le cas d'un tri par état d'acquisition
  // correspond à $tab_tri_note = array_flip(array('r','o','v'));
  private static $tab_tri_etat = array('r'=>0,'o'=>1,'v'=>2);

  // sert pour indiquer la classe css d'un état d'acquisition
  private static $tab_couleur = array('NA'=>'r','VA'=>'o','A'=>'v');

  // sert pour indiquer la légende des notes spéciales
  private static $tab_legende_notes_speciales_texte  = array('ABS'=>'Absent','DISP'=>'Dispensé','NE'=>'Non évalué','NF'=>'Non fait','NN'=>'Non noté','NR'=>'Non rendu');
  public  static $tab_legende_notes_speciales_nombre = array('ABS'=>0       ,'DISP'=>0         ,'NE'=>0           ,'NF'=>0         ,'NN'=>0         ,'NR'=>0          );

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
   * 
   * Astral planes are supported ie. the ints in the output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates are not allowed.
   * Returns FALSE if the input string isn't a valid UTF-8 octet sequence.
   * 
   * Licence : NPL 1.1/GPL 2.0/LGPL 2.1
   * The Original Code is Mozilla Communicator client code.
   * The Initial Developer of the Original Code is Netscape Communications Corporation.
   * Contributor(s): Henri Sivonen, hsivonen@iki.fi
   * The latest version of this file can be obtained from http://iki.fi/hsivonen/php-utf8/
   * Version 1.0, 2003-05-30
   */
  private static function utf8ToUnicode($str)
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
          return FALSE;
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
              return FALSE;
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
          return FALSE;
        }
      }
    }
    return $out;
  }

  /**
   * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
   * 
   * Astral planes are supported ie. the ints in the input can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates are not allowed.
   * Returns FALSE if the input array contains ints that represent surrogates or are outside the Unicode range.
   * 
   * Licence : NPL 1.1/GPL 2.0/LGPL 2.1
   * The Original Code is Mozilla Communicator client code.
   * The Initial Developer of the Original Code is Netscape Communications Corporation.
   * Contributor(s): Henri Sivonen, hsivonen@iki.fi
   * The latest version of this file can be obtained from http://iki.fi/hsivonen/php-utf8/
   * Version 1.0, 2003-05-30
   */
  private static function unicodeToUtf8($arr)
  {
    $dest = '';
    foreach ($arr as $src)
    {
      if($src < 0) 
      {
        return FALSE;
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
        return FALSE;
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
        return FALSE;
      }
    }
    return $dest;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Afficher un lien mailto en masquant l'adresse de courriel pour les robots.
   *
   * @param string $mail_adresse
   * @param string $mail_sujet
   * @param string $texte_lien
   * @param string $mail_contenu
   * @param string $mail_copy
   * @return string
   */
  public static function mailto( $mail_adresse , $mail_sujet , $texte_lien , $mail_contenu='' , $mail_copy='' )
  {
    $mailto = 'mailto:'.$mail_adresse.'?subject='.$mail_sujet;
    $mailto.= ($mail_copy)    ? '&cc='.$mail_copy      : '' ;
    $mailto.= ($mail_contenu) ? '&body='.$mail_contenu : '' ;
    $tab_unicode_valeurs = Html::utf8ToUnicode(str_replace(' ','%20',$mailto));
    $href = '&#'.implode(';'.'&#',$tab_unicode_valeurs).';';
    return '<a href="'.$href.'" class="lien_mail">'.$texte_lien.'</a>';
  }

  /**
   * Convertir une date MySQL ou française en un texte avec le nom du mois en toutes lettres.
   *
   * @param string $date   AAAA-MM-JJ ou JJ/MM/AAAA
   * @return string        JJ nom_du mois AAAA
   */
  public static function date($date)
  {
    if(mb_strpos($date,'-')) { list($annee,$mois,$jour) = explode('-',$date); }
    else                     { list($jour,$mois,$annee) = explode('/',$date); }
    $tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
    return $jour.' '.$tab_mois[$mois].' '.$annee;
  }

  /**
   * Afficher une note Lomer pour une sortie HTML.
   *
   * @param string $note
   * @param string $date
   * @param string $info
   * @param bool   $tri
   * @return string
   */
  public static function note( $note , $date , $info , $tri=FALSE )
  {
    if(isset(Html::$tab_legende_notes_speciales_nombre[$note])) Html::$tab_legende_notes_speciales_nombre[$note]++;
    $insert_tri = ($tri) ? '<i>'.Html::$tab_tri_note[$note].'</i>' : '';
    $dossier = (in_array($note,array('RR','R','V','VV'))) ? $_SESSION['NOTE_DOSSIER'].'/h/' : 'commun/h/';
    $title = ( ($date!='') || ($info!='') ) ? ' title="'.html(html($info)).'<br />'.Html::date($date).'"' : '' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    return (in_array($note,array('-',''))) ? '&nbsp;' : $insert_tri.'<img'.$title.' alt="'.$note.'" src="./_img/note/'.$dossier.$note.'.gif" />';
  }

  /**
   * Afficher un score bilan pour une sortie HTML.
   *
   * @param int|FALSE $score
   * @param string    $methode_tri    'score' | 'etat'
   * @param string    $pourcent       '%' | ''
   * @param string    $checkbox_val   pour un éventuel checkbox
   * @param bool      $make_officiel  TRUE pour un bulletin
   * @return string
   */
  public static function td_score( $score , $methode_tri , $pourcent='' , $checkbox_val=''  , $make_officiel=FALSE )
  {
    // Pour un bulletin on prend les droits du profil parent, surtout qu'il peut être imprimé par un administrateur (pas de droit paramétré pour lui).
    $afficher_score = test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , $make_officiel /*forcer_parent*/ );
    $checkbox = ($checkbox_val) ? ' <input type="checkbox" name="id_req[]" value="'.$checkbox_val.'" />' : '' ;
   if($score===FALSE)
    {
      $affichage = ($afficher_score) ? '-' : '' ;
      return '<td class="hc">'.$affichage.$checkbox.'</td>';
    }
    elseif($score<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
    elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
    else                                          {$etat = 'o';}
    $affichage = ($afficher_score) ? $score.$pourcent : '' ;
    $tri = ($methode_tri=='score') ? sprintf("%03u",$score) : Html::$tab_tri_etat[$etat] ;  // le sprintf et le tab_tri_etat servent pour le tri du tableau
    return '<td class="hc '.$etat.'"><i>'.$tri.'</i>'.$affichage.$checkbox.'</td>';
  }

  /**
   * Initialiser la légende des codes de notation spéciaux
   *
   * @param void
   * @return void
   */
  public static function legende_initialiser()
  {
    Html::$tab_legende_notes_speciales_nombre = array_fill_keys( array_keys(Html::$tab_legende_notes_speciales_nombre) , 0 );
  }

  /**
   * Afficher la légende pour une sortie HTML.
   *
   * Normalement au moins un des paramètres est passé à TRUE.
   *
   * @param bool $codes_notation
   * @param bool $anciennete_notation
   * @param bool $etat_acquisition
   * @param bool $pourcentage_acquis
   * @param bool $etat_validation
   * @param bool $force_nb   FALSE par défaut, TRUE pour $etat_acquisition seulement
   * @return string
   */
  public static function legende( $codes_notation , $anciennete_notation , $score_bilan , $etat_acquisition , $pourcentage_acquis , $etat_validation , $make_officiel , $force_nb = FALSE  )
  {
    // initialisation variables
    $retour = '';
    // légende codes_notation
    if($codes_notation)
    {
      $tab_notes = array('RR','R','V','VV');
      $retour .= '<div><b>Codes d\'évaluation :</b>';
      foreach($tab_notes as $note)
      {
        $retour .= '<img alt="'.$note.'" src="./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/'.$note.'.gif" />'.html($_SESSION['NOTE_LEGENDE'][$note]);
      }
      foreach(Html::$tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $retour .= '<img alt="'.$note.'" src="./_img/note/commun/h/'.$note.'.gif" />'.html(Html::$tab_legende_notes_speciales_texte[$note]);
        }
      }
      Html::legende_initialiser();
      $retour .= '</div>'.NL;
    }
    // légende ancienneté notation
    if($anciennete_notation)
    {
      $retour .= '<div><b>Ancienneté :</b>';
      $retour .=   '<span class="cadre">Sur la période.</span>';
      $retour .=   '<span class="cadre prev_date">Début d\'année scolaire.</span>';
      $retour .=   '<span class="cadre prev_year">Année scolaire précédente.</span>';
      $retour .= '</div>'.NL;
    }
    // légende scores bilan
    if($score_bilan)
    {
      // Pour un bulletin on prend les droits du profil parent, surtout qu'il peut être imprimé par un administrateur (pas de droit paramétré pour lui).
      $afficher_score = test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , $make_officiel /*forcer_parent*/ );
      $tab_etats = array('NA'=>'r','VA'=>'o','A'=>'v');
      $seuil_NA = ( $afficher_score && ($_SESSION['CALCUL_SEUIL']['R']>0)   ) ? '0 à '.($_SESSION['CALCUL_SEUIL']['R']-1)   : '' ;
      $seuil_A  = ( $afficher_score && ($_SESSION['CALCUL_SEUIL']['V']<100) ) ? ($_SESSION['CALCUL_SEUIL']['V']+1).' à 100' : '' ;
      $seuil_VA = ( $afficher_score && ($_SESSION['CALCUL_SEUIL']['R']!=$_SESSION['CALCUL_SEUIL']['V']) ) ? $_SESSION['CALCUL_SEUIL']['R'].' à '.$_SESSION['CALCUL_SEUIL']['V'] : '' ;
      $tab_seuils = array( 'NA'=>$seuil_NA, 'VA'=>$seuil_VA, 'A'=>$seuil_A );
      $retour .= '<div><b>États d\'acquisitions :</b>';
      foreach($tab_etats as $etat => $couleur)
      {
        $retour .= '<span class="cadre '.$couleur.'">'.html($tab_seuils[$etat]).'</span>'.html($_SESSION['ACQUIS_LEGENDE'][$etat]);
      }
      $retour .= '</div>'.NL;
    }
    // légende etat_acquisition
    if($etat_acquisition)
    {
      $tab_etats = (!$force_nb) ? array('NA'=>'r','VA'=>'o','A'=>'v') : array('NA'=>'','VA'=>'','A'=>'') ;
      $retour .= '<div><b>États d\'acquisitions :</b>';
      foreach($tab_etats as $etat => $couleur)
      {
        $retour .= '<span class="cadre '.$couleur.'">'.html($_SESSION['ACQUIS_TEXTE'][$etat]).'</span>'.html($_SESSION['ACQUIS_LEGENDE'][$etat]);
      }
      $retour .= '</div>'.NL;
    }
    // légende pourcentage_acquis
    if($pourcentage_acquis)
    {
      $endroit = ($etat_validation) ? ' (à gauche)' : '' ;
      $tab_seuils = array('r'=>'&lt;&nbsp;'.$_SESSION['CALCUL_SEUIL']['R'].'%','o'=>'médian','v'=>'&gt;&nbsp;'.$_SESSION['CALCUL_SEUIL']['V'].'%');
      $retour .= '<div><b>Pourcentages d\'items acquis'.$endroit.' :</b>';
      foreach($tab_seuils as $couleur => $texte)
      {
        $retour .= '<span class="cadre '.$couleur.'">'.$texte.'</span>';
      }
      $retour .= '</div>'.NL;
    }
    // légende etat_validation
    if($etat_validation)
    {
      $endroit = ($pourcentage_acquis) ? ' (à droite)' : '' ;
      $tab_etats = array(1=>'Validé',0=>'Invalidé',2=>'Non renseigné');
      $retour .= '<div><b>États de validation'.$endroit.' :</b>';
      foreach($tab_etats as $couleur => $texte)
      {
        $retour .= '<span class="cadre v'.$couleur.'">'.$texte.'</span>';
      }
      $retour .= '</div>'.NL;
    }
    // retour
    return ($retour) ? '<h3>Légende</h3>'.NL.'<div class="legende">'.NL.$retour.'</div>'.NL : '' ;
  }

  /**
   * Afficher une barre colorée de synthèse NA VA A pour une sortie HTML.
   *
   * @param int     $td_width
   * @param array   $tab_infos   array( 'A' , 'VA' , 'NA' )
   * @param int     $total
   * @return string
   */
  public static function td_barre_synthese( $td_width , $tab_infos , $total , $avec_texte_nombre , $avec_texte_code )
  {
    $span = '';
    foreach($tab_infos as $etat => $nb)
    {
      $span_width = $td_width * $nb / $total ;
          if(  $avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $nb.' '.$_SESSION['ACQUIS_TEXTE'][$etat]; }
      elseif( !$avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $_SESSION['ACQUIS_TEXTE'][$etat]; }
      elseif( !$avec_texte_nombre && !$avec_texte_code ) { $texte_complet = '&nbsp;'; }
      elseif(  $avec_texte_nombre && !$avec_texte_code ) { $texte_complet = $nb; }
      $texte = ( (5*strlen($texte_complet)<$span_width) || !$avec_texte_code ) ? $texte_complet : ( ($avec_texte_nombre) ? $nb : '&nbsp;' ) ;
      $span .= '<span class="'.Html::$tab_couleur[$etat].'" style="display:inline-block;width:'.$span_width.'px;padding:2px 0">'.$texte.'</span>';
    }
    return '<td style="padding:0;width:'.$td_width.'px" class="hc">'.$span.'</td>';
  }

  /**
   * Afficher un pourcentage d'items acquis pour une sortie socle HTML ou bulletin.
   *
   * @param string   $type_cellule   'td' | 'th'
   * @param array    $tab_infos      array( 'A' , 'VA' , 'NA' , 'nb' , '%' )
   * @param bool     $detail
   * @param int|bool $largeur        en nombre de pixels
   * @return string
   */
  public static function td_pourcentage( $type_cellule , $tab_infos , $detail , $largeur )
  {
    if($tab_infos['%']===FALSE)
    {
      $texte = ($detail) ? '---' : '-' ; // Mettre qq chose sinon en mode daltonien le gris de la case se confond avec les autres couleurs.
      return '<'.$type_cellule.' class="hc">'.$texte.'</'.$type_cellule.'>' ;
    }
    elseif($tab_infos['%']<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
    elseif($tab_infos['%']>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
    else                                                   {$etat = 'o';}
    $style = ($largeur) ? ' style="width:'.$largeur.'px"' : '' ;
    $texte = html($tab_infos['%'].'% acquis ('.$tab_infos['A'].$_SESSION['ACQUIS_TEXTE']['A'].' '.$tab_infos['VA'].$_SESSION['ACQUIS_TEXTE']['VA'].' '.$tab_infos['NA'].$_SESSION['ACQUIS_TEXTE']['NA'].')');
    return ($detail) ? '<'.$type_cellule.' class="hc '.$etat.'"'.$style.'>'.$texte.'</'.$type_cellule.'>' : '<'.$type_cellule.' class="'.$etat.'" title="'.$texte.'"></'.$type_cellule.'>';
  }

  /**
   * Afficher un état de validation pour une sortie socle HTML.
   *
   * @param string   $type_cellule   'td' | 'th'
   * @param array    $tab_infos      array( 'etat' , 'date' , 'info' )
   * @param bool     $detail
   * @param int      $etat_pilier    0 | 1
   * @param bool     $colspan
   * @return string
   */
  public static function td_validation( $type_cellule , $tab_infos , $detail , $etat_pilier=FALSE , $colspan=FALSE )
  {
    $etat    = ($tab_infos['etat']==1) ? 'Validé' : 'Invalidé' ;
    $bulle   = ($tab_infos['etat']==2) ? '' : ' title="'.$etat.' le '.$tab_infos['date'].' par '.html($tab_infos['info']).'"' ;
    $colspan = ($colspan) ? ' colspan="'.$colspan.'"' : '' ; // État de validation d'un pilier dans un colspan
    $class   = ($detail) ? ' class="hc v'.$tab_infos['etat'].'"' : ( ( ($etat_pilier==1) && ($tab_infos['etat']==2) && (!$_SESSION['USER_DALTONISME']) ) ? '' : ' class="v'.$tab_infos['etat'].'"' ) ; // État de validation d'un item à indiquer comme inutile si le pilier est validé
    $texte   = ($detail) ? ( ($tab_infos['etat']==2) ? '---' : $tab_infos['date'] ) : '' ;
    return '<'.$type_cellule.$colspan.$class.$bulle.'>'.$texte.'</'.$type_cellule.'>';
  }

  /**
   * Retourner une liste HTML ordonnée de l'arborescence d'un référentiel matière à partir d'une requête SQL transmise.
   * 
   * @param tab         $DB_TAB
   * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
   * @param bool        $reference   afficher ou pas les références
   * @param bool        $aff_coef    affichage des coefficients des items (sous forme d'image)
   * @param bool        $aff_cart    affichage des possibilités de demandes d'évaluation des items (sous forme d'image)
   * @param bool|string $aff_socle   FALSE | 'texte' | 'image' : affichage de la liaison au socle
   * @param bool|string $aff_lien    FALSE | 'image' | 'click' : affichage des liens (ressources pour travailler)
   * @param bool        $aff_input   affichage ou pas des input checkbox avec label
   * @param string      $aff_id_li   vide par défaut, "n3" pour ajouter des id aux li_n3
   * @return string
   */
  public static function afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique,$reference,$aff_coef,$aff_cart,$aff_socle,$aff_lien,$aff_input,$aff_id_li='')
  {
    $input_all = ($aff_input) ? '<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q>' : '' ;
    $input_texte = '';
    $coef_texte  = '';
    $cart_texte  = '';
    $socle_texte = '';
    $lien_texte  = '';
    $lien_texte_avant = '';
    $lien_texte_apres = '';
    $label_texte_avant = '';
    $label_texte_apres = '';
    // Traiter le retour SQL : on remplit les tableaux suivants.
    $tab_matiere = array();
    $tab_niveau  = array();
    $tab_domaine = array();
    $tab_theme   = array();
    $tab_item    = array();
    $matiere_id = 0;
    foreach($DB_TAB as $DB_ROW)
    {
      if($DB_ROW['matiere_id']!=$matiere_id)
      {
        $matiere_id = $DB_ROW['matiere_id'];
        $tab_matiere[$matiere_id] = ($reference) ? $DB_ROW['matiere_ref'].' - '.$DB_ROW['matiere_nom'] : $DB_ROW['matiere_nom'] ;
        $niveau_id  = 0;
        $domaine_id = 0;
        $theme_id   = 0;
        $item_id    = 0;
      }
      if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
      {
        $niveau_id = $DB_ROW['niveau_id'];
        $prefixe   = ($reference) ? $DB_ROW['niveau_ref'].' - ' : '' ;
        $tab_niveau[$matiere_id][$niveau_id] = $prefixe.$DB_ROW['niveau_nom'];
      }
      if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
      {
        $domaine_id = $DB_ROW['domaine_id'];
        $prefixe   = ($reference) ? $DB_ROW['domaine_ref'].' - ' : '' ;
        $tab_domaine[$matiere_id][$niveau_id][$domaine_id] = $prefixe.$DB_ROW['domaine_nom'];
      }
      if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
      {
        $theme_id = $DB_ROW['theme_id'];
        $prefixe   = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].' - ' : '' ;
        $tab_theme[$matiere_id][$niveau_id][$domaine_id][$theme_id] = $prefixe.$DB_ROW['theme_nom'];
      }
      if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
      {
        $item_id = $DB_ROW['item_id'];
        if($aff_coef)
        {
          $coef_texte = '<img src="./_img/coef/'.sprintf("%02u",$DB_ROW['item_coef']).'.gif" title="Coefficient '.$DB_ROW['item_coef'].'." /> ';
        }
        if($aff_cart)
        {
          $cart_image = ($DB_ROW['item_cart']) ? 'oui' : 'non' ;
          $cart_title = ($DB_ROW['item_cart']) ? 'Demande possible.' : 'Demande interdite.' ;
          $cart_texte = '<img src="./_img/etat/cart_'.$cart_image.'.png" title="'.$cart_title.'" /> ';
        }
        switch($aff_socle)
        {
          case 'texte' :
            $socle_texte = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
            break;
          case 'image' :
            $socle_image = ($DB_ROW['entree_id']) ? 'oui' : 'non' ;
            $socle_title = ($DB_ROW['entree_id']) ? html($DB_ROW['entree_nom']) : 'Hors-socle.' ;
            $socle_texte = '<img src="./_img/etat/socle_'.$socle_image.'.png" title="'.$socle_title.'" /> ';
        }
        switch($aff_lien)
        {
          case 'click' :
            $lien_texte_avant = ($DB_ROW['item_lien']) ? '<a target="_blank" href="'.html($DB_ROW['item_lien']).'">' : '';
            $lien_texte_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
          case 'image' :
            $lien_image = ($DB_ROW['item_lien']) ? 'oui' : 'non' ;
            $lien_title = ($DB_ROW['item_lien']) ? html($DB_ROW['item_lien']) : 'Absence de ressource.' ;
            $lien_texte = '<img src="./_img/etat/link_'.$lien_image.'.png" title="'.$lien_title.'" /> ';
        }
        if($aff_input)
        {
          $input_texte = '<input id="id_'.$item_id.'" name="f_items[]" type="checkbox" value="'.$item_id.'" /> ';
          $label_texte_avant = '<label for="id_'.$item_id.'">';
          $label_texte_apres = '</label>';
        }
        $item_texte = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'].' - '.$DB_ROW['item_nom'] : $DB_ROW['item_nom'] ;
        $tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id][$item_id] = $label_texte_avant.$input_texte.$coef_texte.$cart_texte.$socle_texte.$lien_texte.$lien_texte_avant.html($item_texte).$lien_texte_apres.$label_texte_apres;
      }
    }
    // Affichage de l'arborescence
    $span_avant = ($dynamique) ? '<span>' : '' ;
    $span_apres = ($dynamique) ? '</span>' : '' ;
    $retour  = '<ul class="ul_m1">'.NL;
    if(count($tab_matiere))
    {
      foreach($tab_matiere as $matiere_id => $matiere_texte)
      {
        $retour .= '<li class="li_m1">'.$span_avant.html($matiere_texte).$span_apres.NL;
        $retour .= '<ul class="ul_m2">'.NL;
        if(isset($tab_niveau[$matiere_id]))
        {
          foreach($tab_niveau[$matiere_id] as $niveau_id => $niveau_texte)
          {
            $retour .= '<li class="li_m2">'.$span_avant.html($niveau_texte).$span_apres.NL;
            $retour .= '<ul class="ul_n1">'.NL;
            if(isset($tab_domaine[$matiere_id][$niveau_id]))
            {
              foreach($tab_domaine[$matiere_id][$niveau_id] as $domaine_id => $domaine_texte)
              {
                $retour .= '<li class="li_n1">'.$span_avant.html($domaine_texte).$span_apres.$input_all.NL;
                $retour .= '<ul class="ul_n2">'.NL;
                if(isset($tab_theme[$matiere_id][$niveau_id][$domaine_id]))
                {
                  foreach($tab_theme[$matiere_id][$niveau_id][$domaine_id] as $theme_id => $theme_texte)
                  {
                    $retour .= '<li class="li_n2">'.$span_avant.html($theme_texte).$span_apres.$input_all.NL;
                    $retour .= '<ul class="ul_n3">'.NL;
                    if(isset($tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id]))
                    {
                      foreach($tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id] as $item_id => $item_texte)
                      {
                        $id = ($aff_id_li=='n3') ? ' id="n3_'.$item_id.'"' : '' ;
                        $retour .= '<li class="li_n3"'.$id.'>'.$item_texte.'</li>'.NL;
                      }
                    }
                    $retour .= '</ul>'.NL;
                    $retour .= '</li>'.NL;
                  }
                }
                $retour .= '</ul>'.NL;
                $retour .= '</li>'.NL;
              }
            }
            $retour .= '</ul>'.NL;
            $retour .= '</li>'.NL;
          }
        }
        $retour .= '</ul>'.NL;
        $retour .= '</li>'.NL;
      }
    }
    $retour .= '</ul>'.NL;
    return $retour;
  }

  /**
   * Retourner une liste HTML ordonnée de l'arborescence d'un référentiel socle à partir d'une requête SQL transmise.
   * 
   * @param tab         $DB_TAB
   * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
   * @param bool        $reference   afficher ou pas les références
   * @param bool        $aff_input   affichage ou pas des input radio avec label
   * @param bool        $ids         indiquer ou pas les identifiants des éléments (Pxxx / Sxxx / Exxx)
   * @return string
   */
  public static function afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique,$reference,$aff_input,$ids)
  {
    $input_texte = '';
    $label_texte_avant = '';
    $label_texte_apres = '';
    // Traiter le retour SQL : on remplit les tableaux suivants.
    $tab_palier  = array();
    $tab_pilier  = array();
    $tab_section = array();
    $tab_entree   = array();
    $palier_id = 0;
    foreach($DB_TAB as $DB_ROW)
    {
      if($DB_ROW['palier_id']!=$palier_id)
      {
        $palier_id = $DB_ROW['palier_id'];
        $tab_palier[$palier_id] = $DB_ROW['palier_nom'];
        $pilier_id  = 0;
        $section_id = 0;
        $entree_id   = 0;
      }
      if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
      {
        $pilier_id = $DB_ROW['pilier_id'];
        $tab_pilier[$palier_id][$pilier_id] = $DB_ROW['pilier_nom'];
        $tab_pilier[$palier_id][$pilier_id] = ($reference) ? $DB_ROW['pilier_ref'].' - '.$DB_ROW['pilier_nom'] : $DB_ROW['pilier_nom'];
      }
      if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
      {
        $section_id = $DB_ROW['section_id'];
        $tab_section[$palier_id][$pilier_id][$section_id] = ($reference) ? $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].' - '.$DB_ROW['section_nom'] : $DB_ROW['section_nom'];
      }
      if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
      {
        $entree_id = $DB_ROW['entree_id'];
        if($aff_input)
        {
          $input_texte = '<input id="socle_'.$entree_id.'" name="f_socle" type="radio" value="'.$entree_id.'" /> ';
          $label_texte_avant = '<label for="socle_'.$entree_id.'">';
          $label_texte_apres = '</label>';
        }
        $entree_texte = ($reference) ? $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].'.'.$DB_ROW['entree_ordre'].' - '.$DB_ROW['entree_nom'] : $DB_ROW['entree_nom'] ;
        $tab_entree[$palier_id][$pilier_id][$section_id][$entree_id] = $label_texte_avant.$input_texte.html($entree_texte).$label_texte_apres;
      }
    }
    // Affichage de l'arborescence
    $span_avant = ($dynamique) ? '<span>' : '' ;
    $span_apres = ($dynamique) ? '</span>' : '' ;
    $retour = '<ul class="ul_m1">'.NL;
    if(count($tab_palier))
    {
      foreach($tab_palier as $palier_id => $palier_texte)
      {
        $retour .= '<li class="li_m1" id="palier_'.$palier_id.'">'.$span_avant.html($palier_texte).$span_apres.NL;
        $retour .= '<ul class="ul_n1">'.NL;
        if(isset($tab_pilier[$palier_id]))
        {
          foreach($tab_pilier[$palier_id] as $pilier_id => $pilier_texte)
          {
            $aff_id = ($ids) ? ' id="P'.$pilier_id.'"' : '' ;
            $retour .= '<li class="li_n1"'.$aff_id.'>'.$span_avant.html($pilier_texte).$span_apres.NL;
            $retour .= '<ul class="ul_n2">'.NL;
            if(isset($tab_section[$palier_id][$pilier_id]))
            {
              foreach($tab_section[$palier_id][$pilier_id] as $section_id => $section_texte)
              {
                $aff_id = ($ids) ? ' id="S'.$section_id.'"' : '' ;
                $retour .= '<li class="li_n2"'.$aff_id.'>'.$span_avant.html($section_texte).$span_apres.NL;
                $retour .= '<ul class="ul_n3">'.NL;
                if(isset($tab_entree[$palier_id][$pilier_id][$section_id]))
                {
                  foreach($tab_entree[$palier_id][$pilier_id][$section_id] as $entree_id => $entree_texte)
                  {
                    $aff_id = ($ids) ? ' id="E'.$entree_id.'"' : '' ;
                    $retour .= '<li class="li_n3"'.$aff_id.'>'.$entree_texte.'</li>'.NL;
                    
                  }
                }
                $retour .= '</ul>'.NL;
                $retour .= '</li>'.NL;
              }
            }
            $retour .= '</ul>'.NL;
            $retour .= '</li>'.NL;
          }
        }
        $retour .= '</ul>'.NL;
        $retour .= '</li>'.NL;
      }
    }
    $retour .= '</ul>'.NL;
    return $retour;
  }

  /**
   * Retourner un formulaire à insérer sur un bilan pour enchaîner sur la création d'un groupe de besoin ou d'une évaluation.
   * 
   * @param string $format   'complet' | 'simplifié'
   * @return string
   */
  public static function afficher_formulaire_synthese_exploitation($format)
  {
    return ($format=='complet') ?
    '<p>'.
      '<label class="tab" for="f_action">Action :</label>'.
      '<select id="f_action" name="f_action">'.
        '<option value=""></option>'.
        '<option value="evaluer_items_perso">Évaluer des élèves sur des items personnalisés</option>'.
        '<option value="evaluer_items_commun">Évaluer des élèves sur des items communs</option>'.
        '<option value="constituer_groupe_besoin">Constituer un groupe de besoin</option>'.
      '</select><br />'.
      '<span id="span_submit" class="hide">'.
        '<span class="tab"></span>Cocher les cases adéquates puis <button type="button" id="f_submit" class="parametre">accéder au formulaire</button>'.
        '<label id="check_msg"></label>'.
      '</span>'.
    '</p>'.NL : 
    '<p>'.
      '<label class="tab">Action <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Cocher auparavant les cases adéquates." /> :</label>'.
      '<button type="button" class="ajouter" name="evaluation_gestion">Préparer une évaluation.</button> '.
      '<button type="button" class="ajouter" name="professeur_groupe_besoin">Constituer un groupe de besoin.</button> '.
      '<label id="check_msg"></label>'.
    '</p>'.NL ;
  }

  /**
   * Retourner une liste HTML ordonnée des élèves (d'un professeur) pour chaque classe et groupe qui lui sont affectés, avec des cases à cocher.
   *
   * @param bool $with_pourcent
   * @return string
   */
  public static function afficher_form_element_checkbox_eleves_professeur($with_pourcent)
  {
    $affichage = '';
    $tab_regroupements = array();
    $tab_id = array('classe'=>'','groupe'=>'');
    // Recherche de la liste des classes et des groupes du professeur
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_regroupements[$DB_ROW['groupe_id']] = array('nom'=>$DB_ROW['groupe_nom'],'eleve'=>array());
      $tab_id[$DB_ROW['groupe_type']][] = $DB_ROW['groupe_id'];
    }
    // Recherche de la liste des élèves pour chaque classe du professeur
    if(is_array($tab_id['classe']))
    {
      $listing = implode(',',$tab_id['classe']);
      $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_classes($listing);
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_regroupements[$DB_ROW['eleve_classe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
      }
    }
    // Recherche de la liste des élèves pour chaque groupe du professeur
    if(is_array($tab_id['groupe']))
    {
      $listing = implode(',',$tab_id['groupe']);
      $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_groupes($listing);
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_regroupements[$DB_ROW['groupe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
      }
    }
    // Affichage de la liste des élèves (du professeur) pour chaque classe et groupe
    foreach($tab_regroupements as $groupe_id => $tab_groupe)
    {
      $gradient_pourcent = ($with_pourcent) ? '<span id="groupe_'.$groupe_id.'" class="gradient_pourcent"></span>' : '' ;
      $affichage .= '<ul class="ul_m1">'.NL;
      $affichage .=   '<li class="li_m1"><span class="deja">'.html($tab_groupe['nom']).'</span>'.$gradient_pourcent.NL;
      $affichage .=     '<ul class="ul_n3">'.NL;
      foreach($tab_groupe['eleve'] as $eleve_id => $eleve_nom)
      {
        // C'est plus compliqué que pour les items car un élève peut appartenir à une classe et plusieurs groupes => id du groupe mélé à l'id de l'élève
        $affichage .=       '<li class="li_n3"><input id="id_'.$eleve_id.'_'.$groupe_id.'" name="f_eleves[]" type="checkbox" value="'.$eleve_id.'" /><label for="id_'.$eleve_id.'_'.$groupe_id.'"> '.html($eleve_nom).'</label><span></span></li>'.NL;
      }
      $affichage .=     '</ul>'.NL;
      $affichage .=   '</li>'.NL;
      $affichage .= '</ul>'.NL;
    }
    return $affichage;
  }

  /**
   * Retourner, sur une ou plusieurs colonnes, une liste HTML ordonnée des professeurs, avec des cases à cocher.
   *
   * @param void
   * @return string
   */
  public static function afficher_form_element_checkbox_collegues()
  {
    $affichage = '';
    // Affichage de la liste des professeurs
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl();
    if(is_string($DB_TAB))
    {
      echo $DB_TAB;
    }
    else
    {
      $nb_profs              = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
      $nb_profs_maxi_par_col = 20;
      $nb_cols               = floor(($nb_profs-1)/$nb_profs_maxi_par_col)+1;
      $nb_profs_par_col      = ceil($nb_profs/$nb_cols);
      $tab_div = array_fill(0,$nb_cols,'');
      foreach($DB_TAB as $i => $DB_ROW)
      {
        $checked_and_disabled = ($DB_ROW['valeur']!=$_SESSION['USER_ID']) ? '' : ' checked disabled' ; // readonly ne fonctionne pas sur un checkbox
        $texte_identite       = ($DB_ROW['valeur']!=$_SESSION['USER_ID']) ? html($DB_ROW['texte']) : html($DB_ROW['texte']) ;
        $tab_div[floor($i/$nb_profs_par_col)] .= '<label for="p_'.$DB_ROW['valeur'].'"><input type="checkbox" name="f_profs[]" id="p_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'"'.$checked_and_disabled.' /> '.$texte_identite.'</label><br />';
      }
      $affichage .= '<p><a href="#prof_liste" id="prof_check_all" class="cocher_tout">Tout le monde</a>&nbsp;&nbsp;&nbsp;<a href="#prof_liste" id="prof_uncheck_all" class="cocher_rien">Seulement moi</a></p>'.NL;
      $affichage .= '<div class="prof_liste">'.implode('</div>'.NL.'<div class="prof_liste">',$tab_div).'</div>'.NL;
    }
    return $affichage;
  }

  /**
   * Retourner, sur une ou plusieurs colonnes, une liste HTML ordonnée des professeurs, avec un formulaire de choix d'un attribut pour chacun.
   *
   * @param array   $tab_options
   * @return string
   */
  public static function afficher_form_element_select_collegues($tab_options)
  {
    $affichage = '';
    // Affichage de la liste des professeurs
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl();
    if(is_string($DB_TAB))
    {
      echo $DB_TAB;
    }
    else
    {
      $nb_profs              = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
      $nb_profs_maxi_par_col = 20;
      $nb_cols               = floor(($nb_profs-1)/$nb_profs_maxi_par_col)+1;
      $nb_profs_par_col      = ceil($nb_profs/$nb_cols);
      $tab_div = array_fill(0,$nb_cols,'');
      $select_options = '<option value="x">0</option>';
      foreach($tab_options as $option_texte => $option_value)
      {
        $select_options .= '<option value="'.$option_value.'">'.$option_texte.'</option>';
      }
      foreach($DB_TAB as $i => $DB_ROW)
      {
        if($DB_ROW['valeur']!=$_SESSION['USER_ID'])
        {
          $tab_div[floor($i/$nb_profs_par_col)] .= '<select id="p_'.$DB_ROW['valeur'].'" name="p_'.$DB_ROW['valeur'].'" class="t9">'.$select_options.'</select><span class="select_img droit_x">&nbsp;</span><label>'.html($DB_ROW['texte']).'</label><br />';
        }
        else
        {
          $tab_div[floor($i/$nb_profs_par_col)] .= '<select id="p_'.$DB_ROW['valeur'].'" name="p_'.$DB_ROW['valeur'].'" class="t9" disabled><option value="z">4</option></select><span class="droit_z">&nbsp;</span><label>'.html($DB_ROW['texte']).'</label><br />';
        }
      }
      $affichage .= '<p class="hc">Choisir <label for="p_0_x"><input type="radio" name="prof_check_all" id="p_0_x" value="x" /><span class="select_img droit_x">&nbsp;</span></label>';
      foreach($tab_options as $option_value)
      {
        $affichage .= ' ou <label for="p_0_'.$option_value.'"><input type="radio" name="prof_check_all" id="p_0_'.$option_value.'" value="'.$option_value.'" /><span class="select_img droit_'.$option_value.'">&nbsp;</span></label>';
      }
      $affichage .= ' pour tout le monde.</p>'.NL;
      $affichage .= '<div class="prof_liste">'.implode('</div>'.NL.'<div class="prof_liste">',$tab_div).'</div>'.NL;
    }
    return $affichage;
  }

}
?>