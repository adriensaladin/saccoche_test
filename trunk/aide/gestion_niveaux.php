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
$TITRE = "Gestion des niveaux";
?>

<p>
	L'administrateur doit cocher les niveaux utilisés sur <em>SACoche</em> : seuls les niveaux sélectionnés sont affichés dans les menus déroulants.<br />
	<span class="astuce">Il faut l'indiquer avant d'importer les utilisateurs.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Niveaux]</em>.</li>
</ul>
<p>
	Environ 30 niveaux sont disponibles ; il est possible d'en ajouter (filières...) si besoin : <?php echo mailto('thomas.crespin@sesamath.net','Ajouter un niveau','contactez-moi'); ?> en m'indiquant son nom et son niveau de correspondance dans Sconet (l'idéal est de joindre le fichier <em>nomenclature.xml</em> issu de Sconet).
</p>
