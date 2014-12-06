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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_demandes')){exit('Action désactivée pour la démo...');}

$action        = (isset($_POST['f_action']))        ? Clean::texte($_POST['f_action'])          : '';      // pour le form_prechoix
$action        = (isset($_POST['f_quoi']))          ? Clean::texte($_POST['f_quoi'])            : $action; // pour le form_gestion
$matiere_id    = (isset($_POST['f_matiere']))       ? Clean::entier($_POST['f_matiere'])        : 0;
$matiere_nom   = (isset($_POST['f_matiere_nom']))   ? Clean::texte($_POST['f_matiere_nom'])     : '';
$groupe_id     = (isset($_POST['f_groupe_id']))     ? Clean::entier($_POST['f_groupe_id'])      : 0;   // C'est l'id du groupe d'appartenance de l'élève, pas l'id du groupe associé à un devoir
$groupe_type   = (isset($_POST['f_groupe_type']))   ? Clean::texte($_POST['f_groupe_type'])     : '';
$groupe_nom    = (isset($_POST['f_groupe_nom']))    ? Clean::texte($_POST['f_groupe_nom'])      : '';

$qui           = (isset($_POST['f_qui']))           ? Clean::texte($_POST['f_qui'])             : '';
$date          = (isset($_POST['f_date']))          ? Clean::date_fr($_POST['f_date'])          : '';
$date_visible  = (isset($_POST['f_date_visible']))  ? Clean::date_fr($_POST['f_date_visible'])  : '';
$date_autoeval = (isset($_POST['f_date_autoeval'])) ? Clean::date_fr($_POST['f_date_autoeval']) : '';
$info          = (isset($_POST['f_info']))          ? Clean::texte($_POST['f_info'])            : '';
$devoir_ids    = (isset($_POST['f_devoir']))        ? Clean::texte($_POST['f_devoir'])          : '';
$suite         = (isset($_POST['f_suite']))         ? Clean::texte($_POST['f_suite'])           : '';
$message       = (isset($_POST['f_message']))       ? Clean::texte($_POST['f_message'])         : '' ;

$score         = (isset($_POST['score']))           ? Clean::entier($_POST['score'])            : -2; // normalement entier entre 0 et 100 ou -1 si non évalué

$tab_demande_id = array();
$tab_user_id    = array();
$tab_item_id    = array();
$tab_user_item  = array();
// Récupérer et contrôler la liste des items transmis
$tab_ids = (isset($_POST['ids'])) ? explode(',',$_POST['ids']) : array() ;
if(count($tab_ids))
{
  foreach($tab_ids as $ids)
  {
    $tab_id = explode('x',$ids);
    $tab_demande_id[] = $tab_id[0];
    $tab_user_id[]    = $tab_id[1];
    $tab_item_id[]    = $tab_id[2];
    $tab_user_item[]  = (int)$tab_id[1].'x'.(int)$tab_id[2];
  }
  $tab_demande_id = array_filter( Clean::map_entier($tab_demande_id)            ,'positif');
  $tab_user_id    = array_filter( Clean::map_entier(array_unique($tab_user_id)) ,'positif');
  $tab_item_id    = array_filter( Clean::map_entier(array_unique($tab_item_id)) ,'positif');
}
$nb_demandes = count($tab_demande_id);
$nb_users    = count($tab_user_id);
$nb_items    = count($tab_item_id);

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');
$tab_qui   = array('groupe','select');
$tab_suite = array('changer','retirer');

list($devoir_id,$devoir_groupe_id) = (substr_count($devoir_ids,'_')==1) ? explode('_',$devoir_ids) : array(0,0);

$tab_td_score_bad = array( '<td class="hc'       ,                                                                                         '</td>' );
$tab_td_score_bon = array( '<td class="hd label' , ' <q class="actualiser" title="Actualiser le score (enregistré lors de la demande)."></q></td>' );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une liste de demandes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$selection_matiere = ($matiere_id) ? TRUE : FALSE ;
$selection_groupe  = ($groupe_id)  ? TRUE : FALSE ;

if( ($action=='Afficher_demandes') && ( $matiere_nom || !$selection_matiere ) && ( ( (isset($tab_types[$groupe_type])) && $groupe_nom ) || !$selection_groupe ) )
{
  $retour = '';
  // Récupérer la liste des élèves concernés
  $DB_TAB = ($selection_groupe) ? DB_STRUCTURE_COMMUN::DB_OPT_eleves_regroupement($tab_types[$groupe_type],$groupe_id,$user_statut=1,$eleves_ordre='alpha') : DB_STRUCTURE_PROFESSEUR::DB_OPT_lister_eleves_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']) ;
  if(!is_array($DB_TAB))
  {
    exit($DB_TAB);  // Aucun élève trouvé. | Aucun élève ne vous est affecté.
  }
  $tab_eleves  = array();
  $tab_autres  = array();
  $tab_groupes = array();
  foreach($DB_TAB as $DB_ROW)
  {
    if( ($selection_groupe) || !isset($tab_eleves[ $DB_ROW['valeur']]) ) // Un élève peut être une classe + un groupe associé au prof ; dans ce cas on ne garde que la 1e entrée (la classe)
    {
      $tab_eleves[ $DB_ROW['valeur']] = $DB_ROW['texte'];
      $tab_autres[ $DB_ROW['valeur']] = $DB_ROW['texte'];
      $tab_groupes[$DB_ROW['valeur']] = ($selection_groupe) ? $groupe_nom : $DB_ROW['optgroup'] ;
    }
  }
  $listing_user_id = implode(',', array_keys($tab_eleves) );
  // Lister les demandes (et les messages associés)
  $fnom_export = 'messages_'.$_SESSION['BASE'].'_'.Clean::fichier($matiere_nom).'_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  $separateur = ';';
  $messages_html = '<table><thead><tr><th>Matière - Item</th><th>Groupe - Élève</th><th>Message(s)</th></tr></thead><tbody>';
  $fichier_csv = 'Matière'.$separateur.'Item Ref'.$separateur.'Item Nom'.$separateur.'Groupe'.$separateur.'Élève'.$separateur.'Score'.$separateur.'Date'.$separateur.'Message'."\r\n";
  $tab_demandes = array();
  $DB_TAB = DB_STRUCTURE_DEMANDE::DB_lister_demandes_prof( $_SESSION['USER_ID'] , $matiere_id , $listing_user_id );
  if(empty($DB_TAB))
  {
    exit('Aucune demande n\'a été formulée selon les critères indiqués !');
  }
  foreach($DB_TAB as $DB_ROW)
  {
    unset($tab_autres[$DB_ROW['eleve_id']]);
    $tab_demandes[] = $DB_ROW['demande_id'];
    $score  = ($DB_ROW['demande_score']!==NULL) ? $DB_ROW['demande_score'] : FALSE ;
    $date   = convert_date_mysql_to_french($DB_ROW['demande_date']);
    $statut = ($DB_ROW['demande_statut']=='eleve') ? 'demande non traitée' : 'évaluation en préparation' ;
    $dest   = ($DB_ROW['prof_id']==$_SESSION['USER_ID']) ? 'vous seul' : 'collègues concernés' ;
    $class  = ($DB_ROW['demande_statut']=='eleve') ? ' class="new"' : '' ;
    $matiere_nom = ($selection_matiere) ? $matiere_nom : $DB_ROW['matiere_nom'] ;
    $commentaire = ($DB_ROW['demande_messages']) ? 'oui <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.str_replace(array("\r\n","\r","\n"),'<br />',html(html($DB_ROW['demande_messages']))).'" />' : 'non' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    $messages_html .= '<tr><td>'.html($matiere_nom).'<br />'.html($DB_ROW['item_ref']).'</td><td>'.html($tab_groupes[$DB_ROW['eleve_id']]).'<br />'.html($tab_eleves[$DB_ROW['eleve_id']]).'</td><td>'.str_replace(array("\r\n","\r","\n"),'<br />',html($DB_ROW['demande_messages'])).'</td></tr>';
    $fichier_csv .= '"'.$matiere_nom.'"'.$separateur.'"'.$DB_ROW['item_ref'].'"'.$separateur.'"'.$DB_ROW['item_nom'].'"'.$separateur.'"'.$tab_groupes[$DB_ROW['eleve_id']].'"'.$separateur.'"'.$tab_eleves[$DB_ROW['eleve_id']].'"'.$separateur.'"'.$score.'"'.$separateur.'"'.$date.'"'.$separateur.'"'.$DB_ROW['demande_messages'].'"'."\r\n";
    // Afficher une ligne du tableau 
    $retour .= '<tr'.$class.'>';
    $retour .= '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['demande_id'].'x'.$DB_ROW['eleve_id'].'x'.$DB_ROW['item_id'].'" /></td>';
    $retour .= '<td class="label">'.html($matiere_nom).'</td>';
    $retour .= '<td class="label">'.html($DB_ROW['item_ref']).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html(html($DB_ROW['item_nom'])).'" /></td>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    $retour .= '<td class="label">$'.$DB_ROW['item_id'].'$</td>';
    $retour .= '<td class="label">'.html($tab_groupes[$DB_ROW['eleve_id']]).'</td>';
    $retour .= '<td class="label">'.html($tab_eleves[$DB_ROW['eleve_id']]).'</td>';
    $retour .= str_replace( $tab_td_score_bad , $tab_td_score_bon , Html::td_score( $score , 'score' /*methode_tri*/ , '' /*pourcent*/ ) );
    $retour .= '<td class="label">'.$date.'</td>';
    $retour .= '<td class="label">'.$dest.'</td>';
    $retour .= '<td class="label">'.$statut.'</td>';
    $retour .= '<td class="label">'.$commentaire.'</td>';
    $retour .= '</tr>';
  }
  $messages_html .= '</tbody></table>';
  // Calculer pour chaque item sa popularité (le nb de demandes pour les élèves affichés)
  $listing_demande_id = implode(',', $tab_demandes );
  $DB_TAB = DB_STRUCTURE_DEMANDE::DB_recuperer_item_popularite($listing_demande_id,$listing_user_id);
  $tab_bad = array();
  $tab_bon = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $s = ($DB_ROW['popularite']>1) ? 's' : '' ;
    $tab_bad[] = '$'.$DB_ROW['item_id'].'$';
    $tab_bon[] = '<i>'.sprintf("%02u",$DB_ROW['popularite']).'</i>'.$DB_ROW['popularite'].' demande'.$s;
  }
  // Enregistrer le csv des commentaires
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom_export.'.csv' , To::csv($fichier_csv) );
  // Inclure dans le retour la liste des élèves sans demandes et le tableau des commentaires
  $chaine_autres = ( $selection_matiere && $selection_groupe ) ? implode('<br />',$tab_autres) : 'sur choix d\'une matière et d\'un regroupement' ;
  exit('ok'.'<¤>'.'./force_download.php?fichier='.$fnom_export.'.csv'.'<¤>'.$messages_html.'<¤>'.'<td>'.$chaine_autres.'</td>'.'<¤>'.str_replace($tab_bad,$tab_bon,$retour));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Créer une nouvelle évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='creer') && in_array($qui,$tab_qui) && ( ($qui=='select') || ( (isset($tab_types[$groupe_type])) && $groupe_id ) ) && $date && $date_visible && $date_autoeval && $info && in_array($suite,$tab_suite) && $nb_demandes && $nb_users && $nb_items )
{
  // Dans le cas d'une évaluation sur une liste d'élèves sélectionnés,
  if($qui=='select')
  {
    // Commencer par créer un nouveau groupe de type "eval", utilisé uniquement pour cette évaluation (c'est transparent pour le professeur) ; y associe automatiquement le prof, en responsable du groupe
    $groupe_id = DB_STRUCTURE_PROFESSEUR::DB_ajouter_groupe_par_prof('eval','',0);
  }
  // Insérer l'enregistrement de l'évaluation
  $date_mysql          = convert_date_french_to_mysql($date);
  $date_visible_mysql  = convert_date_french_to_mysql($date_visible);
  $date_autoeval_mysql = convert_date_french_to_mysql($date_autoeval);
  $doc_sujet   = '';
  $doc_corrige = '';
  $devoir_id = DB_STRUCTURE_PROFESSEUR::DB_ajouter_devoir( $_SESSION['USER_ID'] , $groupe_id , $date_mysql , $info , $date_visible_mysql , $date_autoeval_mysql , $doc_sujet , $doc_corrige , $eleves_ordre='alpha' );
  // Dans le cas d'une évaluation sur une liste d'élèves sélectionnés,
  // Affecter tous les élèves choisis
  if($qui=='select')
  {
    DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_eleve($devoir_id,$groupe_id,$tab_user_id,'creer');
  }
  // Insérer les enregistrements des items de l'évaluation
  DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_item($devoir_id,$tab_item_id,'creer');
  // Insérer les scores 'REQ' pour indiquer au prof les demandes dans le tableau de saisie
  $info = 'À saisir ('.afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE).')';
  foreach($tab_user_item as $key)
  {
    list($eleve_id,$item_id) = explode('x',$key);
    DB_STRUCTURE_PROFESSEUR::DB_ajouter_saisie($_SESSION['USER_ID'],$eleve_id,$devoir_id,$item_id,$date_mysql,'REQ',$info,$date_visible_mysql);
  }
  // Pour terminer, on change le statut des demandes ou on les supprime
  $listing_demande_id = implode(',',$tab_demande_id);
  if($suite=='changer')
  {
    DB_STRUCTURE_DEMANDE::DB_modifier_demandes_statut($listing_demande_id,'prof',$message);
  }
  else
  {
    DB_STRUCTURE_DEMANDE::DB_supprimer_demandes_devoir($listing_demande_id);
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Compléter une évaluation existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='completer') && in_array($qui,$tab_qui) && ( ($qui=='select') || (isset($tab_types[$groupe_type])) ) && $devoir_id && $devoir_groupe_id && in_array($suite,$tab_suite) && $nb_demandes && $nb_users && $nb_items && $date && $date_visible )
{
  // Dans le cas d'une évaluation sur une liste d'élèves sélectionnés
  if($qui=='select')
  {
    // Il faut ajouter tous les élèves choisis
    DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_eleve($devoir_id,$devoir_groupe_id,$tab_user_id,'ajouter'); // ($devoir_groupe_id et non $groupe_id qui correspond à la classe d'origine des élèves...)
  }
  // Maintenant on peut modifier les items de l'évaluation
  DB_STRUCTURE_PROFESSEUR::DB_modifier_liaison_devoir_item($devoir_id,$tab_item_id,'ajouter');
  // Insérer les scores 'REQ' pour indiquer au prof les demandes dans le tableau de saisie
  $date_mysql         = convert_date_french_to_mysql($date);
  $date_visible_mysql = convert_date_french_to_mysql($date_visible);
  $info = 'À saisir ('.afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE).')';
  foreach($tab_user_item as $key)
  {
    list($eleve_id,$item_id) = explode('x',$key);
    DB_STRUCTURE_PROFESSEUR::DB_ajouter_saisie($_SESSION['USER_ID'],$eleve_id,$devoir_id,$item_id,$date_mysql,'REQ',$info,$date_visible_mysql);
  }
  // Pour terminer, on change le statut des demandes ou on les supprime
  $listing_demande_id = implode(',',$tab_demande_id);
  if($suite=='changer')
  {
    DB_STRUCTURE_DEMANDE::DB_modifier_demandes_statut($listing_demande_id,'prof',$message);
  }
  else
  {
    DB_STRUCTURE_DEMANDE::DB_supprimer_demandes_devoir($listing_demande_id);
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changer le statut pour "évaluation en préparation" ou "demande non traitée"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ( ($action=='changer_prof') || ($action=='changer_eleve') ) && $nb_demandes )
{
  $listing_demande_id = implode(',',$tab_demande_id);
  $statut = substr($action,8);
  DB_STRUCTURE_DEMANDE::DB_modifier_demandes_statut($listing_demande_id,$statut,$message);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer de la liste des demandes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='retirer') && $nb_demandes )
{
  $listing_demande_id = implode(',',$tab_demande_id);
  DB_STRUCTURE_DEMANDE::DB_supprimer_demandes_devoir($listing_demande_id);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Actualiser un score
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='actualiser_score') && ($nb_demandes==1) && ($nb_users==1) && ($nb_items==1) && ($score>-2) )
{
  $tab_devoirs = array();
  $DB_TAB = DB_STRUCTURE_DEMANDE::DB_lister_result_eleve_item( $tab_user_id[0] , $tab_item_id[0] );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_devoirs[] = array('note'=>$DB_ROW['note']);
  }
  $score_new = (count($tab_devoirs)) ? calculer_score($tab_devoirs,$DB_ROW['calcul_methode'],$DB_ROW['calcul_limite']) : FALSE ;
  if( ( ($score==-1) && ($score_new!==FALSE) ) || ( ($score>-1) && ($score_new!==$score) ) )
  {
    // maj score
    $score_new_bdd = ($score_new!=-1) ? $score_new : NULL ;
    DB_STRUCTURE_DEMANDE::DB_modifier_demande_score( $tab_demande_id[0] , $score_new_bdd );
  }
  $score_retour = str_replace( $tab_td_score_bad , $tab_td_score_bon , Html::td_score( $score_new , 'score' /*methode_tri*/ , '' /*pourcent*/ ) );
  exit($score_retour);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
