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
$TITRE = "Fichiers déposés";

$tab_select_taille_max = array(
	 100=>'100 Ko',
	 200=>'200 Ko',
	 500=>'500 Ko',
	1000=>'1 Mo',
	2000=>'2 Mo',
	5000=>'5 Mo'
);

$select_taille_max = '';
foreach($tab_select_taille_max as $option_value => $option_texte)
{
	$selected = ($option_value==FICHIER_TAILLE_MAX) ? ' selected' : '' ;
	$select_taille_max .= '<option value="'.$option_value.'"'.$selected.'>'.$option_texte.'</option>';
}

$tab_select_duree_conservation = array(
	 1=>'1 mois',
	 3=>'3 mois',
	 6=>'6 mois',
	 9=>'9 mois',
	12=>'1 an',
	24=>'2 ans',
	36=>'3 ans'
);

$select_duree_conservation = '';
foreach($tab_select_duree_conservation as $option_value => $option_texte)
{
	$selected = ($option_value==FICHIER_DUREE_CONSERVATION) ? ' selected' : '' ;
	$select_duree_conservation .= '<option value="'.$option_value.'"'.$selected.'>'.$option_texte.'</option>';
}

?>

<p>
	Les professeurs peuvent joindre à chaque évaluation un sujet et / ou une correction accessible par les élèves et les parents.<br />
	Ces fichiers dont stockés sur le serveur.<br />
	<span class="astuce">Les fichiers au format <em class="file file_php">php</em>, <em class="file file_exe">bat com exe</em> ou <em class="file file_zip">zip</em> sont rejetés car inappropriés ou potentiellement dangereux.</span>
</p>

<hr />

<form action="#" method="post" id="form_fichiers"><fieldset>
	<p>
		Vous pouvez choisir la taille maximale autorisée de ces fichiers.<br />
		La valeur par défaut est 500 Ko (limite considérée raisonnable vu l'usage destiné).<br />
		<span class="astuce">Il faut aussi tenir compte de la configuration du serveur : <?php echo InfoServeur::minimum_limitations_upload() ?>.</span>
	</p>
	<label class="tab" for="f_taille_max">Taille maximale :</label><select id="f_taille_max" name="f_taille_max"><?php echo $select_taille_max ?></select>
	<p>
		Vous pouvez choisir la durée de conservation de ces fichiers.<br />
		La valeur par défaut est 1 an (durée compatible avec une année scolaire).<br />
		<span class="astuce">Une initialisation annuelle des données supprime les accès aux évaluations, donc de toutes façons le référencement de ces documents.</span>
	</p>
	<label class="tab" for="f_duree_conservation">Durée conservation :</label><select id="f_duree_conservation" name="f_duree_conservation"><?php echo $select_duree_conservation ?></select>
	<p>
		<span class="tab"></span><button id="f_enregistrer" type="submit" class="parametre">Enregistrer ces paramètres.</button><label id="ajax_msg_enregistrer">&nbsp;</label><br />
	</p>
</fieldset></form>

<hr />

<div id="retour_test"></div>
