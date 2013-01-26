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
$TITRE = "Gestion des référentiels";
?>

<p class="astuce">Choisir une rubrique ci-dessus ou ci-dessous&hellip;</p>
<table class="simulation">
  <thead>
    <tr>
      <th>Rubrique</th>
      <th>Profils autorisés</th>
      <th>Documentation</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><a href="./index.php?page=<?php echo $PAGE ?>&amp;section=gestion">Créer / paramétrer les référentiels.</a></td>
      <td rowspan="2"><?php echo afficher_profils_droit_specifique($_SESSION['DROIT_GERER_REFERENTIEL'],'br') ?></td>
      <td><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_creer_parametrer">DOC</a></span></td>
    </tr>
    <tr>
      <td><a href="./index.php?page=<?php echo $PAGE ?>&amp;section=edition">Modifier le contenu des référentiels.</a></td>
      <td><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_modifier_contenu">DOC</a></span></td>
    </tr>
    <tr>
      <td><a href="./index.php?page=<?php echo $PAGE ?>&amp;section=ressources">Associer des ressources aux items.</a></td>
      <td><?php echo afficher_profils_droit_specifique($_SESSION['DROIT_GERER_RESSOURCE'],'br') ?></td>
      <td><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_lier_ressources">DOC</a></span></td>
    </tr>
  </tbody>
</table>
<p>&nbsp;</p>