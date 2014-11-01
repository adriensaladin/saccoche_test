<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Vérification des certificats SSL";

require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

unset($tab_serveur_cas['']);
$tab_cas_nom   = array_merge( array('perso') , array_keys($tab_serveur_cas) );
$tab_no_certif = explode(',',mb_substr(PHPCAS_NO_CERTIF_LISTING,1,-1));
?>

Lors d'une connexion CAS à un ENT, la validité du certificat SSL est vérifiée.<br />
Cette interface permet de décocher la vérification pour certains ENT (mais on perd alors tout l'intérêt d'une connexion sécurisée).

<hr />

<form id="table_action" action="#" method="post" id="serveurs_cas">
  <table class="form">
    <thead>
      <tr><th class="nu"></th><th>Serveurs CAS</th></tr>
    </thead>
    <tbody>
      <?php
      foreach($tab_cas_nom as $cas_nom)
      {
        // Afficher une ligne du tableau
        $checked = (in_array($cas_nom,$tab_no_certif)) ? '' : ' checked' ;
        echo'<tr>';
        echo  '<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$cas_nom.'"'.$checked.' /></td>';
        echo  '<td class="label">'.$cas_nom.'</td>';
        echo'</tr>'.NL;
      }
      ?>
    </tbody>
  </table>
  <p>
    <span class="tab"></span><button id="bouton_valider" type="button" class="parametre">Valider ce choix.</button><label id="ajax_msg">&nbsp;</label>
  </p>
</form>
