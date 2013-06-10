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
// Autres cas
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$indicateur        = (isset($_POST['f_indicateur']))        ? Clean::texte($_POST['f_indicateur']) : '';
$conversion_sur_20 = (isset($_POST['f_conversion_sur_20'])) ? 1                                    : 0;
$with_coef         = 1; // Il n'y a que des courbes par matière et pas de courbe commune : on prend en compte les coefficients pour chaque courbe matière.
$groupe_id         = (isset($_POST['f_groupe']))            ? Clean::entier($_POST['f_groupe'])    : 0;
$eleve_id          = (isset($_POST['f_eleve']))             ? Clean::entier($_POST['f_eleve'])     : 0;
$periode_id        = (isset($_POST['f_periode']))           ? Clean::entier($_POST['f_periode'])   : 0;
$date_debut        = (isset($_POST['f_date_debut']))        ? Clean::texte($_POST['f_date_debut']) : '';
$date_fin          = (isset($_POST['f_date_fin']))          ? Clean::texte($_POST['f_date_fin'])   : '';
$retroactif        = (isset($_POST['f_retroactif']))        ? Clean::texte($_POST['f_retroactif']) : '';
$only_socle        = (isset($_POST['f_restriction']))       ? 1                                    : 0;

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_matiere = (isset($_POST['f_matiere'])) ? ( (is_array($_POST['f_matiere'])) ? $_POST['f_matiere'] : explode(',',$_POST['f_matiere']) ) : array() ;
$tab_matiere = array_filter( Clean::map_entier($tab_matiere) , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere);

// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  if(!test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE']))      { $indicateur = 'pourcentage_acquis'; }
  if(!test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS'])) { $indicateur = 'moyenne_scores'; }
  if(!test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION']))   { $indicateur = ''; }
  $conversion_sur_20 = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20']) ? $conversion_sur_20 : 0 ;
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $groupe_id = $_SESSION['ELEVE_CLASSE_ID'];
  $eleve_id  = $_SESSION['USER_ID'];
}

if( !in_array($indicateur,array('moyenne_scores','pourcentage_acquis')) || !$groupe_id || !$eleve_id || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !count($tab_matiere) )
{
  exit('Erreur avec les données transmises !');
}

Form::save_choix('bilan_chronologique');

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Initialisation de tableaux

$tab_item       = array();  // [item_id] => array(item_coef,calcul_methode,calcul_limite,calcul_retroactif);
$tab_liste_item = array();  // [i] => item_id
$tab_matiere    = array();  // [matiere_id] => matiere_nom
$tab_eval       = array();  // [eleve_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.
$tab_date       = array();  // [eleve_id][date][item_id] => nb_evals
$tab_matiere_for_item = array();  // [item_id] => matiere_id

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Période concernée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($periode_id==0)
{
  $date_mysql_debut = convert_date_french_to_mysql($date_debut);
  $date_mysql_fin   = convert_date_french_to_mysql($date_fin);
}
else
{
  $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
  if(empty($DB_ROW))
  {
    exit('La classe et la période ne sont pas reliées !');
  }
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  $date_debut = convert_date_mysql_to_french($date_mysql_debut);
  $date_fin   = convert_date_mysql_to_french($date_mysql_fin);
}
if($date_mysql_debut>$date_mysql_fin)
{
  exit('La date de début est postérieure à la date de fin !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items travaillés durant la période choisie, pour l'élève selectionné, pour la ou les matières indiquées
// Récupération de la liste des matières travaillées (affinée suivant les items trouvés)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

list($tab_item,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_items_travailles($eleve_id,$liste_matiere_id,$date_mysql_debut,$date_mysql_fin);

$item_nb = count($tab_item);
if( !$item_nb && (in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ) // Dans le cas d'un professeur / directeur, où l'on regarde les élèves d'un groupe un à un, ce ne doit pas être bloquant.
{
  exit('Aucun item évalué sur cette période selon les paramètres choisis !');
}
$tab_liste_item = array_keys($tab_item);
$liste_item_id  = implode(',',$tab_liste_item);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
// Comme un seul élève est concerné à chaque appel, il n'y a pas le problème de certains items à  éliminer car pouvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
// Il faut aussi retenir, à une date donnée, combien d'évaluations sont concernées.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$date_mysql_start = ($retroactif=='non') ? $date_mysql_debut : FALSE ; // En 'auto' il faut faire le tri après.
$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlynote*/ , TRUE /*first_order_by_date*/ );
foreach($DB_TAB as $DB_ROW)
{
  if( ($retroactif!='auto') || ($tab_item[$DB_ROW['item_id']][0]['calcul_retroactif']=='oui') || ($DB_ROW['date']>=$date_mysql_debut) )
  {
    $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note']);
    $tab_matiere_for_item[$DB_ROW['item_id']] = $DB_ROW['matiere_id'];
    $date = ($DB_ROW['date']>=$date_mysql_debut) ? $DB_ROW['date'] : $date_mysql_debut ;
    $tab_date[$DB_ROW['eleve_id']][$date][$DB_ROW['item_id']] = count($tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']]);
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
// Tableaux et variables pour mémoriser les infos ; dans cette partie on fait les calculs et on remplit le tableau js pour l'affichage au fur et à mesure
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_score_eleve_item      = array();  // [eleve_id][matiere_id][item_id] => score
$tab_moyenne_eleve_matiere = array();  // [eleve_id][matiere_id] => moyenne : Retenir la moyenne des scores d'acquisitions ou le pourcentage d'items acquis / élève / matière (sert pour ajouter une dernière valeur)

$tab_graph_data = array();

function date_mysql_to_date_js($date_mysql)
{
  list($annee,$mois,$jour) = explode('-',$date_mysql);
  return 'Date.UTC('.$annee.','.((int)$mois-1).','.(int)$jour.')';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// C'est parti !!!
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(count($tab_date))
{
  // Pour chaque évaluation...
  foreach($tab_date[$eleve_id] as $date_mysql => $tab_items)
  {
    $date_js = date_mysql_to_date_js($date_mysql);
    $tab_matiere_todo_moyenne = array();
    // On (re)-calcule les scores des items concernés
    foreach($tab_items as $item_id => $nb_evals)
    {
      extract($tab_item[$item_id][0]);  // $item_coef $calcul_methode $calcul_limite $calcul_retroactif
      $matiere_id = $tab_matiere_for_item[$item_id];
      $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] = calculer_score(array_slice($tab_eval[$eleve_id][$item_id],0,$nb_evals),$calcul_methode,$calcul_limite);
      $tab_matiere_todo_moyenne[] = $matiere_id;
    }
    // On (re)-calcule les moyennes des matières concernées
    foreach($tab_matiere_todo_moyenne as $matiere_id)
    {
      // calcul des bilans des scores
      $tableau_score_filtre = array_filter($tab_score_eleve_item[$eleve_id][$matiere_id],'non_nul');
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
        $somme_scores_simples = array_sum($tableau_score_filtre);
      }
      // Soit la moyenne des pourcentages d'acquisition
      if($indicateur=='moyenne_scores')
      {
        if($with_coef) { $tab_moyenne_eleve_matiere[$eleve_id][$matiere_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) : FALSE ; }
        else           { $tab_moyenne_eleve_matiere[$eleve_id][$matiere_id] = ($nb_scores)   ? round($somme_scores_simples/$nb_scores,0)    : FALSE ; }
      }
      // Soit le nombre d'items considérés acquis ou pas
      elseif($indicateur=='pourcentage_acquis')
      {
        if($nb_scores)
        {
          $nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
          $nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
          $nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
          $tab_moyenne_eleve_matiere[$eleve_id][$matiere_id] = round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0);
        }
        else
        {
          $tab_moyenne_eleve_matiere[$eleve_id][$matiere_id] = FALSE;
        }
      }
      if($tab_moyenne_eleve_matiere[$eleve_id][$matiere_id]!==FALSE)
      {
        $valeur = ($conversion_sur_20) ? $tab_moyenne_eleve_matiere[$eleve_id][$matiere_id]/5 : $tab_moyenne_eleve_matiere[$eleve_id][$matiere_id] ;
        $tab_graph_data[$matiere_id][$date_js] = $valeur;
      }
    }
  }
  // Ajouter un point en fin de période pour chaque matière
  foreach($tab_graph_data as $matiere_id => $tab_data)
  {
    $last_valeur = end($tab_data);
    $tab_graph_data[$matiere_id][$date_js] = $last_valeur;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On fabrique les options js pour le diagramme graphique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// $tab_graph_series n'est pas rangé avec des matières classées comme paramétré par l'admin car on avait besoin de récupérer les scores dans l'ordre chnonologique
// Pas grave puisqu'il vaut mieux de toutes façons présenter par ordre alphabétique des matières.
asort($tab_matiere);

$js_graph = '<SCRIPT>';
// Échelle sur l'axe des ordonées
$ymax = ($conversion_sur_20) ? 20 : 100 ;
$js_graph .= 'ChartOptions.yAxis = [ { min: 0, max: '.$ymax.', title: null } , { min: 0, max: '.$ymax.', title: null, opposite: true } ];';
// Séries de valeurs
$tab_graph_series = array();
if(count($tab_graph_data))
{
  foreach($tab_matiere as $matiere_id => $matiere_nom)
  {
    if(isset($tab_graph_data[$matiere_id]))
    {
      $tab_serie = array();
      foreach($tab_graph_data[$matiere_id] as $date_js => $valeur)
      {
        $tab_serie[] = '['.$date_js.','.$valeur.']';
      }
      $tab_graph_series[] = '{name:"'.$matiere_nom.'",data:['.implode(',',$tab_serie).']}';
    }
  }
}
$js_graph .= 'ChartOptions.series = ['.implode(',',$tab_graph_series).'];';
$js_graph .= 'graphique = new Highcharts.Chart(ChartOptions);';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit($js_graph);

?>
