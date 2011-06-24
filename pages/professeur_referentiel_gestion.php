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
$TITRE = "Créer / paramétrer les référentiels";
$VERSION_JS_FILE += 12;
?>

<script type="text/javascript">
	<?php
	// Pour remplir la cellule avec la méthode de calcul par défaut en cas de création d'un nouveau référentiel
	$methode_calcul_langue = $_SESSION['CALCUL_METHODE'].'_'.$_SESSION['CALCUL_LIMITE'] ;
	if($_SESSION['CALCUL_LIMITE']==1)	// si une seule saisie prise en compte
	{
		$methode_calcul_texte = 'Seule la dernière saisie compte.';
	}
	elseif($_SESSION['CALCUL_METHODE']=='classique')	// si moyenne classique
	{
		$methode_calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Moyenne de toutes les saisies.' : 'Moyenne des '.$_SESSION['CALCUL_LIMITE'].' dernières saisies.';
	}
	elseif(in_array($_SESSION['CALCUL_METHODE'],array('geometrique','arithmetique')))	// si moyenne geometrique | arithmetique
	{
		$seize = (($_SESSION['CALCUL_METHODE']=='geometrique')&&($_SESSION['CALCUL_LIMITE']==5)) ? 1 : 0 ;
		$coefs = ($_SESSION['CALCUL_METHODE']=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$_SESSION['CALCUL_LIMITE']-19) : substr('1/2/4/8/16/',0,2*$_SESSION['CALCUL_LIMITE']-12+$seize) ;
		$methode_calcul_texte = 'Les '.$_SESSION['CALCUL_LIMITE'].' dernières saisies &times;'.$coefs.'.';
	}
	elseif($_SESSION['CALCUL_METHODE']=='bestof1')	// si meilleure note
	{
		$methode_calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Seule la meilleure saisie compte.' : 'Meilleure des '.$_SESSION['CALCUL_LIMITE'].' dernières saisies.';
	}
	elseif(in_array($_SESSION['CALCUL_METHODE'],array('bestof2','bestof3')))	// si 2 | 3 meilleures notes
	{
		$nb_best = (int)substr($_SESSION['CALCUL_METHODE'],-1);
		$methode_calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Moyenne des '.$nb_best.' meilleures saisies.' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$_SESSION['CALCUL_LIMITE'].' dernières.';
	}
	?>
	var methode_calcul_langue     = "<?php echo $methode_calcul_langue ?>";
	var methode_calcul_texte      = "<?php echo $methode_calcul_texte ?>";
	var id_matiere_transversale   = "<?php echo ID_MATIERE_TRANSVERSALE ?>";
	var listing_id_niveaux_cycles = "<?php echo LISTING_ID_NIVEAUX_CYCLES ?>";
</script>

<form id="form_instance" action="" class="noprint">

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_creer_parametrer">DOC : Créer / paramétrer les référentiels.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_organisation">DOC : Organisation des items dans les référentiels.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></li>
	<li><span class="danger">Détruire un référentiel supprime les résultats associés de tous les élèves !</span></li>
</ul>

<hr />

<?php
// J'ai séparé en plusieurs requêtes au bout de plusieurs heures sans m'en sortir (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();
$tab_colonne = array();

// On récupère la liste des matières où le professeur est rattaché, et s'il en est coordonnateur
$DB_TAB = DB_STRUCTURE_lister_matieres_professeur_infos_referentiel($_SESSION['MATIERES'],$_SESSION['USER_ID']);
if(count($DB_TAB))
{
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_matiere[$DB_ROW['matiere_id']] = array( 'nom'=>html($DB_ROW['matiere_nom']) , 'partage'=>$DB_ROW['matiere_partage'] , 'nb_demandes'=>$DB_ROW['matiere_nb_demandes'] , 'coord'=>$DB_ROW['jointure_coord'] );
	}
}
$listing_matieres_id = implode(',',array_keys($tab_matiere));

if(!$listing_matieres_id)
{
	echo'<p><span class="danger">Vous n\'êtes rattaché à aucune matière de l\'établissement !</span></p>';
}
elseif(!$_SESSION['NIVEAUX']) // normalement impossible
{
	echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
}
elseif(!$_SESSION['CYCLES']) // normalement impossible
{
	echo'<p><span class="danger">Aucun cycle n\'est rattaché à l\'établissement !</span></p>';
}
else
{
	// On récupère la liste des niveaux utilisés par l'établissement
	$DB_TAB = DB_STRUCTURE_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$_SESSION['CYCLES']);
	$nb_niveaux = count($DB_TAB);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_niveau[$DB_ROW['niveau_id']] = html($DB_ROW['niveau_nom']);
	}
	// On récupère la liste des référentiels par matière et niveau
	$tab_partage = array('oui'=>'<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	$DB_TAB = DB_STRUCTURE_lister_referentiels_infos_details_matieres_niveaux($listing_matieres_id,$_SESSION['NIVEAUX'],$_SESSION['CYCLES']);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			// Définition de $methode_calcul_texte
			if($DB_ROW['referentiel_calcul_limite']==1)	// si une seule saisie prise en compte
			{
				$methode_calcul_texte = 'Seule la dernière saisie compte.';
			}
			elseif($DB_ROW['referentiel_calcul_methode']=='classique')	// si moyenne classique
			{
				$methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne de toutes les saisies.' : 'Moyenne des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies.';
			}
			elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('geometrique','arithmetique')))	// si moyenne geometrique | arithmetique
			{
				$seize = (($DB_ROW['referentiel_calcul_methode']=='geometrique')&&($DB_ROW['referentiel_calcul_limite']==5)) ? 1 : 0 ;
				$coefs = ($DB_ROW['referentiel_calcul_methode']=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$DB_ROW['referentiel_calcul_limite']-19) : substr('1/2/4/8/16/',0,2*$DB_ROW['referentiel_calcul_limite']-12+$seize) ;
				$methode_calcul_texte = 'Les '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies &times;'.$coefs.'.';
			}
			elseif($DB_ROW['referentiel_calcul_methode']=='bestof1')	// si meilleure note
			{
				$methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Seule la meilleure saisie compte.' : 'Meilleure des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies.';
			}
			elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('bestof2','bestof3')))	// si 2 | 3 meilleures notes
			{
				$nb_best = (int)substr($DB_ROW['referentiel_calcul_methode'],-1);
				$methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne des '.$nb_best.' meilleures saisies.' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$DB_ROW['referentiel_calcul_limite'].' dernières.';
			}
			$tab_colonne[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = '<td lang="'.$DB_ROW['referentiel_partage_etat'].'" class="v">Référentiel présent. '.str_replace('◄DATE►',affich_date($DB_ROW['referentiel_partage_date']),$tab_partage[$DB_ROW['referentiel_partage_etat']]).'</td>'.'<td lang="'.$DB_ROW['referentiel_calcul_methode'].'_'.$DB_ROW['referentiel_calcul_limite'].'" class="v">'.$methode_calcul_texte.'</td>';
		}
	}
	// Construction du formulaire select du nombre de demandes
	$select_demandes = '<select name="f_eleve_demandes" class="t8">';
	for($nb_demandes=0 ; $nb_demandes<10 ; $nb_demandes++)
	{
		$texte = ($nb_demandes>0) ? ( ($nb_demandes>1) ? $nb_demandes.' demandes' : '1 seule demande' ) : 'aucune demande' ;
		$select_demandes .= '<option value="'.$nb_demandes.'">'.$texte.'</option>';
	}
	$select_demandes .= '</select>';
	$infobulle = '<img src="./_img/bulle_aide.png" alt="" title="Nombre maximal de demandes d\'évaluations simultanées autorisées pour un élève." />';
	// On construit et affiche le tableau résultant
	$affichage = '<table class="vm_nug"><thead><tr><th>Matière</th><th>Niveau</th><th>Référentiel</th><th>Méthode de calcul</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$rowspan = ($matiere_id!=ID_MATIERE_TRANSVERSALE) ? $nb_niveaux : mb_substr_count($_SESSION['CYCLES'],',','UTF-8')+1 ;
		$matiere_nom    = $tab['nom'];
		$matiere_coord  = $tab['coord'];
		$matiere_perso  = ($tab['partage']) ? 0 : 1 ;
		$matiere_nombre = ($matiere_coord) ? str_replace('value="'.$tab['nb_demandes'].'"','value="'.$tab['nb_demandes'].'" selected',$select_demandes) : str_replace('<select','<select disabled',$select_demandes) ;
		$affichage .= '<tr><td colspan="5" class="nu">&nbsp;</td></tr>'."\r\n";
		$affichage .= '<tr><td rowspan="'.$rowspan.'" id="mat_'.$matiere_id.'"><b>'.$matiere_nom.'</b><br />'.$matiere_nombre.$infobulle.'</td>';
		$affichage_suite = false;
		foreach($tab_niveau as $niveau_id => $niveau_nom)
		{
			if( ($matiere_id!=ID_MATIERE_TRANSVERSALE) || (strpos(LISTING_ID_NIVEAUX_CYCLES,'.'.$niveau_id.'.')!==FALSE) )
			{
				$ids = 'ids_'.$matiere_perso.'_'.$matiere_id.'_'.$niveau_id;
				if($matiere_coord)
				{
					$partager = ($matiere_perso) ? '<q class="partager_non" title="Le référentiel d\'une matière spécifique à l\'établissement ne peut être partagé."></q>' : '<q class="partager" title="Modifier le partage de ce référentiel."></q>' ;
					$envoyer = ( (isset($tab_colonne[$matiere_id][$niveau_id])) && (substr($tab_colonne[$matiere_id][$niveau_id],0,14)=='<td lang="oui"') ) ? '<q class="envoyer" title="Mettre à jour sur le serveur de partage la dernière version de ce référentiel."></q>' : '<q class="envoyer_non" title="Un référentiel non partagé ne peut pas être transmis à la collectivité."></q>' ;
					$colonnes = (isset($tab_colonne[$matiere_id][$niveau_id])) ? $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q>'.$partager.$envoyer.'<q class="calculer" title="Modifier le mode de calcul associé à ce référentiel."></q><q class="supprimer" title="Supprimer ce référentiel."></q></td>' : '<td class="r">Absence de référentiel.</td><td class="r">Sans objet.</td><td class="nu" id="'.$ids.'"><q class="ajouter" title="Créer un référentiel vierge ou importer un référentiel existant"></q></td>' ;
				}
				else
				{
					$colonnes = (isset($tab_colonne[$matiere_id][$niveau_id])) ? $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q><q class="partager_non" title="Action réservée aux coordonnateurs."></q><q class="envoyer_non" title="Action réservée aux coordonnateurs."></q><q class="calculer_non" title="Action réservée aux coordonnateurs."></q><q class="supprimer_non" title="Action réservée aux coordonnateurs."></q></td>' : '<td class="r">Absence de référentiel.</td><td class="r">Sans objet.</td><td class="nu" id="'.$ids.'"><q class="ajouter_non" title="Action réservée aux coordonnateurs."></q></td>' ;
				}
				if($affichage_suite===false)
				{
					$affichage .= '<td>'.$niveau_nom.'</td>'.$colonnes;
					$affichage_suite = '';
				}
				else
				{
					$affichage_suite .= '<tr><td>'.$niveau_nom.'</td>'.$colonnes.'</tr>'."\r\n";
				}
			}
		}
		$affichage .= '</tr>'."\r\n".$affichage_suite;
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>

<div id="succes_import">
</div>

<div id="choisir_referentiel" class="hide">
	<hr />
	<h2>Choisir un référentiel</h2>
	<p><button id="choisir_initialiser" type="button" value="id_0"><img alt="" src="./_img/bouton/valider.png" /> Démarrer avec un référentiel vierge.</button></p>
	<?php
	if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
	{
		echo'<p><label for="rien" class="erreur">Pour pouvoir effectuer la recherche d\'un référentiel partagé sur le serveur communautaire, un administrateur doit préalablement identifier l\'établissement dans la base Sésamath (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure">DOC : Gestion de l\'identité de l\'établissement</a></span>).</label></p>';
	}
	else
	{
		echo'<p><button id="choisir_rechercher" type="button"><img alt="" src="./_img/bouton/rechercher.png" /> Rechercher parmi les référentiels partagés sur le serveur communautaire.</button></p>';
		echo'<p><button id="choisir_importer" type="button" value="id_x"><img alt="" src="./_img/bouton/valider.png" /> Démarrer avec ce référentiel : <b id="reporter"></b></button></p>';
	}
	?>
	<p><button id="choisir_annuler" type="button"><img alt="" src="./_img/bouton/annuler.png" /> Annuler la création d'un référentiel.</button></p>
	<label id="ajax_msg_choisir">&nbsp;</label>
</div>

</form>

<form id="form_communautaire" action="" class="hide">

<?php
// Fabrication des éléments select du formulaire, pour pouvoir prendre un référentiel d'une autre matière ou d'un autre niveau (demandé...).
$select_matiere = afficher_select(DB_STRUCTURE_OPT_matieres_communes() , $select_nom='f_matiere' , $option_first='val' , $selection=false , $optgroup='non');
$select_niveau  = afficher_select(DB_STRUCTURE_OPT_niveaux()           , $select_nom='f_niveau'  , $option_first='val' , $selection=false , $optgroup='non');
?>

<div class="noprint">
	<div id="choisir_referentiel_communautaire">
		<h2>Rechercher un référentiel partagé sur le serveur communautaire</h2>
		<p><button id="rechercher_annuler" type="button"><img alt="" src="./_img/bouton/annuler.png" /> Annuler la recherche d'un référentiel.</button></p>
		<fieldset>
			<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><br />
			<label class="tab" for="f_niveau">Niveau :</label><?php echo $select_niveau ?><br />
			<label class="tab" for="f_structure"><img alt="" src="./_img/bulle_aide.png" title="Seules les structures partageant au moins un référentiel apparaissent." /> Structure :</label><select id="f_structure" name="f_structure"><option></option></select><br />
			<span class="tab"></span><button id="rechercher" type="button" class="hide"><img alt="" src="./_img/bouton/rechercher.png" /> Lancer / Actualiser la recherche.</button><label id="ajax_msg">&nbsp;</label>
		</fieldset>
		<hr />
		<div id="lister_referentiel_communautaire" class="hide">
			<h2>Liste des référentiels trouvés</h2>
			<div class="danger">Les référentiels partagés ne sont pas des modèles exemplaires à suivre ! Ils peuvent être améliorables, voir inadaptés...</div>
			<ul>
				<li></li>
			</ul>
		</div>
	</div>
</div>

</form>

<div id="voir_referentiel">
</div>
