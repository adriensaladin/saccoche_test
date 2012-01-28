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
$TITRE = "Paramétrage des bulletins";

$check_adresse   = (mb_substr_count($_SESSION['BULLETIN']['INFOS_ETABLISSEMENT'] ,'adresse'))   ? ' checked' : '' ;
$check_telephone = (mb_substr_count($_SESSION['BULLETIN']['INFOS_ETABLISSEMENT'] ,'telephone')) ? ' checked' : '' ;
$check_fax       = (mb_substr_count($_SESSION['BULLETIN']['INFOS_ETABLISSEMENT'] ,'fax'))       ? ' checked' : '' ;
$check_courriel  = (mb_substr_count($_SESSION['BULLETIN']['INFOS_ETABLISSEMENT'] ,'courriel'))  ? ' checked' : '' ;

$options_infos_responsables = '<option value="non">ne pas indiquer les coordonnées des responsables</option><option value="oui_libre">indiquer les coordonnées des responsables, emplacement libre</option><option value="oui_force">indiquer les coordonnées des responsables, emplacement forcé (enveloppe à fenêtre)</option>';
$options_infos_responsables = str_replace( '"'.$_SESSION['BULLETIN']['INFOS_RESPONSABLES'].'"' , '"'.$_SESSION['BULLETIN']['INFOS_RESPONSABLES'].'" selected' , $options_infos_responsables );

$options_nombre_exemplaires = '<option value="un">un bulletin par élève (pour le responsable n°1)</option><option value="deux_si_besoin">deux bulletins seulement si les responsables ont des adresses différentes</option><option value="deux_de_force">deux bulletins par élève</option>';
$options_nombre_exemplaires = str_replace( '"'.$_SESSION['BULLETIN']['NOMBRE_EXEMPLAIRES'].'"' , '"'.$_SESSION['BULLETIN']['NOMBRE_EXEMPLAIRES'].'" selected' , $options_nombre_exemplaires );

$options_marge = '<option value="5">5mm</option><option value="6">6mm</option><option value="7">7mm</option><option value="8">8mm</option><option value="9">9mm</option><option value="10">10mm</option><option value="11">11mm</option><option value="12">12mm</option><option value="13">13mm</option><option value="14">14mm</option><option value="15">15mm</option>';
$options_marge_gauche = str_replace( '"'.$_SESSION['BULLETIN']['MARGE_GAUCHE'].'"' , '"'.$_SESSION['BULLETIN']['MARGE_GAUCHE'].'" selected' , $options_marge );
$options_marge_droite = str_replace( '"'.$_SESSION['BULLETIN']['MARGE_DROITE'].'"' , '"'.$_SESSION['BULLETIN']['MARGE_DROITE'].'" selected' , $options_marge );
$options_marge_haut   = str_replace( '"'.$_SESSION['BULLETIN']['MARGE_HAUT']  .'"' , '"'.$_SESSION['BULLETIN']['MARGE_HAUT']  .'" selected' , $options_marge );
$options_marge_bas    = str_replace( '"'.$_SESSION['BULLETIN']['MARGE_BAS']   .'"' , '"'.$_SESSION['BULLETIN']['MARGE_BAS']   .'" selected' , $options_marge );

$class_enveloppe = ($_SESSION['BULLETIN']['INFOS_RESPONSABLES']=='oui_force') ? '' : ' class="hide"' ;

function fabriquer_chaine_option($mini,$maxi)
{
	$options = '';
	for( $i=$mini ; $i<=$maxi ; $i++ )
	{
		$options .= '<option value="'.$i.'">'.number_format($i/10,1,',','').'cm</option>';
	}
	return $options;
}

$options_horizontal_gauche = str_replace( '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'].'"' , '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'].'" selected' , fabriquer_chaine_option(90,120) );
$options_horizontal_milieu = str_replace( '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'].'"' , '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'].'" selected' , fabriquer_chaine_option(85,115) );
$options_horizontal_droite = str_replace( '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'].'"' , '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'].'" selected' , fabriquer_chaine_option(15,25) );
$options_vertical_haut     = str_replace( '"'.$_SESSION['ENVELOPPE']['VERTICAL_HAUT']    .'"' , '"'.$_SESSION['ENVELOPPE']['VERTICAL_HAUT']    .'" selected' , fabriquer_chaine_option(40,60) );
$options_vertical_milieu   = str_replace( '"'.$_SESSION['ENVELOPPE']['VERTICAL_MILIEU']  .'"' , '"'.$_SESSION['ENVELOPPE']['VERTICAL_MILIEU']  .'" selected' , fabriquer_chaine_option(35,55) );
$options_vertical_bas      = str_replace( '"'.$_SESSION['ENVELOPPE']['VERTICAL_BAS']     .'"' , '"'.$_SESSION['ENVELOPPE']['VERTICAL_BAS']     .'" selected' , fabriquer_chaine_option(15,25) );
?>

<!-- <div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_informations_structure">DOC : Paramétrage des bulletins</a></span></div> -->
<div class="astuce">Fonctionnalité en cours de développement &rarr; davantage d'informations disponibles ultérieurement.</div>

<hr />

<h2>Paramètres de mise en page</h2>

<form action="#" method="post" id="form_mise_en_page">
	<p>
		<label class="tab">Info. établissement :</label>
		<label for="f_coordonnees_adresse"><input type="checkbox" id="f_coordonnees_adresse" name="f_coordonnees[]" value="adresse"<?php echo $check_adresse ?> /> adresse</label>&nbsp;&nbsp;&nbsp;
		<label for="f_coordonnees_telephone"><input type="checkbox" id="f_coordonnees_telephone" name="f_coordonnees[]" value="telephone"<?php echo $check_telephone ?> /> telephone</label>&nbsp;&nbsp;&nbsp;
		<label for="f_coordonnees_fax"><input type="checkbox" id="f_coordonnees_fax" name="f_coordonnees[]" value="fax"<?php echo $check_fax ?> /> fax</label>&nbsp;&nbsp;&nbsp;
		<label for="f_coordonnees_courriel"><input type="checkbox" id="f_coordonnees_courriel" name="f_coordonnees[]" value="courriel"<?php echo $check_courriel ?> /> courriel</label>
	</p>
	<p>
		<label class="tab" for="f_infos_responsables">Coord. responsables :</label><select id="f_infos_responsables" name="f_infos_responsables"><?php echo $options_infos_responsables; ?></select>
	</p>
	<p id="p_enveloppe"<?php echo $class_enveloppe ?>>
		<img src="./_img/enveloppe.png" alt="envelopppe" width="230" height="115" class="fd" />
		<label class="tab">Dim. enveloppe :</label><span class="i">Consulter la légende sur le schéma ci-contre.</span><br />
		<span class="tab"></span>
		<label for="f_horizontal_gauche">HG </label><select id="f_horizontal_gauche" name="f_horizontal_gauche"><?php echo $options_horizontal_gauche; ?></select>&nbsp;&nbsp;&nbsp;
		<label for="f_horizontal_milieu">HM </label><select id="f_horizontal_milieu" name="f_horizontal_milieu"><?php echo $options_horizontal_milieu; ?></select>&nbsp;&nbsp;&nbsp;
		<label for="f_horizontal_droite">HD </label><select id="f_horizontal_droite" name="f_horizontal_droite"><?php echo $options_horizontal_droite; ?></select><br />
		<span class="tab"></span>
		<label for="f_vertical_haut">VH </label><select id="f_vertical_haut" name="f_vertical_haut"><?php echo $options_vertical_haut; ?></select>&nbsp;&nbsp;&nbsp;
		<label for="f_vertical_milieu">VM </label><select id="f_vertical_milieu" name="f_vertical_milieu"><?php echo $options_vertical_milieu; ?></select>&nbsp;&nbsp;&nbsp;
		<label for="f_vertical_bas">VB </label><select id="f_vertical_bas" name="f_vertical_bas"><?php echo $options_vertical_bas; ?></select>
	</p>
	<p>
		<label class="tab" for="f_nombre_exemplaires">Nb d'exemplaires :</label><select id="f_nombre_exemplaires" name="f_nombre_exemplaires"><?php echo $options_nombre_exemplaires; ?></select>
	</p>
	<p>
		<label class="tab">Marges bord page :</label>
			<label for="f_marge_gauche">à gauche </label><select id="f_marge_gauche" name="f_marge_gauche"><?php echo $options_marge_gauche; ?></select>&nbsp;&nbsp;&nbsp;
			<label for="f_marge_droite">à droite </label><select id="f_marge_droite" name="f_marge_droite"><?php echo $options_marge_droite; ?></select>&nbsp;&nbsp;&nbsp;
			<label for="f_marge_haut">en haut </label><select id="f_marge_haut" name="f_marge_haut"><?php echo $options_marge_haut; ?></select>&nbsp;&nbsp;&nbsp;
			<label for="f_marge_bas">en bas </label><select id="f_marge_bas" name="f_marge_bas"><?php echo $options_marge_bas; ?></select>
	</p>
	<p>
		<span class="tab"></span><button id="bouton_valider_mise_en_page" type="button" class="parametre">Enregister.</button><label id="ajax_msg_mise_en_page">&nbsp;</label>
	</p>
</form>

<hr />
