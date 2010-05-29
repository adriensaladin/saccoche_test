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
$TITRE = "Délai avant déconnexion";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_delai_deconnexion">DOC : Délai d'inactivité et déconnexion automatique</a></span></p>

<hr />

<?php
$options = '';
for($delai=10 ; $delai<100 ; $delai+=10)
{
	$selected = ($delai==$_SESSION['DUREE_INACTIVITE']) ? ' selected="selected"' : '' ;
	$options .= '<option value="'.$delai.'"'.$selected.'>'.$delai.' minutes</option>';
}
?>

<form id="delai" action=""><fieldset>
	<label class="tab" for="f_delai">Délai :</label><select id="f_delai" name="f_delai"><?php echo $options ?></select><br />
	<span class="tab"></span><input id="f_submit" type="button" value="Valider ce délai." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>
