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
$TITRE = "Délai avant déconnexion";

// Options du formulaire select
$options = '';
for($delai=10 ; $delai<130 ; $delai+=10)
{
  $options .= '<option value="'.$delai.'">'.$delai.' minutes</option>';
}

// Lister les profils de l'établissement
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_court_pluriel,user_profil_nom_long_pluriel,user_profil_duree_inactivite' /*listing_champs*/ , TRUE /*only_actif*/ );

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_delai_deconnexion">DOC : Délai avant déconnexion</a></span></div>

<hr />

<h2>Appliquer à tous les profils</h2>

<form action="#" method="post">
  <p><label class="tab" for="f_delai_ALL">Délai :</label><select id="f_delai_ALL" name="f_delai_ALL"><?php echo str_replace('value="30"','value="30" selected',$options) ?></select> <button id="bouton_valider_ALL" type="button" class="parametre">Valider.</button><label id="ajax_msg_ALL">&nbsp;</label></p>
</form>

<hr />

<h2>Affiner selon les profils</h2>

<form action="#" method="post">
<?php
foreach($DB_TAB as $DB_ROW)
{
  echo'<p><label class="tab" for="f_delai_'.$DB_ROW['user_profil_sigle'].'">'.$DB_ROW['user_profil_nom_court_pluriel'].' <img alt="" src="./_img/bulle_aide.png" title="'.$DB_ROW['user_profil_nom_long_pluriel'].'" /> :</label><select id="f_delai_'.$DB_ROW['user_profil_sigle'].'" name="f_delai_'.$DB_ROW['user_profil_sigle'].'">'.str_replace('value="'.$DB_ROW['user_profil_duree_inactivite'].'"','value="'.$DB_ROW['user_profil_duree_inactivite'].'" selected',$options).'</select> <button id="bouton_valider_'.$DB_ROW['user_profil_sigle'].'" type="button" class="parametre">Valider.</button><label id="ajax_msg_'.$DB_ROW['user_profil_sigle'].'">&nbsp;</label></p>'.NL;
}
?>
</form>

<hr />
