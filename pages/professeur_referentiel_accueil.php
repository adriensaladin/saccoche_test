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

// Indication des profils ayant accès à ces pages
require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_profils = array('professeur','profcoordonnateur','aucunprof');
$texte_profil_gestion = $_SESSION['DROIT_GERER_REFERENTIEL'];
foreach($tab_profils as $profil)
{
	$texte_profil_gestion = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$texte_profil_gestion);
}
$texte_profil_ressource = $_SESSION['DROIT_GERER_RESSOURCE'];
foreach($tab_profils as $profil)
{
	$texte_profil_ressource = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$texte_profil_ressource);
}
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
			<td><?php echo $texte_profil_gestion ?></td>
			<td><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_creer_parametrer">DOC</a></span></td>
		</tr>
		<tr>
			<td><a href="./index.php?page=<?php echo $PAGE ?>&amp;section=edition">Modifier le contenu des référentiels.</a></td>
			<td><?php echo $texte_profil_gestion ?></td>
			<td><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_modifier_contenu">DOC</a></span></td>
		</tr>
		<tr>
			<td><a href="./index.php?page=<?php echo $PAGE ?>&amp;section=ressources">Associer des ressources aux items.</a></td>
			<td><?php echo $texte_profil_ressource ?></td>
			<td><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_lier_ressources">DOC</a></span></td>
		</tr>
	</tbody>
</table>
<p>&nbsp;</p>