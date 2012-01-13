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
$TITRE = "Format des identifiants";

require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_profils = array('directeur','professeur','eleve','parent');
$affichage_login = '';
foreach($tab_profils as $profil)
{
	$affichage_login .= '<p><label class="tab" for="f_login_'.$profil.'">'.$tab_profil_libelle[$profil]['court'][2].' :</label><input type="text" id="f_login_'.$profil.'" name="f_login_'.$profil.'" value="'.$_SESSION[strtoupper('MODELE_'.$profil)].'" size="20" maxlength="20" /></p>';
}

$affichage_mdp_mini = '<option value="4">4 caractères</option><option value="5">5 caractères</option><option value="6">6 caractères</option><option value="7">7 caractères</option><option value="8">8 caractères</option>';
$affichage_mdp_mini = str_replace( '"'.$_SESSION['MDP_LONGUEUR_MINI'].'"' , '"'.$_SESSION['MDP_LONGUEUR_MINI'].'" selected' , $affichage_mdp_mini);

?>
<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_format_logins">DOC : Format des identifiants</a></span></div>

<h4>Format des noms d'utilisateurs</h4>
<form action="#" method="post">
	<?php echo $affichage_login; ?>
	<p><span class="tab"></span><button id="bouton_valider_login" type="button" class="parametre">Valider ces formats.</button><label id="ajax_msg_login">&nbsp;</label></p>
</form>

<hr />
<h4>Mots de passe</h4>
<form action="#" method="post">
	<p><label class="tab" for="f_mdp_mini">Longueur minimale</label><select id="f_mdp_mini" name="f_mdp_mini"><?php echo $affichage_mdp_mini; ?></select></p>
	<p><span class="tab"></span><button id="bouton_valider_mdp_mini" type="button" class="parametre">Valider ce paramètre.</button><label id="ajax_msg_mdp_mini">&nbsp;</label></p>
</form>
