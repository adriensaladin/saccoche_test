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
$TITRE = "Ordre d'affichage des matières";
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_ordre_matieres">DOC : Réglages synthèses &amp; bilans &rarr; Ordre d'affichage des matières</a></span></div>

<hr />

<form action="#" method="post" id="form_ordonner"><fieldset>
	<?php
	// liste des matières
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_etablissement( FALSE /*order_by_name*/ );
	if(!count($DB_TAB))
	{
		echo'<p class="danger">Aucune matière enregistrée ou associée à l\'établissement !</p>'; // impossible vu qu'il y a au moins la matière transversale...
	}
	else
	{
		echo'<ul id="sortable">';
		foreach($DB_TAB as $DB_ROW)
		{
			echo'<li id="m_'.$DB_ROW['matiere_id'].'">'.html($DB_ROW['matiere_nom']).'</li>';
		}
		echo'</ul>';
		echo'<p><span class="tab"></span><button id="Enregistrer_ordre" type="button" class="valider">Enregistrer cet ordre</button><label id="ajax_msg_ordre">&nbsp;</label></p>';
	}
	?>
</fieldset></form>
