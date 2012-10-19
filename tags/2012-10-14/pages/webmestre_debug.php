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
$TITRE = "Débogueur";
?>

<?php
// Activation du débogueur
$lignes_debug = '';
$tab_debug = array(
	'PHP'     => 'afficher toutes les erreurs PHP <span class="danger">déconseillé en production</span>',
	'PHPCAS'  => 'enregistrer les dialogues avec le serveur CAS <span class="danger">le fichier peut vite gonfler &rarr; décocher une fois les tests terminés</span>',
	'SQL'     => 'console FirePHP &rarr; résultats des requêtes SQL <span class="danger">double le besoin en mémoire &rarr; risqué pour les générations de bilans</span>',
	'SESSION' => 'console FirePHP &rarr; valeurs de $_SESSION <span class="danger">diminue la sécurité en rendant accessible certaines informations</span>',
	'POST'    => 'console FirePHP &rarr; valeurs de $_POST',
	'GET'     => 'console FirePHP &rarr; valeurs de $_GET',
	'FILES'   => 'console FirePHP &rarr; valeurs de $_FILES',
	'COOKIE'  => 'console FirePHP &rarr; valeurs de $_COOKIE <span class="danger">diminue la sécurité en rendant accessible certaines informations</span>',
	'CONST'   => 'console FirePHP &rarr; valeurs des constantes PHP <span class="danger">diminue la sécurité en rendant accessible certaines informations</span>'
);
foreach($tab_debug as $debug_mode => $debug_texte)
{
	$checked = constant('DEBUG_'.$debug_mode) ? ' checked' : '' ;
	$lignes_debug .= '<label class="tab" for="f_debug_'.$debug_mode.'">'.$debug_mode.'</label><input type="checkbox" id="f_debug_'.$debug_mode.'" name="f_debug_'.$debug_mode.'" value="1"'.$checked.' /> '.$debug_texte.'</label><br />';
}
// Fichier logs phpCAS
if(is_file(CHEMIN_FICHIER_DEBUG_PHPCAS))
{
	$info = 'Ce fichier de logs existe et pèse '.afficher_fichier_taille(filesize(CHEMIN_FICHIER_DEBUG_PHPCAS)).' : <a href="'.URL_FICHIER_DEBUG_PHPCAS.'" class="lien_ext">voir son contenu</a>.';
	$disabled = '';
}
else
{
	$info = 'Ce fichier de logs n\'est pas présent.';
	$disabled = ' disabled';
}
?>

<hr />

<h2>Paramétrage du débogueur</h2>

<form action="#" method="post" id="form_debug"><fieldset>
	<p class="danger">Excepté les logs <em>phpCAS</em>, ces fonctionnalités devraient être réservées à un environnement de développement.</p>
	<p class="astuce">Les réglages s'appliquent à tous les utilisateurs ; à n'activer qu'en connaissance de cause.</p>
	<?php echo $lignes_debug; ?>
	<span class="tab"></span><button id="bouton_debug" type="button" class="parametre">Enregistrer.</button><label id="ajax_debug">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Fichier de logs phpCAS</h2>
<form action="#" method="post" id="form_phpCAS"><fieldset>
	<p class="astuce"><?php echo $info; ?></p>
	<span class="tab"></span><button id="bouton_effacer" type="button" class="supprimer"<?php echo $disabled; ?>>Effacer le fichier.</button><label id="ajax_effacer">&nbsp;</label>
</fieldset></form>

<hr />
