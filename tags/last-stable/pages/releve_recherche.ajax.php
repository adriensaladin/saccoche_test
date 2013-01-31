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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des données transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// info groupe
$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::texte($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])  : 0;
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? Clean::texte($_POST['f_groupe_nom'])  : '';

$critere_objet = (isset($_POST['f_critere_objet'])) ? Clean::texte($_POST['f_critere_objet']) : '';
$with_coef     = (isset($_POST['f_with_coef']))     ? 1                                      : 0;

// item(s) matière(s)
$tab_compet_liste = (isset($_POST['f_matiere_items_liste'])) ? explode('_',$_POST['f_matiere_items_liste']) : array() ;
$tab_compet_liste = Clean::map_entier($tab_compet_liste);
$compet_liste = implode(',',$tab_compet_liste);
$compet_nombre = count($tab_compet_liste);

// item ou pilier socle
$socle_item_id   = (isset($_POST['f_socle_item_id'])) ? Clean::entier($_POST['f_socle_item_id']) : 0;
$socle_pilier_id = (isset($_POST['f_select_pilier'])) ? Clean::entier($_POST['f_select_pilier']) : 0;

// mode de recherche (situation n°3 uniquement)
$mode           = (isset($_POST['f_mode']))    ? Clean::texte($_POST['f_mode'])     : '';
$tab_matiere_id = (isset($_POST['f_matiere'])) ? ( (is_array($_POST['f_matiere'])) ? $_POST['f_matiere'] : explode(',',$_POST['f_matiere']) ) : array() ;
$tab_matiere_id = array_filter( Clean::map_entier($tab_matiere_id) , 'positif' );

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$critere_tab_seuil_acquis = ( (isset($_POST['f_critere_seuil_acquis'])) && (is_array($_POST['f_critere_seuil_acquis'])) ) ? $_POST['f_critere_seuil_acquis'] : array();
$critere_tab_seuil_valide = ( (isset($_POST['f_critere_seuil_valide'])) && (is_array($_POST['f_critere_seuil_acquis'])) ) ? $_POST['f_critere_seuil_valide'] : array();
$nb_criteres_acquis = count($critere_tab_seuil_acquis);
$nb_criteres_valide = count($critere_tab_seuil_valide);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des données transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$is_matiere_items_bilanMS   = ( ($critere_objet=='matiere_items_bilanMS')   && $compet_nombre   && $nb_criteres_acquis ) ? TRUE : FALSE ;
$is_matiere_items_bilanPA   = ( ($critere_objet=='matiere_items_bilanPA')   && $compet_nombre   && $nb_criteres_acquis ) ? TRUE : FALSE ;
$is_socle_item_pourcentage  = ( ($critere_objet=='socle_item_pourcentage')  && $socle_item_id   && $nb_criteres_acquis ) ? TRUE : FALSE ;
$is_socle_item_validation   = ( ($critere_objet=='socle_item_validation')   && $socle_item_id   && $nb_criteres_valide ) ? TRUE : FALSE ;
$is_socle_pilier_validation = ( ($critere_objet=='socle_pilier_validation') && $socle_pilier_id && $nb_criteres_valide ) ? TRUE : FALSE ;
$critere_valide = ( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA || $is_socle_item_pourcentage || $is_socle_item_validation || $is_socle_pilier_validation ) ? TRUE : FALSE ;

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

if( (!$critere_valide) || (!$groupe_id) || (!$groupe_nom) || (!isset($tab_types[$groupe_type])) || (!in_array($mode,array('auto','manuel'))) )
{
  exit('Erreur avec les données transmises !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Variables pour récupérer les données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve      = array();  // [i] => array(eleve_id,eleve_nom,eleve_prenom)

// Tableau des langues
require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'user_id,user_nom,user_prenom,eleve_langue' ) ;
$eleve_nb = count($tab_eleve);
if(!$eleve_nb)
{
  exit('Aucun élève trouvé dans le regroupement indiqué !');
}
$tab_eleve_id = array();
$tab_eleve_langue = array();
foreach($tab_eleve as $DB_ROW)
{
  $tab_eleve_id[] = $DB_ROW['user_id'];
  $tab_eleve_langue[$DB_ROW['user_id']] = $DB_ROW['eleve_langue'];
}
$liste_eleve = implode(',',$tab_eleve_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Suite du code un peu en vrac avec des reprises et des adaptations de morceaux existants...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_checkbox = ( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && (SACoche!='webservices') ) ? TRUE : FALSE ;

$tab_eval         = array();  // [eleve_id][item_id][]['note'] => note   [type "pourcentage" uniquement]
$tab_item         = array();  // [item_id] => array(calcul_methode,calcul_limite); [type "pourcentage" uniquement]

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// =====> Cas n°1 : moyenne des scores d'acquisition d'items matières sélectionnés
// =====> Cas n°2 : pourcentage d'items acquis d'items matières sélectionnés

if( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA )
{
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_infos_items($compet_liste,$detail=TRUE);
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_item[$DB_ROW['item_id']] = array('item_coef'=>$DB_ROW['item_coef'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
  }
  // Un directeur effectuant une recherche sur un grand nombre d'items pour tous les élèves de l'établissement peut provoquer un dépassement de mémoire.
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve , $compet_liste , 0 /*matiere_id*/ , NULL /*date_mysql_debut*/ , NULL /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] , TRUE /*onlynote*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
  }
}

// =====> Cas n°3 : pourcentage d'items disciplinaires acquis d'un item du socle

if( $is_socle_item_pourcentage )
{
  $is_langue = (in_array($socle_item_id,$tab_langue_items)) ? TRUE : FALSE ;
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_palier_sans_infos_items($liste_eleve , $socle_item_id , $_SESSION['USER_PROFIL_TYPE']);
  foreach($DB_TAB as $DB_ROW)
  {
    $test_comptabilise = ($mode=='auto') ? ( !$is_langue || in_array($DB_ROW['matiere_id'],$tab_langues[$tab_eleve_langue[$DB_ROW['eleve_id']]]['tab_matiere_id']) ) : in_array($DB_ROW['matiere_id'],$tab_matiere_id) ;
    if($test_comptabilise)
    {
      $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
      $tab_item[$DB_ROW['item_id']] = TRUE;
    }
  }
  if(count($tab_item))
  {
    $listing_item_id = implode(',',array_keys($tab_item));
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_infos_items($listing_item_id,$detail=FALSE);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_item[$DB_ROW['item_id']] = array('calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
    }
  }
}

// =====> Cas n°4 : état de validation d'un item du socle
// =====> Cas n°5 : état de validation d'un pilier du socle

if( $is_socle_item_validation || $is_socle_pilier_validation )
{
  $tab_user_validation = array();
  $is_langue = ( $is_socle_item_validation && in_array($socle_item_id,$tab_langue_items) ) || ( $is_socle_pilier_validation && in_array($socle_pilier_id,$tab_langue_piliers) ) ? TRUE : FALSE ;
  // On commence par remplir tout le tableau pour ne pas avoir ensuite à tester si le champ existe
  foreach($tab_eleve_id as $eleve_id)
  {
    $tab_user_validation[$eleve_id] = array('etat'=>2,'date'=>'','info'=>'');
  }
  // Maintenant on complète avec les valeurs de la base
  if($is_socle_item_validation)
  {
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_entree($liste_eleve,$socle_item_id,$domaine_id=0,$pilier_id=0,$palier_id=0);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_user_validation[$DB_ROW['user_id']] = array('etat'=>$DB_ROW['validation_entree_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_entree_date']),'info'=>$DB_ROW['validation_entree_info']);
    }
  }
  elseif($is_socle_pilier_validation)
  {
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_pilier($liste_eleve,$socle_pilier_id,$palier_id=0);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_user_validation[$DB_ROW['user_id']] = array('etat'=>$DB_ROW['validation_pilier_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_pilier_date']),'info'=>$DB_ROW['validation_pilier_info']);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
// ////////////////////////////////////////////////////////////////////////////////////////////////////
DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement des données => remplissage du tableau $tab_tr[]
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// =====> Cas n°1 : moyenne des scores d'acquisition d'items matières sélectionnés
// =====> Cas n°2 : pourcentage d'items acquis d'items matières sélectionnés

if( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA )
{
  $tab_eleve_moy_scores  = array();
  $tab_eleve_pourcentage = array();
  $tab_init = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0,'%'=>FALSE);
  // Pour chaque élève...
  foreach($tab_eleve_id as $eleve_id)
  {
    $tab_eleve_moy_scores[$eleve_id]  = FALSE;
    $tab_eleve_pourcentage[$eleve_id] = $tab_init;
    // Si cet élève a été évalué...
    if(isset($tab_eval[$eleve_id]))
    {
      // Pour chaque item...
      $tab_score_item = array();
      foreach($tab_eval[$eleve_id] as $item_id => $tab_devoirs)
      {
        extract($tab_item[$item_id]);  // $item_coef $calcul_methode $calcul_limite
        // calcul du bilan de l'item
        $tab_score_item[$item_id] = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
      }
      // calcul des bilans des scores
      $tableau_score_filtre = array_filter($tab_score_item,'non_nul');
      $nb_scores = count( $tableau_score_filtre );
      // la moyenne peut être pondérée par des coefficients
      $somme_scores_ponderes = 0;
      $somme_coefs = 0;
      if($nb_scores)
      {
        foreach($tableau_score_filtre as $item_id => $item_score)
        {
          $somme_scores_ponderes += $item_score*$tab_item[$item_id]['item_coef'];
          $somme_coefs += $tab_item[$item_id]['item_coef'];
        }
        $somme_scores_simples = array_sum($tableau_score_filtre);
      }
      // ... un pour la moyenne des pourcentages d'acquisition
      if($with_coef) { $tab_eleve_moy_scores[$eleve_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) : FALSE ; }
      else           { $tab_eleve_moy_scores[$eleve_id] = ($nb_scores)   ? round($somme_scores_simples/$nb_scores,0)    : FALSE ; }
      // ... un pour le nombre d\'items considérés acquis ou pas
      if($nb_scores)
      {
        $tab_eleve_pourcentage[$eleve_id]['nb'] = $nb_scores;
        $tab_eleve_pourcentage[$eleve_id]['A']  = count( array_filter($tableau_score_filtre,'test_A') );
        $tab_eleve_pourcentage[$eleve_id]['NA'] = count( array_filter($tableau_score_filtre,'test_NA') );
        $tab_eleve_pourcentage[$eleve_id]['VA'] = $nb_scores - $tab_eleve_pourcentage[$eleve_id]['A'] - $tab_eleve_pourcentage[$eleve_id]['NA'];
        $tab_eleve_pourcentage[$eleve_id]['%']  = round( 50 * ( ($tab_eleve_pourcentage[$eleve_id]['A']*2 + $tab_eleve_pourcentage[$eleve_id]['VA']) / $nb_scores ) ,0);
      }
    }
  }
  // On ne garde que les lignes qui satisfont au critère demandé
  $tab_tr = array();
  foreach($tab_eleve as $tab)
  {
    extract($tab);  // $user_id $user_nom $user_prenom $eleve_langue
    if($is_matiere_items_bilanMS)
    {
          if ($tab_eleve_moy_scores[$user_id]===FALSE)                        {$user_acquisition_etat = 'X';}
      elseif ($tab_eleve_moy_scores[$user_id]<$_SESSION['CALCUL_SEUIL']['R']) {$user_acquisition_etat = 'NA';}
      elseif ($tab_eleve_moy_scores[$user_id]>$_SESSION['CALCUL_SEUIL']['V']) {$user_acquisition_etat = 'A';}
      else                                                                    {$user_acquisition_etat = 'VA';}
      if( in_array( $user_acquisition_etat , $critere_tab_seuil_acquis ) )
      {
        $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
        $tab_tr[] = '<tr>'.$checkbox.'<td>'.html($user_nom.' '.$user_prenom).'</td>'.Html::td_score( $tab_eleve_moy_scores[$user_id] , 'score' /*methode_tri*/ , $pourcent='').'</tr>';
      }
    }
    elseif($is_matiere_items_bilanPA)
    {
          if ($tab_eleve_pourcentage[$user_id]['%']===FALSE)                        {$user_acquisition_etat = 'X';}
      elseif ($tab_eleve_pourcentage[$user_id]['%']<$_SESSION['CALCUL_SEUIL']['R']) {$user_acquisition_etat = 'NA';}
      elseif ($tab_eleve_pourcentage[$user_id]['%']>$_SESSION['CALCUL_SEUIL']['V']) {$user_acquisition_etat = 'A';}
      else                                                                          {$user_acquisition_etat = 'VA';}
      if( in_array( $user_acquisition_etat , $critere_tab_seuil_acquis ) )
      {
        $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
        $tab_tr[] = '<tr>'.$checkbox.'<td>'.html($user_nom.' '.$user_prenom).'</td>'.Html::td_pourcentage( 'td' , $tab_eleve_pourcentage[$user_id] , TRUE /*detail*/ , FALSE /*largeur*/ ).'</tr>';
      }
    }
  }
}

// =====> Cas n°3 : pourcentage d'items disciplinaires acquis d'un item du socle

if( $is_socle_item_pourcentage )
{
  // Tableaux et variables pour mémoriser les infos
  $tab_init_compet = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0); // et ensuite '%'=>
  $tab_score_socle_eleve = array();
  // Pour chaque élève...
  foreach($tab_eleve_id as $eleve_id)
  {
    $tab_score_socle_eleve[$eleve_id] = $tab_init_compet;
    // Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
    if(isset($tab_eval[$eleve_id]))
    {
      foreach($tab_eval[$eleve_id] as $item_id => $tab_devoirs)
      {
        extract($tab_item[$item_id]);  // $calcul_methode $calcul_limite
        // calcul du bilan de l'item
        $score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
        if($score!==FALSE)
        {
          // on détermine si elle est acquise ou pas
          $indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
          // on enregistre les infos
          $tab_score_socle_eleve[$eleve_id][$indice]++;
          $tab_score_socle_eleve[$eleve_id]['nb']++;
        }
      }
    }
    // On calcule les états d'acquisition à partir des A / VA / NA
    $tab_score_socle_eleve[$eleve_id]['%'] = ($tab_score_socle_eleve[$eleve_id]['nb']) ? round( 50 * ( ($tab_score_socle_eleve[$eleve_id]['A']*2 + $tab_score_socle_eleve[$eleve_id]['VA']) / $tab_score_socle_eleve[$eleve_id]['nb'] ) ,0) : FALSE ;
  }
  // On ne garde que les lignes qui satisfont au critère demandé
  $tab_tr = array();
  foreach($tab_eleve as $tab)
  {
    extract($tab);  // $user_id $user_nom $user_prenom $eleve_langue
        if ($tab_score_socle_eleve[$user_id]['%']===FALSE)                        {$user_acquisition_etat = 'X';}
    elseif ($tab_score_socle_eleve[$user_id]['%']<$_SESSION['CALCUL_SEUIL']['R']) {$user_acquisition_etat = 'NA';}
    elseif ($tab_score_socle_eleve[$user_id]['%']>$_SESSION['CALCUL_SEUIL']['V']) {$user_acquisition_etat = 'A';}
    else                                                                          {$user_acquisition_etat = 'VA';}
    if( in_array( $user_acquisition_etat , $critere_tab_seuil_acquis ) )
    {
      $drapeau_langue = $is_langue ? $eleve_langue : 0 ;
      $image_langue = ($drapeau_langue) ? '<img src="./_img/drapeau/'.$drapeau_langue.'.gif" alt="" title="'.$tab_langues[$drapeau_langue]['texte'].'" /> ' : '' ;
      $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
      $tab_tr[] = '<tr>'.$checkbox.'<td>'.$image_langue.html($user_nom.' '.$user_prenom).'</td>'.Html::td_pourcentage( 'td' , $tab_score_socle_eleve[$user_id] , TRUE /*detail*/ , FALSE /*largeur*/ ).'</tr>';
    }
  }
}

// =====> Cas n°4 : état de validation d'un item du socle
// =====> Cas n°5 : état de validation d'un pilier du socle

if( $is_socle_item_validation || $is_socle_pilier_validation )
{
  // On ne garde que les lignes qui satisfont au critère demandé
  $tab_tr = array();
  foreach($tab_eleve as $tab)
  {
    extract($tab);  // $user_id $user_nom $user_prenom $eleve_langue
    if( in_array( $tab_user_validation[$user_id]['etat'] , $critere_tab_seuil_valide ) )
    {
      $drapeau_langue = $is_langue ? $eleve_langue : 0 ;
      $image_langue = ($drapeau_langue) ? '<img src="./_img/drapeau/'.$drapeau_langue.'.gif" alt="" title="'.$tab_langues[$drapeau_langue]['texte'].'" /> ' : '' ;
      $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
      $tab_tr[] = '<tr>'.$checkbox.'<td>'.$image_langue.html($user_nom.' '.$user_prenom).'</td>'.Html::td_validation( 'td' , $tab_user_validation[$user_id] , TRUE /*detail*/ ).'</tr>';
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage des données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_resultats = count($tab_tr);
$checkbox = ($affichage_checkbox && $nb_resultats) ? '<td class="nu"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input id="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /> <input id="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></td>' : '' ;
$releve_html  = '<hr />';
$releve_html .= ($affichage_checkbox) ? '<form id="form_synthese" action="#" method="post">' : '' ;
$releve_html .= '<table class="bilan"><thead><tr>'.$checkbox.'<th>Élève</th><th>État</th></tr></thead><tbody>';
$releve_html .= ($nb_resultats) ? implode('',$tab_tr) : '<tr><td colspan="2">aucun résultat</td></tr>' ;
$releve_html .= '</tbody></table>';
$releve_html .= ($affichage_checkbox && $nb_resultats) ? '<p><label class="tab">Action <img alt="" src="./_img/bulle_aide.png" title="Cocher auparavant les cases adéquates." /> :</label><button type="button" class="ajouter" onclick="var form=document.getElementById(\'form_synthese\');form.action=\'./index.php?page=evaluation_gestion\';form.submit();">Préparer une évaluation.</button> <button type="button" class="ajouter" onclick="var form=document.getElementById(\'form_synthese\');form.action=\'./index.php?page=professeur_groupe_besoin\';form.submit();">Constituer un groupe de besoin.</button></p>' : '' ;
$releve_html .= ($affichage_checkbox) ? '</form>' : '' ;
exit($releve_html);

?>
