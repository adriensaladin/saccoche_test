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
$TITRE = "Demandes d'évaluations";
?>

<p><span class="manuel"><a class="pop_up" href="./aide.php?fichier=demandes_evaluations">DOC : Demandes d'évaluations.</a></span></p>

<?php
$options = '';
for($nb_demandes=0 ; $nb_demandes<10 ; $nb_demandes++)
{
	$selected = ($nb_demandes==$_SESSION['ELEVE_DEMANDES']) ? ' selected="selected"' : '' ;
	$texte = ($nb_demandes>0) ? ( ($nb_demandes>1) ? $nb_demandes.' demandes simultanées autorisées par matière' : '1 seule demande à la fois autorisée par matière' ) : 'Aucune demande autorisée (fonctionnalité desactivée).' ;
	$options .= '<option value="'.$nb_demandes.'"'.$selected.'>'.$texte.'</option>';
}
?>

<form id="delai" action=""><fieldset>
	<label class="tab" for="f_demandes">Nombre maximal :</label><select id="f_demandes" name="f_demandes"><?php echo $options ?></select><br />
	<span class="tab"></span><input id="f_submit" type="button" value="Valider ce choix." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>
