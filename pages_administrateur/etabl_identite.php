<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
?>

<form id="form" action="">

	<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_informations_structure">DOC : Gestion de l'identité de l'établissement</a></span></p>

	<hr />

	<h2>Données saisies par le webmestre</h2>

	<fieldset>
		<label class="tab" for="f_uai">Code UAI (ex-RNE) :</label><input id="f_uai" name="f_uai" size="8" type="text" value="<?php echo html($_SESSION['UAI']); ?>" disabled="disabled" /><br />
		<label class="tab" for="f_denomination">Dénomination :</label><input id="f_denomination" name="f_denomination" size="50" type="text" value="<?php echo html($_SESSION['DENOMINATION']); ?>" disabled="disabled" /><br />
	</fieldset>
	<p />
	<ul class="puce"><li>En cas d'erreur, <?php echo mailto(WEBMESTRE_COURRIEL,'Modifier données SACoche '.$_SESSION['BASE'],'contactez le webmestre'); ?> responsable de <em>SACoche</em> sur ce serveur.</li></ul>

	<hr />

	<h2>Identification de l'établissement dans la base Sésamath</h2>

	<fieldset>
		<label class="tab" for="f_sesamath_id">Identifiant <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_id" name="f_sesamath_id" size="5" type="text" value="<?php echo html($_SESSION['SESAMATH_ID']); ?>" readonly="readonly" /><br />
		<label class="tab" for="f_sesamath_uai">Code UAI <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_uai" name="f_sesamath_uai" size="8" type="text" value="<?php echo html($_SESSION['SESAMATH_UAI']); ?>" readonly="readonly" /><br />
		<label class="tab" for="f_sesamath_type_nom">Dénomination <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_type_nom" name="f_sesamath_type_nom" size="50" type="text" value="<?php echo html($_SESSION['SESAMATH_TYPE_NOM']); ?>" readonly="readonly" /><br />
		<label class="tab" for="f_sesamath_key">Clef de contrôle <img alt="" src="./_img/bulle_aide.png" title="Valeur non modifiable manuellement.<br />Utilisez le lien ci-dessous." /> :</label><input id="f_sesamath_key" name="f_sesamath_key" size="35" type="text" value="<?php echo html($_SESSION['SESAMATH_KEY']); ?>" readonly="readonly" /><br />
		<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label>
	</fieldset>
	<p />
	<ul class="puce"><li><a id="ouvrir_recherche" href="#"><img alt="" src="./_img/find.png" /> Rechercher l'établissement dans la base Sésamath</a> afin de pouvoir échanger ensuite avec le serveur communautaire.</li></ul>

	<hr />

</form>

<script type="text/javascript">
	var url_debut = "<?php echo html(SERVEUR_COMMUNAUTAIRE) ?>";
</script>


<div id="object_container" class="hide">
	<h2>Rechercher l'établissement dans la base Sésamath</h2>
	<p><a id="rechercher_annuler" href="#"><img alt="" src="./_img/action/action_annuler.png" /> Annuler la recherche.</a></p>
	<?php
	// La balise object fonctionne sauf avec Internet Explorer qui n'affiche rien si on appelle une page provenant d'un autre domaine.
	// Par ailleurs, il faut mettre une adresse valide au départ sous peine de se voir retirer la balise par son substitut (pour Opéra).
	require_once('./_inc/fonction_css_browser_selector.php');
	$chaine_detection = css_browser_selector();
	if(substr($chaine_detection,0,3)!='ie ')
	{
		$balise   = 'object';
		$attribut = 'data';
	}
	else
	{
		$balise   = 'iframe';
		$attribut = 'src';
	}
	echo'<'.$balise.' id="cadre" '.$attribut.'="./_img/ajax/ajax_loader.gif" type="text/html" height="350px" style="width:100%;border:none;"><img src="./_img/ajax/ajax_loader.gif" alt="Chargement..." /> Appel au serveur communautaire...</'.$balise.'>';
	?>
</div>
