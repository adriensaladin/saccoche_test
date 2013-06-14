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
$TITRE = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? "Fiches brevet" :  "Étape n°5 - Fiches brevet" ;

// Lister les séries de Brevet en place
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_series_etablissement();
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucun élève n\'est associé à une série du brevet !<p>';
  echo ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? '<div class="astuce">Un administrateur ou directeur doit effectuer les étapes préliminaires.<div>' : '<div class="astuce"><a href="./index.php?page=brevet&amp;section=series">Effectuer l\'étape n°1.</a><div>' ;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
$tab_brevet_series = array();
foreach($DB_TAB as $DB_ROW)
{
  $tab_brevet_series[$DB_ROW['brevet_serie_ref']] = html($DB_ROW['brevet_serie_nom']);
}

// Vérifier que les séries de Brevet sont configurées
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_series_etablissement_non_configurees();
if(count($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    echo'<p class="danger">'.html($DB_ROW['brevet_serie_nom']).' &rarr; non configurée !<p>';
  }
  echo ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? '<div class="astuce">Un administrateur ou directeur doit effectuer les étapes préliminaires.<div>' : '<div class="astuce"><a href="./index.php?page=brevet&amp;section=epreuves">Effectuer l\'étape n°2</a> ou <a href="./index.php?page=brevet&amp;section=series">Rectifier l\'étape n°1.</a><div>' ;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Vérifier que des élèves ont des notes enregistrées, et récupérer les classes concernées
$listing_classes_concernees = DB_STRUCTURE_BREVET::DB_recuperer_brevet_listing_classes_editables();
if(!$listing_classes_concernees)
{
  echo'<p class="danger">Aucun élève d\'une classe n\'a de notes enregistrées pour les fiches brevet !<p>';
  echo ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? '<div class="astuce">Un administrateur ou directeur doit effectuer les étapes préliminaires.<div>' : '<div class="astuce"><a href="./index.php?page=brevet&amp;section=series">Effectuer l\'étape n°3.</a><div>' ;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
$tab_classes_concernees = explode(',',$listing_classes_concernees);

// Indication des profils pouvant modifier le statut d'une fiche brevet
$profils_modifier_statut = 'administrateurs (de l\'établissement)<br />'.afficher_profils_droit_specifique($_SESSION['DROIT_FICHE_BREVET_MODIFIER_STATUT'],'br');
// Indication des profils ayant accès à l'appréciation générale
$profils_appreciation_generale = afficher_profils_droit_specifique($_SESSION['DROIT_FICHE_BREVET_APPRECIATION_GENERALE'],'br');
// Indication des profils ayant accès à l'impression PDF
$profils_impression_pdf = 'administrateurs (de l\'établissement)<br />'.afficher_profils_droit_specifique($_SESSION['DROIT_FICHE_BREVET_IMPRESSION_PDF'],'br');
// Indication des profils ayant accès aux copies des impressions PDF
$profils_archives_pdf = 'administrateurs (de l\'établissement)<br />'.afficher_profils_droit_specifique($_SESSION['DROIT_FICHE_BREVET_VOIR_ARCHIVE'],'br');

// Droit de modifier le statut d'une fiche brevet (dans le cas PP, restera à affiner classe par classe...).
$affichage_formulaire_statut = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_FICHE_BREVET_MODIFIER_STATUT']) ;

$tab_etats = array
(
  '1vide'     => 'Vide (fermé)',
  '2rubrique' => '<span class="now">Saisies Profs</span>',
  '3synthese' => '<span class="now">Saisie Synthèse</span>',
  '4complet'  => 'Complet (fermé)'
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération et traitement des données postées, si formulaire soumis
// Pas de passage par la page ajax.php => protection contre attaques type CSRF ajoutée ici
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($affichage_formulaire_statut) && ($_SESSION['SESAMATH_ID']!=ID_DEMO) )
{
  $tab_ids  = (isset($_POST['classe_ids'])) ? explode(',',$_POST['classe_ids'])  : array() ;
  $new_etat = (isset($_POST['etat']))       ? Clean::texte($_POST['etat'])       : '' ;
  $tab_ids = array_intersect( array_filter( Clean::map_entier($tab_ids) , 'positif' ) , $tab_classes_concernees );
  if( count($tab_ids) && isset($tab_etats[$new_etat]) )
  {
    Session::verifier_jeton_anti_CSRF($PAGE);
    foreach($tab_ids as $classe_id)
    {
      DB_STRUCTURE_BREVET::DB_modifier_brevet_classe_etat($classe_id,$new_etat);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer l'état de la fiche brevet des classes concernées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_classe_etat = array();
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_classes_editables_etat($listing_classes_concernees);
foreach($DB_TAB as $DB_ROW)
{
  $tab_classe_etat[$DB_ROW['groupe_id']] = $DB_ROW['fiche_brevet'];
}

?>

<p>
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__notanet_fiches_brevet#toggle_etape5_fiches_brevet_etapes_processus">DOC : Notanet &amp; Fiches brevet &rarr; Fiches brevet</a></span><br />
  <span class="astuce"><?php echo($affichage_formulaire_statut) ? 'Vous pouvez utiliser l\'outil d\'<a href="./index.php?page=compte_message">affichage de messages en page d\'accueil</a> pour informer les professeurs de l\'ouverture à la saisie.' : '<a title="'.$profils_modifier_statut.'" href="#">Profils pouvant modifier le statut des fiches brevet.</a>' ; ?></span></li>
</p>
<div id="cadre_photo"><button id="voir_photo" type="button" class="voir_photo">Photo</button></div>
<hr />
<script type="text/javascript">
  var TODAY_FR   = "<?php echo TODAY_FR ?>";
  var USER_ID    = "<?php echo $_SESSION['USER_ID'] ?>";
  var CODE_TOTAL = "<?php echo CODE_BREVET_EPREUVE_TOTAL ?>";
</script>

<?php

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des classes de l'établissement ; on ne garde que les classes concernées.
// Utile pour les profils administrateurs / directeurs, et requis concernant les professeurs pour une recherche s'il est affecté à des groupes.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);

$tab_classe_etabl = array(); // tableau temporaire avec les noms des classes de l'établissement
if(is_array($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    if(in_array($DB_ROW['valeur'],$tab_classes_concernees))
    {
      $tab_classe_etabl[$DB_ROW['valeur']] = $DB_ROW['texte'];
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des classes accessibles à l'utilisateur.
// Indiquer celles potentiellement accessibles à l'utilisateur pour l'appréciation générale.
// Indiquer celles potentiellement accessibles à l'utilisateur pour l'impression PDF.
// Initialiser les cellules du tableau à afficher
//
// Pour les administrateurs et les directeurs, ce sont les classes de l'établissement.
// Mais attention, les fiches brevet ne sont définies que sur les classes, pas sur des groupes (car il ne peut y avoir qu'une seule fiche brevet par élève).
// Alors quand les professeurs sont associés à des groupes, il faut chercher de quelle(s) classe(s) proviennent les élèves et proposer autant de choix partiels... sur ces classes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$annee_session_brevet = annee_session_brevet();

$tab_classe = array(); // tableau important avec les droits [classe_id][0|groupe_id]
$tab_groupe = array(); // tableau temporaire avec les noms des groupes du prof
$tab_options_classes = array(); // Pour un futur formulaire select

// Préparation du tableau avec les cellules à afficher
$check = ($affichage_formulaire_statut) ? ' <q id="classe_check_all" class="cocher_tout" title="Tout cocher."></q><q id="classe_uncheck_all" class="cocher_rien" title="Tout décocher."></q>' : '' ;
$tab_affich = array(); // [classe_id_groupe_id] (ligne) ; les indices [title] sont ceux des intitulés
$tab_affich[0]['title'] = '<td class="nu"></td>' ;
$tab_affich[0]['fiche'] = '<th class="hc" id="session_'.$annee_session_brevet.'">Session '.$annee_session_brevet.$check.'</th>' ;

if($_SESSION['USER_PROFIL_TYPE']!='professeur') // administrateur | directeur
{
  $droit_modifier_statut       = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_FICHE_BREVET_MODIFIER_STATUT'])       );
  $droit_appreciation_generale = ( ($_SESSION['USER_PROFIL_TYPE']=='directeur')      && test_user_droit_specifique($_SESSION['DROIT_FICHE_BREVET_APPRECIATION_GENERALE']) );
  $droit_impression_pdf        = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_FICHE_BREVET_IMPRESSION_PDF'])        );
  $droit_voir_archives_pdf     = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_FICHE_BREVET_VOIR_ARCHIVE'])          );
  foreach($tab_classe_etabl as $classe_id => $classe_nom)
  {
    $tab_classe[$classe_id][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' , 'droit_voir_archives_pdf' );
    $tab_affich[$classe_id.'_0']['title'] = '<th id="groupe_'.$classe_id.'_0">'.html($classe_nom).'</th>' ;
    $tab_affich[$classe_id.'_0']['fiche'] = '<td class="hc">-</td>' ;
    $tab_options_classes[$classe_id.'_0'] = '<option value="'.$classe_id.'_0">'.html($classe_nom).'</option>';
  }
}
else // professeur
{
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['groupe_type']=='classe')
    {
      if(in_array($DB_ROW['groupe_id'],$tab_classes_concernees))
      {
        // Pour les classes, RAS
        $droit_modifier_statut       = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_MODIFIER_STATUT']       , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
        $droit_appreciation_generale = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_APPRECIATION_GENERALE'] , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
        $droit_impression_pdf        = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_IMPRESSION_PDF']        , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
        $droit_voir_archives_pdf     = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_VOIR_ARCHIVE']);
        $tab_classe[$DB_ROW['groupe_id']][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' );
        $tab_affich[$DB_ROW['groupe_id'].'_0']['title'] = '<th id="groupe_'.$DB_ROW['groupe_id'].'_0">'.html($DB_ROW['groupe_nom']).'</th>' ;
        $tab_affich[$DB_ROW['groupe_id'].'_0']['fiche'] = '<td class="hc">-</td>' ;
        $tab_options_classes[$DB_ROW['groupe_id'].'_0'] = '<option value="'.$DB_ROW['groupe_id'].'_0">'.html($DB_ROW['groupe_nom']).'</option>';
      }
    }
    else
    {
      // Pour les groupes, il faudra récupérer les classes dont sont issues les élèves
      $tab_groupe[$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
    }
  }
  if(count($tab_groupe))
  {
    // On récupère les classes dont sont issues les élèves des groupes et on complète $tab_classe
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_eleves_from_groupes( implode(',',array_keys($tab_groupe)) );
    foreach($tab_groupe as $groupe_id => $groupe_nom)
    {
      if(isset($DB_TAB[$groupe_id]))
      {
        foreach($DB_TAB[$groupe_id] as $tab)
        {
          if(in_array($tab['eleve_classe_id'],$tab_classes_concernees))
          {
            $classe_id = $tab['eleve_classe_id'];
            $droit_modifier_statut       = FALSE ;
            $droit_appreciation_generale = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_APPRECIATION_GENERALE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
            $droit_impression_pdf        = test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_IMPRESSION_PDF']        , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
            $tab_classe[$classe_id][$groupe_id] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' );
            $tab_affich[$classe_id.'_'.$groupe_id]['title'] = '<th id="groupe_'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id]).'<br />'.html($groupe_nom).'</th>' ;
            $tab_affich[$classe_id.'_'.$groupe_id]['fiche'] = '<td class="hc">-</td>' ;
            $tab_options_classes[$classe_id.'_'.$groupe_id] = '<option value="'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id].' - '.$groupe_nom).'</option>';
          }
        }
      }
    }
  }
}

if(!count($tab_classe))
{
  echo'<p class="danger">Aucune classe ni aucun groupe associé à votre compte n\'est actuellement concerné !</label></p>';
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Passer en revue les classes et les groupes et afficher ce qu'il faut en focntion de l'état de la fiche brevet (de la classe) et des droits.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_js_disabled = '';
$listing_classes_id = implode(',',array_keys($tab_classe));
$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_jointure_groupe_periode($listing_classes_id);
foreach($tab_classe as $classe_id => $tab_groupes)
{
  $etat = $tab_classe_etat[$classe_id];
  // État
  $affich_etat = '<span class="off_etat '.substr($etat,1).'">'.$tab_etats[$etat].'</span>';
  // images action : vérification
  if( ($etat=='2rubrique') || ($etat=='3synthese') )
  {
    $icone_verification = '<q class="detailler" title="Rechercher les saisies manquantes."></q>';
  }
  else
  {
    $icone_verification = '<q class="detailler_non" title="La recherche de saisies manquantes est sans objet lorsque l\'accès en saisie est fermé."></q>';
  }
  // images action : consultation contenu en cours d'élaboration (fiche HTML)
  if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
  {
    if($etat=='1vide')
    {
      $icone_voir_html = '<q class="voir_non" title="Consultation du contenu sans objet (fiche déclarée vide)."></q>';
    }
    else
    {
      $icone_voir_html = '<q class="voir" title="Consulter le contenu (format HTML)."></q>';
    }
  }
  else
  {
    $icone_voir_html = '';
  }
  // images action : consultation contenu finalisé (fiches PDF)
  if(!$droit_voir_archives_pdf)
  {
    $icone_voir_pdf = '<q class="voir_archive_non" title="Accès restreint aux copies des impressions PDF :<br />'.$profils_archives_pdf.'."></q>';
  }
  elseif($etat!='4complet')
  {
    $icone_voir_pdf = '<q class="voir_archive_non" title="Consultation de la fiche imprimée sans objet (fiche déclarée non finalisée)."></q>';
  }
  else
  {
    $icone_voir_pdf = '<q class="voir_archive" title="Consulter une copie de la fiche imprimée finalisée (format PDF)."></q>';
  }
  // Il n'y a pas que la ligne de la classe, il y a les lignes des groupes dont des élèves font partie de la classe
  // Les images action de saisie et d'impression dépendent du groupe
  foreach($tab_classe[$classe_id] as $groupe_id=> $tab_droits)
  {
    // checkbox de gestion
    if( ($affichage_formulaire_statut) && ($tab_droits['droit_modifier_statut']) )
    {
      $id = 'c'.$classe_id;
      $label_avant = '<label for="'.$id.'">' ;
      $checkbox    = ' <input id="'.$id.'" name="'.$id.'" type="checkbox" />';
      $label_apres = '</label>' ;
    }
    else
    {
      $label_avant = $checkbox = $label_apres = '' ;
    }
    // images action : saisie
    if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
    {
      if($etat=='2rubrique')
      {
        $icone_saisie = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? '<q class="modifier" title="Saisir les appréciations par épreuve."></q>' : '<q class="modifier_non" title="Accès réservé aux professeurs."></q>' ;
      }
      else
      {
        $icone_saisie = '<q class="modifier_non" title="Accès fermé aux saisies intermédiaires."></q>';
      }
    }
    else
    {
      $icone_saisie = '';
    }
    // images action : tamponner
    if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
    {
      if($etat=='3synthese')
      {
        $icone_tampon = ($tab_droits['droit_appreciation_generale']) ? '<q class="tamponner" title="Saisir l\'appréciation générale."></q>' : '<q class="tamponner_non" title="Accès restreint à la saisie de l\'appréciation générale :<br />'.$profils_appreciation_generale.'."></q>' ;
      }
      else
      {
        $icone_tampon = '<q class="tamponner_non" title="Accès fermé à la saisie de synthèse."></q>';
      }
    }
    else
    {
      $icone_tampon = '';
    }
    // images action : impression
    if($tab_droits['droit_impression_pdf'])
    {
      $icone_impression = ($etat=='4complet') ? '<q class="imprimer" title="Imprimer la fiche (PDF)."></q>' : '<q class="imprimer_non" title="L\'impression est possible une fois la fiche déclarée complète."></q>' ;
    }
    else
    {
      $icone_impression = '<q class="imprimer_non" title="Accès restreint à l\'impression PDF :<br />'.$profils_impression_pdf.'."></q>';
    }
    if($etat!='0absence')
    {
      $tab_affich[$classe_id.'_'.$groupe_id]['fiche'] = '<td id="cg_'.$classe_id.'_'.$groupe_id.'" class="hc notnow">'.$label_avant.$affich_etat.$checkbox.$label_apres.'<br />'.$icone_saisie.$icone_tampon.$icone_verification.$icone_voir_html.$icone_impression.$icone_voir_pdf.'</td>';
    }
    elseif($checkbox!='')
    {
      $tab_affich[$classe_id.'_'.$groupe_id]['fiche'] = '<td class="hc notnow">'.$label_avant.$affich_etat.$checkbox.$label_apres.'</td>';
    }
    else
    {
      $tab_affich[$classe_id.'_'.$groupe_id]['fiche'] = '<td class="hc notnow">'.$affich_etat.'</td>';
    }
    // tableau javascript pour desactiver ce qui est inaccessible
    $disabled_examiner = strpos($icone_verification,'detailler_non') ? 'true' : 'false' ;
    $disabled_imprimer = strpos($icone_impression  ,'imprimer_non')  ? 'true' : 'false' ;
    $disabled_voir_pdf = strpos($icone_voir_pdf    ,'archive_non')   ? 'true' : 'false' ;
    $tab_js_disabled .= 'tab_disabled["examiner"]["'.$classe_id.'_'.$groupe_id.'"]='.$disabled_examiner.';';
    $tab_js_disabled .= 'tab_disabled["imprimer"]["'.$classe_id.'_'.$groupe_id.'"]='.$disabled_imprimer.';';
    $tab_js_disabled .= 'tab_disabled["voir_pdf"]["'.$classe_id.'_'.$groupe_id.'"]='.$disabled_voir_pdf.';'."\r\n";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage d'un tableau js utilisé pour désactiver des options d'un select.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

echo'<script type="text/javascript">var tab_disabled = new Array();tab_disabled["examiner"] = new Array();tab_disabled["imprimer"] = new Array();tab_disabled["voir_pdf"] = new Array();'."\r\n".$tab_js_disabled.'</script>';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du tableau.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

echo'<table id="table_accueil"><thead>';
foreach($tab_affich as $ligne_id => $tab_colonne)
{
  echo '<tr>'.implode('',$tab_colonne).'</tr>'."\r\n";
  echo (!$ligne_id) ? '</thead><tbody>'."\r\n" : '' ;
}
echo'</tbody></table>';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du formulaire pour modifier les états d'accès.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($affichage_formulaire_statut)
{
  $tab_radio = array();
  foreach($tab_etats as $etat_id => $etat_text)
  {
    $tab_radio[] = '<label for="etat_'.$etat_id.'"><input id="etat_'.$etat_id.'" name="etat" type="radio" value="'.$etat_id.'" /> <span class="off_etat '.substr($etat_id,1).'">'.$etat_text.'</span></label>';
  }
  echo'
    <form action="#" method="post" id="cadre_statut">
      <h4>Accès / Statut : <img alt="" src="./_img/bulle_aide.png" title="Pour les cases cochées du tableau (classes uniquement)." /></h4>
      <div>'.implode('<br />',$tab_radio).'</div>
      <p><input id="classe_ids" name="classe_ids" type="hidden" value="" /><input id="csrf" name="csrf" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider</button><label id="ajax_msg_gestion">&nbsp;</label></p>
    </form>
  ';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire de choix des épreuves pour une recherche de saisies manquantes. -> zone_chx_rubriques
// Paramètres supplémentaires envoyés pour éviter d'avoir à les retrouver à chaque fois. -> form_hidden
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$form_hidden = '';
$tab_checkbox_rubriques = array();
// Lister les matières rattachées au prof
$listing_matieres_id = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? DB_STRUCTURE_COMMUN::DB_recuperer_matieres_professeur($_SESSION['USER_ID']) : '' ;
$form_hidden .= '<input type="hidden" id="f_listing_matieres" name="f_listing_matieres" value="'.$listing_matieres_id.'" />';
$tab_matieres_id = explode(',',$listing_matieres_id);
// Lister les épreuves par série de Brevet en place dans l'établissement
foreach($tab_brevet_series as $brevet_serie_ref => $brevet_serie_nom)
{
  $tab_checkbox_rubriques[$brevet_serie_ref] = '<h4>'.$brevet_serie_nom.'</h4>';
  $DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves($brevet_serie_ref);
  foreach($DB_TAB as $DB_ROW)
  {
    $checked = ( ($_SESSION['USER_PROFIL_TYPE']!='professeur') || count(array_intersect(explode(',',$DB_ROW['brevet_epreuve_choix_matieres']),$tab_matieres_id)) ) ? ' checked' : '' ;
    $tab_checkbox_rubriques[$brevet_serie_ref] .= '<label for="rubrique_'.$brevet_serie_ref.'_'.$DB_ROW['brevet_epreuve_code'].'"><input type="checkbox" name="f_rubrique[]" id="rubrique_'.$brevet_serie_ref.'_'.$DB_ROW['brevet_epreuve_code'].'" value="'.$brevet_serie_ref.'_'.$DB_ROW['brevet_epreuve_code'].'"'.$checked.' /> '.html($DB_ROW['brevet_epreuve_nom']).'</label><br />';
  }
  $tab_checkbox_rubriques[$brevet_serie_ref] .= '<label for="rubrique_'.$brevet_serie_ref.'_'.CODE_BREVET_EPREUVE_TOTAL.'"><input type="checkbox" name="f_rubrique[]" id="rubrique_'.$brevet_serie_ref.'_'.CODE_BREVET_EPREUVE_TOTAL.'" value="'.$brevet_serie_ref.'_'.CODE_BREVET_EPREUVE_TOTAL.'" /> <i>Avis de synthèse</i></label><br />';
}
?>

<form action="#" method="post" id="zone_chx_rubriques" class="hide">
  <h2>Rechercher des saisies manquantes</h2>
  <div class="astuce">La recherche sera dans tous les cas aussi restreinte aux épreuves où les élèves ont des notes reportées.</div>
  <p><a href="#zone_chx_rubriques" id="rubrique_check_all" class="cocher_tout">Toutes</a>&nbsp;&nbsp;&nbsp;<a href="#zone_chx_rubriques" id="rubrique_uncheck_all" class="cocher_rien">Aucune</a></p>
  <div class="prof_liste"><?php echo implode('</div><div class="prof_liste">',$tab_checkbox_rubriques) ?></div>
  <p style="clear:both"><span class="tab"></span><button id="lancer_recherche" type="button" class="rechercher">Lancer la recherche</button> <button id="fermer_zone_chx_rubriques" type="button" class="annuler">Annuler</button><label id="ajax_msg_recherche">&nbsp;</label></p>
</form>

<form action="#" method="post" id="form_hidden" class="hide">
  <div>
    <?php echo $form_hidden ?>
    <input type="hidden" id="f_objet" name="f_objet" value="" />
    <input type="hidden" id="f_listing_rubriques" name="f_listing_rubriques" value="" />
    <input type="hidden" id="f_listing_eleves" name="f_listing_eleves" value="" />
    <input type="hidden" id="f_mode" name="f_mode" value="texte" />
  </div>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaires utilisés pour les opérations ultérieures sur les bilans.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div id="zone_action_eleve"></div>

<div id="zone_action_classe" class="hide">
  <h2>Recherche de saisies manquantes | Imprimer le bilan (PDF)</h2>
  <form action="#" method="post" id="form_choix_classe"><div><button id="go_precedent_classe" type="button" class="go_precedent">Précédent</button> <select id="go_selection_classe" name="go_selection_classe" class="b"><?php echo implode('',$tab_options_classes) ?></select> <button id="go_suivant_classe" type="button" class="go_suivant">Suivant</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_classe" type="button" class="retourner">Retour</button></div></form>
  <hr />
  <div id="zone_resultat_classe"></div>
  <div id="zone_imprimer" class="hide">
    <form action="#" method="post" id="form_choix_eleves">
      <table id="table_action" class="form t9">
        <thead>
          <tr>
            <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>
            <th>Élèves</th>
            <th>Généré</th>
          </tr>
        </thead>
        <tbody>
          <tr><td class="nu" colspan="3"></td></tr>
        </tbody>
      </table>
    </form>
    <p class="ti">
      <button id="valider_imprimer" type="button" class="valider">Lancer l'impression</button><label id="ajax_msg_imprimer">&nbsp;</label>
    </p>
  </div>
  <div id="zone_voir_archive" class="hide">
    <p class="astuce">Ces documents ne sont que des copies informatives, laissées à disposition pour information jusqu'à la fin de l'année scolaire.<br /><span class="u">Seul le document original fait foi.</span></p>
    <table class="t9">
      <thead>
        <tr>
          <th>Élèves</th>
          <th>Généré</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="nu" colspan="2"></td></tr>
      </tbody>
    </table>
    <p class="ti">
      <label id="ajax_msg_voir_archive">&nbsp;</label>
    </p>
  </div>
</div>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire pour signaler ou corriger une faute dans une appréciation.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
$date_plus1semaine = date("d/m/Y",mktime(0,0,0,date("m"),date("d")+7,date("Y")));
?>

<form action="#" method="post" id="zone_signaler_corriger" class="hide" onsubmit="return false">
  <h2>Signaler | Corriger une faute</h2>
  <div id="section_corriger">
  </div>
  <div id="section_signaler">
    <div>
      <input type="hidden" value="<?php echo TODAY_FR ?>" name="f_debut_date" id="f_debut_date" />
      <input type="hidden" value="<?php echo $date_plus1semaine ?>" name="f_fin_date" id="f_fin_date" />
      <input type="hidden" value="" name="f_destinataires_liste" id="f_destinataires_liste" />
      <input type="hidden" value="signaler_faute|corriger_faute" name="f_action" id="f_action" />
      <label for="f_message_contenu" class="tab">Message informatif :</label><textarea name="f_message_contenu" id="f_message_contenu" rows="5" cols="100"></textarea><br />
      <span class="tab"></span><label id="f_message_contenu_reste"></label>
    </div>
    <p class="astuce">Le message est affiché en page d'accueil du collègue concerné pendant une semaine (jusqu'au <?php echo $date_plus1semaine ?>).</p>
  </div>
  <p>
    <span class="tab"></span><button id="valider_signaler_corriger" type="button" class="valider">Valider</button>&nbsp;&nbsp;&nbsp;<button id="annuler_signaler_corriger" type="button" class="annuler">Annuler / Retour</button><label id="ajax_msg_signaler_corriger">&nbsp;</label>
  </p>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liens pour archiver / imprimer des saisies.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div id="zone_archiver_imprimer" class="hide">
  <h2>Archiver / Imprimer des données</h2>
  <p class="noprint">Afin de préserver l'environnement, n'imprimer qu'en cas de nécessité !</p>
  <ul class="puce">
    <li><button id="imprimer_donnees_eleves_epreuves"  type="button" class="imprimer">Archiver / Imprimer</button> les appréciations par épreuve pour chaque élève.</li>
    <li><button id="imprimer_donnees_eleves_syntheses" type="button" class="imprimer">Archiver / Imprimer</button> les avis de synthèse pour chaque élève.</li>
    <li><button id="imprimer_donnees_eleves_moyennes"  type="button" class="imprimer">Archiver / Imprimer</button> le tableau des notes pour chaque élève.</li>
  </ul>
  <hr />
  <p><label id="ajax_msg_archiver_imprimer">&nbsp;</label></p>
</div>


