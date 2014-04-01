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
$TITRE = "Données personnelles";

if(isset($_SESSION['STOP_CNIL']))
{
  $form_activation = '<h2>Activation de votre compte</h2>';
  $form_activation.= '<p>';
  $form_activation.= '  <span class="tab"></span><input type="checkbox" id="confirmation_cnil" name="confirmation_cnil" /><label for="confirmation_cnil"> J\'ai pris connaissance des informations relatives à mes données personnelles.</label><br />';
  $form_activation.= '  <span class="tab"></span><button id="f_enregistrer" type="button" class="valider" disabled>Valider.</button><label id="ajax_msg_enregistrer">&nbsp;</label>';
  $form_activation.= '</p>';
}
else
{
  $form_activation = '<h2>Votre compte est activé</h2>';
  $form_activation.= '<p><label class="valide">J\'ai pris connaissance des informations relatives à mes données personnelles.</label></p>';
}
?>

<p class="astuce">
  Veuillez prendre connaissance des <a class="lien_ext" href="<?php echo SERVEUR_CNIL ?>">informations CNIL relatives à l'application <em>SACoche</em></a>.
</p>
<p>
  Sont précisés en particulier :
</p>
<ul class="puce">
  <li>la nature des données enregistrées</li>
  <li>la confidentialité de ces données</li>
  <li>la durée de conservation de ces données</li>
  <li>votre droit d'accès et de rectification aux données qui vous concernent</li>
</ul>
<p class="astuce">
  Des informations peuvent évoluer ; vous pouvez à tout moment les consulter depuis votre menu <em>[Informations] [Données personnelles]</em>.
</p>
<hr />
<form action="#" method="post" id="form_cnil">
  <?php echo $form_activation ?>
</form>
<hr />
