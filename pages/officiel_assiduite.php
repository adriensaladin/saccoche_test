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
$TITRE = "Absences / Retards";

if( ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :<div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Formulaire de choix d'une période (utilisé deux fois)
// Formulaire des classes
if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || ($_SESSION['USER_PROFIL_TYPE']=='directeur') )
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);
}
elseif($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  if(test_droit_specifique_restreint($_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'],'ONLY_PP'))
  {
    $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_prof_principal($_SESSION['USER_ID']);
  }
  else
  {
    $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_classes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/) ;
  }
}

$select_periode = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl() ,      FALSE /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/);
$select_groupe  = Form::afficher_select($tab_groupes                                 , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/);

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var date_mysql = "'.TODAY_MYSQL.'";';
// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__officiel_assiduite">DOC : Bilan officiel - Absences &amp; Retards</a></span></div>

<hr />

<h2>Import de fichier</h2>
<form action="#" method="post" id="form_fichier">
  <!-- Pour la gestion de plusieurs imports, prendre modèle sur les fichiers validation_socle_fichier.* -->
  <p>
    <label class="tab" for="f_periode_import">Période :</label><select id="f_periode_import" name="f_periode_import"><?php echo $select_periode ?></select><br />
    <label class="tab" for="f_choix_principal">Origine :</label>
    <select id="f_choix_principal" name="f_choix_principal">
      <option value=""></option>
      <option value="import_siecle">issu de SIÈCLE</option>
      <option value="import_gepi">issu de GEPI</option>
    </select>
  </p>
  <ul class="puce hide" id="puce_import_siecle">
    <li>Indiquer le fichier <em>SIECLE_exportAbsence.xml</em> : <button type="button" id="import_siecle" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_siecle">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_gepi">
    <li>Indiquer le fichier <em>extraction_abs_plus_*.csv</em> : <button type="button" id="import_gepi" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_gepi">&nbsp;</label></li>
  </ul>
</form>

<hr />

<h2>Saisie / Modification manuelle</h2>
<form action="#" method="post" id="form_manuel">
  <p>
    <label class="tab" for="f_groupe">Classe :</label><?php echo $select_groupe ?><br />
    <label class="tab" for="f_periode">Période :</label><select id="f_periode" name="f_periode" class="hide"><?php echo $select_periode ?></select><br />
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="afficher_formulaire_manuel" /><button id="valider_manuel" type="submit" class="modifier">Saisir.</button><label id="ajax_msg_manuel">&nbsp;</label>
  </p>
</form>

<hr />

<div id="zone_confirmer" class="hide">
  <h2>Confirmation d'import</h2>
  <div class="hide" id="comfirm_import_siecle">
    <p class="astuce">Ce fichier, généré le <b id="date_export"></b>, comporte les données de la période <b id="periode_libelle"></b>, allant du <b id="periode_date_debut"></b> au <b id="periode_date_fin"></b>.</p>
  </div>
  <div class="hide" id="comfirm_import_gepi">
    <p class="astuce">Ce fichier comporte les données de <b id="eleves_nb"></b> élève(s).</p>
  </div>
  <p>Confirmez-vous vouloir importer ces données dans <em>SACoche</em> pour la période <b id="periode_import"></b> ?</p>
  <form action="#" method="post">
    <p>
      <span class="tab"></span><button id="confirmer_import" type="button" class="valider">Confirmer.</button> <button id="fermer_zone_confirmer" type="button" class="annuler">Annuler.</button><label id="ajax_msg_confirm">&nbsp;</label>
    </p>
  </form>
</div>

<div id="zone_saisir" class="hide">
  <h2>Saisie des absences et retards | Résultat du traitement</h2>
  <p>
    <b id="titre_saisir"></b>
  </p>
  <table id="table_saisir" class="bilan">
    <thead><tr><th>Élève</th><th>Absences<br />nb &frac12; journées</th><th>dont &frac12; journées<br />non justifiées</th><th>Nb retards</th></tr></thead>
    <tbody><tr><td colspan="4"></td></tr></tbody>
  </table>
  <form action="#" method="post">
    <p>
      <span class="tab"></span><button id="Enregistrer_saisies" type="button" class="valider">Enregistrer les saisies</button> <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button><label id="ajax_msg_saisir"></label>
    </p>
  </form>
</div>
