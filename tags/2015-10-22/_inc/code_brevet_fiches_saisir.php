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

$OBJET        = (isset($_POST['f_objet']))        ? Clean::texte($_POST['f_objet'])        : '';
$ACTION       = (isset($_POST['f_action']))       ? Clean::texte($_POST['f_action'])       : '';
$mode         = (isset($_POST['f_mode']))         ? Clean::texte($_POST['f_mode'])         : '';
$classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])      : 0;
$groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])      : 0;
$eleve_id     = (isset($_POST['f_user']))         ? Clean::entier($_POST['f_user'])        : 0;
$serie_ref    = (isset($_POST['f_serie']))        ? Clean::texte($_POST['f_serie'])        : '';
$epreuve_id   = (isset($_POST['f_epreuve']))      ? Clean::entier($_POST['f_epreuve'])     : 0;
$prof_id      = (isset($_POST['f_prof']))         ? Clean::entier($_POST['f_prof'])        : 0; // id du prof dont on corrige l'appréciation
$avis_conseil = (isset($_POST['f_avis_conseil'])) ? Clean::texte($_POST['f_avis_conseil']) : '';
$appreciation = (isset($_POST['f_appreciation'])) ? Clean::texte($_POST['f_appreciation']) : '';
// Autres chaines spécifiques...
$listing_matieres = (isset($_POST['f_listing_matieres'])) ? $_POST['f_listing_matieres'] : '' ;
$tab_matiere_id = array_filter( Clean::map_entier( explode(',',$listing_matieres) ) , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$tab_objet  = array('modifier','tamponner','voir'); // "voir" car on peut corriger une appréciation dans ce mode
$tab_action = array('initialiser','charger','enregistrer_appr','corriger_faute','supprimer_appr');
$tab_mode   = array('texte','graphique');

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!in_array($OBJET,$tab_objet)) || (!in_array($mode,$tab_mode)) || !$classe_id || ( (!$eleve_id)&&($ACTION!='initialiser') ) )
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
if(!in_array($OBJET.$BILAN_ETAT,array('modifier2rubrique','modifier3mixte','tamponner3mixte','tamponner4synthese','voir2rubrique','voir3mixte','voir4synthese'))) //  'voir*' est transmis dans le cas d'une correction de faute
{
  exit('Fiche brevet interdite d\'accès pour cette action !');
}
if(!$DB_ROW['listing_user_id'])
{
  exit('Aucun élève concerné dans cette classe !');
}

// Si un personnel accède à la saisie de synthèse, il ne faut pas seulement récupérer les données qui concerne ses matières.
$liste_matiere_id = ( ($OBJET=='modifier') || ($BILAN_ETAT=='2rubrique') ) ? $liste_matiere_id : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1 : enregistrement d'une appréciation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='enregistrer_appr')
{
  if( (!$appreciation) || (($BILAN_ETAT=='2rubrique')&&($epreuve_id==CODE_BREVET_EPREUVE_TOTAL)) || (($avis_conseil!='F')&&($avis_conseil!='D')&&($epreuve_id==CODE_BREVET_EPREUVE_TOTAL)) )
  {
    exit('Erreur avec les données transmises !');
  }
  $avis_et_appreciation = ($epreuve_id!=CODE_BREVET_EPREUVE_TOTAL) ? $appreciation : $avis_conseil.'|'.$appreciation ;
  DB_STRUCTURE_BREVET::DB_modifier_brevet_appreciation($serie_ref , $epreuve_id , $eleve_id , $_SESSION['USER_ID'] , $avis_et_appreciation);
  $prof_info = afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
  $ACTION = ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>';
  $txt_avis_conseil_classe = ($epreuve_id!=CODE_BREVET_EPREUVE_TOTAL) ? '' : ( ($avis_conseil=='F') ? '<div id="avis_conseil_classe" class="b">Avis favorable</div>' : '<div id="avis_conseil_classe" class="b">Doit faire ses preuves</div>' ) ;
  exit('<div class="notnow">'.html($prof_info).$ACTION.'</div><div class="appreciation">'.html($appreciation).'</div>'.$txt_avis_conseil_classe);
}

if($ACTION=='corriger_faute')
{
  if( (!$appreciation) || (!$prof_id) || (($avis_conseil!='F')&&($avis_conseil!='D')&&($epreuve_id==CODE_BREVET_EPREUVE_TOTAL)) )
  {
    exit('Erreur avec les données transmises !');
  }
  $avis_et_appreciation = ($epreuve_id!=CODE_BREVET_EPREUVE_TOTAL) ? $appreciation : $avis_conseil.'|'.$appreciation ;
  DB_STRUCTURE_BREVET::DB_modifier_brevet_appreciation($serie_ref , $epreuve_id , $eleve_id , $prof_id , $avis_et_appreciation);
  exit('<ok>'.html($appreciation));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2 : suppression d'une appréciation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='supprimer_appr')
{
  if( ($BILAN_ETAT=='2rubrique') && ($epreuve_id==CODE_BREVET_EPREUVE_TOTAL) )
  {
    exit('Erreur avec les données transmises !');
  }
  DB_STRUCTURE_BREVET::DB_modifier_brevet_appreciation($serie_ref , $epreuve_id , $eleve_id , 0 /*prof_id*/ , '' /*appreciation*/ );
  $ACTION = ($epreuve_id!=CODE_BREVET_EPREUVE_TOTAL) ? '<button type="button" class="ajouter">Ajouter l\'appréciation.</button>' : '<button type="button" class="ajouter">Ajouter l\'avis de synthèse.</button>' ;
  exit('<div class="hc">'.$ACTION.'</div>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3 & 4 : affichage des données d'un élève (le premier si initialisation ; l'élève indiqué sinon)
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
  $form_choix_eleve .= ($OBJET=='tamponner') ? ( ($mode=='texte') ? ' <button id="change_mode" type="button" class="stats">Interface graphique</button>' : ' <button id="change_mode" type="button" class="texte">Interface détaillée</button>' ) : '' ;
  $form_choix_eleve .= '</div></form><hr />';
  $eleve_id = $tab_eleve_id[0];
  // sous-titre
  $sous_titre = ($ACTION=='tamponner') ? 'Éditer l\'avis de synthèse' : 'Éditer les appréciations par épreuve' ;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_action = $OBJET; // 'modifier' || 'tamponner' (et plus seulement 'saisir')
$make_html   = ( ($OBJET=='tamponner') && ($mode=='graphique') ) ? FALSE : TRUE ;
$make_pdf    = FALSE;
$make_graph  = ( ($OBJET=='tamponner') && ($mode=='graphique') ) ? TRUE : FALSE ;
$js_graph    = '';
$droit_corriger_appreciation = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_CORRIGER_APPRECIATION'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );

$groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
$groupe_nom     = $groupe_nom; // Déjà défini avant car on en avait besoin
$tab_eleve      = array($eleve_id); // tableau de l'unique élève à considérer
$liste_eleve    = (string)$eleve_id;
$tab_matiere_id = $tab_matiere_id;
require(CHEMIN_DOSSIER_INCLUDE.'noyau_brevet_fiches.php');
$nom_bilan_html = 'fiche_brevet_HTML';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='initialiser')
{
  exit('<h2>'.$sous_titre.'</h2>'.$form_choix_eleve.'<form action="#" method="post" id="zone_resultat_eleve" onsubmit="return false">'.${$nom_bilan_html}.'</form>'.$js_graph);
}
else
{
  exit(${$nom_bilan_html}.$js_graph);
}

?>
