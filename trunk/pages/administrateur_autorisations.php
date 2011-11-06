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
$TITRE = "Réglage des autorisations";
$VERSION_JS_FILE += 6;

$tab_titres  = array();
$tab_profils = array();
$tab_objets  = array();

$tab_titres[]  = 'Validations du socle';
$tab_profils[] = array( 'directeur'=>'directeurs' , 'professeur'=>'tous les<br />professeurs' , 'profprincipal'=>'professeurs<br />principaux' , 'aucunprof'=>'aucun<br />professeur' );
$tab_objets[]  = array( 'droit_validation_entree'=>'valider des items du socle' , 'droit_validation_pilier'=>'valider des compétences du socle' , 'droit_annulation_pilier'=>'annuler des validations de compétences' );

$tab_titres[]  = 'Référentiels en place dans l\'établissement';
$tab_profils[] = array( 'directeur'=>'directeurs' , 'professeur'=>'professeurs' , 'parent'=>'parents' , 'eleve'=>'élèves' );
$tab_objets[]  = array( 'droit_voir_referentiels'=>'consulter tous les référentiels' );

$tab_titres[]  = 'Score d\'un item &amp; état d\'acquisition';
$tab_profils[] = array( 'directeur'=>'directeurs' , 'professeur'=>'professeurs' , 'parent'=>'parents' , 'eleve'=>'élèves' );
$tab_objets[]  = array( 'droit_voir_score_bilan'=>'voir les scores des items (bilans)' , 'droit_voir_algorithme'=>' voir et simuler l\'algorithme de calcul' );

$tab_titres[]  = 'Mot de passe';
$tab_profils[] = array( 'directeur'=>'directeurs' , 'professeur'=>'professeurs' , 'parent'=>'parents' , 'eleve'=>'élèves' );
$tab_objets[]  = array( 'droit_modifier_mdp'=>'modifier son mot de passe' );

$tab_titres[]  = 'Bilan d\'items d\'une matière';
$tab_profils[] = array( 'parent'=>'parents' , 'eleve'=>'élèves' );
$tab_objets[]  = array( 'droit_bilan_moyenne_score'=>'afficher la ligne avec la moyenne des scores d\'acquisitions' , 'droit_bilan_pourcentage_acquis'=>'afficher la ligne avec le pourcentage d\'items acquis' , 'droit_bilan_note_sur_vingt'=>'ajouter la conversion en note sur 20' );

$tab_titres[]  = 'Détail de maîtrise de socle';
$tab_profils[] = array( 'parent'=>'parents' , 'eleve'=>'élèves' );
$tab_objets[]  = array( 'droit_socle_acces'=>'accéder au relevé avec les items évalués par item du socle' , 'droit_socle_pourcentage_acquis'=>'afficher les pourcentages d\'items acquis' , 'droit_socle_etat_validation'=>'afficher les états de validation saisis' );

$tab_false = array(
	'droit_validation_entree__profprincipal','droit_validation_entree__aucunprof',
	'droit_validation_pilier__professeur','droit_validation_pilier__aucunprof',
	'droit_annulation_pilier__professeur','droit_annulation_pilier__profprincipal',
	'droit_bilan_note_sur_vingt__parent','droit_bilan_note_sur_vingt__eleve',
	'droit_socle_etat_validation__parent','droit_socle_etat_validation__eleve'
);

$tab_init_js = 'var tab_init = new Array();';
$affichage = '';

foreach($tab_titres as $i => $titre)
{
	$affichage .= '<h4>'.$titre.'</h4>';
	$affichage .= '<table class="vm_nug">';
	// ligne en tête
	$affichage .= '<thead><tr><th class="nu"></th>';
	foreach($tab_profils[$i] as $profil_key => $profil_txt)
	{
		$affichage .= '<th class="hc">'.$profil_txt.'</th>';
	}
	$affichage .= '<th class="nu"></th></tr></thead>';
	// lignes avec boutons
	$affichage .= '<tbody>';
	foreach($tab_objets[$i] as $objet_key => $objet_txt)
	{
		$tab_init_js .= 'tab_init["'.$objet_key.'"] = new Array();';
		$affichage .= '<tr id="tr_'.$objet_key.'"><th>'.$objet_txt.'</th>';
		$tab_check = explode(',',$_SESSION[strtoupper($objet_key)]);
		foreach($tab_profils[$i] as $profil_key => $profil_txt)
		{
			$init = in_array($objet_key.'__'.$profil_key,$tab_false) ? 'false' : 'true' ;
			$tab_init_js .= 'tab_init["'.$objet_key.'"]["'.$profil_key.'"] = '.$init.';';
			$checked = (in_array($profil_key,$tab_check)) ? ' checked' : '' ;
			$type = (($i!=0)||($profil_key=='directeur')) ? 'checkbox' : 'radio' ;
			$affichage .= '<td class="hc"><input type="'.$type.'" name="'.$objet_key.'" value="'.$profil_key.'"'.$checked.' /></td>';
		}
		$affichage .= '<td class="nu">&nbsp;<button name="initialiser" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Par défaut</button> <button name="valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer</button> <label id="ajax_msg_'.$objet_key.'">&nbsp;</label></td></tr>';
	}
	$affichage .= '</tbody>';
	$affichage .= '</table>';
	$affichage .= '<hr />';
}
?>

<script type="text/javascript">
	<?php echo $tab_init_js ?> 
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_autorisations">DOC : Réglage des autorisations</a></span></div>

<hr />

<form action="#" method="post" id="form_autorisations">
<?php echo $affichage ?>
</form>
<p />