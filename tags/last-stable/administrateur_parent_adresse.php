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
$TITRE = "Adresses des parents";
?>

<?php
// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$afficher     = (isset($_POST['f_afficher']))     ? TRUE                                    : FALSE ;
$debut_nom    = (isset($_POST['f_debut_nom']))    ? Clean::nom($_POST['f_debut_nom'])       : '' ;
$debut_prenom = (isset($_POST['f_debut_prenom'])) ? Clean::prenom($_POST['f_debut_prenom']) : '' ;
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></p>

<form action="./index.php?page=administrateur_parent&amp;section=adresse" method="post" id="form0">
	<div><label class="tab" for="f_debut_nom">Recherche :</label>le nom commence par <input type="text" id="f_debut_nom" name="f_debut_nom" value="<?php echo html($debut_nom) ?>" size="5" /> le prénom commence par <input type="text" id="f_debut_prenom" name="f_debut_prenom" value="<?php echo html($debut_prenom) ?>" size="5" /> <input type="hidden" id="f_afficher" name="f_afficher" value="1" /><button id="actualiser" type="submit" class="actualiser">Actualiser.</button></div>
</form>

<hr />

<?php
if($afficher)
{
	require(CHEMIN_DOSSIER_PAGES.'administrateur_parent_adresse.inc.php');
}
?>

<script type="text/javascript">var select_login="<?php echo $_SESSION['MODELE_PARENT']; ?>";</script>
