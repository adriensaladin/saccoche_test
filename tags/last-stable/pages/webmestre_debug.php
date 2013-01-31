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
  $lignes_debug .= '<label class="tab" for="f_debug_'.$debug_mode.'">'.$debug_mode.'</label><input type="checkbox" id="f_debug_'.$debug_mode.'" name="f_debug_'.$debug_mode.'" value="1"'.$checked.' /> '.$debug_texte.'<br />';
}
// Fichiers de logs phpCAS
$tab_files = FileSystem::lister_contenu_dossier(CHEMIN_LOGS_PHPCAS);
$tab_fichiers = array();
foreach($tab_files as $file)
{
  if(substr($file,0,9)=='debugcas_')
  {
    if(HEBERGEUR_INSTALLATION=='mono-structure')
    {
      $etabl = 'pour l\'établissement';
      $id_etabl = 0;
    }
    else
    {
      $tab = explode('_',$file);
      $id_etabl = $tab[1];
      $etabl = 'pour la base n°'.$id_etabl;
    }
    $tab_fichiers[$id_etabl] = '<li id="'.html(substr($file,0,-4)).'">Logs présents '.$etabl.', le fichier pesant '.afficher_fichier_taille(filesize(CHEMIN_LOGS_PHPCAS.$file)).'<q class="voir" title="Récupérer ce fichier."></q><q class="supprimer" title="Supprimer ce fichier."></q></li>';
  }
  ksort($tab_fichiers);
}
$listing_fichiers = count($tab_fichiers) ? implode('',$tab_fichiers) : '<li>Pas de fichier trouvé.</li>' ;
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

<h2>Chemin des logs phpCAS</h2>
<form action="#" method="post" id="form_phpCAS"><fieldset>
  <p class="astuce">Si des fichiers de logs phpCAS existent déjà, ils ne seront pas déplacés : penser à les supprimer avant.</p>
  <label class="tab" for="f_chemin_logs">Chemin</label><input type="text" size="100" id="f_chemin_logs" name="f_chemin_logs" value="<?php echo CHEMIN_LOGS_PHPCAS ?>" /><br />
  <span class="tab"></span><button id="bouton_save_chemin" type="button" class="parametre">Enregistrer.</button><label id="ajax_save_chemin">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Fichiers de logs phpCAS</h2>
<ul id="fichiers_logs" class="puce">
  <?php echo $listing_fichiers; ?>
</ul>

<hr />

<div id="bilan">
</div>
