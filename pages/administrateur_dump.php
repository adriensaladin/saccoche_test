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
$TITRE = "Sauvegarde / Restauration";
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_dump">DOC : Sauvegarde et restauration de la base</a></span></p>

<hr />

<h2>Sauvegarder la base</h2>
<form action="#" method="post" id="form1"><fieldset>
	<span class="tab"></span><button id="bouton_form1" type="button" class="dump_export">Lancer la sauvegarde.</button><label id="ajax_msg1">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Restaurer la base</h2>
<div class="danger">Restaurer une sauvegarde antérieure écrasera irrémédiablement les données actuelles !</div>
<form action="#" method="post" id="form2"><fieldset>
	<label class="tab" for="bouton_form2">Uploader le fichier :</label><button id="bouton_form2" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg2">&nbsp;</label>
</fieldset></form>

<hr />

<ul class="puce" id="ajax_info">
</ul>
<p>&nbsp;</p>
