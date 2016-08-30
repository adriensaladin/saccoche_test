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

class HtmlArborescence
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Retourner une liste HTML ordonnée de l'arborescence d'un référentiel matière à partir d'une requête SQL transmise.
   * 
   * @param tab         $DB_TAB
   * @param tab         $DB_TAB_socle2016
   * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
   * @param bool        $aff_ref     afficher ou pas les références
   * @param bool        $aff_coef    affichage des coefficients des items (sous forme d'image)
   * @param bool        $aff_cart    affichage des possibilités de demandes d'évaluation des items (sous forme d'image)
   * @param bool|string $aff_socle   FALSE | 'texte' | 'image' : affichage de la liaison au socle
   * @param bool|string $aff_lien    FALSE | 'image' | 'click' : affichage des liens (ressources pour travailler)
   * @param bool        $aff_comm    affichage ou pas du commentaire associé à l'item
   * @param bool        $aff_input   affichage ou pas des input checkbox avec label
   * @param string      $aff_id_li   vide par défaut, "n3" pour ajouter des id aux li_n3
   * @return string
   */
  public static function afficher_matiere_from_SQL( $DB_TAB , $DB_TAB_socle2016 , $dynamique , $aff_ref , $aff_coef , $aff_cart , $aff_socle , $aff_lien , $aff_comm , $aff_input , $aff_id_li='' )
  {
    $input_all = ($aff_input) ? '<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q>' : '' ;
    $input_texte = '';
    $comm_texte  = '';
    $coef_texte  = '';
    $cart_texte  = '';
    $socle_texte = '';
    $s2016_texte = '';
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
        $tab_matiere[$matiere_id] = ($aff_ref) ? $DB_ROW['matiere_ref'].' - '.$DB_ROW['matiere_nom'] : $DB_ROW['matiere_nom'] ;
        $niveau_id  = 0;
        $domaine_id = 0;
        $theme_id   = 0;
        $item_id    = 0;
      }
      if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
      {
        $niveau_id = $DB_ROW['niveau_id'];
        $prefixe   = ($aff_ref) ? $DB_ROW['niveau_ref'].' - ' : '' ;
        $tab_niveau[$matiere_id][$niveau_id] = $prefixe.$DB_ROW['niveau_nom'];
      }
      if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
      {
        $domaine_id = $DB_ROW['domaine_id'];
        $reference  = ($DB_ROW['domaine_ref']) ? $DB_ROW['domaine_ref'] : $DB_ROW['domaine_code'] ;
        $prefixe    = ($aff_ref) ? $reference.' - ' : '' ;
        $tab_domaine[$matiere_id][$niveau_id][$domaine_id] = $prefixe.$DB_ROW['domaine_nom'];
      }
      if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
      {
        $theme_id  = $DB_ROW['theme_id'];
        $reference = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'] : $DB_ROW['domaine_code'].$DB_ROW['theme_ordre'] ;
        $prefixe   = ($aff_ref) ? $reference.' - ' : '' ;
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
            $s2016_texte = ($DB_ROW['s2016_nb'])  ? '[S] ' : '[–] ';
            break;
          case 'image' :
            $socle_image = ($DB_ROW['entree_id']) ? 'oui' : 'non' ;
            $socle_title = ($DB_ROW['entree_id']) ? html($DB_ROW['entree_nom']) : 'Hors-socle.' ;
            $socle_texte = '<img src="./_img/etat/socle_'.$socle_image.'.png" title="'.$socle_title.'" /> ';
            $s2016_image = isset($DB_TAB_socle2016[$DB_ROW['item_id']]) ? 'oui' : 'non' ;
            $s2016_title = isset($DB_TAB_socle2016[$DB_ROW['item_id']]) ? implode('<br />',$DB_TAB_socle2016[$DB_ROW['item_id']]['nom']) : 'Hors-socle 2016.' ;
            $s2016_texte = '<img src="./_img/etat/socle_'.$s2016_image.'.png" title="'.$s2016_title.'" /> ';
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
        if($aff_comm)
        {
          $comm_image  = ($DB_ROW['item_comm']) ? 'oui' : 'non' ;
          $comm_title  = ($DB_ROW['item_comm']) ? convertCRtoBR(html(html($DB_ROW['item_comm']))) : 'Sans commentaire.' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
          $comm_texte  = '<img src="./_img/etat/comm_'.$comm_image.'.png" title="'.$comm_title.'" /> ';
        }
        if($aff_input)
        {
          $input_texte = '<input id="item_'.$item_id.'" name="f_items[]" type="checkbox" value="'.$item_id.'" /> ';
          $label_texte_avant = '<label for="item_'.$item_id.'">';
          $label_texte_apres = '</label>';
        }
        $reference = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref'] || $DB_ROW['item_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'].$DB_ROW['item_ref'] : $DB_ROW['domaine_code'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'] ;
        $prefixe   = ($aff_ref) ? $reference.' - ' : '' ;
        $tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id][$item_id] = $label_texte_avant.$input_texte.$coef_texte.$cart_texte.$socle_texte.$s2016_texte.$lien_texte.$comm_texte.$lien_texte_avant.html($prefixe.$DB_ROW['item_nom']).$lien_texte_apres.$label_texte_apres;
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
   * @param array $DB_TAB
   * @param bool  $dynamique   arborescence cliquable ou pas (plier/replier)
   * @param bool  $reference   afficher ou pas les références
   * @param bool  $aff_input   affichage ou pas des input radio avec label
   * @param bool  $ids         indiquer ou pas les identifiants des éléments (Pxxx / Sxxx / Exxx)
   * @return string
   */
  public static function afficher_socle_from_SQL( $DB_TAB , $dynamique , $reference , $aff_input , $ids )
  {
    $input_texte = '';
    $label_texte_avant = '';
    $label_texte_apres = '';
    // Traiter le retour SQL : on remplit les tableaux suivants.
    $tab_palier  = array();
    $tab_pilier  = array();
    $tab_section = array();
    $tab_entree  = array();
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
   * Retourner une liste HTML ordonnée de l'arborescence du socle 2016.
   * 
   * @return array(string_html,string_js,string_select)
   */
  public static function afficher_socle2016()
  {
    // On remplit les tableaux suivants à partir des requêtes SQL
    $tab_cycle      = array();
    $tab_domaine    = array();
    $tab_composante = array();
    // 1) Cycles
    $DB_TAB_Cycles = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_cycles();
    foreach($DB_TAB_Cycles as $DB_ROW)
    {
      $cycle_id = $DB_ROW['socle_cycle_id'];
      $tab_cycle[$cycle_id] = html($DB_ROW['socle_cycle_nom']).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html($DB_ROW['socle_cycle_description']).'" />';
    }
    // 2) Domaines et composantes
    $DB_TAB_Socle = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_arborescence();
    foreach($DB_TAB_Socle as $DB_ROW)
    {
      $domaine_id    = $DB_ROW['socle_domaine_id'];
      $composante_id = $DB_ROW['socle_composante_id'];
      $tab_domaine[$domaine_id] = html($DB_ROW['socle_domaine_ordre'].' '.$DB_ROW['socle_domaine_nom']);
      $tab_composante[$domaine_id][$composante_id] = html($DB_ROW['socle_composante_nom']);
    }
    // Affichage de l'arborescence
    $span_avant = '<span>';
    $span_apres = '</span>';
    $retour_html   = '<ul class="ul_m1">'.NL;
    $retour_js     = 'var tab_socle = new Array();';
    $retour_select = '<option value="">&nbsp;</option>';
    foreach($tab_cycle as $cycle_id => $cycle_texte)
    {
      $retour_html .= '<li class="li_m1">'.$span_avant.$cycle_texte.$span_apres.NL;
      $retour_html .= '<ul class="ul_n1">'.NL;
      foreach($tab_domaine as $domaine_id => $domaine_texte)
      {
        $retour_html .= '<li class="li_n1">'.$span_avant.$domaine_texte.$span_apres.NL;
        $retour_html .= '<ul class="ul_n2">'.NL;
        foreach($tab_composante[$domaine_id] as $composante_id => $composante_texte)
        {
          $input_id = $cycle_id.$composante_id;
          $input_texte       = '<input id="socle2016_'.$input_id.'" name="f_socle2016" type="checkbox" value="'.$input_id.'" /> ';
          $label_texte_avant = '<label for="socle2016_'.$input_id.'">';
          $label_texte_apres = '</label>';
          $retour_html   .= '<li class="li_n2">'.$label_texte_avant.$input_texte.$composante_texte.$label_texte_apres.'</li>'.NL;
          $retour_js     .= 'tab_socle['.$input_id.']="Cycle '.$cycle_id.' - Domaine '.$domaine_id.' - '.$composante_texte.'";';
          $retour_select .= '<option value="'.$input_id.'">Cycle '.$cycle_id.' - Domaine '.$domaine_id.' - '.$composante_texte.'</option>';
        }
        $retour_html .= '</ul>'.NL;
        $retour_html .= '</li>'.NL;
      }
      $retour_html .= '</ul>'.NL;
      $retour_html .= '</li>'.NL;
    }
    $retour_html .= '</ul>'.NL;
    return array( $retour_html , $retour_js , $retour_select );
  }

}
?>