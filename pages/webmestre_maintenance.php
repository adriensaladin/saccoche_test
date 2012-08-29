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
?>

<?php
// Initialisation de l'état de l'accès
$blocage_msg = LockAcces::tester_blocage('webmestre',0);
if($blocage_msg!==NULL)
{
	$label_acces = '<label class="erreur">Application fermée : '.To::html($blocage_msg).'</label>';
}
else
{
	$label_acces = '<label class="valide">Application accessible.</label>';
}
// Pas de bouton maj automatique si LCS
$fichier = './webservices/import_lcs.php';
if(!is_file($fichier))
{
	$disabled = '';
	$label_maj = '<label id="ajax_maj">&nbsp;</label>';
}
else
{
	$disabled = ' disabled';
	$label_maj = '<label id="ajax_maj" class="erreur">La mise à jour du module LCS-SACoche doit s\'effectuer via le LCS.</label>';
}
?>

<p>
	<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__maj">DOC : Mise à jour de l'application</a></span><br />
	<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__verrouillage">DOC : Verrouillage de l'application</a></span>
</p>

<h2>Version de SACoche</h2>

<ul class="puce">
	<li>Version actuellement installée : <label id="ajax_version_installee"><?php echo VERSION_PROG ?></label></li>
	<li>Dernière version disponible : <span id="ajax_version_disponible" class="astuce"><?php echo recuperer_numero_derniere_version() ?></span></li>
</ul>

<hr />

<h2>Mise à jour automatique des fichiers</h2>

<form action="#" method="post" id="form_maj"><fieldset>
	<span class="tab"></span><button id="bouton_droit" type="button" class="parametre">Vérification des droits en écriture.</button><label id="ajax_droit">&nbsp;</label><br />
	<span class="tab"></span><button id="bouton_maj" type="button" class="parametre"<?php echo $disabled ?>>Lancer la mise à jour automatique.</button><?php echo $label_maj ?>
</fieldset></form>

<hr />

<h2>Vérification des fichiers en place</h2>

<form action="#" method="post" id="form_verif"><fieldset>
	<span class="tab"></span><button id="bouton_verif" type="button" class="parametre">Lancer la vérification.</button><label id="ajax_verif">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Verrouillage de l'application</h2>

<form action="#" method="post" id="form"><fieldset>
	<label class="tab">État actuel :</label><span id="ajax_acces_actuel"><?php echo $label_acces ?></span><br />
	<label class="tab">Action :</label><label for="f_bloquer"><input type="radio" id="f_bloquer" name="f_action" value="bloquer" /> Bloquer l'application</label>&nbsp;&nbsp;&nbsp;<label for="f_debloquer"><input type="radio" id="f_debloquer" name="f_action" value="debloquer" /> Débloquer l'application</label><br />
	<div id="span_motif" class="hide"><label class="tab" for="f_motif">Motif :</label><select id="f_proposition" name="f_proposition"><option value="rien">autre motif</option><option value="mise-a-jour" selected>mise à jour</option><option value="maintenance">maintenance</option><option value="demenagement">déménagement</option></select> <input id="f_motif" name="f_motif" size="50" maxlength="100" type="text" value="Mise à jour des fichiers en cours." /></div>
	<span class="tab"></span><button id="bouton_valider" type="submit" class="parametre">Valider cet état.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<hr />
