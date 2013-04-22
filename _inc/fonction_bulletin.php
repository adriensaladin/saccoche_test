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

/**
 * Pour un bulletin d'une période et d'une classe donnée, calculer et mettre à jour toutes les moyennes qui en ont besoin et qui ne sont pas figées manuellement.
 * Si demandé, calcule et met en session les moyennes de classe.
 * 
 * @param int    $periode_id
 * @param int    $classe_id
 * @param string $liste_eleve_id
 * @param string $liste_matiere_id   renseigné pour un prof effectuant une saisie, vide sinon
 * @param string $retroactif   oui|non|auto
 * @param bool   $memo_moyennes_classe
 * @param bool   $memo_moyennes_generale
 * @return void
 */
function calculer_et_enregistrer_moyennes_eleves_bulletin($periode_id,$classe_id,$liste_eleve_id,$liste_matiere_id,$retroactif,$memo_moyennes_classe,$memo_moyennes_generale)
{
  if(!$liste_eleve_id) return FALSE;
  // Dates période
  $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($classe_id,$periode_id);
  if(empty($DB_ROW)) return FALSE;
  // Récupération de la liste des items travaillés et affiner la liste des matières concernées
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  list($tab_item,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_items_travailles($liste_eleve_id,$liste_matiere_id,$date_mysql_debut,$date_mysql_fin);
  $item_nb = count($tab_item);
  if(!$item_nb) return FALSE;
  $tab_liste_item = array_keys($tab_item);
  $liste_item_id = implode(',',$tab_liste_item);
  // Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
  // Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
  $tab_score_a_garder = array();
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items($liste_eleve_id,$liste_item_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? FALSE : TRUE ;
  }
  $date_mysql_start = ($retroactif=='non') ? $date_mysql_debut : FALSE ; // En 'auto' il faut faire le tri après.
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($liste_eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE']);
  foreach($DB_TAB as $DB_ROW)
  {
    if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
    {
      if( ($retroactif!='auto') || ($tab_item[$DB_ROW['item_id']][0]['calcul_retroactif']=='oui') || ($DB_ROW['date']>=$date_mysql_debut) )
      {
        $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['matiere_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note']);
      }
    }
  }
  // On calcule les moyennes des élèves dans chaque matière
  $tab_eleve_id = explode(',',$liste_eleve_id);
  // On ne calcule pas les moyennes de classe à partir de ces données car on peut n'avoir ici qu'une partie de la classe
  $tab_moyennes_calculees = array();  // $tab_moyennes_calculees[$matiere_id][$eleve_id]         Retenir la moyenne des scores d'acquisitions / matière / élève
  // Pour chaque élève...
  foreach($tab_eleve_id as $eleve_id)
  {
    // Si cet élève a été évalué...
    if(isset($tab_eval[$eleve_id]))
    {
      // Pour chaque matiere...
      foreach($tab_matiere as $matiere_id => $matiere_nom)
      {
        // Si cet élève a été évalué dans cette matière...
        if(isset($tab_eval[$eleve_id][$matiere_id]))
        {
          $tab_score = array();
          // Pour chaque item...
          foreach($tab_eval[$eleve_id][$matiere_id] as $item_id => $tab_devoirs)
          {
            extract($tab_item[$item_id][0]);  // $item_ref $item_nom $item_coef $item_socle $item_lien $calcul_methode $calcul_limite $calcul_retroactif
            // calcul du bilan de l'item
            $tab_score[$item_id] = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
          }
          // calcul des bilans des scores
          $tableau_score_filtre = array_filter($tab_score,'non_nul');
          $nb_scores = count( $tableau_score_filtre );
          // la moyenne peut être pondérée par des coefficients
          $somme_scores_ponderes = 0;
          $somme_coefs = 0;
          if($nb_scores)
          {
            foreach($tableau_score_filtre as $item_id => $item_score)
            {
              $somme_scores_ponderes += $item_score*$tab_item[$item_id][0]['item_coef'];
              $somme_coefs += $tab_item[$item_id][0]['item_coef'];
            }
          }
          // et voilà la moyenne des pourcentages d'acquisition
          $tab_moyennes_calculees[$matiere_id][$eleve_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) / 5 : FALSE ; // Pas NULL car un test isset() sur une valeur NULL renvoie FALSE !!! (voir qq lignes plus bas)
        }
      }
    }
  }
  // Rechercher les moyennes déjà enregistrées, et si elles ont été calculées automatiquement ou imposées
  $tab_moyennes_enregistrees      = array();
  $tab_appreciations_enregistrees = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_notes_eleves( $periode_id , $liste_eleve_id , FALSE /*tri_matiere*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_moyennes_enregistrees['eleve'][$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']] = ($DB_ROW['saisie_note']!==NULL) ? (float)$DB_ROW['saisie_note'] : FALSE ; // Pas NULL car un test isset() sur une valeur NULL renvoie FALSE !!! (voir qq lignes plus bas)
    $tab_appreciations_enregistrees[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']] = $DB_ROW['saisie_appreciation'];
  }
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_notes_classe( $periode_id , $classe_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_moyennes_enregistrees['groupe'][$DB_ROW['rubrique_id']] = ($DB_ROW['saisie_note']!==NULL) ? (float)$DB_ROW['saisie_note'] : FALSE ; // Pas NULL car un test isset() sur une valeur NULL renvoie FALSE !!! (voir qq lignes plus bas)
  }
  // Mettre à jour les moyennes qui le nécessitent
  foreach($tab_moyennes_calculees as $matiere_id => $tab)
  {
    foreach($tab as $eleve_id => $note)
    {
      if( (!isset($tab_moyennes_enregistrees['eleve'][$matiere_id][$eleve_id])) || ( ($tab_moyennes_enregistrees['eleve'][$matiere_id][$eleve_id]!=$note) && ($tab_appreciations_enregistrees[$matiere_id][$eleve_id]=='') ) )
      {
        $note = ($note!==FALSE) ? $note : NULL ;
        DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( 'bulletin' , $periode_id , $eleve_id , $matiere_id , 0 /*prof_id*/ , 'eleve' , $note , '' /*appreciation*/ );
        $tab_moyennes_enregistrees['eleve'][$matiere_id][$eleve_id] = $note;
      }
    }
  }
  // Il peut aussi falloir supprimer des moyennes calculées ou imposées précédemment mais qui n'ont plus lieu d'être car les notes ont été supprimées, ou les items déplacés, ou le référentiel supprimé depuis...
  $tab_matiere_id = ($liste_matiere_id) ? explode(',',$liste_matiere_id) : array_keys($tab_moyennes_enregistrees['eleve']);
  foreach($tab_moyennes_enregistrees['eleve'] as $matiere_id => $tab)
  {
    if( $matiere_id && in_array($matiere_id,$tab_matiere_id) ) // Parce que dans le cas d'un prof effectuant une saisie, toutes les matières ne sont pas récupérées : il ne faut pas supprimer les notes des autres matières (ni la moyenne générale, donc quand l'id matière est à 0).
    {
      foreach($tab as $eleve_id => $note)
      {
        if(!isset($tab_moyennes_calculees[$matiere_id][$eleve_id]))
        {
          DB_STRUCTURE_OFFICIEL::DB_supprimer_bilan_officiel_saisie( 'bulletin' , $periode_id , $eleve_id , $matiere_id , 0 /*prof_id*/ , 'eleve' );
          unset($tab_moyennes_enregistrees['eleve'][$matiere_id][$eleve_id]);
        }
      }
    }
  }
  // Calculer les moyennes de classe, et mettre à jour les moyennes qui le nécessitent
  if($memo_moyennes_classe)
  {
    foreach($tab_moyennes_enregistrees['eleve'] as $matiere_id => $tab)
    {
      if($matiere_id!=0)
      {
        if(count($tab_moyennes_enregistrees['eleve'][$matiere_id]))
        {
          $somme   = array_sum($tab_moyennes_enregistrees['eleve'][$matiere_id]);
          $nombre  = count( array_filter($tab_moyennes_enregistrees['eleve'][$matiere_id],'non_nul') );
          $moyenne = ($nombre) ? round($somme/$nombre,1) : NULL ;
          if( (!isset($tab_moyennes_enregistrees['groupe'][$matiere_id])) || ( ($tab_moyennes_enregistrees['groupe'][$matiere_id]!=$moyenne) ) )
          {
            DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( 'bulletin' , $periode_id , $classe_id , $matiere_id , 0 /*prof_id*/ , 'classe' , $moyenne , '' /*appreciation*/ );
          }
        }
        else
        {
          // Possible si toutes les notes viennent d'être supprimées car n'ayant plus lieu d'être (voir qq lignes plus haut)
          DB_STRUCTURE_OFFICIEL::DB_supprimer_bilan_officiel_saisie( 'bulletin' , $periode_id , $classe_id , $matiere_id , 0 /*prof_id*/ , 'classe' );
          unset($tab_moyennes_enregistrees['eleve'][$matiere_id]);
        }
      }
    }
  }
  // Calculer les moyennes générales des élèves, et mettre à jour les moyennes qui le nécessitent
  if($memo_moyennes_generale)
  {
    $tab_moyenne_eleve_generale = array();
    $tab_moyennes_enregistrees_par_eleve = array();
    // inverser les clefs du tableau pour pouvoir effectuer les totaux par élève
    foreach($tab_moyennes_enregistrees['eleve'] as $matiere_id => $tab)
    {
      if($matiere_id!=0)
      {
        foreach($tab as $eleve_id => $note)
        {
          $tab_moyennes_enregistrees_par_eleve[$eleve_id][$matiere_id] = $note;
        }
      }
    }
    foreach($tab_moyennes_enregistrees_par_eleve as $eleve_id => $tab)
    {
      $somme  = array_sum($tab_moyennes_enregistrees_par_eleve[$eleve_id]);
      $nombre = count( array_filter($tab_moyennes_enregistrees_par_eleve[$eleve_id],'non_nul') );
      $moyenne = ($nombre) ? round($somme/$nombre,1) : NULL ;
      if( (!isset($tab_moyennes_enregistrees['eleve'][0][$eleve_id])) || ( ($tab_moyennes_enregistrees['eleve'][0][$eleve_id]!=$note) ) )
      {
        DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( 'bulletin' , $periode_id , $eleve_id , 0 /*matiere_id*/ , 0 /*prof_id*/ , 'eleve' , $moyenne , '' /*appreciation*/ );
      }
      $tab_moyenne_eleve_generale[$eleve_id] = $moyenne;
    }
    // Enfin, moyenne de classe des moyennes générales...
    if($memo_moyennes_classe)
    {
      $somme   = array_sum($tab_moyenne_eleve_generale);
      $nombre  = count( array_filter($tab_moyenne_eleve_generale,'non_nul') );
      $moyenne = ($nombre) ? round($somme/$nombre,1) : NULL ;
      if( (!isset($tab_moyennes_enregistrees['groupe'][0])) || ( ($tab_moyennes_enregistrees['groupe'][0]!=$moyenne) ) )
      {
        DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( 'bulletin' , $periode_id , $classe_id , 0 /*matiere_id*/ , 0 /*prof_id*/ , 'classe' , $moyenne , '' /*appreciation*/ );
      }
    }
  }
}

/**
 * Pour un bulletin d'une période / d'un élève et d'une matière donné, calculer et forcer la mise à jour d'une moyenne (effacée ou figée).
 * 
 * @param int    $periode_id
 * @param int    $classe_id
 * @param int    $eleve_id
 * @param array  $matiere_id
 * @param string $retroactif   oui|non|auto
 * @return float   la moyenne en question (FALSE si pb)
 */
function calculer_et_enregistrer_moyenne_precise_bulletin($periode_id,$classe_id,$eleve_id,$matiere_id,$retroactif)
{
  // Dates période
  $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($classe_id,$periode_id);
  if(empty($DB_ROW)) return FALSE;
  // Récupération de la liste des items travaillés
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  list($tab_item,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_items_travailles($eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin);
  $item_nb = count($tab_item);
  if(!$item_nb) return FALSE;
  $tab_liste_item = array_keys($tab_item);
  $liste_item_id = implode(',',$tab_liste_item);
  // Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
  $date_mysql_start = ($retroactif=='non') ? $date_mysql_debut : FALSE ; // En 'auto' il faut faire le tri après.
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE']);
  if(empty($DB_TAB)) return FALSE;
  foreach($DB_TAB as $DB_ROW)
  {
    if( ($retroactif!='auto') || ($tab_item[$DB_ROW['item_id']][0]['calcul_retroactif']=='oui') || ($DB_ROW['date']>=$date_mysql_debut) )
    {
      $tab_eval[$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note']);
    }
  }
  if(empty($tab_eval)) return FALSE;
  // On calcule la moyenne voulue
  $tab_score = array();
  // Pour chaque item...
  foreach($tab_eval as $item_id => $tab_devoirs)
  {
    extract($tab_item[$item_id][0]);  // $item_ref $item_nom $item_coef $item_socle $item_lien $calcul_methode $calcul_limite
    // calcul du bilan de l'item
    $tab_score[$item_id] = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
  }
  // calcul des bilans des scores
  $tableau_score_filtre = array_filter($tab_score,'non_nul');
  $nb_scores = count( $tableau_score_filtre );
  // la moyenne peut être pondérée par des coefficients
  $somme_scores_ponderes = 0;
  $somme_coefs = 0;
  if($nb_scores)
  {
    foreach($tableau_score_filtre as $item_id => $item_score)
    {
      $somme_scores_ponderes += $item_score*$tab_item[$item_id][0]['item_coef'];
      $somme_coefs += $tab_item[$item_id][0]['item_coef'];
    }
  }
  // et voilà la moyenne des pourcentages d'acquisition
  if(!$somme_coefs) return FALSE;
  $moyennes_calculee = round($somme_scores_ponderes/$somme_coefs,0) / 5 ;
  DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( 'bulletin' , $periode_id , $eleve_id , $matiere_id , 0 /*prof_id*/ , 'eleve' , $moyennes_calculee , '' /*appreciation*/ );
  return $moyennes_calculee;
}

/**
 * Retourner le texte indiquant les absences et retard à partir des données transmises.
 * 
 * @param array  $tab_assiduite
 * @return string
 */
function texte_ligne_assiduite($tab_assiduite)
{
  $intro = 'Assiduité et ponctualité : ';
  extract($tab_assiduite); // $absence $non_justifie $retard
  if( ($absence===NULL) && ($non_justifie===NULL) && ($retard===NULL) )
  {
    return $intro.'aucune absence ni retard.';
  }
  if(!$absence)
  {
    $txt_absences = 'aucune absence, ';
  }
  else
  {
    $s = ($absence>1) ? 's' : '' ;
    $txt_absences = $absence.' demi-journée'.$s.' d\'absence, ';
    if(!$non_justifie)
    {
      $txt_absences .= ($s) ? 'toutes justifiées, ' : 'justifiée, ' ;
    }
    elseif($non_justifie==$absence)
    {
      $txt_absences .= ($s) ? 'dont aucune justifiée, ' : 'non justifiée, ' ;
    }
    else
    {
      $s = ($non_justifie>1) ? 's' : '' ;
      $txt_absences .= 'dont '.$non_justifie.' non justifiée'.$s.', ';
    }
  }
  if(!$retard)
  {
    $txt_retards = 'et aucun retard.';
  }
  else
  {
    $s = ($retard>1) ? 's' : '' ;
    $txt_retards = 'et '.$retard.' retard'.$s.'.';
  }
  return $intro.$txt_absences.$txt_retards;
}

?>
