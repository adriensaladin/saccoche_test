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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$OBJET     = (isset($_POST['f_objet']))  ? Clean::texte($_POST['f_objet'])   : '';
$ACTION    = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])  : '';
$mode      = (isset($_POST['f_mode']))   ? Clean::texte($_POST['f_mode'])    : '';
$classe_id = (isset($_POST['f_classe'])) ? Clean::entier($_POST['f_classe']) : 0;
$groupe_id = (isset($_POST['f_groupe'])) ? Clean::entier($_POST['f_groupe']) : 0;
$eleve_id  = (isset($_POST['f_user']))   ? Clean::entier($_POST['f_user'])   : 0;
// Autres chaines spécifiques...

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$tab_action = array('initialiser','charger');

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || !$classe_id || ( (!$eleve_id)&&($ACTION!='initialiser') ) )
{
  exit('Erreur avec les données transmises !');
}

// On vérifie que la fiche brevet est bien accessible en modification et on récupère les infos associées (nom de la classe, id des élèves concernés avec lesquels l'intersection est faite ultérieurement).

$DB_ROW = DB_STRUCTURE_BREVET::DB_recuperer_brevet_classe_infos($classe_id);
if(empty($DB_ROW))
{
  exit('Classe sans élèves concernés !');
}
$BILAN_ETAT = $DB_ROW['fiche_brevet'];
$classe_nom = $DB_ROW['groupe_nom'];
$tab_id_eleves_avec_notes = explode(',',$DB_ROW['listing_user_id']);

if(!$BILAN_ETAT)
{
  exit('Fiche brevet introuvable !');
}
if(in_array($BILAN_ETAT,array('0absence','1vide')))
{
  exit('Fiche brevet interdite d\'accès pour cette action !');
}
if(!$DB_ROW['listing_user_id'])
{
  exit('Aucun élève concerné dans cette classe !');
}

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_IMPRESSION_PDF'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  $is_bouton_test_impression = TRUE;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage des données d'un élève (le premier si initialisation ; l'élève indiqué sinon)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Si besoin, fabriquer le formulaire avec la liste des élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves
$groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;

if($ACTION=='initialiser')
{
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
  if(empty($DB_TAB))
  {
    exit('Aucun élève trouvé dans ce regroupement !');
  }
  $tab_eleve_id = array();
  $form_choix_eleve = '<form action="#" method="post" id="form_choix_eleve"><div><b>'.html($classe_nom).' :</b> <button id="go_premier_eleve" type="button" class="go_premier">Premier</button> <button id="go_precedent_eleve" type="button" class="go_precedent">Précédent</button> <select id="go_selection_eleve" name="go_selection" class="b">';
  foreach($DB_TAB as $DB_ROW)
  {
    if(in_array($DB_ROW['user_id'],$tab_id_eleves_avec_notes))
    {
      $form_choix_eleve .= '<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</option>';
      $tab_eleve_id[] = $DB_ROW['user_id'];
    }
  }
  if(empty($tab_eleve_id))
  {
    exit('Aucun élève concerné dans ce regroupement !');
  }
  $form_choix_eleve .= '</select> <button id="go_suivant_eleve" type="button" class="go_suivant">Suivant</button> <button id="go_dernier_eleve" type="button" class="go_dernier">Dernier</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button>';
  $form_choix_eleve .= ($mode=='texte') ? ' <button id="change_mode" type="button" class="stats">Interface graphique</button>' : ' <button id="change_mode" type="button" class="texte">Interface détaillée</button>' ;
  $form_choix_eleve .= '</div></form><hr />';
  $eleve_id = $tab_eleve_id[0];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_action = 'consulter';
$make_html   = ($mode=='graphique') ? FALSE : TRUE ;
$make_pdf    = FALSE;
$make_graph  = ($mode=='graphique') ? TRUE : FALSE ;
$js_graph    = '';
$droit_corriger_appreciation = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_CORRIGER_APPRECIATION'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );

$groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
$groupe_nom     = $groupe_nom; // Déjà défini avant car on en avait besoin
$tab_eleve      = array($eleve_id); // tableau de l'unique élève à considérer
$liste_eleve    = (string)$eleve_id;
$tab_matiere_id = array();
require(CHEMIN_DOSSIER_INCLUDE.'noyau_brevet_fiches.php');
$nom_bilan_html = 'fiche_brevet_HTML';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='initialiser')
{
  exit('<h2>Consulter le contenu</h2>'.$form_choix_eleve.'<div id="zone_resultat_eleve">'.${$nom_bilan_html}.'</div>'.$js_graph);
}
else
{
  exit(${$nom_bilan_html}.$js_graph);
}

?>
