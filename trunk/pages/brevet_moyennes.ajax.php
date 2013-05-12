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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

// Récupération et test des paramètres communs (affichage et enregistrement)

$action    = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])  : '' ;
$serie_ref = (isset($_POST['f_serie']))  ? Clean::texte($_POST['f_serie'])   : '' ;
$classe_id = (isset($_POST['f_classe'])) ? Clean::entier($_POST['f_classe']) : 0 ;
$eleve_id  = (isset($_POST['f_user']))   ? Clean::entier($_POST['f_user'])   : 0 ;

if( !in_array($action,array('proposer','enregistrer')) || !$serie_ref || !$classe_id || !$eleve_id )
{
  exit('Erreur avec les données transmises !');
}

$DB_TAB_epreuves = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves($serie_ref);

if(empty($DB_TAB_epreuves))
{
  exit('Erreur : série inconnue !');
}

// Récupérer les paramètres des épreuves

$tab_epreuve = array();
foreach($DB_TAB_epreuves as $DB_ROW)
{
  $tab_epreuve[$DB_ROW['brevet_epreuve_code']] = array(
    'epreuve_nom'             =>       $DB_ROW['brevet_epreuve_nom'],
    'epreuve_obligatoire'     => (bool)$DB_ROW['brevet_epreuve_obligatoire'],
    'epreuve_note_chiffree'   => (bool)$DB_ROW['brevet_epreuve_note_chiffree'],
    'epreuve_point_sup_10'    => (bool)$DB_ROW['brevet_epreuve_point_sup_10'],
    'epreuve_note_comptee'    => (bool)$DB_ROW['brevet_epreuve_note_comptee'],
    'epreuve_coefficient'     =>  (int)$DB_ROW['brevet_epreuve_coefficient'],
    'epreuve_code_speciaux'   =>       $DB_ROW['brevet_epreuve_code_speciaux'],
    'epreuve_choix_recherche' => (bool)$DB_ROW['brevet_epreuve_choix_recherche'],
    'epreuve_choix_moyenne'   => (bool)$DB_ROW['brevet_epreuve_choix_moyenne'],
    'epreuve_choix_matieres'  =>       $DB_ROW['brevet_epreuve_choix_matieres']
  );
}

// Récupérer les notes déjà enregistrées

$tab_notes_enregistrees = array();
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_notes_eleve( $serie_ref , $eleve_id );
if(count($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_notes_enregistrees[$DB_ROW['brevet_epreuve_code']] = array( 'note'=>$DB_ROW['saisie_note'] , 'matieres_id'=>$DB_ROW['matieres_id'] );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Proposer une note pour chaque épreuve
// ////////////////////////////////////////////////////////////////////////////////////////////////////


if($action=='proposer')
{
  $tab_codes = array(
    'AB' => 'AB (absent)',
    'DI' => 'DI (dispensé)',
    'VA' => 'VA (validé)',
    'NV' => 'NV (non validé)'
  );
  $optgroup_notes_chiffrees = '<optgroup label="Notes chiffrées">';
  for( $note=0 ; $note<=20 ; $note+=0.5 )
  {
    $optgroup_notes_chiffrees .= '<option value="'.str_replace('.','v',(string)$note).'">'.sprintf("%05.2f",$note).' / 20</option>';
  }
  $optgroup_notes_chiffrees .= '</optgroup>';
  /*
   * Fonction pour fabriquer la liste des options d'un select de choix d'une note d'une épreuve
   */
  function options_note($epreuve_obligatoire,$epreuve_note_chiffree,$epreuve_code_speciaux)
  {
    global $optgroup_notes_chiffrees,$tab_codes;
    $option_sans_objet = ($epreuve_obligatoire) ? '' : '<option value="">sans objet</option>' ;
    // Codes spéciaux
    $optgroup_codes_speciaux = '<optgroup label="Codes spéciaux">';
    $tab_code_speciaux = explode(',',$epreuve_code_speciaux);
    foreach($tab_code_speciaux as $code_special)
    {
      $optgroup_codes_speciaux .= '<option value="'.$code_special.'">'.$tab_codes[$code_special].'</option>';
    }
    $optgroup_codes_speciaux .= '</optgroup>';
    return(!$epreuve_note_chiffree) ? $option_sans_objet.$optgroup_codes_speciaux : $option_sans_objet.$optgroup_codes_speciaux.$optgroup_notes_chiffrees;
  }
  // Récupérer les moyennes de bulletins (on ne sait pas encore pour quelle matière c'est demandé, mais c'est le mode par défaut, et ce n'est pas dur à récupérer)
  $tab_moyennes_bulletin = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_notes_eleve_periodes($eleve_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $note_affichee = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $DB_ROW['saisie_note'] : (round($DB_ROW['saisie_note']*5)).'%' ;
    $tab_moyennes_bulletin[$DB_ROW['matiere_id']]['note'][$DB_ROW['periode_id']] = $DB_ROW['saisie_note'];
    $tab_moyennes_bulletin[$DB_ROW['matiere_id']]['txt' ][$DB_ROW['periode_id']] = $note_affichee.' <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['periode_nom']).'" />';
  }
  // Récupérer les noms des matières
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_matieres_etabl = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_matieres_etabl[$DB_ROW['valeur']] = html($DB_ROW['texte']);
  }
  // Paramètres requis par [noyau_items_releve.php] pour calculer une moyenne annuelle
  $format                 = 'matiere';
  $aff_etat_acquisition   = 0;
  $aff_moyenne_scores     = 0;
  $aff_pourcentage_acquis = 0;
  $matiere_id             = TRUE;
  $matiere_nom            = '';
  $with_coef              = 1; // Il n'y a qu'une matière, on prend en compte les coefficients.
  $groupe_nom             = '';
  $periode_id = 0;
  $date_debut             = jour_debut_annee_scolaire('french');
  $date_fin               = TODAY_FR;
  $retroactif             = 'non';
  $only_socle             = 0;
  $aff_domaine            = 0;
  $aff_theme              = 0;
  $legende                = 'non';
  $tab_eleve              = array($eleve_id); // tableau de l'unique élève à considérer
  $liste_eleve            = (string)$eleve_id;
  $tab_type[]             = 'bulletin';
  $type_individuel        = 0;
  $type_synthese          = 0;
  $type_bulletin          = 1;
  $make_officiel          = FALSE;
  $make_brevet            = TRUE;
  $make_action            = '';
  $make_html              = FALSE;
  $make_pdf               = FALSE;
  $make_graph             = FALSE;
  // Analyser épreuve par épreuve
  foreach($tab_epreuve as $epreuve_code => $tab_infos)
  {
    $tab_td = array();
    extract($tab_infos); // $epreuve_*
    // 1/4 Épreuve
    if(!$epreuve_note_comptee)
    {
      $infobulle = ' <img alt="" src="./_img/bulle_aide.png" title="Présence d\'une note obligatoire, mais seulement à titre informatif, celle-ci n\'étant pas comptabilisée dans le total des points." />';
    }
    elseif(!$epreuve_note_chiffree)
    {
      $infobulle = ' <img alt="" src="./_img/bulle_aide.png" title="Pas de note chiffrée à saisir pour cette épreuve : uniquement un état de validation." />';
    }
    elseif($epreuve_point_sup_10)
    {
      $infobulle = ' <img alt="" src="./_img/bulle_aide.png" title="Épreuve dont seuls les points supérieurs à la moyenne sont pris en compte." />';
    }
    elseif($epreuve_coefficient!=1)
    {
      $infobulle = ' <img alt="" src="./_img/bulle_aide.png" title="Épreuve de coefficient '.$epreuve_coefficient.' (note sur '.($epreuve_coefficient*20).')." />';
    }
    else
    {
      $infobulle = '';
    }
    $tab_td[1] = '<td>'.html($epreuve_nom).$infobulle.'</td>';
    // 2/4 Référentiel(s)
    $note_proposee = FALSE ;
    $tab_td[2] = '<td>';
    if( !$epreuve_obligatoire && !$epreuve_choix_matieres )
    {
      $tab_td[2] .= 'sans objet';
    }
    else
    {
      // Passer en revue les référentiels
      $tab_moyenne_referentiel = array();
      $tab_moyenne_annuelle    = array();
      $tab_choix_matieres = explode(',',$epreuve_choix_matieres);
      $tab_choix_matieres_avec_donnees = array();
      foreach($tab_choix_matieres as $matiere_id)
      {
        $tab_moyenne_referentiel[$matiere_id] = FALSE ;
        $ligne_calcul_moyenne = '';
        // Calculer une moyenne à partir du bulletin
        if( $epreuve_choix_recherche && isset($tab_moyennes_bulletin[$matiere_id]) )
        {
          $tab_moyenne_referentiel[$matiere_id] = round( array_sum($tab_moyennes_bulletin[$matiere_id]['note']) / count($tab_moyennes_bulletin[$matiere_id]['note']) , 1 );
          $ligne_calcul_moyenne = '<div>Bulletins : '.implode(' ',$tab_moyennes_bulletin[$matiere_id]['txt']).' &rarr; <b>'.$tab_moyenne_referentiel[$matiere_id].'</b></div>';
          $tab_choix_matieres_avec_donnees[] = $matiere_id;
        }
        // Calculer une moyenne annuelle des acquisitions
        if($tab_moyenne_referentiel[$matiere_id]===FALSE)
        {
          if(!isset($tab_moyenne_annuelle[$matiere_id]))
          {
            require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_releve.php');
            $tab_moyenne_annuelle[$matiere_id] = ($moyenne_moyenne_scores!==FALSE) ? round( $moyenne_moyenne_scores/5 , 1 ) : FALSE ;
          }
          if($tab_moyenne_annuelle[$matiere_id]!==FALSE)
          {
            $tab_moyenne_referentiel[$matiere_id] = $tab_moyenne_annuelle[$matiere_id];
            $ligne_calcul_moyenne = '<div>Moyenne annuelle des acquisitions : <b>'.$tab_moyenne_annuelle[$matiere_id].'</b></div>';
            $tab_choix_matieres_avec_donnees[] = $matiere_id;
          }
        }
        // Déterminer celui qui doit être coché et dont la note doit être reportée
        if(isset($tab_notes_enregistrees[$epreuve_code]))
        {
          $test_checked = $tab_notes_enregistrees[$epreuve_code]['matieres_id'];
        }
        elseif( (count($tab_choix_matieres)==1) || (!$epreuve_choix_moyenne) )
        {
          $test_checked = $epreuve_choix_matieres;
        }
        elseif( ($note_proposee===FALSE) && ($tab_moyenne_referentiel[$matiere_id]!==FALSE) )
        {
          $test_checked = $matiere_id ;
        }
        // Attention au test suivant : ( 414 == '414,406' ) renvoie TRUE
        $checked       = ((string)$matiere_id===(string)$test_checked) ? ' checked' : '' ;
        $note_proposee = ((string)$matiere_id===(string)$test_checked) ? $tab_moyenne_referentiel[$matiere_id] : $note_proposee ;
        $note_reportee = ($tab_moyenne_referentiel[$matiere_id]!==FALSE) ? ceilTo( $tab_moyenne_referentiel[$matiere_id] , 0.5 ) : '' ;
        $tab_td[2] .= '<div class="b"><input type="radio" id="radio_'.$epreuve_code.'_'.$matiere_id.'" name="check_'.$epreuve_code.'" value="'.$matiere_id.'"'.$checked.' /><i>'.str_replace('.','v',(string)$note_reportee).'</i><label for="radio_'.$epreuve_code.'_'.$matiere_id.'"> '.$tab_matieres_etabl[$matiere_id].'</label></div>'.$ligne_calcul_moyenne;
      }
      // Si besoin, terminer avec une moyenne des référentiels
      if( (count($tab_choix_matieres)>1) && (!$epreuve_choix_moyenne) )
      {
        $somme  = array_sum($tab_moyenne_referentiel);
        $nombre = count( array_filter($tab_moyenne_referentiel,'non_nul') );
        $moyenne_moyenne_referentiels = ($nombre) ? round($somme/$nombre,1) : FALSE ;
        // Déterminer celui qui doit être coché et dont la note doit être reportée
        $test_checked = (isset($tab_notes_enregistrees[$epreuve_code])) ? $tab_notes_enregistrees[$epreuve_code]['matieres_id'] : $epreuve_choix_matieres ;
        // Attention au test suivant : ( 414 == '414,406' ) renvoie TRUE
        $checked       = ((string)$epreuve_choix_matieres===(string)$test_checked) ? ' checked' : '' ;
        $note_proposee = ((string)$epreuve_choix_matieres===(string)$test_checked) ? $moyenne_moyenne_referentiels : $note_proposee ;
        $note_reportee = ($moyenne_moyenne_referentiels!==FALSE) ? ceilTo( $moyenne_moyenne_referentiels , 0.5 ) : '' ;
        $tab_td[2] .= '<div class="b"><input type="radio" id="radio_'.$epreuve_code.'_multi" name="check_'.$epreuve_code.'" value="'.implode('-',$tab_choix_matieres_avec_donnees).'"'.$checked.' /><i>'.str_replace('.','v',(string)$note_reportee).'</i><label for="radio_'.$epreuve_code.'_multi"> Ensemble des référentiels</label></div>';
        if($moyenne_moyenne_referentiels!==FALSE)
        {
          $tab_td[2] .= '<div>Moyenne : <b>'.$moyenne_moyenne_referentiels.'</b></div>';
        }
      }
    }
    $tab_td[2] .= '</td>';
    // 3/4 Note proposée
    if($note_proposee!==FALSE)
    {
      if($epreuve_note_chiffree)
      {
        $note_selectionnee = ceilTo( $note_proposee , 0.5 );
      }
      else
      {
        $note_selectionnee = ($note_proposee>=10) ? 'VA' : 'NV' ;
      }
    }
    elseif(!$epreuve_obligatoire)
    {
      $note_selectionnee = '-';
    }
    else
    {
      $note_selectionnee = ($epreuve_code==106) ? 'DI' : 'AB' ; // Par défaut on prend DI pour l'EPS et EB pour les autres épreuves
    }
    // Couleur de fond
    if($note_selectionnee=='-')
    {
      $class = (isset($tab_notes_enregistrees[$epreuve_code])) ? 'bj' : '' ;
    }
    elseif(isset($tab_notes_enregistrees[$epreuve_code]))
    {
      $class = ($note_selectionnee==$tab_notes_enregistrees[$epreuve_code]['note']) ? 'bv' : 'bj' ;
    }
    else
    {
      $class = is_numeric($note_selectionnee) ? 'bv' : 'bj' ;
    }
    $note_recherchee = str_replace('.','v',(string)$note_selectionnee);
    $tab_td[3] = '<td class="'.$class.'"><select id="note_'.$epreuve_code.'" name="note_'.$epreuve_code.'">'.str_replace( 'value="'.$note_recherchee.'"' , 'value="'.$note_recherchee.'" selected' , options_note($epreuve_obligatoire,$epreuve_note_chiffree,$epreuve_code_speciaux) ).'</select></td>';
    if(!is_numeric($note_selectionnee))
    {
      $tab_td[2] = str_replace( '<i></i>' , '<i>'.$note_selectionnee.'</i>' , $tab_td[2] );
    }
    // 4/4 Note enregistrée
    if(isset($tab_notes_enregistrees[$epreuve_code]))
    {
      $note = is_numeric($tab_notes_enregistrees[$epreuve_code]['note']) ? sprintf("%05.2f",$tab_notes_enregistrees[$epreuve_code]['note']) : $tab_notes_enregistrees[$epreuve_code]['note'] ;
      $tab_td[4] = '<td class="hc bv">'.$note.'</td>';
    }
    else
    {
      $class = ($epreuve_obligatoire || is_numeric($note_selectionnee)) ? 'br' : '' ;
      $tab_td[4] = '<td class="hc '.$class.'">-</td>';
    }
    // Affichage de la ligne
    echo'<tr>'.implode('',$tab_td).'</tr>';
  }
  // Ligne avec le total des points
  if(isset($tab_notes_enregistrees[CODE_BREVET_EPREUVE_TOTAL]))
  {
    $note = is_numeric($tab_notes_enregistrees[CODE_BREVET_EPREUVE_TOTAL]['note']) ? sprintf("%06.2f",$tab_notes_enregistrees[CODE_BREVET_EPREUVE_TOTAL]['note']) : $tab_notes_enregistrees[CODE_BREVET_EPREUVE_TOTAL]['note'] ;
  }
  else
  {
    $note = '-';
  }
  echo'<tr><th colspan="2" class="nu"></th><th class="hc">Total des points</th><th class="hc">'.$note.'</th></tr>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer les notes pour chaque épreuve
// ////////////////////////////////////////////////////////////////////////////////////////////////////


if($action=='enregistrer')
{
  // Récupérer et contrôler les valeurs transmises épreuve par épreuve
  // Calculer au passage le total des points
  $tab_notes_transmises = array();
  $tab_notes_transmises[CODE_BREVET_EPREUVE_TOTAL] = array( 'note' => 'AB' , 'matieres_id' => '' );
  foreach($tab_epreuve as $epreuve_code => $tab_infos)
  {
    extract($tab_infos); // $epreuve_*
    // Matières utilisées
    $tab_matieres_id = (isset($_POST['check_'.$epreuve_code])) ? explode('-',$_POST['check_'.$epreuve_code]) : array() ;
    $tab_matieres_id = Clean::map_entier($tab_matieres_id);
    $tab_matieres_id = array_filter($tab_matieres_id,'positif');
    $matieres_id = implode(',',$tab_matieres_id);
    if( !$matieres_id && $epreuve_obligatoire )
    {
      exit('Référentiel(s) manquant(s) ou incorrect(s) pour l\'épreuve "'.html($epreuve_nom).'" !');
    }
    // Note
    $note_transmise = (isset($_POST['note_'.$epreuve_code])) ? str_replace('v5','.5',$_POST['note_'.$epreuve_code]) : NULL ;
    if( ($note_transmise===NULL) || ( ($note_transmise==='') && $epreuve_obligatoire ) )
    {
      exit('Note manquante pour l\'épreuve "'.html($epreuve_nom).'" !');
    }
    if( is_numeric($note_transmise) && ( (ceilTo($note_transmise,0.5)!=$note_transmise) || ($note_transmise<0) || ($note_transmise>20) || (!$epreuve_note_chiffree) ) )
    {
      exit('Note '.html($note_transmise).' invalide pour l\'épreuve "'.html($epreuve_nom).'" !');
    }
    if( !is_numeric($note_transmise) && ($note_transmise!='') && (strpos($epreuve_code_speciaux,$note_transmise)===FALSE) )
    {
      exit('Note '.html($note_transmise).' invalide pour l\'épreuve "'.html($epreuve_nom).'" !');
    }
    // On garde la note et les matières
    if($note_transmise!=='')
    {
      if(is_numeric($note_transmise))
      {
        $tab_notes_transmises[$epreuve_code] = array( 'note' => (float)$note_transmise , 'matieres_id' => $matieres_id );
        if($epreuve_note_comptee)
        {
          $tab_notes_transmises[CODE_BREVET_EPREUVE_TOTAL]['note'] += ($epreuve_point_sup_10) ? max(0,$note_transmise-10) : $note_transmise*$epreuve_coefficient ;
        }
      }
      else
      {
        $tab_notes_transmises[$epreuve_code] = array( 'note' => (string)$note_transmise , 'matieres_id' => $matieres_id );
      }
    }
  }
  // Mettre à jour ce qu'il faut, en retenant ce qui est concerné pour un (re)calcul des moyennes
  // S'occuper aussi du total des points
  $tab_epreuves_maj = array();
  $tab_epreuve[CODE_BREVET_EPREUVE_TOTAL] = array();
  $tab_td = array();
  foreach($tab_epreuve as $epreuve_code => $tab_infos)
  {
    // Si note non transmise...
    if(!isset($tab_notes_transmises[$epreuve_code]))
    {
      // et note déjà enregistrée...
      if(isset($tab_notes_enregistrees[$epreuve_code]))
      {
        // Retirer la note (et l'appréciation éventuelle)
        DB_STRUCTURE_BREVET::DB_supprimer_brevet_saisie( $serie_ref , $epreuve_code , 'eleve' /*saisie_type*/ , $eleve_id );
        $tab_epreuves_maj[] = $epreuve_code;
      }
    }
    // Si note transmise et non enregistrée...
    elseif(!isset($tab_notes_enregistrees[$epreuve_code]))
    {
      // Ajouter la note
      DB_STRUCTURE_BREVET::DB_ajouter_brevet_note( $serie_ref , $epreuve_code , 'eleve' /*saisie_type*/ , $eleve_id , $tab_notes_transmises[$epreuve_code]['matieres_id'] , $tab_notes_transmises[$epreuve_code]['note'] );
      $tab_epreuves_maj[] = $epreuve_code;
    }
    // Si note transmise et différente de celle enregistrée...
    elseif( ( $tab_notes_enregistrees[$epreuve_code]['note'] != $tab_notes_transmises[$epreuve_code]['note'] ) || ( $tab_notes_enregistrees[$epreuve_code]['matieres_id'] != $tab_notes_transmises[$epreuve_code]['matieres_id'] ) )
    {
      // Mettre à jour la note (sans toucher à l'appréciation)
      DB_STRUCTURE_BREVET::DB_modifier_brevet_note( $serie_ref , $epreuve_code , 'eleve' /*saisie_type*/ , $eleve_id , $tab_notes_transmises[$epreuve_code]['matieres_id'] , $tab_notes_transmises[$epreuve_code]['note'] );
      $tab_epreuves_maj[] = $epreuve_code;
    }
    // Retour à renvoyer
    if($epreuve_code==CODE_BREVET_EPREUVE_TOTAL)
    {
      $note = is_numeric($tab_notes_transmises[CODE_BREVET_EPREUVE_TOTAL]['note']) ? sprintf("%06.2f",$tab_notes_transmises[CODE_BREVET_EPREUVE_TOTAL]['note']) : $tab_notes_transmises[CODE_BREVET_EPREUVE_TOTAL]['note'] ;
      $tab_td[] = '<th class="hc">'.$note.'</th>';
    }
    elseif(isset($tab_notes_transmises[$epreuve_code]))
    {
      $note = is_numeric($tab_notes_transmises[$epreuve_code]['note']) ? sprintf("%05.2f",$tab_notes_transmises[$epreuve_code]['note']) : $tab_notes_transmises[$epreuve_code]['note'] ;
      $tab_td[] = '<td class="hc bv">'.$note.'</td>';
    }
    else
    {
      $tab_td[] = '<td class="hc">-</td>';
    }
  }
  // (re)calculer les moyennes de classe concernées
  if(count($tab_epreuves_maj))
  {
    $listing_epreuves_maj = implode(',',$tab_epreuves_maj);
    $DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_notes_epreuves_classe( $serie_ref , $listing_epreuves_maj , $classe_id );
    if(count($DB_TAB))
    {
      $tab_notes_considerees = array();
      foreach($DB_TAB as $DB_ROW)
      {
        if(is_numeric($DB_ROW['saisie_note']))
        {
          $tab_notes_considerees[$DB_ROW['brevet_epreuve_code']][$DB_ROW['saisie_type']][] = (float)$DB_ROW['saisie_note'];
        }
      }
      foreach($tab_notes_considerees as $epreuve_code => $tab_saisie_type)
      {
        if(isset($tab_saisie_type['eleve']))
        {
          $moyenne_classe_epreuve = round( array_sum($tab_saisie_type['eleve']) / count($tab_saisie_type['eleve']) , 1 );
          if(!isset($tab_saisie_type['classe']))
          {
            // Ajouter la moyenne de classe
            DB_STRUCTURE_BREVET::DB_ajouter_brevet_note( $serie_ref , $epreuve_code , 'classe' /*saisie_type*/ , $classe_id , $tab_notes_transmises[$epreuve_code]['matieres_id'] , $moyenne_classe_epreuve );
          }
          elseif($tab_saisie_type['classe'][0]!=$moyenne_classe_epreuve)
          {
            // Mettre à jour la moyenne de classe
            DB_STRUCTURE_BREVET::DB_modifier_brevet_note( $serie_ref , $epreuve_code , 'classe' /*saisie_type*/ , $classe_id , $tab_notes_transmises[$epreuve_code]['matieres_id'] , $moyenne_classe_epreuve );
          }
        }
        elseif(isset($tab_saisie_type['classe']))
        {
          // Retirer la moyenne de classe
          DB_STRUCTURE_BREVET::DB_supprimer_brevet_saisie( $serie_ref , $epreuve_code , 'classe' /*saisie_type*/ , $classe_id );
        }
      }
    }
  }
  // game over
  exit(implode('¤',$tab_td));
}

?>
