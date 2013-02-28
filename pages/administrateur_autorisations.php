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
$TITRE = "Réglage des autorisations";

// Tableau avec les noms des profils activés dans l'établissement
$tab_profils_libelles = array();
$tab_profil_join_groupes  = array();
$tab_profil_join_matieres = array();
$tab_profil_join_groupes_js  = 'var tab_profil_join_groupes = new Array();';
$tab_profil_join_matieres_js = 'var tab_profil_join_matieres = new Array();';

$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_type,user_profil_join_groupes,user_profil_join_matieres,user_profil_nom_court_pluriel' /*listing_champs*/ , TRUE /*only_actif*/ );
$DB_TAB[] = array( 'user_profil_sigle' => 'ONLY_COORD' , 'user_profil_type' => '' , 'user_profil_join_groupes' => 0 , 'user_profil_join_matieres' => 0 , 'user_profil_nom_court_pluriel' => 'restriction aux<br />coordonnateurs<br />matières' );
$DB_TAB[] = array( 'user_profil_sigle' => 'ONLY_PP'    , 'user_profil_type' => '' , 'user_profil_join_groupes' => 0 , 'user_profil_join_matieres' => 0 , 'user_profil_nom_court_pluriel' => 'restriction aux<br />professeurs<br />principaux' );
foreach($DB_TAB as $DB_ROW)
{
  $tab_profils_libelles[$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_nom_court_pluriel'];
  $is_profil_join_groupe = ( ($DB_ROW['user_profil_type']=='professeur') && ($DB_ROW['user_profil_join_groupes']=='config') ) ? TRUE : FALSE ;
  $tab_profil_join_groupes[$DB_ROW['user_profil_sigle']] = $is_profil_join_groupe;
  $is_profil_join_groupe = ($is_profil_join_groupe) ? 'true' : 'false' ;
  $tab_profil_join_groupes_js .= 'tab_profil_join_groupes["'.$DB_ROW['user_profil_sigle'].'"] = '.$is_profil_join_groupe.';';
  $is_profil_join_matiere = ( ($DB_ROW['user_profil_type']=='professeur') && ($DB_ROW['user_profil_join_matieres']=='config') ) ? TRUE : FALSE ;
  $tab_profil_join_matieres[$DB_ROW['user_profil_sigle']] = $is_profil_join_matiere;
  $is_profil_join_matiere = ($is_profil_join_matiere) ? 'true' : 'false' ;
  $tab_profil_join_matieres_js .= 'tab_profil_join_matieres["'.$DB_ROW['user_profil_sigle'].'"] = '.$is_profil_join_matiere.';';
}

// Tableau avec les sigles des profils pouvant être proposés, ou à cocher par défaut
$tab_profils_possibles = array();
$tab_profils_possibles['dir_pers_pp']  = array(                  'DIR','ENS','IEX','ONLY_PP','DOC','EDU','AED','SUR','ORI','MDS','ADF');
$tab_profils_possibles['dir_prof_pp']  = array(                  'DIR','ENS','IEX','ONLY_PP');
$tab_profils_possibles['dir_pers']     = array(                  'DIR','ENS','IEX',          'DOC','EDU','AED','SUR','ORI','MDS','ADF');
$tab_profils_possibles['dir_cpe']      = array(                  'DIR',                            'EDU');
$tab_profils_possibles['dir']          = array(                  'DIR');
$tab_profils_possibles['pers_coord']   = array(                        'ENS','IEX',          'DOC','EDU','AED','SUR','ORI','MDS','ADF','ONLY_COORD');
$tab_profils_possibles['pers']         = array(                        'ENS','IEX',          'DOC','EDU','AED','SUR','ORI','MDS','ADF');
$tab_profils_possibles['tous']         = array('ELV','TUT','AVS','DIR','ENS','IEX',          'DOC','EDU','AED','SUR','ORI','MDS','ADF');
$tab_profils_possibles['parent_eleve'] = array('ELV','TUT','AVS');
$tab_profils_possibles['personne']     = array();

// Tableau avec les infos (titres, profils, options par défaut)
$tab_droits  = array
(
  "Validations du socle" => array
  (
    'dir_pers_pp',
    array( 'droit_validation_entree' , "valider des items du socle"             , 'dir_pers' ),
    array( 'droit_validation_pilier' , "valider des compétences du socle"       , 'dir_prof_pp' ),
    array( 'droit_annulation_pilier' , "annuler des validations de compétences" , 'dir' )
  ),
  "Gestion des référentiels de l'établissement" => array
  (
    'pers_coord',
    array( 'droit_gerer_referentiel' , "créer / modifier / paramétrer les référentiels" , 'pers_coord' ),
    array( 'droit_gerer_ressource'   , "associer des ressources aux items"              , 'pers' )
  ),
  "Consultation des référentiels de l'établissement" => array
  (
    'tous',
    array( 'droit_voir_referentiels'  , "visualiser les référentiels" , 'tous' ),
    array( 'droit_voir_grilles_items' , "accéder aux grilles d'items" , 'tous' )
  ),
  "Score d'un item &amp; état d'acquisition" => array
  (
    'tous',
    array( 'droit_voir_score_bilan' , "voir les scores des items (bilans)"     , 'tous' ),
    array( 'droit_voir_algorithme'  , "voir et simuler l'algorithme de calcul" , 'tous' )
  ),
  "Mot de passe" => array
  (
    'tous',
    array( 'droit_modifier_mdp' , "modifier son mot de passe" , 'tous' )
  ),
  "Relevé d'items (matière ou pluridisciplinaire) & Bilan chronologique" => array
  (
    'parent_eleve',
    array( 'droit_releve_etat_acquisition'   , "afficher la colonne / le graphique avec les états d'acquisitions"        , 'parent_eleve' ),
    array( 'droit_releve_moyenne_score'      , "afficher la ligne / la courbe avec la moyenne des scores d'acquisitions" , 'parent_eleve' ),
    array( 'droit_releve_pourcentage_acquis' , "afficher la ligne / la courbe avec le pourcentage d'items acquis"        , 'parent_eleve' ),
    array( 'droit_releve_conversion_sur_20'  , "ajouter la conversion en note sur 20"                                    , 'personne' )
  ),
  "Relevé de maîtrise du socle" => array
  (
    'parent_eleve',
    array( 'droit_socle_acces'              , "accéder au relevé avec les items évalués par item du socle" , 'parent_eleve' ),
    array( 'droit_socle_pourcentage_acquis' , "afficher les pourcentages d'items acquis"                   , 'parent_eleve' ),
    array( 'droit_socle_etat_validation'    , "afficher les états de validation saisis"                    , 'personne' )
  ),
  "Bilans officiels &rarr; Absences" => array
  (
    'dir_pers_pp',
    array( 'droit_officiel_saisir_assiduite'       , "importer / saisir les absences &amp; retards" , 'dir_cpe' )
  ),
  "Bilans officiels &rarr; Relevé d'évaluations" => array
  (
    'dir_pers_pp',
    array( 'droit_officiel_releve_modifier_statut'       , "modifier le statut (accès saisies&hellip;)" , 'dir' ),
    array( 'droit_officiel_releve_corriger_appreciation' , "corriger l'appréciation d'un collègue"      , 'dir' ),
    array( 'droit_officiel_releve_appreciation_generale' , "éditer l'appréciation générale"             , 'dir_prof_pp' ),
    array( 'droit_officiel_releve_impression_pdf'        , "générer la version PDF imprimable"          , 'dir' )
  ),
  "Bilans officiels &rarr; Bulletin scolaire" => array
  (
    'dir_pers_pp',
    array( 'droit_officiel_bulletin_modifier_statut'       , "modifier le statut (accès saisies&hellip;)" , 'dir' ),
    array( 'droit_officiel_bulletin_corriger_appreciation' , "corriger l'appréciation d'un collègue"      , 'dir' ),
    array( 'droit_officiel_bulletin_appreciation_generale' , "éditer l'appréciation générale"             , 'dir_prof_pp' ),
    array( 'droit_officiel_bulletin_impression_pdf'        , "générer la version PDF imprimable"          , 'dir' )
  ),
  "Bilans officiels &rarr; État de maîtrise du socle" => array
  (
    'dir_pers_pp',
    array( 'droit_officiel_socle_modifier_statut'       , "modifier le statut (accès saisies&hellip;)" , 'dir' ),
    array( 'droit_officiel_socle_corriger_appreciation' , "corriger l'appréciation d'un collègue"      , 'dir' ),
    array( 'droit_officiel_socle_appreciation_generale' , "éditer l'appréciation générale"             , 'dir_prof_pp' ),
    array( 'droit_officiel_socle_impression_pdf'        , "générer la version PDF imprimable"          , 'dir' )
  ),
  "Consultation des bilans officiels finalisés" => array
  (
    'tous',
    array( 'droit_officiel_releve_voir_archive'   , "accéder aux copies des relevés d'évaluations"      , 'dir_pers' ),
    array( 'droit_officiel_bulletin_voir_archive' , "accéder aux copies des bulletins scolaires"        , 'dir_pers' ),
    array( 'droit_officiel_socle_voir_archive'    , "accéder aux copies des états de maîtrise du socle" , 'dir_pers' ),
  )
);

$tab_init_js  = 'var tab_init = new Array();';
$affichage = '';

foreach($tab_droits as $titre => $tab_infos_paragraphe)
{
  $affichage .= '<h4>'.$titre.'</h4>';
  $affichage .= '<table class="vm_nug">';
  // ligne en tête
  $i_profils_possibles = array_shift($tab_infos_paragraphe);
  $affichage .= '<thead><tr><th class="nu"></th>';
  foreach($tab_profils_possibles[$i_profils_possibles] as $profil_sigle)
  {
    if(isset($tab_profils_libelles[$profil_sigle]))
    {
      $affichage .= '<th class="hc">'.$tab_profils_libelles[$profil_sigle].'</th>';
    }
  }
  $affichage .= '<th class="nu"></th></tr></thead>';
  // lignes avec boutons
  $affichage .= '<tbody>';
  foreach($tab_infos_paragraphe as $tab_infos_ligne)
  {
    list( $droit_key , $droit_txt , $i_profils_defaut ) = $tab_infos_ligne;
    $tab_init_js .= 'tab_init["'.$droit_key.'"] = new Array();';
    $affichage .= '<tr id="tr_'.$droit_key.'"><th>'.$droit_txt.'</th>';
    $tab_check = explode(',',$_SESSION[strtoupper($droit_key)]);
    $check_pp    = (in_array('ONLY_PP'   ,$tab_check)) ? TRUE : FALSE ;
    $check_coord = (in_array('ONLY_COORD',$tab_check)) ? TRUE : FALSE ;
    foreach($tab_profils_possibles[$i_profils_possibles] as $profil_sigle)
    {
      if(isset($tab_profils_libelles[$profil_sigle]))
      {
        $init = in_array($profil_sigle,$tab_profils_possibles[$i_profils_defaut]) ? 'true' : 'false' ;
        $tab_init_js .= 'tab_init["'.$droit_key.'"]["'.$profil_sigle.'"] = '.$init.';';
        $checked = (in_array($profil_sigle,$tab_check)) ? ' checked' : '' ;
        $color   = ($checked) ? ( ( ($check_pp && $tab_profil_join_groupes[$profil_sigle]) || ($check_coord && $tab_profil_join_matieres[$profil_sigle]) ) ? 'bj' : 'bv' ) : 'br' ;
        $affichage .= '<td class="hc '.$color.'"><input type="checkbox" name="'.$droit_key.'" value="'.$profil_sigle.'"'.$checked.' /></td>';
      }
    }
    $affichage .= '<td class="nu">&nbsp;<button name="initialiser" type="button" class="retourner">Par défaut</button> <button name="valider" type="button" class="parametre">Enregistrer</button> <label id="ajax_msg_'.$droit_key.'">&nbsp;</label></td></tr>';
  }
  $affichage .= '</tbody>';
  $affichage .= '</table>';
  $affichage .= '<hr />';
}
?>

<script type="text/javascript">
  <?php echo $tab_init_js ?>
  <?php echo $tab_profil_join_groupes_js ?>
  <?php echo $tab_profil_join_matieres_js ?>
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_autorisations">DOC : Réglage des autorisations</a></span></div>

<hr />

<form action="#" method="post" id="form_autorisations">
<?php echo $affichage ?>
</form>
<p>&nbsp;</p>