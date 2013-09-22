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
$TITRE = "Photos des élèves";

// Fabrication des éléments select du formulaire
$select_groupe = Form::afficher_select( DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( FALSE /*sans*/ , FALSE /*tout*/ ) , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/);
// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var url_export_rapport = "'.URL_DIR_EXPORT.'rapport_zip_photos_'.$_SESSION['BASE'].'.php";';
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__photos_eleves">DOC : Photos des élèves</a></span></p>
<div class="danger">Respectez les conditions légales d'utilisation (droit à l'image précisé dans la documentation ci-dessus).</div>

<hr />

<h2>Ajout multiple</h2>

<form action="#" method="post" id="form2"><fieldset>
  <p class="astuce">
    Taille maximale du fichier : <b><?php echo InfoServeur::minimum_limitations_upload(FALSE /*avec_explication*/) ?></b> (<a href="./index.php?page=compte_info_serveur">voir les caractéristiques du serveur</a>).
  </p>
  <label class="tab" for="f_masque">Forme noms fichiers :</label><input id="f_masque" name="f_masque" size="50" maxlength="50" type="text" value="" /><br />
  <label class="tab" for="bouton_zip">Upload fichier <em>zip</em> :</label><button id="bouton_zip" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_zip">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Gestion individuelle</h2>

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?> <label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<p id="liste_eleves">
</p>
