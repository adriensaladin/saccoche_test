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
$TITRE = "Gérer les élèves";

// Récupérer d'éventuels paramètres pour restreindre l'affichage
$afficher    = (isset($_POST['f_afficher'])) ? TRUE                             : FALSE ;
$statut      = (isset($_POST['f_statut']))   ? Clean::entier($_POST['f_statut']) : 1 ;
$groupe      = (isset($_POST['f_groupes']))  ? Clean::texte($_POST['f_groupes']) : '' ;
$groupe_type = Clean::texte( substr($groupe,0,1) );
$groupe_id   = Clean::entier( substr($groupe,1) );
// Construire et personnaliser le formulaire pour restreindre l'affichage
$select_f_groupes = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl() , $select_nom='f_groupes' , $option_first='oui' , $selection=$groupe , $optgroup='oui');
$select_f_statuts = Form::afficher_select(Form::$tab_select_statut                    , $select_nom='f_statut'  , $option_first='non' , $selection=$statut , $optgroup='non');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_eleves">DOC : Gestion des élèves</a></span></p>

<form action="./index.php?page=administrateur_eleve&amp;section=gestion" method="post" id="form0">
	<div><label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_f_groupes ?> <label id="ajax_msg0">&nbsp;</label></div>
	<div><label class="tab" for="f_statut">Statut :</label><?php echo $select_f_statuts ?><input type="hidden" id="f_afficher" name="f_afficher" value="1" /></div>
</form>

<hr />

<?php
if($afficher)
{
	require(CHEMIN_DOSSIER_PAGES.'administrateur_eleve_gestion.inc.php');
}
?>

<script type="text/javascript">
	var input_date = "<?php echo TODAY_FR ?>";
	var date_mysql = "<?php echo TODAY_MYSQL ?>";
	var select_login="<?php echo $_SESSION['MODELE_ELEVE']; ?>";
	var mdp_longueur_mini=<?php echo $_SESSION['MDP_LONGUEUR_MINI'] ?>;
</script>
