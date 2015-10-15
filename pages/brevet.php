<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
$TITRE = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? html(Lang::_("Fiches brevet")) : html(Lang::_("Notanet & Fiches brevet")) ;
?>

<?php if($_SESSION['USER_PROFIL_TYPE']!='professeur'): ?>
<div class="hc">
  <a href="./index.php?page=<?php echo $PAGE ?>&amp;section=series">[ Étape n°1 : Séries ]</a>&nbsp;&nbsp;&nbsp;
  <a href="./index.php?page=<?php echo $PAGE ?>&amp;section=epreuves">[ Étape n°2 : Épreuves ]</a>&nbsp;&nbsp;&nbsp;
  <a href="./index.php?page=<?php echo $PAGE ?>&amp;section=moyennes">[ Étape n°3 : Notes ]</a>&nbsp;&nbsp;&nbsp;
  <a href="./index.php?page=<?php echo $PAGE ?>&amp;section=notanet">[ Étape n°4 : Export Notanet ]</a>&nbsp;&nbsp;&nbsp;
  <a href="./index.php?page=<?php echo $PAGE ?>&amp;section=fiches">[ Étape n°5 : Fiches brevet ]</a>
</div>
<hr />
<?php endif; ?>

<?php
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $SECTION = 'fiches';
}
if($SECTION=='accueil')
{
  echo'<p>'.NL;
  echo  '<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=releves_bilans__notanet_fiches_brevet">DOC : Notanet &amp; Fiches brevet</a></span><br />'.NL;
  echo  '<span class="astuce">Effectuer dans l\'ordre les étapes ci-dessus&hellip;</span>'.NL;
  echo'</p>'.NL;
  echo'<hr />'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
  $PAGE = $PAGE.'_'.$SECTION ;
  require($fichier_section);
}
else
{
  echo'<p class="danger">Page introuvable (paramètre manquant ou incorrect) !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>
