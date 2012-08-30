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
$TITRE = "Identité de l'établissement";

$options_mois = '<option value="1">calquée sur l\'année civile</option><option value="2">bascule au 1er février</option><option value="3">bascule au 1er mars</option><option value="4">bascule au 1er avril</option><option value="5">bascule au 1er mai</option><option value="6">bascule au 1er juin</option><option value="7">bascule au 1er juillet</option><option value="8">bascule au 1er août</option><option value="9">bascule au 1er septembre</option><option value="10">bascule au 1er octobre</option><option value="11">bascule au 1er novembre</option><option value="12">bascule au 1er décembre</option>';
$options_mois = str_replace( '"'.$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'].'"' , '"'.$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'].'" selected' , $options_mois );
?>

<div id="div_instance">

	<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_informations_structure">DOC : Gestion de l'identité de l'établissement</a></span></div>

	<hr />

	<h2>Données saisies par le webmestre</h2>

	<form action="#" method="post" id="form_webmestre">
		<p>
			<label class="tab" for="f_webmestre_uai">Code UAI (ex-RNE) :</label><input id="f_webmestre_uai" name="f_webmestre_uai" size="8" type="text" value="<?php echo html($_SESSION['WEBMESTRE_UAI']); ?>" disabled /><br />
			<label class="tab" for="f_webmestre_denomination">Dénomination :</label><input id="f_webmestre_denomination" name="f_webmestre_denomination" size="50" type="text" value="<?php echo html($_SESSION['WEBMESTRE_DENOMINATION']); ?>" disabled />
		</p>
		<ul class="puce"><li>En cas d'erreur, <?php echo mailto(WEBMESTRE_COURRIEL,'Modifier données SACoche '.$_SESSION['BASE'],'contactez le webmestre'); ?> responsable de <em>SACoche</em> sur ce serveur.</li></ul>
	</form>

	<hr />

	<h2>Identification de l'établissement dans la base Sésamath</h2>

	<form action="#" method="post" id="form_sesamath">
		<p>
			<label class="tab" for="f_sesamath_id">Identifiant <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_id" name="f_sesamath_id" size="5" type="text" value="<?php echo html($_SESSION['SESAMATH_ID']); ?>" readonly /><br />
			<label class="tab" for="f_sesamath_uai">Code UAI <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_uai" name="f_sesamath_uai" size="8" type="text" value="<?php echo html($_SESSION['SESAMATH_UAI']); ?>" readonly /><br />
			<label class="tab" for="f_sesamath_type_nom">Dénomination <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_type_nom" name="f_sesamath_type_nom" size="50" type="text" value="<?php echo html($_SESSION['SESAMATH_TYPE_NOM']); ?>" readonly /><br />
			<label class="tab" for="f_sesamath_key">Clef de contrôle <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_key" name="f_sesamath_key" size="35" type="text" value="<?php echo html($_SESSION['SESAMATH_KEY']); ?>" readonly /><br />
			<span class="tab"></span><button id="bouton_valider_sesamath" type="submit" class="parametre">Valider.</button><label id="ajax_msg_sesamath">&nbsp;</label>
		</p>
	<ul class="puce"><li><a id="ouvrir_recherche" href="#"><img alt="" src="./_img/find.png" /> Rechercher l'établissement dans la base Sésamath</a> afin de pouvoir échanger ensuite avec le serveur communautaire.</li></ul>
	</form>

	<hr />

	<h2>Coordonnées de l'établissement</h2>

	<form action="#" method="post" id="form_etablissement">
		<p>
			<label class="tab" for="f_etablissement_denomination">Dénomination :</label><input id="f_etablissement_denomination" name="f_etablissement_denomination" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['DENOMINATION']); ?>" /><br />
			<label class="tab" for="f_etablissement_adresse1">Adresse ligne 1 :</label><input id="f_etablissement_adresse1" name="f_etablissement_adresse1" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['ADRESSE1']); ?>" /><br />
			<label class="tab" for="f_etablissement_adresse2">Adresse ligne 2 :</label><input id="f_etablissement_adresse2" name="f_etablissement_adresse2" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['ADRESSE2']); ?>" /><br />
			<label class="tab" for="f_etablissement_adresse3">Adresse ligne 3 :</label><input id="f_etablissement_adresse3" name="f_etablissement_adresse3" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['ADRESSE3']); ?>" /><br />
			<label class="tab" for="f_etablissement_telephone">Téléphone :</label><input id="f_etablissement_telephone" name="f_etablissement_telephone" size="25" maxlength="25" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['TELEPHONE']); ?>" /><br />
			<label class="tab" for="f_etablissement_fax">Fax :</label><input id="f_etablissement_fax" name="f_etablissement_fax" size="25" maxlength="25" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['FAX']); ?>" /><br />
			<label class="tab" for="f_etablissement_courriel">Courriel :</label><input id="f_etablissement_courriel" name="f_etablissement_courriel" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['COURRIEL']); ?>" /><br />
			<span class="tab"></span><button id="bouton_valider_etablissement" type="submit" class="parametre">Valider.</button><label id="ajax_msg_etablissement">&nbsp;</label>
		</p>
	</form>

	<hr />

	<h2>Année scolaire</h2>

	<form action="#" method="post" id="form_annee_scolaire">
		<p>
			<label class="tab" for="f_mois_bascule_annee_scolaire">Fonctionnement :</label><select id="f_mois_bascule_annee_scolaire" name="f_mois_bascule_annee_scolaire"><?php echo $options_mois; ?></select><br />
			<label class="tab">Affichage obtenu :</label><span class="i">&laquo;&nbsp;Année scolaire <span id="span_simulation"></span>&nbsp;&raquo;</span><br />
			<span class="tab"></span><button id="bouton_valider_annee_scolaire" type="button" class="parametre">Valider.</button><label id="ajax_msg_annee_scolaire">&nbsp;</label>
		</p>
	</form>

	<hr />

</div>

<form action="#" method="post" id="form_communautaire" class="hide">
	<h2>Rechercher l'établissement dans la base Sésamath</h2>
	<p><button id="rechercher_annuler" type="button" class="annuler">Annuler la recherche.</button></p>
	<p id="f_recherche_mode">
		<label class="tab">Technique :</label><label for="f_mode_geo"><input type="radio" id="f_mode_geo" name="f_mode" value="geo" /> recherche sur critères géographiques</label>&nbsp;&nbsp;&nbsp;<label for="f_mode_uai"><input type="radio" id="f_mode_uai" name="f_mode" value="uai" /> recherche à partir du numéro UAI (ex-RNE)</label>
	</p>
	<fieldset id="f_recherche_geo" class="hide">
		<label class="tab" for="f_geo1">Etape 1/3 :</label><select id="f_geo1" name="f_geo1"><option value=""></option></select><br />
		<label class="tab" for="f_geo2">Etape 2/3 :</label><select id="f_geo2" name="f_geo2"><option value=""></option></select><br />
		<label class="tab" for="f_geo3">Etape 3/3 :</label><select id="f_geo3" name="f_geo3"><option value=""></option></select><br />
	</fieldset>
	<fieldset id="f_recherche_uai" class="hide">
		<label class="tab" for="f_uai2">Code UAI (ex-RNE) :</label><input id="f_uai2" name="f_uai2" size="8" type="text" value="" /><br />
		<span class="tab"></span><button id="rechercher_uai" type="button" class="rechercher">Lancer la recherche.</button>
	</fieldset>
	<ul id="f_recherche_resultat" class="puce p hide">
		<li></li>
	</ul>
	<span class="tab"></span><label id="ajax_msg_communautaire">&nbsp;</label>
</form>
