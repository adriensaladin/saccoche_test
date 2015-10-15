<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
  private static $tab_tri_etat = array( 'r'=>0 , 'o'=>1 , 'v'=>2 );

  // sert pour indiquer la classe css d'un état d'acquisition
  private static $tab_couleur = array( 'NA'=>'r' , 'VA'=>'o' , 'A'=>'v' );

  // sert pour indiquer la légende des notes spéciales
  private static $tab_legende_notes_speciales_texte  = array('ABS'=>'Absent','DISP'=>'Dispensé','NE'=>'Non évalué','NF'=>'Non fait','NN'=>'Non noté','NR'=>'Non rendu');
  public  static $tab_legende_notes_speciales_nombre = array('ABS'=>0       ,'DISP'=>0         ,'NE'=>0           ,'NF'=>0         ,'NN'=>0         ,'NR'=>0          );

  // remarque : des tableaux réciproques sont aussi utilisés en javascript
  public static $tab_genre = array(
    'enfant' => array( 'I'=>'' , 'M'=>'Masculin' , 'F'=>'Féminin' ) ,
    'adulte' => array( 'I'=>'' , 'M'=>'M.'       , 'F'=>'Mme'     ) ,
  );

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Convertir une date MySQL ou française en un texte avec le nom du mois en toutes lettres.
   *
   * @param string $date   AAAA-MM-JJ ou JJ/MM/AAAA
   * @return string        JJ nom_du mois AAAA
   */
  public static function date_texte($date)
  {
    if(mb_strpos($date,'-')) { list($annee,$mois,$jour) = explode('-',$date); }
    else                     { list($jour,$mois,$annee) = explode('/',$date); }
    $tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
    return $jour.' '.$tab_mois[$mois].' '.$annee;
  }

  /**
   * Renvoyer le chemin d'une note (code couleur) pour une sortie HTML.
   * Le daltonisme a déjà été pris en compte pour forger $_SESSION['IMG_*']
   *
   * @param string $note
   * @return string
   */
  public static function note_src( $note )
  {
    return (in_array($note,array('RR','R','V','VV'))) ? $_SESSION['IMG_'.$note] : './_img/note/commun/h/'.$note.'.gif' ;
  }

  /**
   * Afficher une note (code couleur) pour une sortie HTML.
   *
   * @param string $note
   * @param string $date
   * @param string $info
   * @param bool   $tri
   * @return string
   */
  public static function note_image( $note , $date , $info , $tri=FALSE )
  {
    if(isset(Html::$tab_legende_notes_speciales_nombre[$note])) Html::$tab_legende_notes_speciales_nombre[$note]++;
    $insert_tri = ($tri) ? '<i>'.Html::$tab_tri_note[$note].'</i>' : '';
    $title = ( ($date!='') || ($info!='') ) ? ' title="'.html(html($info)).'<br />'.Html::date_texte($date).'"' : '' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    return (in_array($note,array('-',''))) ? '&nbsp;' : $insert_tri.'<img'.$title.' alt="'.$note.'" src="'.Html::note_src($note).'" />';
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
        $retour .= '<img alt="'.$note.'" src="'.Html::note_src($note).'" />'.html($_SESSION['NOTE_LEGENDE'][$note]);
      }
      foreach(Html::$tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $retour .= '<img alt="'.$note.'" src="'.Html::note_src($note).'" />'.html(Html::$tab_legende_notes_speciales_texte[$note]);
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

}
?>