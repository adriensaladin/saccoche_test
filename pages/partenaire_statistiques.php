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
$TITRE = "Statistiques d'utilisation (partenariat ENT)";
?>

<p class="astuce">
  Seuls les établissements utilisant le connecteur ENT conventionné sont comptabilisés.
</p>

<div id="ajax_info" class="hide">
  <label id="ajax_msg1"></label>
  <ul class="puce"><li id="ajax_msg2"></li></ul>
  <span id="ajax_num" class="hide"></span>
  <span id="ajax_max" class="hide"></span>
</div>

<form action="#" method="post" id="structures" class="hide">
  <table class="hsort" id="table_action">
    <thead>
      <tr>
        <th>Géographie</th>
        <th>Structure</th>
        <th>connexion ENT</th>
        <th>professeurs<br />connectés</th>
        <th>élèves<br />connectés</th>
        <th>saisies<br />enregistrées</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td class="nu" colspan="6"></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <td class="nu" colspan="6"></td>
      </tr>
    </tbody>
  </table>
</form>
