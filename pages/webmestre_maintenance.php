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
$TITRE = "Maintenance &amp; mise à jour";
$VERSION_JS_FILE += 0;
?>

<?php
// Initialisation de l'état de l'accès
$fichier_blocage_webmestre = $CHEMIN_CONFIG.'blocage_webmestre.txt';
if(is_file($fichier_blocage_webmestre))
{
	$label = '<label class="erreur">Application fermée : '.html(file_get_contents($fichier_blocage_webmestre)).'</label>';
}
else
{
	$label = '<label class="valide">Application accessible.</label>';
}
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__verrouillage">DOC : Verrouillage de l'application</a></span></p>

<hr />

<h2>État de l'accès actuel</h2>

<p id="ajax_acces_actuel"><?php echo $label ?></p>

<hr />

<h2>Verrouillage de l'application</h2>

<form id="form" action=""><fieldset>
	<label for="f_bloquer"><input type="radio" id="f_bloquer" name="f_action" value="bloquer" /> Bloquer l'application</label><br />
	<span id="span_motif" class="hide">
		<label class="tab" for="f_motif">Motif :</label>
			<select id="f_proposition" name="f_proposition">
				<option value="rien">autre motif</option>
				<option value="mise-a-jour" selected="selected">mise à jour</option>
				<option value="maintenance">maintenance</option>
				<option value="demenagement">déménagement</option>
			</select>
			<input id="f_motif" name="f_motif" size="50" maxlength="100" type="text" value="Mise à jour des fichiers en cours." />
	</span>&nbsp;<p />
	<label for="f_debloquer"><input type="radio" id="f_debloquer" name="f_action" value="debloquer" /> Débloquer l'application</label><p />
	<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Valider cet état.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<p />
