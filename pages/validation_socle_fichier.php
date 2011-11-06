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
$TITRE = "Import / Export de validations du socle";
$VERSION_JS_FILE += 1;
?>

<?php
// Test pour l'export
$nb_eleves_sans_sconet = DB_STRUCTURE_SOCLE::DB_compter_eleves_actifs_sans_id_sconet();
$s = ($nb_eleves_sans_sconet>1) ? 's' : '' ;

$test_uai          = ($_SESSION['UAI'])                                               ? TRUE : FALSE ;
$test_cnil         = (intval(CNIL_NUMERO)&&CNIL_DATE_ENGAGEMENT&&CNIL_DATE_RECEPISSE) ? TRUE : FALSE ;
$test_id_sconet    = (!$nb_eleves_sans_sconet)                                        ? TRUE : FALSE ;
$test_key_sesamath = ( $_SESSION['SESAMATH_KEY'] && $_SESSION['SESAMATH_ID'] )        ? TRUE : FALSE ;

$msg_uai          = ($test_uai)          ? '<label class="valide">Référence '.html($_SESSION['UAI']).'</label>'                                                                                            : '<label class="erreur">Référence non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.mailto(WEBMESTRE_COURRIEL,'SACoche','contact','Bonjour. La référence UAI de notre établissement (base n°'.$_SESSION['BASE'].') n\'est pas renseigné. Pouvez-vous faire le nécessaire ?') ;
$msg_cnil         = ($test_cnil)         ? '<label class="valide">Déclaration n°'.html(CNIL_NUMERO).' - demande effectuée le '.html(CNIL_DATE_ENGAGEMENT).' - récépissé reçu le '.html(CNIL_DATE_RECEPISSE).'</label>' : '<label class="erreur">Déclaration non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.mailto(WEBMESTRE_COURRIEL,'SACoche','contact','Bonjour. Les informations CNIL de l\'installation '.SERVEUR_ADRESSE.' ne sont pas renseignées. Pouvez-vous faire le nécessaire depuis votre menu [Administration du site] [Identité de l\'installation] ?') ;
$msg_id_sconet    = ($test_id_sconet)    ? '<label class="valide">Identifiants élèves présents.</label>'                                                                                                   : '<label class="alerte">'.$nb_eleves_sans_sconet.' élève'.$s.' trouvé'.$s.' sans identifiant Sconet.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_sconet">DOC</a></span>' ;
$msg_key_sesamath = ($test_key_sesamath) ? '<label class="valide">Etablissement identifié sur le serveur communautaire.</label>'                                                                           : '<label class="erreur">Identification non effectuée par un administrateur.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure">DOC</a></span>' ;

$bouton_export_lpc = ($test_uai && $test_cnil && $test_key_sesamath) ? 'id="bouton_export" class="enabled"' : 'id="disabled_export" disabled' ;
?>

<?php
// Fabrication des éléments select du formulaire
$select_f_groupes = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl() , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__socle_export_import">DOC : Import / Export de validations du socle</a></span></p>

<hr />

<form action="#" method="post">

	<fieldset>
		<label class="tab" for="f_choix_principal">Procédure :</label>
		<select id="f_choix_principal" name="f_choix_principal">
			<option value=""></option>
			<optgroup label="Exporter un fichier">
				<option value="export_lpc">à destination de Sconet-LPC</option>
				<option value="export_sacoche">à destination de SACoche</option>
			</optgroup>
			<optgroup label="Importer un fichier">
				<option value="import_lpc">en provenance de Sconet-LPC</option>
				<option value="import_sacoche">en provenance de SACoche</option>
			</optgroup>
		</select><br />
	</fieldset>

	<fieldset id="fieldset_export" class="hide">
		<hr />
		<label class="tab">Regroupement :</label><select id="f_groupe" name="f_groupe"><?php echo $select_f_groupes ?></select><label id="ajax_msg_groupe">&nbsp;</label><br />
		<label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élèves :</label><select id="select_eleves" name="select_eleves[]" multiple size="8"><option value=""></option></select><p />
	</fieldset>

	<fieldset id="fieldset_export_lpc" class="hide">
		<label class="tab">UAI :</label><?php echo $msg_uai ?><br />
		<label class="tab">CNIL :</label><?php echo $msg_cnil ?><br />
		<label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><br />
		<label class="tab">Sésamath :</label><?php echo $msg_key_sesamath ?><p />
		<span class="tab"></span><button type="button" id="disabled_export" disabled><img alt="" src="./_img/bouton/fichier_export.png" /> A venir, procédure ministérielle d'accréditation en cours&hellip;</button><label id="ajax_msg_export">&nbsp;</label>
		<!-- <span class="tab"></span><button type="button" id="export_lpc" <?php echo $bouton_export_lpc ?>><img alt="" src="./_img/bouton/fichier_export.png" /> Générer le fichier.</button> -->
	</fieldset>

	<fieldset id="fieldset_export_sacoche" class="hide">
		<label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><br />
		<span class="tab"></span><button type="button" id="export_sacoche" class="enabled"><img alt="" src="./_img/bouton/fichier_export.png" /> Générer le fichier.</button>
	</fieldset>

	<fieldset id="fieldset_import" class="hide">
		<hr />
	</fieldset>

	<fieldset id="fieldset_import_lpc" class="hide">
		<label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><p />
		<span class="tab"></span><button type="button" id="import_lpc_disabled" disabled><img alt="" src="./_img/bouton/fichier_import.png" /> A notre connaissance, <em>LPC</em> ne permet pas d'exporter un fichier de validations&hellip;</button>
	</fieldset>

	<fieldset id="fieldset_import_sacoche" class="hide">
		<label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><p />
		<span class="tab"></span><button type="button" id="import_sacoche" class="enabled"><img alt="" src="./_img/bouton/fichier_import.png" /> Transmettre le fichier.</button>
	</fieldset>

</form>

<hr />
<label id="ajax_msg">&nbsp;</label>
<p />
<ul class="puce" id="ajax_info">
</ul>
<p />
