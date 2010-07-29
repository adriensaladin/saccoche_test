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
$TITRE = "Profils autorisés à valider le socle";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_profils_validation">DOC : Gestion des profils autorisés à valider le socle</a></span></p>

<hr />

<form action="">
	<table>
	<?php
	$tab_profils = array( 'directeur'=>'directeurs' , 'professeur'=>'tous les professeurs' , 'profprincipal'=>'seulement les<br />professeurs principaux' , 'aucunprof'=>'aucun professeur' );
	$tab_objets  = array( 'profil_validation_entree'=>'validation des items du socle' , 'profil_validation_pilier'=>'validation des compétences du socle (ou piliers)' );
	// 1ère ligne
	echo'<thead><tr><th class="nu"></th>';
	foreach($tab_profils as $profil_key => $profil_txt)
	{
		echo'<th class="hc">'.$profil_txt.'</th>';
	}
	echo'</tr></thead>';
	// Les lignes avec checkbox
	echo'<tbody>';
	foreach($tab_objets as $objet_key => $objet_txt)
	{
		echo'<tr><th>'.$objet_txt.'</th>';
		$tab_check = explode(',',$_SESSION[strtoupper($objet_key)]);
		foreach($tab_profils as $profil_key => $profil_txt)
		{
			$checked = (in_array($profil_key,$tab_check)) ? ' checked="checked"' : '' ;
			$type = ($profil_key=='directeur') ? 'checkbox' : 'radio' ;
			echo'<td class="hc"><input type="'.$type.'" name="'.$objet_key.'" value="'.$profil_key.'"'.$checked.' /></td>';
		}
		echo'</tr>';
	}
	echo'</tbody>';
	?>
	<table>
	<p>
		<span class="tab"></span>
		<button id="initialiser_defaut" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les droits par défaut.</button>
		<button id="bouton_valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces droits.</button>
		<label id="ajax_msg">&nbsp;</label>
	</p>
</form>
