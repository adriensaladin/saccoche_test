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
if( ($_SESSION['SESAMATH_ID']==ID_DEMO) && (!in_array($_POST['f_action'],array('lister_evaluations','ordonner','indiquer_eleves_deja','saisir','voir','voir_repart','imprimer_cartouche'))) ) {exit('Action désactivée pour la démo...');}

$action         = (isset($_POST['f_action']))          ? Clean::texte($_POST['f_action'])                : '';
$type           = (isset($_POST['f_type']))            ? Clean::texte($_POST['f_type'])                  : '';
$aff_classe_txt = (isset($_POST['f_aff_classe']))      ? Clean::texte($_POST['f_aff_classe'])            : '';
$aff_classe_id  = (isset($_POST['f_aff_classe']))      ? Clean::entier(substr($_POST['f_aff_classe'],1)) : 0;
$aff_periode    = (isset($_POST['f_aff_periode']))     ? Clean::entier($_POST['f_aff_periode'])          : 0;
$date_debut     = (isset($_POST['f_date_debut']))      ? Clean::texte($_POST['f_date_debut'])            : '';
$date_fin       = (isset($_POST['f_date_fin']))        ? Clean::texte($_POST['f_date_fin'])              : '';
$ref            = (isset($_POST['f_ref']))             ? Clean::texte($_POST['f_ref'])                   : '';
$date           = (isset($_POST['f_date']))            ? Clean::texte($_POST['f_date'])                  : '';
$date_fr        = (isset($_POST['f_date_fr']))         ? Clean::texte($_POST['f_date_fr'])               : '';
$date_visible   = (isset($_POST['f_date_visible']))    ? Clean::texte($_POST['f_date_visible'])          : ''; // JJ/MM/AAAA
$date_autoeval  = (isset($_POST['f_date_autoeval']))   ? Clean::texte($_POST['f_date_autoeval'])         : ''; // JJ/MM/AAAA mais peut valoir 00/00/0000
$description    = (isset($_POST['f_description']))     ? Clean::texte($_POST['f_description'])           : '';
$doc_sujet      = (isset($_POST['f_doc_sujet']))       ? Clean::texte($_POST['f_doc_sujet'])             : ''; // Pas Clean::fichier() car transmis pour "dupliquer" (et "modifier") avec le chemin complet http://...
$doc_corrige    = (isset($_POST['f_doc_corrige']))     ? Clean::texte($_POST['f_doc_corrige'])           : ''; // Pas Clean::fichier() car transmis pour "dupliquer" (et "modifier") avec le chemin complet http://...
$groupe         = (isset($_POST['f_groupe']))          ? Clean::texte($_POST['f_groupe'])                : '';
$groupe_nom     = (isset($_POST['f_groupe_nom']))      ? Clean::texte($_POST['f_groupe_nom'])            : '';
$cart_contenu   = (isset($_POST['f_contenu']))         ? Clean::texte($_POST['f_contenu'])               : '';
$cart_detail    = (isset($_POST['f_detail']))          ? Clean::texte($_POST['f_detail'])                : '';
$orientation    = (isset($_POST['f_orientation']))     ? Clean::texte($_POST['f_orientation'])           : '';
$marge_min      = (isset($_POST['f_marge_min']))       ? Clean::texte($_POST['f_marge_min'])             : '';
$couleur        = (isset($_POST['f_couleur']))         ? Clean::texte($_POST['f_couleur'])               : '';
$only_req       = (isset($_POST['f_restriction_req'])) ? TRUE                                            : FALSE;
$doc_objet      = (isset($_POST['f_doc_objet']))       ? Clean::texte($_POST['f_doc_objet'])             : '';
$doc_url        = (isset($_POST['f_doc_url']))         ? Clean::texte($_POST['f_doc_url'])               : '';
$fini           = (isset($_POST['f_fini']))            ? Clean::texte($_POST['f_fini'])                  : '';

$chemin_devoir      =  CHEMIN_DOSSIER_DEVOIR.$_SESSION['BASE'].DS;
$url_dossier_devoir = URL_DIR_DEVOIR.$_SESSION['BASE'].'/';
$fnom_export = $_SESSION['BASE'].'_'.Clean::fichier($groupe_nom).'_'.Clean::fichier($description).'_'.fabriquer_fin_nom_fichier__date_et_alea();

// Si "ref" est renseigné (pour Éditer ou Retirer ou Saisir ou ...), il contient l'id de l'évaluation + '_' + l'initiale du type de groupe + l'id du groupe
// Dans le cas d'une duplication, "ref" sert à retrouver l'évaluation d'origine pour évenuellement récupérer l'ordre des items
if(mb_strpos($ref,'_'))
{
  list($devoir_id,$groupe_temp) = explode('_',$ref,2);
  $devoir_id = Clean::entier($devoir_id);
  // Si "groupe" est transmis en POST (pour Ajouter ou Éditer), il faut le prendre comme référence nouvelle ; sinon, on prend le groupe extrait de "ref"
  $groupe = ($groupe) ? $groupe : Clean::texte($groupe_temp) ;
}
else
{
  $devoir_id = 0;
}

// Si "groupe" est transmis via "ref", il contient l'initiale du type de groupe + l'id du groupe
if($groupe)
{
  $groupe_type_initiale = $groupe{0};
  $tab_groupe  = array('classe'=>'C','groupe'=>'G','besoin'=>'B','eval'=>'E');
  $groupe_type = array_search($groupe_type_initiale,$tab_groupe);
  $groupe_id   = Clean::entier(mb_substr($groupe,1));
}
else
{
  $groupe_type = 'eval';
  $groupe_id   = 0;
}

// Contrôler la liste des items transmis
$tab_id     = (isset($_POST['tab_id'])) ? explode(',',$_POST['tab_id']) : array() ;
$tab_id     = Clean::map_entier($tab_id);
$tab_id     = array_filter($tab_id,'positif');
// Contrôler la liste des items transmis
$tab_items  = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_items  = Clean::map_entier($tab_items);
$tab_items  = array_filter($tab_items,'positif');
$nb_items   = count($tab_items);
// Contrôler la liste des élèves transmis (sur des élèves sélectionnés uniquement)
$tab_eleves = (isset($_POST['f_eleve_liste']))  ? explode('_',$_POST['f_eleve_liste'])  : array() ;
$tab_eleves = Clean::map_entier($tab_eleves);
$tab_eleves = array_filter($tab_eleves,'positif');
$nb_eleves  = count($tab_eleves);
// Contrôler la liste des profs transmis
$tab_profs  = (isset($_POST['f_prof_liste'])) ? explode('_',$_POST['f_prof_liste']) : array() ;
$tab_profs  = Clean::map_entier($tab_profs);
$tab_profs  = array_filter($tab_profs,'positif');
// Liste des notes transmises
$tab_notes  = (isset($_POST['f_notes'])) ? explode(',',$_POST['f_notes']) : array() ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une liste d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='lister_evaluations') && $type && ( ($type=='selection') || ($aff_classe_txt && $aff_classe_id) ) && ( $aff_periode || ($date_debut && $date_fin) ) )
{
  // Restreindre la recherche à une période donnée, cas d'une date personnalisée (toujours le cas pour une sélection d'élèves)
  if($aff_periode==0)
  {
    // Formater les dates
    $date_debut_mysql = convert_date_french_to_mysql($date_debut);
    $date_fin_mysql   = convert_date_french_to_mysql($date_fin);
    // Vérifier que la date de début est antérieure à la date de fin
    if($date_debut_mysql>$date_fin_mysql)
    {
      exit('Erreur : la date de début est postérieure à la date de fin !');
    }
  }
  // Restreindre la recherche à une période donnée, cas d'une période associée à une classe ou à un groupe
  else
  {
    $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($aff_classe_id,$aff_periode);
    if(empty($DB_ROW))
    {
      exit('Erreur : cette classe et cette période ne sont pas reliées !');
    }
    // Formater les dates
    $date_debut_mysql = $DB_ROW['jointure_date_debut'];
    $date_fin_mysql   = $DB_ROW['jointure_date_fin'];
  }
  // Lister les évaluations
  $script = '';
  $classe_id = ($aff_classe_txt!='d2') ? $aff_classe_id : -1 ; // 'd2' est transmis si on veut toutes les classes / tous les groupes ; classe_id vaut 0 si selection d'élèves
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_devoirs_prof($_SESSION['USER_ID'],$classe_id,$date_debut_mysql,$date_fin_mysql);
  if(!empty($DB_TAB))
  {
    // Récupérer le nb de saisies déjà effectuées par évaluation (ça posait trop de problème dans la requête précédente : saisies comptées plusieurs fois, évaluations sans saisies non retournées...)
    $tab_devoir_id = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_devoir_id[$DB_ROW['devoir_id']] = $DB_ROW['devoir_id'];
    }
    $tab_nb_saisies_effectuees = array_fill_keys($tab_devoir_id,0);
    $DB_TAB2 = DB_STRUCTURE_PROFESSEUR::DB_lister_nb_saisies_par_evaluation( implode(',',$tab_devoir_id) );
    foreach($DB_TAB2 as $DB_ROW)
    {
      $tab_nb_saisies_effectuees[$DB_ROW['devoir_id']] = $DB_ROW['saisies_nombre'];
    }
    // Récupérer les effectifs des classes et groupes
    $tab_effectifs = array();
    if($type=='groupe')
    {
      $DB_TAB2 = DB_STRUCTURE_PROFESSEUR::DB_lister_effectifs_groupes();
      foreach($DB_TAB2 as $DB_ROW)
      {
        $tab_effectifs[$DB_ROW['groupe_id']] = $DB_ROW['eleves_nombre'];
      }
    }
    foreach($DB_TAB as $DB_ROW)
    {
      $date_affich   = convert_date_mysql_to_french($DB_ROW['devoir_date']);
      $date_visible  = ($DB_ROW['devoir_date']==$DB_ROW['devoir_visible_date']) ? 'identique'  : convert_date_mysql_to_french($DB_ROW['devoir_visible_date']) ;
      $date_autoeval = ($DB_ROW['devoir_autoeval_date']===NULL)                 ? 'sans objet' : convert_date_mysql_to_french($DB_ROW['devoir_autoeval_date']) ;
      $ref = $DB_ROW['devoir_id'].'_'.strtoupper($DB_ROW['groupe_type']{0}).$DB_ROW['groupe_id'];
      $cs = ($DB_ROW['items_nombre']>1) ? 's' : '';
      $us = ($type=='groupe') ? '' : ( ($DB_ROW['users_nombre']>1) ? 's' : '' );
      if(!$DB_ROW['devoir_partage'])
      {
        $profs_liste = '';
        $profs_nombre = 'moi seul';
      }
      else
      {
        $profs_liste  = str_replace(',','_',mb_substr($DB_ROW['devoir_partage'],1,-1));
        $profs_nombre = (mb_substr_count($DB_ROW['devoir_partage'],',')-1).' collègues';
      }
      $proprio = ($DB_ROW['prof_id']==$_SESSION['USER_ID']) ? TRUE : FALSE ;
      $image_sujet   = ($DB_ROW['devoir_doc_sujet'])   ? '<a href="'.$DB_ROW['devoir_doc_sujet'].'" target="_blank"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
      $image_corrige = ($DB_ROW['devoir_doc_corrige']) ? '<a href="'.$DB_ROW['devoir_doc_corrige'].'" target="_blank"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
      $effectif_eleve = ($type=='groupe') ? $tab_effectifs[$DB_ROW['groupe_id']] : $DB_ROW['users_nombre'] ;
      $nb_saisies_possibles = $DB_ROW['items_nombre']*$effectif_eleve;
      $remplissage_nombre   = $tab_nb_saisies_effectuees[$DB_ROW['devoir_id']].'/'.$nb_saisies_possibles ;
      $remplissage_class    = (!$tab_nb_saisies_effectuees[$DB_ROW['devoir_id']]) ? 'br' : ( ($tab_nb_saisies_effectuees[$DB_ROW['devoir_id']]<$nb_saisies_possibles) ? 'bj' : 'bv' ) ;
      $remplissage_class2   = ($DB_ROW['devoir_fini']) ? ' bf' : '' ;
      $remplissage_contenu  = ($DB_ROW['devoir_fini']) ? '<span>terminé</span><i>'.$remplissage_nombre.'</i>' : '<span>'.$remplissage_nombre.'</span><i>terminé</i>' ;
      $remplissage_lien1    = (!$proprio) ? '' : '<a href="#fini" class="fini" title="Cliquer pour indiquer (ou pas) qu\'il n\'y a plus de saisies à effectuer.">' ;
      $remplissage_lien2    = (!$proprio) ? '' : '</a>' ;
      $remplissage_td_title = ( $proprio) ? '' : ' title="Non cliquable (évaluation du collègue '.html($DB_ROW['proprietaire']).')."' ;
      // Afficher une ligne du tableau
      echo'<tr>';
      echo  '<td>'.$date_affich.'</td>';
      echo  '<td>'.$date_visible.'</td>';
      echo  '<td>'.$date_autoeval.'</td>';
      echo  ($type=='groupe') ? '<td>'.html($DB_ROW['groupe_nom']).'</td>' : '<td>'.$DB_ROW['users_nombre'].' élève'.$us.'</td>' ;
      echo  '<td>'.$profs_nombre.'</td>';
      echo  '<td>'.html($DB_ROW['devoir_info']).'</td>';
      echo  '<td>'.$DB_ROW['items_nombre'].' item'.$cs.'</td>';
      echo  '<td>'.$image_sujet.$image_corrige;
      echo  ($proprio) ? '<q class="uploader_doc" title="Ajouter / retirer un sujet ou une correction."></q>' : '<q class="uploader_doc_non" title="Non modifiable (évaluation du collègue '.html($DB_ROW['proprietaire']).')."></q>' ;
      echo  '</td>';
      echo  '<td class="'.$remplissage_class.$remplissage_class2.'"'.$remplissage_td_title.'>'.$remplissage_lien1.$remplissage_contenu.$remplissage_lien2.'</td>';
      echo  '<td class="nu" id="devoir_'.$ref.'">';
      echo    ($proprio) ? '<q class="modifier" title="Modifier cette évaluation (date, description, ...)."></q>' : '<q class="modifier_non" title="Non modifiable (évaluation du collègue '.html($DB_ROW['proprietaire']).')."></q>' ;
      echo    ($proprio) ? '<q class="ordonner" title="Réordonner les items de cette évaluation."></q>' : '<q class="ordonner_non" title="Non réordonnable (évaluation du collègue '.html($DB_ROW['proprietaire']).')."></q>' ;
      echo    '<q class="dupliquer" title="Dupliquer cette évaluation."></q>';
      echo    ($proprio) ? '<q class="supprimer" title="Supprimer cette évaluation."></q>' : '<q class="supprimer_non" title="Non supprimable (évaluation du collègue '.html($DB_ROW['proprietaire']).')."></q>' ;
      echo    '<q class="imprimer" title="Imprimer un cartouche pour cette évaluation."></q>';
      echo    '<q class="saisir" title="Saisir les acquisitions des élèves à cette évaluation."></q>';
      echo    '<q class="voir" title="Voir les acquisitions des élèves à cette évaluation."></q>';
      echo    '<q class="voir_repart" title="Voir les répartitions des élèves à cette évaluation."></q>';
      echo  '</td>';
      echo'</tr>';
      $script .= 'tab_items["'.$ref.'"]="'.$DB_ROW['items_listing'].'";';
      $script .= 'tab_profs["'.$ref.'"]="'.$profs_liste.'";';
      $script .= ($type=='selection') ? 'tab_eleves["'.$ref.'"]="'.$DB_ROW['users_listing'].'";' : '' ;
      $script .= 'tab_sujets["'.$ref.'"]="'.$DB_ROW['devoir_doc_sujet'].'";';
      $script .= 'tab_corriges["'.$ref.'"]="'.$DB_ROW['devoir_doc_corrige'].'";';
    }
  }
  echo'<SCRIPT>'.$script;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='ajouter')||(($action=='dupliquer')&&($devoir_id))) && $type && $date && $date_visible && $date_autoeval && ( ($groupe_type && $groupe_id) || $nb_eleves ) && $nb_items )
{
  $date_mysql          = convert_date_french_to_mysql($date);
  $date_visible_mysql  = convert_date_french_to_mysql($date_visible);
  $date_autoeval_mysql = convert_date_french_to_mysql($date_autoeval);
  // Tester les dates
  $date_stamp          = strtotime($date_mysql);
  $date_visible_stamp  = strtotime($date_visible_mysql);
  $date_autoeval_stamp = strtotime($date_autoeval_mysql);
  $mini_stamp          = strtotime("-3 month");
  $maxi_stamp          = strtotime("+3 month");
  $maxi_visible_stamp  = strtotime("+10 month");
  if( ($date_stamp<$mini_stamp) || ($date_stamp>$maxi_stamp) )
  {
    exit('Date devoir trop éloignée !');
  }
  if( ($date_visible_stamp<$mini_stamp) || ($date_visible_stamp>$maxi_visible_stamp) )
  {
    exit('Date visible trop éloignée !');
  }
  if( ($date_autoeval!='00/00/0000') && ( ($date_autoeval_stamp<$mini_stamp) || ($date_autoeval_stamp>$maxi_visible_stamp) ) )
  {
    exit('Date fin auto-éval. trop éloignée !');
  }
  if( ($date_autoeval!='00/00/0000') && ($date_autoeval_mysql<$date_visible_mysql) )
  {
    exit('Date fin auto-éval. avant date visible !');
  }
  // Tester les profs, mais plus leur appartenance au groupe (pour qu'un prof puisse accéder à l'éval même s'il n'a pas le groupe, même si on duplique une évaluation pour un autre groupe...) [absurde dans le cas d'élèves sélectionnés]
  if(count($tab_profs))
  {
    if(!in_array($_SESSION['USER_ID'],$tab_profs))
    {
      exit('Erreur : absent de la liste des professeurs !');
    }
  }
  if($type=='selection')
  {
    // Commencer par créer un nouveau groupe de type "eval", utilisé uniquement pour cette évaluation (c'est transparent pour le professeur) ; y associe automatiquement le prof, en responsable du groupe
    $groupe_id = DB_STRUCTURE_PROFESSEUR::DB_ajouter_groupe_par_prof($groupe_type,'',0);
  }
  // Insèrer l'enregistrement de l'évaluation
  $devoir_id2 = DB_STRUCTURE_PROFESSEUR::DB_ajouter_devoir($_SESSION['USER_ID'],$groupe_id,$date_mysql,$description,$date_visible_mysql,$date_autoeval_mysql,$doc_sujet,$doc_corrige,$tab_profs);
  if($type=='selection')
  {
    // Affecter tous les élèves choisis
    DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_user($devoir_id2,$groupe_id,$tab_eleves,'creer');
  }
  // Insérer les enregistrements des items de l'évaluation
  DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_item($devoir_id2,$tab_items,'dupliquer',$devoir_id);
  // Récupérer l'effectif de la classe ou du groupe
  $effectif_eleve = ($type=='groupe') ? DB_STRUCTURE_PROFESSEUR::DB_lister_effectifs_groupes($groupe_id) : $nb_eleves ;
  // Afficher le retour
  $date_visible  = ($date_visible==$date)         ? 'identique'  : $date_visible  ;
  $date_autoeval = ($date_autoeval=='00/00/0000') ? 'sans objet' : $date_autoeval ;
  $ref = $devoir_id2.'_'.strtoupper($groupe_type{0}).$groupe_id;
  $cs = ($nb_items>1) ? 's' : '';
  $us = ($nb_eleves>1)      ? 's' : '';
  $profs_nombre = count($tab_profs) ? count($tab_profs).' collègues' : 'moi seul' ;
  $image_sujet   = ($doc_sujet)   ? '<a href="'.$doc_sujet.'" target="_blank"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
  $image_corrige = ($doc_corrige) ? '<a href="'.$doc_corrige.'" target="_blank"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
  $nb_saisies_possibles = $nb_items*$effectif_eleve;
  $remplissage_nombre   = '0/'.$nb_saisies_possibles ;
  $remplissage_class    = 'br';
  $remplissage_class2   = '' ;
  $remplissage_contenu  = '<span>'.$remplissage_nombre.'</span><i>terminé</i>';
  $remplissage_lien1    = '<a href="#fini" class="fini" title="Cliquer pour indiquer (ou pas) qu\'il n\'y a plus de saisies à effectuer.">';
  $remplissage_lien2    = '</a>';
  echo'<td>'.$date.'</td>';
  echo'<td>'.$date_visible.'</td>';
  echo'<td>'.$date_autoeval.'</td>';
  echo ($type=='groupe') ? '<td>{{GROUPE_NOM}}</td>' : '<td>'.$nb_eleves.' élève'.$us.'</td>' ;
  echo'<td>'.$profs_nombre.'</td>';
  echo'<td>'.html($description).'</td>';
  echo'<td>'.$nb_items.' item'.$cs.'</td>';
  echo'<td>'.$image_sujet.$image_corrige.'<q class="uploader_doc" title="Ajouter / retirer un sujet ou une correction."></q></td>';
  echo'<td class="'.$remplissage_class.$remplissage_class2.'">'.$remplissage_lien1.$remplissage_contenu.$remplissage_lien2.'</td>';
  echo'<td class="nu" id="devoir_'.$ref.'">';
  echo  '<q class="modifier" title="Modifier cette évaluation (date, description, ...)."></q>';
  echo  '<q class="ordonner" title="Réordonner les items de cette évaluation."></q>';
  echo  '<q class="dupliquer" title="Dupliquer cette évaluation."></q>';
  echo  '<q class="supprimer" title="Supprimer cette évaluation."></q>';
  echo  '<q class="imprimer" title="Imprimer un cartouche pour cette évaluation."></q>';
  echo  '<q class="saisir" title="Saisir les acquisitions des élèves à cette évaluation."></q>';
  echo  '<q class="voir" title="Voir les acquisitions des élèves à cette évaluation."></q>';
  echo  '<q class="voir_repart" title="Voir les répartitions des élèves à cette évaluation."></q>';
  echo'</td>';
  echo'<SCRIPT>';
  echo'tab_items["'.$ref.'"]="'.implode('_',$tab_items).'";';
  echo'tab_profs["'.$ref.'"]="'.implode('_',$tab_profs).'";';
  echo ($type=='selection') ? 'tab_eleves["'.$ref.'"]="'.implode('_',$tab_eleves).'";' : '' ;
  echo'tab_sujets["'.$ref.'"]="'.$doc_sujet.'";';
  echo'tab_corriges["'.$ref.'"]="'.$doc_corrige.'";';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une évaluation existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $devoir_id && $groupe_id && $date && $date_visible && $date_autoeval && ( ($type=='groupe') || $nb_eleves ) && $nb_items && in_array($fini,array('oui','non')) )
{
  $date_mysql          = convert_date_french_to_mysql($date);
  $date_visible_mysql  = convert_date_french_to_mysql($date_visible);
  $date_autoeval_mysql = convert_date_french_to_mysql($date_autoeval);
  // Tester les dates
  $date_stamp          = strtotime($date_mysql);
  $date_visible_stamp  = strtotime($date_visible_mysql);
  $date_autoeval_stamp = strtotime($date_autoeval_mysql);
  $mini_stamp          = strtotime("-10 month");
  $maxi_stamp          = strtotime("+10 month");
  if( ($date_stamp<$mini_stamp) || ($date_stamp>$maxi_stamp) )
  {
    exit('Date devoir trop éloignée !');
  }
  if( ($date_visible_stamp<$mini_stamp) || ($date_visible_stamp>$maxi_stamp) )
  {
    exit('Date visible trop éloignée !');
  }
  if( ($date_autoeval!='00/00/0000') && ( ($date_autoeval_stamp<$mini_stamp) || ($date_autoeval_stamp>$maxi_stamp) ) )
  {
    exit('Date fin auto-éval. trop éloignée !');
  }
  if( ($date_autoeval!='00/00/0000') && ($date_autoeval_mysql<$date_visible_mysql) )
  {
    exit('Date fin auto-éval. avant date visible !');
  }
  // Tester les profs, mais plus leur appartenance au groupe (pour qu'un prof puisse accéder à l'éval même s'il n'a pas le groupe, même si on duplique une évaluation pour un autre groupe...) [absurde dans le cas d'élèves sélectionnés]
  if(count($tab_profs))
  {
    if(!in_array($_SESSION['USER_ID'],$tab_profs))
    {
      exit('Erreur : absent de la liste des professeurs !');
    }
  }
  // sacoche_devoir (maj des paramètres date & info)
  DB_STRUCTURE_PROFESSEUR::DB_modifier_devoir($devoir_id,$_SESSION['USER_ID'],$date_mysql,$description,$date_visible_mysql,$date_autoeval_mysql,$tab_profs);
  if($type=='selection')
  {
    // sacoche_jointure_user_groupe + sacoche_saisie pour les users supprimés
    DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_user($devoir_id,$groupe_id,$tab_eleves,'substituer');
  }
  elseif($type=='groupe')
  {
    // sacoche_devoir (maj groupe_id) + sacoche_saisie pour TOUS les users !
    DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_groupe($devoir_id,$groupe_id);
  }
  // sacoche_jointure_devoir_item + sacoche_saisie pour les items supprimés
  DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_item($devoir_id,$tab_items,'substituer');
  // Récupérer le nb de saisies déjà effectuées pour l'évaluation
  $nb_saisies_effectuees = DB_STRUCTURE_PROFESSEUR::DB_lister_nb_saisies_par_evaluation($devoir_id);
  // Récupérer l'effectif de la classe ou du groupe
  $effectif_eleve = ($type=='groupe') ? DB_STRUCTURE_PROFESSEUR::DB_lister_effectifs_groupes($groupe_id) : $nb_eleves ;
  // Afficher le retour
  $date_visible  = ($date==$date_visible)         ? 'identique'  : $date_visible  ;
  $date_autoeval = ($date_autoeval=='00/00/0000') ? 'sans objet' : $date_autoeval ;
  $ref = $devoir_id.'_'.strtoupper($groupe_type{0}).$groupe_id;
  $cs = ($nb_items>1)  ? 's' : '';
  $us = ($nb_eleves>1) ? 's' : '';
  $profs_nombre = count($tab_profs) ? count($tab_profs).' collègues' : 'moi seul' ;
  $image_sujet   = ($doc_sujet)   ? '<a href="'.$doc_sujet.'" target="_blank"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
  $image_corrige = ($doc_corrige) ? '<a href="'.$doc_corrige.'" target="_blank"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
  $nb_saisies_possibles = $nb_items*$effectif_eleve;
  $remplissage_nombre   = $nb_saisies_effectuees.'/'.$nb_saisies_possibles ;
  $remplissage_class    = (!$nb_saisies_effectuees) ? 'br' : ( ($nb_saisies_effectuees<$nb_saisies_possibles) ? 'bj' : 'bv' ) ;
  $remplissage_class2   = ($fini=='oui') ? ' bf' : '' ;
  $remplissage_contenu  = ($fini=='oui') ? '<span>terminé</span><i>'.$remplissage_nombre.'</i>' : '<span>'.$remplissage_nombre.'</span><i>terminé</i>' ;
  $remplissage_lien1    = '<a href="#fini" class="fini" title="Cliquer pour indiquer (ou pas) qu\'il n\'y a plus de saisies à effectuer.">';
  $remplissage_lien2    = '</a>';
  echo'<td>'.$date.'</td>';
  echo'<td>'.$date_visible.'</td>';
  echo'<td>'.$date_autoeval.'</td>';
  echo ($type=='groupe') ? '<td>{{GROUPE_NOM}}</td>' : '<td>'.$nb_eleves.' élève'.$us.'</td>' ;
  echo'<td>'.$profs_nombre.'</td>';
  echo'<td>'.html($description).'</td>';
  echo'<td>'.$nb_items.' item'.$cs.'</td>';
  echo'<td>'.$image_sujet.$image_corrige.'<q class="uploader_doc" title="Ajouter / retirer un sujet ou une correction."></q></td>';
  echo  '<td class="'.$remplissage_class.$remplissage_class2.'">'.$remplissage_lien1.$remplissage_contenu.$remplissage_lien2.'</td>';
  echo'<td class="nu" id="devoir_'.$ref.'">';
  echo  '<q class="modifier" title="Modifier cette évaluation (date, description, ...)."></q>';
  echo  '<q class="ordonner" title="Réordonner les items de cette évaluation."></q>';
  echo  '<q class="dupliquer" title="Dupliquer cette évaluation."></q>';
  echo  '<q class="supprimer" title="Supprimer cette évaluation."></q>';
  echo  '<q class="imprimer" title="Imprimer un cartouche pour cette évaluation."></q>';
  echo  '<q class="saisir" title="Saisir les acquisitions des élèves à cette évaluation."></q>';
  echo  '<q class="voir" title="Voir les acquisitions des élèves à cette évaluation."></q>';
  echo  '<q class="voir_repart" title="Voir les répartitions des élèves à cette évaluation."></q>';
  echo'</td>';
  echo'<SCRIPT>';
  echo'tab_items["'.$ref.'"]="'.implode('_',$tab_items).'";';
  echo'tab_profs["'.$ref.'"]="'.implode('_',$tab_profs).'";';
  echo ($type=='selection') ? 'tab_eleves["'.$ref.'"]="'.implode('_',$tab_eleves).'";' : '' ;
  echo'tab_sujets["'.$ref.'"]="'.$doc_sujet.'";';
  echo'tab_corriges["'.$ref.'"]="'.$doc_corrige.'";';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une évaluation existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $devoir_id && ( ($type=='groupe') || $groupe_id ) )
{
  if($type=='selection')
  {
    // supprimer le groupe spécialement associé (invisible à l'utilisateur) et les entrées dans sacoche_jointure_user_groupe pour une évaluation avec des élèves piochés en dehors de tout groupe prédéfini
    DB_STRUCTURE_PROFESSEUR::DB_supprimer_groupe_par_prof( $groupe_id , $groupe_type , FALSE /*with_devoir*/ );
    SACocheLog::ajouter('Suppression d\'un regroupement ('.$groupe_type.' '.$groupe_id.'), sans les devoirs associés.');
  }
  // on supprime l'évaluation avec ses saisies
  DB_STRUCTURE_PROFESSEUR::DB_supprimer_devoir_et_saisies($devoir_id,$_SESSION['USER_ID']);
  SACocheLog::ajouter('Suppression d\'un devoir ('.$devoir_id.') avec les saisies associées.');
  // Afficher le retour
  exit('<td>ok</td>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le formulaire pour réordonner les items d'une évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ordonner') && $devoir_id )
{
  // liste des items
  $DB_TAB_COMP = DB_STRUCTURE_PROFESSEUR::DB_lister_items_devoir( $devoir_id , FALSE /*with_lien*/ , TRUE /*with_coef*/ );
  if(empty($DB_TAB_COMP))
  {
    exit('Aucun item n\'est associé à cette évaluation !');
  }
  foreach($DB_TAB_COMP as $DB_ROW)
  {
    $item_ref = $DB_ROW['item_ref'];
    $texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
    $texte_coef  = ' ['.$DB_ROW['item_coef'].']';
    echo'<li id="i'.$DB_ROW['item_id'].'"><b>'.html($item_ref.$texte_socle.$texte_coef).'</b> - '.html($DB_ROW['item_nom']).'</li>';
  }
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Indiquer la liste des élèves associés à une évaluation de même nom( uniquement pour une sélection d'élèves)
// Reprise d'un développement initié par Alain Pottier <alain.pottier613@orange.fr>
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='indiquer_eleves_deja') && $description && $date_debut )
{
  $date_debut_mysql = convert_date_french_to_mysql($date_debut);
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_devoirs($_SESSION['USER_ID'],$description,$date_debut_mysql);
  $tab_retour = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_retour[] = $DB_ROW['user_id'].'_'.convert_date_mysql_to_french($DB_ROW['devoir_date']);
  }
  exit( 'ok,'.implode(',',$tab_retour) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le formulaire pour saisir les items acquis par les élèves à une évaluation
// Générer en même temps un csv à récupérer pour une saisie déportée
// Générer en même temps un pdf contenant un tableau de saisie vide
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='saisir') && $devoir_id && $groupe_id && $date_fr ) // $description et $groupe_nom sont aussi transmis
{
  // liste des items
  $DB_TAB_COMP = DB_STRUCTURE_PROFESSEUR::DB_lister_items_devoir( $devoir_id , FALSE /*with_lien*/ , TRUE /*with_coef*/ );
  // liste des élèves
  $DB_TAB_USER = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $groupe_type , $groupe_id );
  // Let's go
  $item_nb = count($DB_TAB_COMP);
  if(!$item_nb)
  {
    exit('Aucun item n\'est associé à cette évaluation !');
  }
  $eleve_nb = count($DB_TAB_USER);
  if(!$eleve_nb)
  {
    exit('Aucun élève n\'est associé à cette évaluation !');
  }
  $separateur = ';';
  $tab_affich  = array(); // tableau bi-dimensionnel [n°ligne=id_item][n°colonne=id_user]
  $tab_user_id = array(); // pas indispensable, mais plus lisible
  $tab_comp_id = array(); // pas indispensable, mais plus lisible
  $tab_affich[0][0] = '<td>';
  $tab_affich[0][0].= '<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_saisie_resultats">DOC : Saisie des résultats.</a></span>';
  $tab_affich[0][0].= '<p>';
  $tab_affich[0][0].= '<label for="radio_clavier"><input type="radio" id="radio_clavier" name="mode_saisie" value="clavier" /> <span class="pilot_keyboard">Piloter au clavier</span></label> <img alt="" src="./_img/bulle_aide.png" title="Sélectionner un rectangle blanc<br />au clavier (flèches) ou à la souris<br />puis utiliser les touches suivantes :<br />&nbsp;1 ; 2 ; 3 ; 4 ; A ; D ; N ; P ; suppr .<br />Pour un report multiple, presser avant<br />C (Colonne), L (Ligne) ou T (Tableau)." /><br />';
  $tab_affich[0][0].= '<span id="arrow_continue"><label for="arrow_continue_down"><input type="radio" id="arrow_continue_down" name="arrow_continue" value="down" /> <span class="arrow_continue_down">par élève</span></label>&nbsp;&nbsp;&nbsp;<label for="arrow_continue_rigth"><input type="radio" id="arrow_continue_rigth" name="arrow_continue" value="rigth" /> <span class="arrow_continue_rigth">par item</span></label></span><br />';
  $tab_affich[0][0].= '<label for="radio_souris"><input type="radio" id="radio_souris" name="mode_saisie" value="souris" /> <span class="pilot_mouse">Piloter à la souris</span></label> <img alt="" src="./_img/bulle_aide.png" title="Survoler une case du tableau avec la souris<br />puis cliquer sur une des images proposées." />';
  $tab_affich[0][0].= '</p><p>';
  $tab_affich[0][0].= '<label for="check_largeur"><input type="checkbox" id="check_largeur" name="check_largeur" value="retrecir_largeur" /> <span class="retrecir_largeur">Largeur optimale</span></label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la largeur des colonnes<br />si les élèves sont nombreux." /><br />';
  $tab_affich[0][0].= '<label for="check_hauteur"><input type="checkbox" id="check_hauteur" name="check_hauteur" value="retrecir_hauteur" /> <span class="retrecir_hauteur">Hauteur optimale</span></label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la hauteur des lignes<br />si les items sont nombreux." />';
  $tab_affich[0][0].= '</p>';
  $tab_affich[0][0].= '</td>';
  // première ligne (noms prénoms des élèves)
  $csv_ligne_eleve_nom = $separateur;
  $csv_ligne_eleve_id  = $separateur;
  $csv_nb_colonnes = 1;
  foreach($DB_TAB_USER as $DB_ROW)
  {
    $tab_affich[0][$DB_ROW['user_id']] = '<th><img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'&amp;br" /></th>';
    $tab_user_id[$DB_ROW['user_id']] = html($DB_ROW['user_prenom'].' '.$DB_ROW['user_nom']);
    $csv_ligne_eleve_nom .= '"'.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'"'.$separateur;
    $csv_ligne_eleve_id  .= $DB_ROW['user_id'].$separateur;
    $csv_nb_colonnes++;
  }
  $export_csv = $csv_ligne_eleve_id."\r\n";
  // première colonne (noms items)
  foreach($DB_TAB_COMP as $DB_ROW)
  {
    $item_ref = $DB_ROW['item_ref'];
    $texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
    $texte_coef  = ' ['.$DB_ROW['item_coef'].']';
    $tab_affich[$DB_ROW['item_id']][0] = '<th><b>'.html($item_ref.$texte_socle.$texte_coef).'</b> <img alt="" src="./_img/bulle_aide.png" title="'.html(html($DB_ROW['item_nom'])).'" /><div>'.html($DB_ROW['item_nom']).'</div></th>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    $tab_comp_id[$DB_ROW['item_id']] = $item_ref;
    $export_csv .= $DB_ROW['item_id'].str_repeat($separateur,$csv_nb_colonnes).$item_ref.$texte_socle.$texte_coef.' '.$DB_ROW['item_nom']."\r\n";
  }
  $export_csv .= $csv_ligne_eleve_nom."\r\n\r\n";
  // cases centrales avec un champ input de base
  $num_colonne = 0;
  foreach($tab_user_id as $user_id=>$val_user)
  {
    $num_colonne++;
    $num_ligne=0;
    foreach($tab_comp_id as $comp_id=>$val_comp)
    {
      $num_ligne++;
      $tab_affich[$comp_id][$user_id] = '<td class="td_clavier" id="td_C'.$num_colonne.'L'.$num_ligne.'"><input type="text" class="X" value="X" id="C'.$num_colonne.'L'.$num_ligne.'" name="'.$comp_id.'x'.$user_id.'" readonly /></td>';
    }
  }
  // configurer le champ input
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_saisies_devoir( $devoir_id , TRUE /*with_REQ*/ );
  $bad = 'class="X" value="X"';
  foreach($DB_TAB as $DB_ROW)
  {
    // Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
    if(isset($tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]))
    {
      $bon = 'class="'.$DB_ROW['saisie_note'].'" value="'.$DB_ROW['saisie_note'].'"';
      $tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = str_replace($bad,$bon,$tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]);
    }
  }
  // Enregistrer le csv
  $export_csv .= $groupe_nom."\r\n".$date_fr."\r\n".$description."\r\n\r\n";
  $export_csv .= 'CODAGES AUTORISÉS : 1 2 3 4 A D N P'."\r\n";
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.'saisie_deportee_'.$fnom_export.'.zip' , 'saisie_deportee_'.$fnom_export.'.csv' , To::csv($export_csv) );
  //
  // pdf contenant un tableau de saisie vide ; on a besoin de tourner du texte à 90°
  //
  $sacoche_pdf = new PDF( FALSE /*officiel*/ , 'landscape' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 10 /*marge_haut*/ , 10 /*marge_bas*/ , 'non' /*couleur*/ );
  $sacoche_pdf->tableau_saisie_initialiser($eleve_nb,$item_nb);
  // 1ère ligne : référence devoir, noms élèves
  $sacoche_pdf->tableau_saisie_reference_devoir($groupe_nom,$date_fr,$description);
  foreach($DB_TAB_USER as $DB_ROW)
  {
    $sacoche_pdf->tableau_saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
  }
  // ligne suivantes : référence item, cases vides
  $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
  foreach($DB_TAB_COMP as $DB_ROW)
  {
    $item_ref = $DB_ROW['item_ref'];
    $texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
    $sacoche_pdf->tableau_saisie_reference_item($item_ref.$texte_socle,$DB_ROW['item_nom']);
    for($i=0 ; $i<$eleve_nb ; $i++)
    {
      $sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , '' , 1 , 0 , 'C' , FALSE , '');
    }
    $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
  }
  $sacoche_pdf->Output(CHEMIN_DOSSIER_EXPORT.'tableau_sans_notes_'.$fnom_export.'.pdf','F');
  //
  // c'est fini ; affichage du retour
  //
  foreach($tab_affich as $comp_id => $tab_user)
  {
    if(!$comp_id)
    {
      echo'<thead>';
    }
    echo'<tr>';
    foreach($tab_user as $user_id => $val)
    {
      echo $val;
    }
    echo'</tr>';
    if(!$comp_id)
    {
      echo'</thead><tbody class="h">';
    }
  }
  echo'</tbody>';
  echo'<SEP>'; // Séparateur
  echo $fnom_export;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir les items acquis par les élèves à une évaluation
// Générer en même temps un csv à récupérer pour une saisie déportée
// Générer en même temps un pdf contenant un tableau de saisie vide
// Générer en même temps un pdf contenant un tableau de saisie plein, couleur ou noir & blanc
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir') && $devoir_id && $groupe_id && $date_fr ) // $description et $groupe_nom sont aussi transmis
{
  // liste des items
  $DB_TAB_COMP = DB_STRUCTURE_PROFESSEUR::DB_lister_items_devoir( $devoir_id , TRUE /*with_lien*/ , TRUE /*with_coef*/ );
  // liste des élèves
  $DB_TAB_USER = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $groupe_type , $groupe_id );
  // Let's go
  $item_nb = count($DB_TAB_COMP);
  if(!$item_nb)
  {
    exit('Aucun item n\'est associé à cette évaluation !');
  }
  $eleve_nb = count($DB_TAB_USER);
  if(!$eleve_nb)
  {
    exit('Aucun élève n\'est associé à cette évaluation !');
  }
  $separateur = ';';
  $tab_affich  = array(); // tableau bi-dimensionnel [n°ligne=id_item][n°colonne=id_user]
  $tab_user_id = array(); // pas indispensable, mais plus lisible
  $tab_comp_id = array(); // pas indispensable, mais plus lisible
  $tab_affich[0][0] = '<td>';
  $tab_affich[0][0].= '<p>';
  $tab_affich[0][0].= '<label for="check_largeur"><input type="checkbox" id="check_largeur" name="check_largeur" value="retrecir_largeur" /> <span class="retrecir_largeur">Largeur optimale</label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la largeur des colonnes<br />si les élèves sont nombreux." /><br />';
  $tab_affich[0][0].= '<label for="check_hauteur"><input type="checkbox" id="check_hauteur" name="check_hauteur" value="retrecir_hauteur" /> <span class="retrecir_hauteur">Hauteur optimale</label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la hauteur des lignes<br />si les items sont nombreux." />';
  $tab_affich[0][0].= '</p>';
  $tab_affich[0][0].= '</td>';
  // première ligne (noms prénoms des élèves)
  $csv_ligne_eleve_nom = $separateur;
  $csv_ligne_eleve_id  = $separateur;
  foreach($DB_TAB_USER as $DB_ROW)
  {
    $tab_affich[0][$DB_ROW['user_id']] = '<th><img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'&amp;br" /></th>';
    $tab_user_id[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
    $csv_ligne_eleve_nom .= '"'.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'"'.$separateur;
    $csv_ligne_eleve_id  .= $DB_ROW['user_id'].$separateur;
  }
  $export_csv = $csv_ligne_eleve_id."\r\n";
  $csv_lignes_scores = array();
  $csv_colonne_texte = array();
  // première colonne (noms items)
  foreach($DB_TAB_COMP as $DB_ROW)
  {
    $item_ref = $DB_ROW['item_ref'];
    $texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
    $texte_coef  = ' ['.$DB_ROW['item_coef'].']';
    $texte_lien_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
    $texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
    $tab_affich[$DB_ROW['item_id']][0] = '<th><b>'.$texte_lien_avant.html($item_ref.$texte_socle.$texte_coef).$texte_lien_apres.'</b> <img alt="" src="./_img/bulle_aide.png" title="'.html(html($DB_ROW['item_nom'])).'" /><div>'.html($DB_ROW['item_nom']).'</div></th>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    $tab_comp_id[$DB_ROW['item_id']] = $item_ref;
    $csv_lignes_scores[$DB_ROW['item_id']][0] = $DB_ROW['item_id'];
    $csv_colonne_texte[$DB_ROW['item_id']]    = $item_ref.$texte_socle.$texte_coef.' '.$DB_ROW['item_nom'];
  }
  // cases centrales vierges
  foreach($tab_user_id as $user_id=>$val_user)
  {
    foreach($tab_comp_id as $comp_id=>$val_comp)
    {
      $tab_affich[$comp_id][$user_id] = '<td title="'.$val_user.'<br />'.$val_comp.'">-</td>';
      $csv_lignes_scores[$comp_id][$user_id] = '';
    }
  }
  // ajouter le contenu
  $tab_dossier = array( ''=>'' , 'RR'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'R'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'V'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'VV'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'ABS'=>'commun/h/' , 'NN'=>'commun/h/' , 'DISP'=>'commun/h/' , 'REQ'=>'commun/h/' );
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_saisies_devoir( $devoir_id , TRUE /*with_REQ*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    // Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
    if(isset($tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]))
    {
      $tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = str_replace('>-<','><img alt="'.$DB_ROW['saisie_note'].'" src="./_img/note/'.$tab_dossier[$DB_ROW['saisie_note']].$DB_ROW['saisie_note'].'.gif" /><',$tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]);
      $csv_lignes_scores[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = $DB_ROW['saisie_note'];
    }
  }
  // assemblage du csv
  $tab_conversion = array( ''=>' ' , 'RR'=>'1' , 'R'=>'2' , 'V'=>'3' , 'VV'=>'4' , 'ABS'=>'A' , 'DISP'=>'D' , 'NN'=>'N' , 'REQ'=>'P' );
  foreach($tab_comp_id as $comp_id=>$val_comp)
  {
    $export_csv .= $csv_lignes_scores[$comp_id][0].$separateur;
    foreach($tab_user_id as $user_id=>$val_user)
    {
      $export_csv .= $tab_conversion[$csv_lignes_scores[$comp_id][$user_id]].$separateur;
    }
    $export_csv .= $csv_colonne_texte[$comp_id]."\r\n";
  }
  $export_csv .= $csv_ligne_eleve_nom."\r\n\r\n";
  // Enregistrer le csv
  $export_csv .= $groupe_nom."\r\n".$date_fr."\r\n".$description."\r\n\r\n";
  $export_csv .= 'CODAGES AUTORISÉS : 1 2 3 4 A D N P'."\r\n";
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.'saisie_deportee_'.$fnom_export.'.zip' , 'saisie_deportee_'.$fnom_export.'.csv' , To::csv($export_csv) );
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // pdf contenant un tableau de saisie vide ; on a besoin de tourner du texte à 90°
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  $sacoche_pdf = new PDF( FALSE /*officiel*/ , 'landscape' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 10 /*marge_haut*/ , 10 /*marge_bas*/ , 'non' /*couleur*/ );
  $sacoche_pdf->tableau_saisie_initialiser($eleve_nb,$item_nb);
  // 1ère ligne : référence devoir, noms élèves
  $sacoche_pdf->tableau_saisie_reference_devoir($groupe_nom,$date_fr,$description);
  foreach($DB_TAB_USER as $DB_ROW)
  {
    $sacoche_pdf->tableau_saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
  }
  // ligne suivantes : référence item, cases vides
  $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
  foreach($DB_TAB_COMP as $DB_ROW)
  {
    $item_ref = $DB_ROW['item_ref'];
    $texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
    $sacoche_pdf->tableau_saisie_reference_item($item_ref.$texte_socle,$DB_ROW['item_nom']);
    for($i=0 ; $i<$eleve_nb ; $i++)
    {
      $sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , '' , 1 , 0 , 'C' , FALSE , '');
    }
    $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
  }
  $sacoche_pdf->Output(CHEMIN_DOSSIER_EXPORT.'tableau_sans_notes_'.$fnom_export.'.pdf','F');
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // pdf contenant un tableau de saisie plein, en couleurs ou en noir & blanc ; on a besoin de tourner du texte à 90°
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  $tab_couleurs = array( 'oui'=>'couleur' , 'non'=>'monochrome' );
  foreach($tab_couleurs as $couleur => $fichier_couleur)
  {
    $sacoche_pdf = new PDF( FALSE /*officiel*/ , 'landscape' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 10 /*marge_haut*/ , 10 /*marge_bas*/ , $couleur );
    $sacoche_pdf->tableau_saisie_initialiser($eleve_nb,$item_nb);
    // 1ère ligne : référence devoir, noms élèves
    $sacoche_pdf->tableau_saisie_reference_devoir($groupe_nom,$date_fr,$description);
    foreach($DB_TAB_USER as $DB_ROW)
    {
      $sacoche_pdf->tableau_saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
    }
    // ligne suivantes : référence item, cases vides
    $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
    foreach($DB_TAB_COMP as $DB_ROW_COMP)
    {
      $item_ref = $DB_ROW_COMP['item_ref'];
      $texte_socle = ($DB_ROW_COMP['entree_id']) ? ' [S]' : ' [–]';
      $sacoche_pdf->tableau_saisie_reference_item($item_ref.$texte_socle,$DB_ROW_COMP['item_nom']);
      foreach($DB_TAB_USER as $DB_ROW_USER)
      {
        $sacoche_pdf->afficher_note_lomer( $csv_lignes_scores[$DB_ROW_COMP['item_id']][$DB_ROW_USER['user_id']] , $border=1 , $br=0 );
      }
      $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
    }
    $sacoche_pdf->Output(CHEMIN_DOSSIER_EXPORT.'tableau_avec_notes_'.$fichier_couleur.'_'.$fnom_export.'.pdf','F');
  }
  //
  // c'est fini ; affichage du retour
  //
  foreach($tab_affich as $comp_id => $tab_user)
  {
    if(!$comp_id)
    {
      echo'<thead>';
    }
    echo'<tr>';
    foreach($tab_user as $user_id => $val)
    {
      echo $val;
    }
    echo'</tr>';
    if(!$comp_id)
    {
      echo'</thead><tbody>';
    }
  }
  echo'</tbody>';
  echo'<SEP>'; // Séparateur
  echo $fnom_export;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir en proportion la répartition, nominative ou quantitative, des élèves par item (html + pdf, couleur ou noir & blanc)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir_repart') && $devoir_id && $groupe_id && $date_fr ) // $description et $groupe_nom sont aussi transmis
{
  // liste des items
  $DB_TAB_ITEM = DB_STRUCTURE_PROFESSEUR::DB_lister_items_devoir( $devoir_id , TRUE /*with_lien*/ , TRUE /*with_coef*/ );
  // liste des élèves
  $DB_TAB_USER = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $groupe_type , $groupe_id );
  // Let's go
  $item_nb = count($DB_TAB_ITEM);
  if(!$item_nb)
  {
    exit('Aucun item n\'est associé à cette évaluation !');
  }
  $eleve_nb = count($DB_TAB_USER);
  if(!$eleve_nb)
  {
    exit('Aucun élève n\'est associé à cette évaluation !');
  }
  $tab_user_id = array(); // pas indispensable, mais plus lisible
  $tab_item_id = array(); // pas indispensable, mais plus lisible
  // noms prénoms des élèves
  foreach($DB_TAB_USER as $DB_ROW)
  {
    $tab_user_id[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
  }
  // noms des items
  foreach($DB_TAB_ITEM as $DB_ROW)
  {
    $texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
    $texte_coef  = ' ['.$DB_ROW['item_coef'].']';
    $tab_item_id[$DB_ROW['item_id']] = array( $DB_ROW['item_ref'].$texte_socle.$texte_coef , $DB_ROW['item_nom'] , $DB_ROW['item_lien'] );
  }
  // tableaux utiles ou pour conserver les infos
  $tab_dossier = array( 'RR'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'R'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'V'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'VV'=>$_SESSION['NOTE_DOSSIER'].'/h/' );
  $tab_init_nominatif   = array('RR'=>array(),'R'=>array(),'V'=>array(),'VV'=>array());
  $tab_init_quantitatif = array('RR'=>0 ,'R'=>0 ,'V'=>0 ,'VV'=>0 );
  $tab_repartition_nominatif   = array();
  $tab_repartition_quantitatif = array();
  // initialisation
  foreach($tab_item_id as $item_id=>$tab_infos_item)
  {
    $tab_repartition_nominatif[$item_id]   = $tab_init_nominatif;
    $tab_repartition_quantitatif[$item_id] = $tab_init_quantitatif;
  }
  // 1e ligne : référence des codes
  $affichage_repartition_head = '<th class="nu"></th>';
  foreach($tab_init_quantitatif as $note=>$vide)
  {
    $affichage_repartition_head .= '<th><img alt="'.$note.'" src="./_img/note/'.$tab_dossier[$note].$note.'.gif" /></th>';
  }
  // ligne suivantes
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_saisies_devoir( $devoir_id , FALSE /*with_REQ*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    // Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
    if( isset($tab_user_id[$DB_ROW['eleve_id']]) && isset($tab_item_id[$DB_ROW['item_id']]) )
    {
      if(isset($tab_init_quantitatif[$DB_ROW['saisie_note']])) // On ne garde que RR R V VV
      {
        $tab_repartition_nominatif[$DB_ROW['item_id']][$DB_ROW['saisie_note']][] = $tab_user_id[$DB_ROW['eleve_id']];
        $tab_repartition_quantitatif[$DB_ROW['item_id']][$DB_ROW['saisie_note']]++;
      }
    }
  }
  // assemblage / affichage du tableau avec la répartition quantitative
  echo'<thead><tr>'.$affichage_repartition_head.'</tr></thead><tbody>';
  foreach($tab_item_id as $item_id=>$tab_infos_item)
  {
    $texte_lien_avant = ($tab_infos_item[2]) ? '<a class="lien_ext" href="'.html($tab_infos_item[2]).'">' : '';
    $texte_lien_apres = ($tab_infos_item[2]) ? '</a>' : '';
    echo'<tr>';
    echo'<th><b>'.$texte_lien_avant.html($tab_infos_item[0]).$texte_lien_apres.'</b><br />'.html($tab_infos_item[1]).'</th>';
    foreach($tab_repartition_quantitatif[$item_id] as $code=>$note_nb)
    {
      echo'<td style="font-size:'.round(75+100*$note_nb/$eleve_nb).'%">'.round(100*$note_nb/$eleve_nb).'%</td>';
    }
    echo'</tr>';
  }
  echo'</tbody>';
  // Séparateur
  echo'<SEP>';
  // assemblage / affichage du tableau avec la répartition nominative
  echo'<thead><tr>'.$affichage_repartition_head.'</tr></thead><tbody>';
  foreach($tab_item_id as $item_id=>$tab_infos_item)
  {
    $texte_lien_avant = ($tab_infos_item[2]) ? '<a class="lien_ext" href="'.html($tab_infos_item[2]).'">' : '';
    $texte_lien_apres = ($tab_infos_item[2]) ? '</a>' : '';
    echo'<tr>';
    echo'<th><b>'.$texte_lien_avant.html($tab_infos_item[0]).$texte_lien_apres.'</b><br />'.html($tab_infos_item[1]).'</th>';
    foreach($tab_repartition_nominatif[$item_id] as $code=>$tab_eleves)
    {
      echo'<td>'.implode('<br />',$tab_eleves).'</td>';
    }
    echo'</tr>';
  }
  echo'</tbody>';
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // pdf contenant un tableau avec la répartition quantitative, en couleur ou en noir & blanc
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  $tab_couleurs = array( 'oui'=>'couleur' , 'non'=>'monochrome' );
  foreach($tab_couleurs as $couleur => $fichier_couleur)
  {
    $sacoche_pdf = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 10 /*marge_haut*/ , 10 /*marge_bas*/ , $couleur );
    $sacoche_pdf->tableau_devoir_repartition_quantitative_initialiser($item_nb);
    // 1ère ligne : référence des codes
    $sacoche_pdf->tableau_saisie_reference_devoir($groupe_nom,$date_fr,$description);
    $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche+$sacoche_pdf->reference_largeur , $sacoche_pdf->marge_haut);
    foreach($tab_init_quantitatif as $note=>$vide)
    {
      $sacoche_pdf->afficher_note_lomer($note,$border=1,$br=0);
    }
    // ligne suivantes : référence item, cases répartition quantitative
    $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
    foreach($tab_item_id as $item_id=>$tab_infos_item)
    {
      $sacoche_pdf->tableau_saisie_reference_item($tab_infos_item[0],$tab_infos_item[1]);
      foreach($tab_repartition_quantitatif[$item_id] as $code=>$note_nb)
      {
        $coefficient = $note_nb/$eleve_nb ;
        // Tracer un rectangle coloré d'aire et d'intensité de niveau de gris proportionnels
        $teinte_gris = 255-128*$coefficient ;
        $sacoche_pdf->SetFillColor($teinte_gris,$teinte_gris,$teinte_gris);
        $memo_X = $sacoche_pdf->GetX();
        $memo_Y = $sacoche_pdf->GetY();
        $rect_largeur = $sacoche_pdf->cases_largeur * sqrt( $coefficient ) ;
        $rect_hauteur = $sacoche_pdf->cases_hauteur * sqrt( $coefficient ) ;
        $pos_X = $memo_X + ($sacoche_pdf->cases_largeur - $rect_largeur) / 2 ;
        $pos_Y = $memo_Y + ($sacoche_pdf->cases_hauteur - $rect_hauteur) / 2 ;
        $sacoche_pdf->SetXY($pos_X , $pos_Y);
        $sacoche_pdf->Cell($rect_largeur , $rect_hauteur , '' , 0 , 0 , 'C' , TRUE , '');
        // Écrire le %
        $sacoche_pdf->SetXY($memo_X , $memo_Y);
        $sacoche_pdf->SetFont('Arial' , '' , $sacoche_pdf->taille_police*(1+$coefficient));
        $sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , To::pdf(round(100*$coefficient).'%') , 1 , 0 , 'C' , FALSE , '');
      }
      $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
    }
    $sacoche_pdf->Output(CHEMIN_DOSSIER_EXPORT.'repartition_quantitative_'.$fichier_couleur.'_'.$fnom_export.'.pdf','F');
  }
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // pdf contenant un tableau avec la répartition nominative, en couleur ou en noir & blanc
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  $tab_couleurs = array( 'oui'=>'couleur' , 'non'=>'monochrome' );
  foreach($tab_couleurs as $couleur => $fichier_couleur)
  {
    $sacoche_pdf = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 10 /*marge_haut*/ , 10 /*marge_bas*/ , $couleur );
    // il faut additionner le nombre maxi d'élèves par case de chaque item (sans descendre en dessous de 4 pour avoir la place d'afficher l'intitulé de l'item) afin de prévoir le nb de lignes nécessaires
    $somme = 0;
    foreach($tab_repartition_quantitatif as $item_id => $tab_effectifs)
    {
      $somme += max(4,max($tab_effectifs));
    }
    $sacoche_pdf->tableau_devoir_repartition_nominative_initialiser($somme);
    foreach($tab_item_id as $item_id=>$tab_infos_item)
    {
      // 1ère ligne : nouvelle page si besoin + référence du devoir et des codes si besoin
      $sacoche_pdf->tableau_devoir_repartition_nominative_entete($groupe_nom,$date_fr,$description,$tab_init_quantitatif,$tab_repartition_quantitatif[$item_id]);
      // ligne de répartition pour 1 item : référence item
      $sacoche_pdf->tableau_saisie_reference_item($tab_infos_item[0],$tab_infos_item[1]);
      // ligne de répartition pour 1 item : cases répartition nominative
      foreach($tab_repartition_nominatif[$item_id] as $code=>$tab_eleves)
      {
        // Ecrire les noms ; plus court avec MultiCell() mais pb des retours à la ligne pour les noms trop longs
        $memo_X = $sacoche_pdf->GetX();
        $memo_Y = $sacoche_pdf->GetY();
        foreach($tab_eleves as $key => $eleve_texte)
        {
          $sacoche_pdf->CellFit($sacoche_pdf->cases_largeur , $sacoche_pdf->lignes_hauteur , To::pdf($eleve_texte) , 0 , 2 , 'L' , FALSE , '');
        }
        // Ajouter la bordure
        $sacoche_pdf->SetXY($memo_X , $memo_Y);
        $sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , '' , 1 , 0 , 'C' , FALSE , '');
      }
      $sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
    }
    $sacoche_pdf->Output(CHEMIN_DOSSIER_EXPORT.'repartition_nominative_'.$fichier_couleur.'_'.$fnom_export.'.pdf','F');
  }
  //
  // c'est fini...
  //
  echo'<SEP>'.$fnom_export;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour l'ordre des items d'une évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='enregistrer_ordre') && $devoir_id && count($tab_id) )
{
  DB_STRUCTURE_PROFESSEUR::DB_modifier_ordre_item($devoir_id,$tab_id);
  exit('<ok>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour les items acquis par les élèves à une évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='enregistrer_saisie') && $devoir_id && $date_fr && $date_visible && count($tab_notes) )
{
  $nb_saisies_possibles  = 0;
  $nb_saisies_effectuees = 0;
  // Tout est transmis : il faut comparer avec le contenu de la base pour ne mettre à jour que ce dont il y a besoin
  // On récupère les notes transmises dans $tab_post
  $tab_post = array();
  foreach($tab_notes as $key_note)
  {
    list( $key , $note ) = explode('_',$key_note);
    list( $item_id , $eleve_id ) = explode('x',$key);
    if( (int)$item_id && (int)$eleve_id )
    {
      $tab_post[$item_id.'x'.$eleve_id] = $note;
      $nb_saisies_possibles++;
      $nb_saisies_effectuees += ( ($note!='X') && ($note!='REQ') ) ? 1 : 0 ;
    }
  }
  // On recupère le contenu de la base déjà enregistré pour le comparer ; on remplit au fur et à mesure $tab_nouveau_modifier / $tab_nouveau_supprimer
  // $tab_demande_supprimer sert à supprimer des demandes d'élèves dont on met une note.
  $tab_nouveau_modifier = array();
  $tab_nouveau_supprimer = array();
  $tab_demande_supprimer = array();
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_saisies_devoir( $devoir_id , TRUE /*with_REQ*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $key = $DB_ROW['item_id'].'x'.$DB_ROW['eleve_id'];
    if(isset($tab_post[$key])) // Test nécessaire si élève ou item évalués dans ce devoir, mais retiré depuis (donc non transmis dans la nouvelle saisie, mais à conserver).
    {
      if($tab_post[$key]!=$DB_ROW['saisie_note'])
      {
        if($tab_post[$key]=='X')
        {
          // valeur de la base à supprimer
          $tab_nouveau_supprimer[$key] = $key;
        }
        else
        {
          // valeur de la base à modifier
          $tab_nouveau_modifier[$key] = $tab_post[$key];
          if($DB_ROW['saisie_note']=='REQ')
          {
            // demande d'évaluation à supprimer
            $tab_demande_supprimer[$key] = $key;
          }
        }
      }
      unset($tab_post[$key]);
    }
  }
  // Il reste dans $tab_post les données à ajouter (mises dans $tab_nouveau_ajouter) et les données qui ne servent pas (non enregistrées et non saisies)
  $tab_nouveau_ajouter = array_filter($tab_post,'sans_rien');
  // Il n'y a plus qu'à mettre à jour la base
  if( !count($tab_nouveau_ajouter) && !count($tab_nouveau_modifier) && !count($tab_nouveau_supprimer) )
  {
    exit('Aucune modification détectée !');
  }
  // L'information associée à la note comporte le nom de l'évaluation + celui du professeur (c'est une information statique, conservée sur plusieurs années)
  $date_mysql         = convert_date_french_to_mysql($date_fr);
  $date_visible_mysql = ($date_visible=='identique') ? $date_mysql : convert_date_french_to_mysql($date_visible);
  $info = $description.' ('.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.)';
  foreach($tab_nouveau_ajouter as $key => $note)
  {
    list($item_id,$eleve_id) = explode('x',$key);
    DB_STRUCTURE_PROFESSEUR::DB_ajouter_saisie($_SESSION['USER_ID'],$eleve_id,$devoir_id,$item_id,$date_mysql,$note,$info,$date_visible_mysql);
  }
  foreach($tab_nouveau_modifier as $key => $note)
  {
    list($item_id,$eleve_id) = explode('x',$key);
    DB_STRUCTURE_PROFESSEUR::DB_modifier_saisie($eleve_id,$devoir_id,$item_id,$note,$info);
  }
  foreach($tab_nouveau_supprimer as $key => $key)
  {
    list($item_id,$eleve_id) = explode('x',$key);
    DB_STRUCTURE_PROFESSEUR::DB_supprimer_saisie($eleve_id,$devoir_id,$item_id);
  }
  foreach($tab_demande_supprimer as $key => $key)
  {
    list($item_id,$eleve_id) = explode('x',$key);
    DB_STRUCTURE_PROFESSEUR::DB_supprimer_demande_precise($eleve_id,$item_id);
  }
  $remplissage_nombre   = $nb_saisies_effectuees.'/'.$nb_saisies_possibles ;
  $remplissage_class    = (!$nb_saisies_effectuees) ? 'br' : ( ($nb_saisies_effectuees<$nb_saisies_possibles) ? 'bj' : 'bv' ) ;
  $remplissage_class2   = ($fini=='oui') ? ' bf' : '' ;
  $remplissage_contenu  = ($fini=='oui') ? '<span>terminé</span><i>'.$remplissage_nombre.'</i>' : '<span>'.$remplissage_nombre.'</span><i>terminé</i>' ;
  $remplissage_lien1    = '<a href="#fini" class="fini" title="Cliquer pour indiquer (ou pas) qu\'il n\'y a plus de saisies à effectuer.">';
  $remplissage_lien2    = '</a>';
  exit('<td class="'.$remplissage_class.$remplissage_class2.'">'.$remplissage_lien1.$remplissage_contenu.$remplissage_lien2.'</td>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imprimer un cartouche d'une évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='imprimer_cartouche') && $devoir_id && $groupe_id && $date_fr && $cart_contenu && $cart_detail && $orientation && $marge_min && $couleur )
{
  Form::save_choix('cartouche');
  $with_nom    = (substr($cart_contenu,0,8)=='AVEC_nom')  ? TRUE : FALSE ;
  $with_result = (substr($cart_contenu,9)=='AVEC_result') ? TRUE : FALSE ;
  // liste des items
  $DB_TAB_COMP = DB_STRUCTURE_PROFESSEUR::DB_lister_items_devoir( $devoir_id , FALSE /*with_lien*/ , TRUE /*with_coef*/ );
  // liste des élèves
  $DB_TAB_USER = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $groupe_type , $groupe_id );
  // Let's go
  if(empty($DB_TAB_COMP))
  {
    exit('Aucun item n\'est associé à cette évaluation !');
  }
  if(empty($DB_TAB_USER))
  {
    exit('Aucun élève n\'est associé à cette évaluation !');
  }
  $tab_result  = array(); // tableau bi-dimensionnel [n°ligne=id_item][n°colonne=id_user]
  $tab_user_id = array(); // pas indispensable, mais plus lisible
  $tab_comp_id = array(); // pas indispensable, mais plus lisible
  $tab_user_nb_req = array(); // pour retenir le nb d'items par utilisateur : variable et utile uniquement si cartouche avec les demandes d'évaluations 
  // enregistrer noms prénoms des élèves
  foreach($DB_TAB_USER as $DB_ROW)
  {
    $tab_user_id[$DB_ROW['user_id']] = ($with_nom) ? html($DB_ROW['user_prenom'].' '.$DB_ROW['user_nom']) : '' ;
    $tab_user_nb_req[$DB_ROW['user_id']] = 0 ;
  }
  // enregistrer refs noms items
  foreach($DB_TAB_COMP as $DB_ROW)
  {
    $item_ref = $DB_ROW['item_ref'];
    $texte_socle = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
    $texte_coef  = '['.$DB_ROW['item_coef'].'] ';
    $tab_comp_id[$DB_ROW['item_id']] = array($item_ref,$texte_socle.$texte_coef.$DB_ROW['item_nom']);
  }
  // résultats vierges
  foreach($tab_user_id as $user_id=>$val_user)
  {
    foreach($tab_comp_id as $comp_id=>$val_comp)
    {
      $tab_result[$comp_id][$user_id] = '';
    }
  }
  // compléter si demandé avec les résultats et/ou les demandes d'évaluations
  if($with_result || $only_req)
  {
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_saisies_devoir( $devoir_id , $only_req );
    foreach($DB_TAB as $DB_ROW)
    {
      // Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
      if(isset($tab_result[$DB_ROW['item_id']][$DB_ROW['eleve_id']]))
      {
        $valeur = ($with_result) ? $DB_ROW['saisie_note'] : ( ($DB_ROW['saisie_note']) ? 'REQ' : '' ) ;
        if($valeur)
        {
          $tab_result[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = $valeur ;
          $tab_user_nb_req[$DB_ROW['eleve_id']]++;
        }
      }
    }
  }
  // On attaque l'élaboration des sorties HTML, CSV et PDF
  $sacoche_htm = '<hr /><a class="lien_ext" href="'.URL_DIR_EXPORT.'cartouche_'.$fnom_export.'.pdf"><span class="file file_pdf">Cartouches &rarr; Archiver / Imprimer (format <em>pdf</em>).</span></a><br />';
  $sacoche_htm.= '<a class="lien_ext" href="'.URL_DIR_EXPORT.'cartouche_'.$fnom_export.'.zip"><span class="file file_zip">Cartouches &rarr; Récupérer / Manipuler (fichier <em>csv</em> pour tableur).</span></a>';
  $sacoche_csv = '';
  $separateur  = ';';
  // Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
  $item_nb = count($tab_comp_id);
  if(!$only_req)
  {
    $tab_user_nb_req = array_fill_keys( array_keys($tab_user_nb_req) , $item_nb );
  }
  $sacoche_pdf = new PDF( FALSE /*officiel*/ , $orientation , $marge_min /*marge_gauche*/ , $marge_min /*marge_droite*/ , $marge_min /*marge_haut*/ , $marge_min /*marge_bas*/ , $couleur , 'oui' /*legende*/ );
  $sacoche_pdf->cartouche_initialiser($cart_detail,$item_nb);
  if($cart_detail=='minimal')
  {
    // dans le cas d'un cartouche minimal
    foreach($tab_user_id as $user_id=>$val_user)
    {
      if($tab_user_nb_req[$user_id])
      {
        $texte_entete = $date_fr.' - '.$description.' - '.$val_user;
        $sacoche_htm .= '<table class="bilan"><thead><tr><th colspan="'.$tab_user_nb_req[$user_id].'">'.html($texte_entete).'</th></tr></thead><tbody>';
        $sacoche_csv .= $texte_entete."\r\n";
        $sacoche_pdf->cartouche_entete( $texte_entete , $lignes_nb=4 );
        $ligne1_csv = ''; $ligne1_html = '';
        $ligne2_csv = ''; $ligne2_html = '';
        foreach($tab_comp_id as $comp_id=>$tab_val_comp)
        {
          if( ($only_req==FALSE) || ($tab_result[$comp_id][$user_id]) )
          {
            $note = ($tab_result[$comp_id][$user_id]!='REQ') ? $tab_result[$comp_id][$user_id] : '' ; // Si on voulait récupérer les items ayant fait l'objet d'une demande d'évaluation, il n'y a pour autant pas lieu d'afficher les paniers sur les cartouches.
            $ligne1_html .= '<td>'.html($tab_val_comp[0]).'</td>';
            $ligne2_html .= '<td class="hc">'.Html::note($note,$date_fr,$description,FALSE).'</td>';
            $ligne1_csv .= '"'.$tab_val_comp[0].'"'.$separateur;
            $ligne2_csv .= '"'.$note.'"'.$separateur;
            $sacoche_pdf->cartouche_minimal_competence($tab_val_comp[0] , $note);
          }
        }
        $sacoche_htm .= '<tr>'.$ligne1_html.'</tr><tr>'.$ligne2_html.'</tr></tbody></table>';
        $sacoche_csv .= $ligne1_csv."\r\n".$ligne2_csv."\r\n\r\n";
        $sacoche_pdf->cartouche_interligne(4);
      }
    }
  }
  elseif($cart_detail=='complet')
  {
    // dans le cas d'un cartouche complet
    foreach($tab_user_id as $user_id=>$val_user)
    {
      if($tab_user_nb_req[$user_id])
      {
        $texte_entete = $date_fr.' - '.$description.' - '.$val_user;
        $sacoche_htm .= '<table class="bilan"><thead><tr><th colspan="3">'.html($texte_entete).'</th></tr></thead><tbody>';
        $sacoche_csv .= $texte_entete."\r\n";
        $sacoche_pdf->cartouche_entete( $texte_entete , $lignes_nb=$tab_user_nb_req[$user_id]+1 );
        foreach($tab_comp_id as $comp_id=>$tab_val_comp)
        {
          if( ($only_req==FALSE) || ($tab_result[$comp_id][$user_id]) )
          {
            $note = ($tab_result[$comp_id][$user_id]!='REQ') ? $tab_result[$comp_id][$user_id] : '' ; // Si on voulait récupérer les items ayant fait l'objet d'une demande d'évaluation, il n'y a pour autant pas lieu d'afficher les paniers sur les cartouches.
            $sacoche_htm .= '<tr><td>'.html($tab_val_comp[0]).'</td><td>'.html($tab_val_comp[1]).'</td><td>'.Html::note($note,$date_fr,$description,FALSE).'</td></tr>';
            $sacoche_csv .= '"'.$tab_val_comp[0].'"'.$separateur.'"'.$tab_val_comp[1].'"'.$separateur.'"'.$note.'"'."\r\n";
            $sacoche_pdf->cartouche_complet_competence($tab_val_comp[0] , $tab_val_comp[1] , $note);
          }
        }
        $sacoche_htm .= '</tbody></table>';
        $sacoche_csv .= "\r\n";
        $sacoche_pdf->cartouche_interligne(2);
      }
    }
  }
  // On archive le cartouche dans un fichier tableur zippé (csv tabulé)
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.'cartouche_'.$fnom_export.'.zip' , 'cartouche_'.$fnom_export.'.csv' , To::csv($sacoche_csv) );
  // On archive le cartouche dans un fichier pdf
  $sacoche_pdf->Output(CHEMIN_DOSSIER_EXPORT.'cartouche_'.$fnom_export.'.pdf','F');
  // Affichage
  exit($sacoche_htm);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traiter une demande d'importation d'une saisie déportée ; on n'enregistre rien, on ne fait que décrypter le contenu du fichier et renvoyer une chaine résultante au javascript
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (isset($_GET['f_action'])) && ($_GET['f_action']=='importer_saisie_csv') )
{
  $fichier_nom = 'saisie_deportee_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.<EXT>';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('txt','csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit('Erreur : '.$result);
  }
  $contenu_csv = file_get_contents(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
  $contenu_csv = To::deleteBOM(To::utf8($contenu_csv)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $tab_lignes = extraire_lignes($contenu_csv); // Extraire les lignes du fichier
  $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
  // Pas de ligne d'en-tête à supprimer
  // Mémoriser les eleve_id de la 1ère ligne
  $tab_eleve = array();
  $tab_elements = explode($separateur,$tab_lignes[0]);
  unset($tab_elements[0]);
  foreach ($tab_elements as $num_colonne => $element_contenu)
  {
    $eleve_id = Clean::entier($element_contenu);
    if($eleve_id)
    {
      $tab_eleve[$num_colonne] = $eleve_id ;
    }
  }
  // Parcourir les lignes suivantes et mémoriser les scores
  $retour = '|';
  unset($tab_lignes[0]);
  $scores_autorises = '1234AaDdNnPp';
  foreach ($tab_lignes as $ligne_contenu)
  {
    $tab_elements = explode($separateur,$ligne_contenu);
    $item_id = Clean::entier($tab_elements[0]);
    if($item_id)
    {
      foreach ($tab_eleve as $num_colonne => $eleve_id)
      {
        if( (isset($tab_elements[$num_colonne])) && ($tab_elements[$num_colonne]!='') )
        {
          $score = $tab_elements[$num_colonne];
          if(strpos($scores_autorises,$score)!==FALSE)
          {
            $retour .= $eleve_id.'.'.$item_id.'.'.strtoupper($score).'|';
          }
        }
      }
    }
  }
  exit($retour);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Référencer un sujet ou un corrigé d'évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='referencer_document') && $devoir_id && in_array($doc_objet,array('sujet','corrige')) && $doc_url )
{
  // Mise à jour dans la base
  DB_STRUCTURE_PROFESSEUR::DB_modifier_devoir_document($devoir_id,$_SESSION['USER_ID'],$doc_objet,$doc_url);
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un sujet ou un corrigé d'évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='uploader_document') && $devoir_id && in_array($doc_objet,array('sujet','corrige')) )
{
  $fichier_nom = 'devoir_'.$devoir_id.'_'.$doc_objet.'_'.time().'.<EXT>'; // pas besoin de le rendre inaccessible -> fabriquer_fin_nom_fichier__date_et_alea() inutilement lourd
  $result = FileSystem::recuperer_upload( $chemin_devoir /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , NULL /*tab_extensions_autorisees*/ , array('bat','com','exe','php','zip') /*tab_extensions_interdites*/ , FICHIER_TAILLE_MAX /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit($result);
  }
  // Mise à jour dans la base
  DB_STRUCTURE_PROFESSEUR::DB_modifier_devoir_document($devoir_id,$_SESSION['USER_ID'],$doc_objet,$url_dossier_devoir.FileSystem::$file_saved_name);
  // Retour
  exit('ok'.']¤['.$ref.']¤['.$doc_objet.']¤['.$url_dossier_devoir.FileSystem::$file_saved_name);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un sujet ou un corrigé d'évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='retirer_document') && $devoir_id && in_array($doc_objet,array('sujet','corrige')) && $doc_url )
{
  // Suppression du fichier, uniquement si ce n'est pas un lien externe ou vers un devoir d'un autre établissement
  if(mb_strpos($doc_url,$url_dossier_devoir)===0)
  {
    $chemin_doc = str_replace($url_dossier_devoir,$chemin_devoir,$doc_url);
    // Il peut être référencé dans une autre évaluation et donc avoir déjà été effacé, ou ne pas être présent sur le serveur en cas de restauration de base ailleurs, etc.
    if(file_exists($chemin_doc))
    {
      unlink($chemin_doc);
    }
  }
  // Mise à jour dans la base
  DB_STRUCTURE_PROFESSEUR::DB_modifier_devoir_document($devoir_id,$_SESSION['USER_ID'],$doc_objet,'');
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Déclarer (ou pas) une évaluation complète en saisie
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='maj_fini') && $devoir_id && in_array($fini,array('oui','non')) )
{
  DB_STRUCTURE_PROFESSEUR::DB_modifier_devoir_fini($devoir_id,$_SESSION['USER_ID'],$fini);
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
