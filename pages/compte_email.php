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
$TITRE = "Changer son adresse e-mail";

if( ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>
<div class="travaux">Page en construction ; documentation et finalisation à venir prochainement !</div>

<p><span class="astuce">Si vous avez plusieurs comptes <em>SACoche</em> (profils d'accès multiples...), ils ne peuvent pas être associés à la même adresse de courriel.</span></p>

<form action="#" method="post"><fieldset>
  <label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="" size="50" maxlength="63" /><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="mdp_perso">Valider le changement.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
<hr />
