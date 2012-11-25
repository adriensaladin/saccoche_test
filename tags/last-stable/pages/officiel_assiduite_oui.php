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
if(empty($page_maitre)) {exit('Ce fichier ne peut être appelé directement !');}

// Formulaire de choix d'une période (utilisé deux fois)
// Formulaire des classes
$tab_groupes    = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);
$select_periode = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl() , $select_nom=FALSE      , $option_first='oui' , $selection=FALSE , $optgroup='non');
$select_groupe  = Form::afficher_select($tab_groupes                                 , $select_nom='f_groupe' , $option_first='oui' , $selection=FALSE , $optgroup='non');

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
list( $tab_groupe_periode_js ) = Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*return_jointure_periode*/ , FALSE /*return_jointure_niveau*/ );
?>

<script type="text/javascript">
	var profil = "<?php echo $_SESSION['USER_PROFIL'] ?>";
	var date_mysql="<?php echo TODAY_MYSQL ?>";
	<?php echo $tab_groupe_periode_js ?> 
</script>

<!-- <div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_mise_en_page">DOC : Réglages synthèses &amp; bilans &rarr; Mise en page des bilans officiels</a></span></div> -->
<div class="travaux">Page en construction ; documentation et finalisation à venir prochainement !</div>

<hr />

<h2>Import de fichier</h2>
<form action="#" method="post" id="form_fichier">
	<!-- Pour la gestion de plusieurs imports, prendre modèle sur les fichiers validation_socle_fichier.* -->
	<p>
		<label class="tab" for="f_periode_import">Période :</label><select id="f_periode_import" name="f_periode_import"><?php echo $select_periode ?></select><br />
		<label class="tab" for="f_choix_principal">Origine :</label>
		<select id="f_choix_principal" name="f_choix_principal">
			<option value="import_siecle">issu de Siècle</option>
		</select>
	</p>
	<ul class="puce">
		<li>Indiquez le fichier <em>SIECLE_exportAbsence.xml</em> : <button type="button" id="import_siecle" class="fichier_import">Parcourir...</button><label id="ajax_msg_import">&nbsp;</label></li>
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
