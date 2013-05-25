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

// Détecter si usage d'un appareil mobile (tablette, téléphone...) auquel cas on propose un mode de saisie des acquisitions adapté.
$MobileDetect = new MobileDetect();
$isMobile = $MobileDetect->isMobile();

// Réception d'un formulaire depuis un tableau de synthèse bilan
// Dans ce cas il s'agit d'une évaluation sur une sélection d'élèves.
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$tab_items = ( isset($_POST['id_item']) && is_array($_POST['id_item']) ) ? $_POST['id_item'] : array() ;
$tab_items = Clean::map_entier($tab_items);
$tab_items = array_filter($tab_items,'positif');
$nb_items  = count($tab_items);
$txt_items = ($nb_items) ? ( ($nb_items>1) ? $nb_items.' items' : $nb_items.' item' ) : 'aucun' ;
$tab_users = ( isset($_POST['id_user']) && is_array($_POST['id_user']) ) ? $_POST['id_user'] : array() ;
$tab_users = Clean::map_entier($tab_users);
$tab_users = array_filter($tab_users,'positif');
$nb_users  = count($tab_users);
$txt_users = ($nb_users) ? ( ($nb_users>1) ? $nb_users.' élèves' : $nb_users.' élève' ) : 'aucun' ;
$reception_todo = ($nb_items || $nb_users) ? 'true' : 'false' ;
$script_reception = 'var reception_todo = '.$reception_todo.';';
$script_reception.= 'var reception_items_texte = "'.$txt_items.'";';
$script_reception.= 'var reception_users_texte = "'.$txt_users.'";';
$script_reception.= 'var reception_items_liste = "'.implode('_',$tab_items).'";';
$script_reception.= 'var reception_users_liste = "'.implode('_',$tab_users).'";';

// $TYPE vaut "groupe" ou "selection"
$TYPE = ($nb_items || $nb_users)                    ? 'selection' : $SECTION ;
$TYPE = in_array($TYPE,array('groupe','selection')) ? $TYPE       : 'groupe' ;

$TITRE = ($TYPE=='groupe') ? "Évaluer une classe ou un groupe" : "Évaluer des élèves sélectionnés" ;

require(CHEMIN_DOSSIER_INCLUDE.'fonction_affichage_sections_communes.php');

// Formulaires de choix des élèves et de choix d'une période dans le cas d'une évaluation sur un groupe
$select_eleve   = '';
$select_periode = '';
$tab_niveau_js  = 'var tab_niveau = new Array();';
$tab_groupe_js  = 'var tab_groupe = new Array();';

if($TYPE=='groupe')
{
  // Élément de formulaire "f_aff_classe" pour le choix des élèves (liste des classes / groupes / besoins) du professeur, enregistré dans une variable javascript pour utilisation suivant le besoin, et utilisé pour un tri initial
  // Fabrication de tableaux javascript "tab_niveau" et "tab_groupe" indiquant le niveau et le nom d'un groupe
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  $tab_options = array('classe'=>'','groupe'=>'','besoin'=>'');
  foreach($DB_TAB as $DB_ROW)
  {
    $groupe = strtoupper($DB_ROW['groupe_type']{0}).$DB_ROW['groupe_id'];
    $tab_options[$DB_ROW['groupe_type']] .= '<option value="'.$groupe.'">'.html($DB_ROW['groupe_nom']).'</option>';
    $tab_niveau_js .= 'tab_niveau["'.$groupe.'"]="'.sprintf("%02u",$DB_ROW['niveau_ordre']).'";';
    $tab_groupe_js .= 'tab_groupe["'.$groupe.'"]="'.html($DB_ROW['groupe_nom']).'";';
  }
  foreach($tab_options as $type => $contenu)
  {
    if($contenu)
    {
      $select_eleve .= '<optgroup label="'.ucwords($type).'s">'.$contenu.'</optgroup>';
    }
  }
  // Élément de formulaire "f_aff_periode" pour le choix d'une période
  $select_periode = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl() , 'f_aff_periode' /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/);
  // On désactive les périodes prédéfinies pour le choix "toute classe / tout groupe" initialement sélectionné
  $select_periode = preg_replace( '#'.'value="([1-9].*?)"'.'#' , 'value="$1" disabled' , $select_periode );
}

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
$tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
list( $tab_groupe_periode_js ) = Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*return_jointure_periode*/ , FALSE /*return_jointure_niveau*/ );

$select_selection_items = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , 'f_selection_items' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/);
?>

<script type="text/javascript">
  var TYPE="<?php echo $TYPE ?>";
  // <![CDATA[
  var select_groupe = "<?php echo str_replace('"','\"','<option value=""></option>'.$select_eleve); ?>";
  // ]]>
  var url_export = "<?php echo URL_DIR_EXPORT ?>";
  var input_date = "<?php echo TODAY_FR ?>";
  var date_mysql = "<?php echo TODAY_MYSQL ?>";
  var input_autoeval = "<?php echo date("d/m/Y",mktime(0,0,0,date("m"),date("d")+7,date("Y"))) ?>"; // J + 1 semaine
  var isMobile = <?php echo (int)$isMobile; ?>;
  var tab_items    = new Array();
  var tab_profs    = new Array();
  var tab_eleves   = new Array();
  var tab_sujets   = new Array();
  var tab_corriges = new Array();
  <?php echo $script_reception ?>
  <?php echo $tab_niveau_js ?> 
  <?php echo $tab_groupe_js ?> 
  <?php echo $tab_groupe_periode_js ?> 
</script>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form_prechoix" class="hide"><fieldset>
<?php if($TYPE=='groupe'): ?>
  <label class="tab" for="f_aff_classe">Classe / groupe :</label><select id="f_aff_classe" name="f_aff_classe"><option value="d2">Toute classe / tout groupe</option><?php echo $select_eleve ?></select>
<?php endif; ?>
  <div id="zone_periodes">
    <label class="tab" for="f_aff_periode">Période :</label><?php echo $select_periode ?>
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo jour_fin_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </span><br />
    <span class="tab"></span><input type="hidden" name="f_action" value="lister_evaluations" /><input type="hidden" name="f_type" value="<?php echo $TYPE ?>" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg_prechoix">&nbsp;</label>
  </div>
</fieldset><hr />
</form>

<table id="table_action" class="form hsort t9 hide">
  <thead>
    <tr>
      <th>Date devoir</th>
      <th>Date visible</th>
      <th>Fin auto-éval.</th>
      <th><?php echo($TYPE=='groupe')?'Classe / Groupe':'Élèves'; ?></th>
      <th>Collègues</th>
      <th>Description</th>
      <th>Items</th>
      <th>Fichiers</th>
      <th>Saisies</th>
      <th class="nu"><q class="ajouter" title="Ajouter une évaluation."></q></th>
    </tr>
  </thead>
  <tbody>
    <tr><td class="nu" colspan="10"></td></tr>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Dupliquer | Supprimer une évaluation</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_date">Date du devoir :</label><input id="f_date" name="f_date" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </p>
    <p>
      <label class="tab" for="f_date_visible">Date de visibilité :</label><input id="box_visible" type="checkbox" checked /> <label for="box_visible">identique</label><span><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
      <label class="tab" for="f_date_autoeval">Fin auto-évaluation :</label><input id="box_autoeval" type="checkbox" checked /> <label for="box_autoeval">sans objet</label><span><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span>
    </p>
    <p>
      <?php if($TYPE=='groupe'): ?>
        <label class="tab" for="f_groupe">Classe / groupe :</label><select id="f_groupe" name="f_groupe"><option></option></select><br />
        <span id="alerte_groupe" class="hide danger b">Attention : si vous modifiez le groupe, alors les notes de l'évaluation seront effacées !<br />En cas de même évaluation sur plusieurs groupes, il faut la <span class="u">dupliquer</span> et non la <span class="u">modifier</span>.</span><br />
      <?php endif; ?>
      <?php if($TYPE=='selection'): ?>
        <label class="tab" for="f_eleve_nombre">Élèves :</label><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q><br />
      <?php endif; ?>
      <label class="tab" for="f_prof_nombre">Collègues :</label><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="" readonly /><q class="choisir_prof" title="Voir ou choisir les collègues."></q><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="" />
    <p>
      <label class="tab" for="f_description">Description :</label><input id="f_description" name="f_description" type="text" value="" size="50" maxlength="60" /><br />
      <label class="tab" for="f_compet_nombre">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="" readonly /><q class="choisir_compet" title="Voir ou choisir les items."></q><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="" />
    </p>
    <p class="astuce">
      Sujet et corrigé de l'évaluation peuvent être joints depuis l'interface principale.
      <input id="f_doc_sujet" name="f_doc_sujet" type="hidden" value="" />
      <input id="f_doc_corrige" name="f_doc_corrige" type="hidden" value="" />
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">Les notes associées à l'évaluation seront effacées !</p>
    <p>Confirmez-vous la suppression de l'évaluation &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_ref" name="f_ref" type="hidden" value="" /><input id="f_type" name="f_type" type="hidden" value="<?php echo $TYPE; ?>" /><input id="f_fini" name="f_fini" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <?php
  // Affichage de la liste des items pour toutes les matières d'un professeur, sur tous les niveaux
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( $_SESSION['USER_ID'] , 0 /*matiere_id*/ , 0 /*niveau_id*/ , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*socle_nom*/ );
  echo Html::afficher_arborescence_matiere_from_SQL( $DB_TAB , TRUE /*dynamique*/ , TRUE /*reference*/ , FALSE /*aff_coef*/ , FALSE /*aff_cart*/ , 'texte' /*aff_socle*/ , FALSE /*aff_lien*/ , TRUE /*aff_input*/ );
  ?>
  <p id="alerte_items" class="fluo"><span class="danger b">Une évaluation dont la saisie a commencé ne devrait pas voir ses items modifiés.<br />En particulier, retirer des items d'une évaluation efface les scores correspondants déjà saisis !</span></p>
  <div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
  <hr />
  <p>
    <label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
    <label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_profs" class="hide">
  <div class="astuce">Vous pouvez permettre à des collègues de co-saisir les notes de ce devoir (et de le dupliquer).</div>
  <?php echo afficher_form_element_checkbox_collegues() ?>
  <div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<?php if($TYPE=='selection'): ?>
<form action="#" method="post" id="zone_eleve" class="arbre_dynamique hide">
  <div><button id="indiquer_eleves_deja" type="button" class="eclair">Indiquer les élèves associés à une évaluation de même nom</button> depuis le <input id="f_date_deja" name="f_date_deja" size="9" type="text" value="<?php echo jour_debut_annee_scolaire('french'); ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><label id="msg_indiquer_eleves_deja"></label></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <?php echo afficher_form_element_checkbox_eleves_professeur(TRUE /*with_pourcent*/); ?>
  <p id="alerte_eleves" class="fluo"><span class="danger b">Une évaluation dont la saisie a commencé ne devrait pas voir ses élèves modifiés.<br />En particulier, retirer des élèves d'une évaluation efface les scores correspondants déjà saisis !</span></p>
  <div><span class="tab"></span><button id="valider_eleve" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_eleve" type="button" class="annuler">Annuler / Retour</button></div>
</form>
<?php endif; ?>

<form action="#" method="post" id="zone_upload" class="hide">
  <h2>Ajouter / Retirer un sujet ou une correction d'une évaluation</h2>
  <p class="hc b" id="titre_upload"></p>
  <p>
    <label class="tab">Sujet :</label><span id="span_sujet"></span> <button id="bouton_supprimer_sujet" type="button" class="supprimer">Retirer</button><br />
    <span class="tab"></span><button id="bouton_referencer_sujet" type="button" class="referencer_lien">Diriger vers ce lien externe.</button> <input id="f_adresse_sujet" name="f_adresse_sujet" maxlength="256" size="50" type="text" value="" /><br />
    <span class="tab"></span><button id="bouton_uploader_sujet" type="button" class="fichier_import">Envoyer un fichier à utiliser.</button> <?php echo FICHIER_TAILLE_MAX ?> Ko maxi, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre." />
  </p>
  <p>
    <label class="tab">Corrigé :</label><span id="span_corrige"></span> <button id="bouton_supprimer_corrige" type="button" class="supprimer">Retirer</button><br />
    <span class="tab"></span><button id="bouton_referencer_corrige" type="button" class="referencer_lien">Diriger vers ce lien externe.</button> <input id="f_adresse_corrige" name="f_adresse_corrige" maxlength="256" size="50" type="text" value="" /><br />
    <span class="tab"></span><button id="bouton_uploader_corrige" type="button" class="fichier_import">Envoyer un fichier à utiliser.</button> <?php echo FICHIER_TAILLE_MAX ?> Ko maxi, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre." />
  </p>
  <p><span class="tab"></span><button id="fermer_zone_upload" type="button" class="retourner">Retour</button><label id="ajax_document_upload">&nbsp;</label></p>
</form>

<form action="#" method="post" id="zone_ordonner" class="hide">
  <h2>Réordonner les items d'une évaluation</h2>
  <p class="b" id="titre_ordonner"></p>
  <ul id="sortable">
    <li></li>
  </ul>
  <p>
    <button id="valider_ordre" type="button" class="valider">Enregistrer cet ordre</button> <button id="fermer_zone_ordonner" type="button" class="retourner">Retour</button> <label id="ajax_msg_ordonner">&nbsp;</label>
    <input id="ordre_ref" type="hidden" value="" />
  </p>
</form>

<?php
// Fabrication des éléments select du formulaire
Form::load_choix_memo();
$select_cart_contenu = Form::afficher_select(Form::$tab_select_cart_contenu , 'f_contenu'     /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['cart_contenu'] /*selection*/ , '' /*optgroup*/);
$select_cart_detail  = Form::afficher_select(Form::$tab_select_cart_detail  , 'f_detail'      /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['cart_detail']  /*selection*/ , '' /*optgroup*/);
$select_orientation  = Form::afficher_select(Form::$tab_select_orientation  , 'f_orientation' /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['orientation']  /*selection*/ , '' /*optgroup*/);
$select_couleur      = Form::afficher_select(Form::$tab_select_couleur      , 'f_couleur'     /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['couleur']      /*selection*/ , '' /*optgroup*/);
$select_marge_min    = Form::afficher_select(Form::$tab_select_marge_min    , 'f_marge_min'   /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['marge_min']    /*selection*/ , '' /*optgroup*/);
?>

<form action="#" method="post" id="zone_imprimer" class="hide"><fieldset>
  <h2>Imprimer le cartouche d'une évaluation</h2>
  <p class="b" id="titre_imprimer"></p>
  <label class="tab" for="f_contenu">Remplissage :</label><?php echo $select_cart_contenu ?><br />
  <label class="tab" for="f_detail">Détail :</label><?php echo $select_cart_detail ?><br />
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Orientation :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_marge_min ?><br />
    <label class="tab">Restriction :</label><input type="checkbox" id="f_restriction_req" name="f_restriction_req" value="1" /> <label for="f_restriction_req">Uniquement les items ayant fait l'objet d'une demande d'évaluation (ou dont une note est saisie).</label>
  </div>
  <span class="tab"></span><button id="valider_imprimer" type="button" class="valider">Générer le cartouche</button> <button id="fermer_zone_imprimer" type="button" class="retourner">Retour</button> <label id="ajax_msg_imprimer">&nbsp;</label>
  <input id="imprimer_ref" name="f_ref" type="hidden" value="" />
  <input id="imprimer_date_fr" name="f_date_fr" type="hidden" value="" />
  <input id="imprimer_groupe_nom" name="f_groupe_nom" type="hidden" value="" />
  <input id="imprimer_description" name="f_description" type="hidden" value="" />
  <p id="zone_imprimer_retour"></p>
</fieldset></form>

<div id="zone_voir_repart" class="hide">
  <h2>Voir les répartitions des élèves à une évaluation</h2>
  <p class="b" id="titre_voir_repart"></p>
  <table id="table_voir_repart1" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  <p>
  <ul class="puce">
    <li><a id="export_voir_repart_quantitative_couleur" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec la répartition quantitative des scores (format <em>pdf</em> en couleurs).</span></a></li>
    <li><a id="export_voir_repart_quantitative_gris" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec la répartition quantitative des scores (format <em>pdf</em> monochrome).</span></a></li>
  </ul>
  </p>
  <p>
  <table id="table_voir_repart2" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  </p>
  <p>
  <ul class="puce">
    <li><a id="export_voir_repart_nominative_couleur" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec la répartition nominative des scores (format <em>pdf</em> en couleurs).</span></a></li>
    <li><a id="export_voir_repart_nominative_gris" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec la répartition nominative des scores (format <em>pdf</em> monochrome).</span></a></li>
  </ul>
  </p>
</div>

<!-- Sans onsubmit="return false" une soumission incontrôlée s'effectue quand on presse "entrée" dans le cas d'un seul élève évalué sur un seul item. -->
<form action="#" method="post" id="zone_saisir" class="hide" onsubmit="return false">
  <h2>Saisir les acquisitions à une évaluation</h2>
  <p>
    <b id="titre_saisir"></b> <button id="valider_saisir" type="button" class="valider">Enregistrer les saisies</button> <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button> <label id="ajax_msg_saisir"></label>
    <input id="saisir_ref" name="f_ref" type="hidden" value="" />
    <input id="saisir_date_fr" name="f_date_fr" type="hidden" value="" />
    <input id="saisir_date_visible" name="f_date_visible" type="hidden" value="" />
    <input id="saisir_description" name="f_description" type="hidden" value="" />
    <input id="saisir_fini" name="f_fini" type="hidden" value="" />
  </p>
  <table id="table_saisir" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  <div id="td_souris_container"><div class="td_souris">
    <img alt="RR" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/RR.gif" /><img alt="ABS" src="./_img/note/commun/h/ABS.gif" /><br />
    <img alt="R" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/R.gif" /><img alt="DISP" src="./_img/note/commun/h/DISP.gif" /><br />
    <img alt="V" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/V.gif" /><img alt="NN" src="./_img/note/commun/h/NN.gif" /><br />
    <img alt="VV" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/VV.gif" /><img alt="X" src="./_img/note/commun/h/X.gif" /><br />
    <img alt="REQ" src="./_img/note/commun/h/REQ.gif" />
  </div></div>
  <p class="ti">Note à reporter dans &hellip;
    <label for="f_report_cellule">[ <input type="radio" id="f_report_cellule" name="f_endroit_report_note" value="cellule" checked /> la cellule ]</label>
    <label for="f_report_colonne">[ <input type="radio" id="f_report_colonne" name="f_endroit_report_note" value="colonne" /> la <span class="u">C</span>olonne ]</label>
    <label for="f_report_ligne">[ <input type="radio" id="f_report_ligne" name="f_endroit_report_note" value="ligne" /> la <span class="u">L</span>igne ]</label>
    <label for="f_report_tableau">[ <input type="radio" id="f_report_tableau" name="f_endroit_report_note" value="tableau" /> le <span class="u">T</span>ableau ]</label>.
    <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_resultats#toggle_saisies_multiples ">DOC : Report multiple.</a></span>
  </p>
  <div>
    <a id="to_zone_saisir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée
    <div id="zone_saisir_deport" class="hide">
      <ul class="puce">
        <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></li>
        <li><a id="export_file_saisir_tableau_scores_csv" class="lien_ext" href=""><span class="file file_txt">Récupérer un fichier vierge pour une saisie déportée (format <em>csv</em>).</span></a></li>
        <li><a id="export_file_saisir_tableau_scores_vierge" class="lien_ext" href=""><span class="file file_pdf">Imprimer un tableau vierge utilisable pour un report manuel des notes (format <em>pdf</em>).</span></a></li>
        <li><button id="import_file" type="button" class="fichier_import">Envoyer un fichier de notes complété (format <em>csv</em>).</button><label id="msg_import">&nbsp;</label></li>
      </ul>
      <p class="astuce">Pour récupérer un fichier <em>csv</em> ou un tableau <em>pdf</em> avec les notes saisies, choisir "<em>Voir les acquisitions</em>".</p>
    </div>
  </div>
</form>

<div id="zone_voir" class="hide">
  <h2>Voir les acquisitions à une évaluation</h2>
  <p>
    <b id="titre_voir"></b> <button id="fermer_zone_voir" type="button" class="retourner">Retour</button> <label id="ajax_msg_voir"></label>
  </p>
  <table id="table_voir" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  <p>
    <a id="to_zone_voir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée &amp; archivage
    <div id="zone_voir_deport" class="hide">
      <ul class="puce">
        <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></li>
        <li><a id="export_file_voir_tableau_scores_csv" class="lien_ext" href=""><span class="file file_txt">Récupérer un fichier des scores pour une saisie déportée (format <em>csv</em>).</span></a></li>
        <li><a id="export_file_voir_tableau_scores_vierge" class="lien_ext" href=""><span class="file file_pdf">Imprimer un tableau vierge utilisable pour un report manuel des notes (format <em>pdf</em>).</span></a></li>
        <li><a id="export_file_voir_tableau_scores_couleur" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec les scores (format <em>pdf</em> en couleurs).</span></a></li>
        <li><a id="export_file_voir_tableau_scores_gris" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec les scores (format <em>pdf</em> monochrome).</span></a></li>
      </ul>
      <p class="astuce">Pour importer un fichier <em>csv</em> de notes complété, choisir "<em>Saisir les acquisitions</em>".</p>
    </div>
  </p>
</div>

<div id="zone_confirmer_fermer_saisir" class="hide">
  <p class="danger">Des saisies ont été effectuées, mais n'ont pas été enregistrées.</p>
  <p>Confirmez-vous vouloir quitter l'interface de saisie ?</p>
  <p>
    <button id="confirmer_fermer_zone_saisir" type="button" class="valider">Oui, je ne veux pas enregistrer</button>
    <button id="annuler_fermer_zone_saisir" type="button" class="annuler">Non, je reste sur l'interface</button>
  </p>
</div>

<!--  Clavier virtuel pour les dispositifs tactiles -->
<div id="cadre_tactile">
  <div><kbd id="kbd_37"><img alt="Gauche" src="./_img/fleche/fleche_g1.gif" /></kbd><kbd id="kbd_39"><img alt="Droite" src="./_img/fleche/fleche_d1.gif" /></kbd><kbd id="kbd_38"><img alt="Haut" src="./_img/fleche/fleche_h1.gif" /></kbd><kbd id="kbd_40"><img alt="Bas" src="./_img/fleche/fleche_b1.gif" /></kbd></div>
  <div><kbd id="kbd_97"><img alt="RR" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/RR.gif" /></kbd><kbd id="kbd_98"><img alt="R" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/R.gif" /></kbd><kbd id="kbd_99"><img alt="V" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/V.gif" /></kbd><kbd id="kbd_100"><img alt="VV" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/VV.gif" /></kbd></div>
  <div><kbd id="kbd_65"><img alt="ABS" src="./_img/note/commun/h/ABS.gif" /></kbd><kbd id="kbd_68"><img alt="DISP" src="./_img/note/commun/h/DISP.gif" /></kbd><kbd id="kbd_78"><img alt="NN" src="./_img/note/commun/h/NN.gif" /></kbd><kbd id="kbd_46"><img alt="X" src="./_img/note/commun/h/X.gif" /></kbd></div>
  <div><kbd id="kbd_80"><img alt="ABS" src="./_img/note/commun/h/REQ.gif" /></kbd><kbd style="visibility:hidden"></kbd><kbd id="kbd_13" class="img valider"></kbd><kbd id="kbd_27" class="img retourner"></kbd></div>
</div>
