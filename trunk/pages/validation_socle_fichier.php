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
$TITRE = "Import / Export avec Sconet-LPC";
$VERSION_JS_FILE += 0;
?>

<?php
// Test pour l'export
$nb_eleves_sans_sconet = DB_STRUCTURE_compter_eleves_actifs_sans_id_sconet();
$s = ($nb_eleves_sans_sconet>1) ? 's' : '' ;

$test_uai          = ($_SESSION['UAI'])                                               ? TRUE : FALSE ;
$test_cnil         = (intval(CNIL_NUMERO)&&CNIL_DATE_ENGAGEMENT&&CNIL_DATE_RECEPISSE) ? TRUE : FALSE ;
$test_id_sconet    = (!$nb_eleves_sans_sconet)                                        ? TRUE : FALSE ;
$test_key_sesamath = ( $_SESSION['SESAMATH_KEY'] && $_SESSION['SESAMATH_ID'] )        ? TRUE : FALSE ;

$msg_uai          = ($test_uai)          ? '<label class="valide">Référence '.html($_SESSION['UAI']).'</label>'                                                                                            : '<label class="erreur">Référence non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.mailto(WEBMESTRE_COURRIEL,'SACoche','contact','Bonjour. La référence UAI de notre établissement (base n°'.$_SESSION['BASE'].') n\'est pas renseigné. Pouvez-vous faire le nécessaire ?') ;
$msg_cnil         = ($test_cnil)         ? '<label class="valide">Déclaration n°'.html(CNIL_NUMERO).' - demande effectuée le '.html(CNIL_DATE_ENGAGEMENT).' - récépissé reçu le '.html(CNIL_DATE_RECEPISSE).'</label>' : '<label class="erreur">Déclaration non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.mailto(WEBMESTRE_COURRIEL,'SACoche','contact','Bonjour. Les informations CNIL de l\'installation '.SERVEUR_ADRESSE.' ne sont pas renseignées. Pouvez-vous faire le nécessaire depuis votre menu [Administration du site] [Identité de l\'installation] ?') ;
$msg_id_sconet    = ($test_id_sconet)    ? '<label class="valide">Identifiants élèves présents.</label>'                                                                                                   : '<label class="alerte">'.$nb_eleves_sans_sconet.' élève'.$s.' trouvé'.$s.' sans identifiant Sconet.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_sconet">DOC</a></span>' ;
$msg_key_sesamath = ($test_key_sesamath) ? '<label class="valide">Etablissement identifié sur le serveur communautaire.</label>'                                                                           : '<label class="erreur">Identification non effectuée par un administrateur.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure">DOC</a></span>' ;

$bouton_export = ($test_uai && $test_cnil && $test_key_sesamath) ? 'id="bouton_export"' : 'id="disabled_export" disabled' ;
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=...">DOC : Import / Export avec Sconet-LPC</a></span></p>

<hr />

<h2>Exporter un fichier de validations à destination de LPC</h2>
<form id="form_export" action=""><fieldset>
	<label class="tab">UAI :</label><?php echo $msg_uai ?><br />
	<label class="tab">CNIL :</label><?php echo $msg_cnil ?><br />
	<label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><br />
	<label class="tab">Sésamath :</label><?php echo $msg_key_sesamath ?><p />
	<span class="tab"></span><button type="button" id="disabled_export" disabled><img alt="" src="./_img/bouton/fichier_export.png" /> A venir, procédure ministérielle d'accréditation en cours&hellip;</button><label id="ajax_msg_export">&nbsp;</label>
	<!-- <span class="tab"></span><button type="button" <?php echo $bouton_export ?>><img alt="" src="./_img/bouton/fichier_export.png" /> Générer le fichier.</button><label id="ajax_msg_export">&nbsp;</label> -->
</fieldset></form>

<hr />

<h2>Importer un fichier de validations en provenance de LPC</h2>
<form id="form_import" action=""><fieldset>
	<label class="tab">Sconet :</label><?php echo $msg_id_sconet ?><p />
	<span class="tab"></span><button type="button" id="disabled_import" disabled><img alt="" src="./_img/bouton/fichier_import.png" /> A venir, si possible&hellip;</button><label id="ajax_msg_import">&nbsp;</label>
	<!-- <span class="tab"></span><button type="button" id="bouton_import"><img alt="" src="./_img/bouton/fichier_import.png" /> Transmettre le fichier.</button><label id="ajax_msg_import">&nbsp;</label> -->
</fieldset></form>

<hr />

<ul class="puce" id="ajax_info">
</ul>
<p />
