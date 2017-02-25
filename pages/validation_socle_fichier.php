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
$TITRE = html(Lang::_("Import / Export de validations du socle"));
?>

<?php
// Test pour l'export
$nb_eleves_sans_sconet = DB_STRUCTURE_SOCLE::DB_compter_eleves_actuels_sans_id_sconet();
$s = ($nb_eleves_sans_sconet>1) ? 's' : '' ;

$test_uai            = ($_SESSION['WEBMESTRE_UAI'])                                     ? TRUE : FALSE ;
$test_cnil           = (intval(CNIL_NUMERO)&&CNIL_DATE_ENGAGEMENT&&CNIL_DATE_RECEPISSE) ? TRUE : FALSE ;
$test_id_sconet      = (!$nb_eleves_sans_sconet)                                        ? TRUE : FALSE ;
$test_key_sesamath   = ( $_SESSION['SESAMATH_KEY'] && $_SESSION['SESAMATH_ID'] )        ? TRUE : FALSE ;
$webmestre_menu_uai  = (HEBERGEUR_INSTALLATION=='multi-structures') ? '[Gestion des inscriptions] [Gestion des établissements]' : '[Paramétrages installation] [Identité de l\'installation]' ;
$webmestre_menu_cnil = '[Paramétrages installation] [Identité de l\'installation]';

$msg_uai          = ($test_uai)          ? '<label class="valide">Référence '.html($_SESSION['WEBMESTRE_UAI']).'</label>'                                                                                              : '<label class="erreur">Référence non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.HtmlMail::to(WEBMESTRE_COURRIEL,'SACoche - référence UAI','contact','Bonjour,<br />La référence UAI de notre établissement (base n°'.$_SESSION['BASE'].') n\'est pas renseignée.<br />Pouvez-vous faire le nécessaire depuis votre menu '.$webmestre_menu_uai.' ?<br />Merci.') ;
$msg_cnil         = ($test_cnil)         ? '<label class="valide">Déclaration n°'.html(CNIL_NUMERO).' - demande effectuée le '.html(CNIL_DATE_ENGAGEMENT).' - récépissé reçu le '.html(CNIL_DATE_RECEPISSE).'</label>' : '<label class="erreur">Déclaration non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.HtmlMail::to(WEBMESTRE_COURRIEL,'SACoche - Informations CNIL','contact','Bonjour,<br />Les informations CNIL de l\'installation '.URL_INSTALL_SACOCHE.' ne sont pas renseignées.<br />Pouvez-vous faire le nécessaire depuis votre menu '.$webmestre_menu_cnil.' ?<br />Merci.') ;
$msg_id_sconet    = ($test_id_sconet)    ? '<label class="valide">Identifiants élèves présents.</label>'                                                                                                   : '<label class="alerte">'.$nb_eleves_sans_sconet.' élève'.$s.' trouvé'.$s.' sans identifiant Sconet.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_sconet">DOC</a></span>' ;
$msg_key_sesamath = ($test_key_sesamath) ? '<label class="valide">Etablissement identifié sur le serveur communautaire.</label>'                                                                           : '<label class="erreur">Identification non effectuée par un administrateur.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure">DOC</a></span>' ;

$bouton_export_lpc = ($test_uai && $test_cnil && $test_key_sesamath) ? 'id="bouton_export" class="fichier_export enabled"' : 'id="disabled_export" class="fichier_export" disabled' ; /* la classe .enabled sert pour javascript */
?>

<?php
// Fabrication des éléments select du formulaire
$select_cycle     = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_socle2016_cycles( TRUE /*only_used*/ )                                 , 'f_cycle'  /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );
$select_f_groupes = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( TRUE /*sans*/ , TRUE /*tout*/ , TRUE /*ancien*/ ) , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
?>

<p class="probleme">
  Un export vers GEPI des estimations des degrés de maîtrise du nouveau socle (2016) a été ajouté.<br />
  En dehors de ce cas précis, cette section ne concerne le socle commun que sur la période 2006-2015.<br />
  L'application nationale LPC ayant été arrêtée depuis la rentrée 2016, son export a été retiré afin d'éviter toute confusion.<br />
  Concernant le nouveau socle, utiliser le module "Livret Scolaire" de <em>SACoche</em> pour un export vers LSU.
</p>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__socle_export_import">DOC : Import / Export de validations du socle</a></span></p>

<hr />

<form action="#" method="post" id="form_principal">

  <fieldset>
    <label class="tab" for="f_choix_principal">Procédure :</label>
    <select id="f_choix_principal" name="f_choix_principal">
      <option value="">&nbsp;</option>
      <optgroup label="Exporter un fichier">
        <option style="color:green" value="export_gepi">à destination de GEPI (socle 2016)</option>
        <option disabled            value="export_lpc">à destination de Sconet-LPC (2006-2015)</option>
        <option style="color:red"   value="export_sacoche">à destination de SACoche (2006-2015)</option>
      </optgroup>
      <optgroup label="Importer un fichier">
        <option disabled          value="import_lpc">en provenance de Sconet-LPC (2006-2015)</option>
        <option style="color:red" value="import_sacoche">en provenance de SACoche (2006-2015)</option>
        <option style="color:red" value="import_compatible">en provenance de Gibii, Pronote, etc. (2006-2015)</option>
      </optgroup>
    </select><br />
  </fieldset>

</form>

<form action="#" method="post" id="form_export">

  <fieldset id="fieldset_export" class="hide">
    <hr />
    <p id="bloc_cycle" class="hide">
      <label class="tab" for="f_cycle">Cycle :</label><?php echo $select_cycle ?>
    </p>
    <p>
      <label class="tab">Regroupement :</label><?php echo $select_f_groupes ?><label id="ajax_msg_groupe">&nbsp;</label><br />
      <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
    </p>
  </fieldset>

  <fieldset id="fieldset_export_gepi" class="hide">
    <label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><br />
    <span class="tab"></span><button type="button" id="export_gepi" class="fichier_export enabled">Générer le fichier.</button><?php /* la classe .enabled sert pour javascript */ ?>
  </fieldset>

  <fieldset id="fieldset_export_lpc" class="hide">
    <label class="tab">UAI :</label><?php echo $msg_uai ?><br />
    <label class="tab">CNIL :</label><?php echo $msg_cnil ?><br />
    <label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><br />
    <label class="tab">Sésamath :</label><?php echo $msg_key_sesamath ?>
    <p><span class="tab"></span><button type="button" id="export_lpc" <?php echo $bouton_export_lpc ?>>Générer le fichier.</button><label id="ajax_msg_export">&nbsp;</label></p>
  </fieldset>

  <fieldset id="fieldset_export_sacoche" class="hide">
    <label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><br />
    <span class="tab"></span><button type="button" id="export_sacoche" class="fichier_export enabled">Générer le fichier.</button><?php /* la classe .enabled sert pour javascript */ ?>
  </fieldset>

</form>

<form action="#" method="post" id="form_import">

  <fieldset id="fieldset_import" class="hide">
    <hr /><input type="hidden" id="f_action" name="f_action" value="" /><input id="f_import" type="file" name="userfile" />
  </fieldset>

  <fieldset id="fieldset_import_lpc" class="hide">
    <label class="tab">Sconet :</label><?php echo $msg_id_sconet ?>
    <p><span class="tab"></span><button type="button" id="import_lpc_disabled" disabled class="fichier_import">A notre connaissance, <em>LPC</em> ne permet pas d'exporter un fichier de validations&hellip;</button></p>
  </fieldset>

  <fieldset id="fieldset_import_sacoche" class="hide">
    <label class="tab">Sconet :</label><?php echo $msg_id_sconet ?>
    <p><span class="tab"></span><button type="button" id="import_sacoche" class="fichier_import enabled">Transmettre le fichier.</button></p><?php /* la classe .enabled sert pour javascript */ ?>
  </fieldset>

  <fieldset id="fieldset_import_compatible" class="hide">
    <p class="astuce">On peut importer dans <em>SACoche</em> un fichier obtenu depuis un logiciel compatible avec <em>LPC</em> : <em>Gibii</em>, <em>Pronote</em>, <em>Educ-Horus</em>, <em>Campus</em>, etc.</p>
    <label class="tab">Sconet :</label><?php echo $msg_id_sconet ?>
    <p><span class="tab"></span><button type="button" id="import_compatible" class="fichier_import enabled">Transmettre le fichier.</button></p><?php /* la classe .enabled sert pour javascript */ ?>
  </fieldset>

</form>

<hr />
<label id="ajax_msg">&nbsp;</label>
<ul class="puce p" id="ajax_info">
</ul>
