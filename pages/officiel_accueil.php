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

$tab_types = array
(
	'releve'   => array( 'droit'=>'RELEVE'   , 'doc'=>'officiel_releve_evaluations' , 'titre'=>'Relevé d\'évaluations' , 'modif_rubrique'=>'les appréciations par matière' ) ,
	'bulletin' => array( 'droit'=>'BULLETIN' , 'doc'=>'officiel_bulletin_scolaire'  , 'titre'=>'Bulletin scolaire'     , 'modif_rubrique'=>'les notes et appréciations par matière' ) ,
	'palier1'  => array( 'droit'=>'SOCLE'    , 'doc'=>'officiel_maitrise_palier'    , 'titre'=>'Maîtrise du palier 1'  , 'modif_rubrique'=>'les appréciations par compétence' ) ,
	'palier2'  => array( 'droit'=>'SOCLE'    , 'doc'=>'officiel_maitrise_palier'    , 'titre'=>'Maîtrise du palier 2'  , 'modif_rubrique'=>'les appréciations par compétence' ) ,
	'palier3'  => array( 'droit'=>'SOCLE'    , 'doc'=>'officiel_maitrise_palier'    , 'titre'=>'Maîtrise du palier 3'  , 'modif_rubrique'=>'les appréciations par compétence' )
);

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(!isset($BILAN_TYPE)) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = $tab_types[$BILAN_TYPE]['titre'];

require(CHEMIN_DOSSIER_INCLUDE.'tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_profils = array('directeur','professeur','profprincipal');

// Indication des profils pouvant modifier le statut d'un bilan
$str_objet = str_replace( array(',aucunprof','aucunprof,','aucunprof') , '' , $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'] );
foreach($tab_profils as $profil)
{
	$str_objet = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$str_objet);
}
$profils_modifier_statut = ($str_objet=='') ? 'aucun' : ( (strpos($str_objet,',')===FALSE) ? 'uniquement les '.$str_objet : str_replace(',',' + ',$str_objet) ) ;

// Indication des profils ayant accès à l'appréciation générale
$str_objet = str_replace( array(',aucunprof','aucunprof,','aucunprof') , '' , $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'] );
foreach($tab_profils as $profil)
{
	$str_objet = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$str_objet);
}
$profils_appreciation_generale = ($str_objet=='') ? 'aucun' : ( (strpos($str_objet,',')===FALSE) ? 'uniquement les '.$str_objet : str_replace(',',' + ',$str_objet) ) ;

// Indication des profils ayant accès à l'impression PDF
$str_objet = str_replace( array(',aucunprof','aucunprof,','aucunprof') , '' , $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'] );
foreach($tab_profils as $profil)
{
	$str_objet = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$str_objet);
}
$profils_impression_pdf = ($str_objet=='') ? 'aucun' : ( (strpos($str_objet,',')===FALSE) ? 'uniquement les '.$str_objet : str_replace(',',' + ',$str_objet) ) ;

// Indication des profils ayant accès aux copies des impressions PDF
$tab_profils = array('directeur','professeur','eleve','parent');
$str_objet = $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_VOIR_ARCHIVE'];
foreach($tab_profils as $profil)
{
	$str_objet = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$str_objet);
}
$profils_archives_pdf = ($str_objet=='') ? 'aucun' : ( (strpos($str_objet,',')===FALSE) ? 'uniquement les '.$str_objet : str_replace(',',' + ',$str_objet) ) ;

// Droit de modifier le statut d'un bilan (dans le cas PP, restera à affiner classe par classe...).
$affichage_formulaire_statut = 
( 
	   ($_SESSION['USER_PROFIL']=='administrateur')
	|| ( ($_SESSION['USER_PROFIL']=='directeur')  && (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'directeur')!==FALSE) )
	|| ( ($_SESSION['USER_PROFIL']=='professeur') && (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'professeur')!==FALSE) )
	|| ( ($_SESSION['USER_PROFIL']=='professeur') && (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'profprincipal')!==FALSE) && (DB_STRUCTURE_PROFESSEUR::DB_tester_prof_principal($_SESSION['USER_ID'])) )
) ? TRUE : FALSE ;

?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__<?php echo $tab_types[$BILAN_TYPE]['doc'] ?>">DOC : Bilan officiel &rarr; <?php echo $tab_types[$BILAN_TYPE]['titre'] ?></a></span></li>
</ul>
<ul class="puce" id="puces_secondaires">
	<li><span class="astuce">Profils autorisés à modifier le statut du bilan : <span class="u"><?php echo $profils_modifier_statut ?></span>.</span></li>
	<li><span class="astuce">Profils autorisés à éditer l'appréciation générale : <span class="u"><?php echo $profils_appreciation_generale ?></span>.</span></li>
	<li><span class="astuce">Profils autorisés à générer la version imprimable (<em>pdf</em>) : <span class="u"><?php echo $profils_impression_pdf ?></span>.</span></li>
	<li><span class="astuce">Profils autorisés à consulter les copies imprimées (<em>pdf</em>) : <span class="u"><?php echo $profils_archives_pdf ?></span>.</span></li>
	<?php if($affichage_formulaire_statut) echo'<li><span class="astuce">Vous pouvez utiliser l\'outil d\'<a href="./index.php?page=compte_message">affichage de messages en page d\'accueil</a> pour informer les professeurs de l\'ouverture à la saisie.</span></li>'; ?>
</ul>

<script type="text/javascript">
	var TODAY_FR   = "<?php echo TODAY_FR ?>";
	var BILAN_TYPE = "<?php echo $BILAN_TYPE ?>";
	var APP_RUBRIQUE = <?php echo $_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE'] ?>;
	var APP_GENERALE = <?php echo $_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'] ?>;
	var NOTE_SUR_20  = <?php echo $_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20'] ?>;
	var BACKGROUND_NA = "<?php echo $_SESSION['BACKGROUND_NA'] ?>";
	var BACKGROUND_VA = "<?php echo $_SESSION['BACKGROUND_VA'] ?>";
	var BACKGROUND_A  = "<?php echo $_SESSION['BACKGROUND_A'] ?>";
	"'..'"
</script>

<hr />

<?php
$tab_etats = array
(
	'0absence'  => 'indéfini',
	'1vide'     => 'Vide (fermé)',
	'2rubrique' => '<span class="now">Saisies Profs</span>',
	'3synthese' => '<span class="now">Saisie Synthèse</span>',
	'4complet'  => 'Complet (fermé)'
);

// Préparation du tableau avec les cellules à afficher
$tab_affich = array(); // [classe_id_groupe_id][periode_id] (ligne colonne) ; les indices [check] sont ceux des checkbox multiples ; les indices [title] sont ceux des intitulés
$tab_affich['check']['check'] = ($affichage_formulaire_statut) ? '<td class="nu"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /></td>' : '' ;
$tab_affich['check']['title'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['title']['check'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['title']['title'] = '<td class="nu"></td>' ;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Récupération et traitement des données postées, si formulaire soumis
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($affichage_formulaire_statut) && ($_SESSION['SESAMATH_ID']!=ID_DEMO) )
{
	$tab_ids  = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : array() ;
	$new_etat = (isset($_POST['etat']))        ? Clean::texte($_POST['etat'])        : '' ;
	if( count($tab_ids) && isset($tab_etats[$new_etat]) )
	{
		$champ = 'officiel_'.$BILAN_TYPE;
		$new_etat = ($new_etat!='x') ? $new_etat : '' ;
		foreach($tab_ids as $ids)
		{
			list( $classe_id , $periode_id ) = explode('p',substr($ids,1));
			if( (int)$classe_id && (int)$periode_id )
			{
				DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_bilan_officiel($classe_id,$periode_id,$champ,$new_etat);
			}
		}
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Récupération de la liste des classes de l'établissement.
//	Utile pour les profils administrateurs / directeurs, et requis concernant les professeurs pour une recherche s'il est affecté à des groupes.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_classes_etabl_for_bilan_officiel();

$tab_classe_etabl = array(); // tableau temporaire avec les noms des classes de l'établissement
foreach($DB_TAB as $DB_ROW)
{
	$tab_classe_etabl[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Récupérer la liste des classes accessibles à l'utilisateur.
//	Indiquer celles potentiellement accessibles à l'utilisateur pour l'appréciation générale.
//	Indiquer celles potentiellement accessibles à l'utilisateur pour l'impression PDF.
//
//	Pour les administrateurs et les directeurs, ce sont les classes de l'établissement.
//	Mais attention, les bilans ne sont définis que sur les classes, pas sur des groupes (car il ne peut y avoir qu'un type de bilan par élève / période).
//	Alors quand les professeurs sont associés à des groupes, il faut chercher de quelle(s) classe(s) proviennent les élèves et proposer autant de choix partiels... sur ces classes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_classe = array(); // tableau important avec les droits [classe_id][0|groupe_id]
$tab_groupe = array(); // tableau temporaire avec les noms des groupes du prof
$tab_options_classes = array(); // Pour un futur formulaire select

$droit_voir_archives_pdf = (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_VOIR_ARCHIVE'],'professeur')!==FALSE) ? TRUE : FALSE ;

if($_SESSION['USER_PROFIL']!='professeur') // administrateur | directeur
{
	$droit_modifier_statut       = ( ($_SESSION['USER_PROFIL']=='administrateur') || (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'directeur')!==FALSE)       ) ? TRUE : FALSE ;
	$droit_appreciation_generale = ( ($_SESSION['USER_PROFIL']=='directeur')      && (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'],'directeur')!==FALSE) ) ? TRUE : FALSE ;
	$droit_impression_pdf        = ( ($_SESSION['USER_PROFIL']=='administrateur') || (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'],'directeur')!==FALSE)        ) ? TRUE : FALSE ;
	$droit_voir_archives_pdf     = ( ($_SESSION['USER_PROFIL']=='administrateur') || (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_VOIR_ARCHIVE'],'directeur')!==FALSE)               ) ? TRUE : FALSE ;
	foreach($tab_classe_etabl as $classe_id => $classe_nom)
	{
		$tab_classe[$classe_id][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' , 'droit_voir_archives_pdf' );
		$tab_affich[$classe_id.'_0']['check'] = '<th class="nu"><input name="all_check" type="image" id="id_deb1_g'.$classe_id.'p" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" id="id_deb2_g'.$classe_id.'p" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>' ;
		$tab_affich[$classe_id.'_0']['title'] = '<th id="groupe_'.$classe_id.'_0">'.html($classe_nom).'</th>' ;
		$tab_options_classes[$classe_id.'_0'] = '<option value="'.$classe_id.'_0">'.html($classe_nom).'</option>';
	}
}
else // professeur
{
	$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_groupes_professeur($_SESSION['USER_ID']);
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['groupe_type']=='classe')
		{
			// Pour les classes, RAS
			$droit_modifier_statut       = ( (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'professeur')!==FALSE)       || ( (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'profprincipal')!==FALSE)       && $DB_ROW['jointure_pp'] ) ) ? TRUE : FALSE ;
			$droit_appreciation_generale = ( (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'],'professeur')!==FALSE) || ( (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'],'profprincipal')!==FALSE) && $DB_ROW['jointure_pp'] ) ) ? TRUE : FALSE ;
			$droit_impression_pdf        = ( (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'],'professeur')!==FALSE)        || ( (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'],'profprincipal')!==FALSE)        && $DB_ROW['jointure_pp'] ) ) ? TRUE : FALSE ;
			$tab_classe[$DB_ROW['groupe_id']][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' );
			$tab_affich[$DB_ROW['groupe_id'].'_0']['check'] = ($affichage_formulaire_statut) ? ( ($droit_modifier_statut) ? '<th class="nu"><input name="all_check" type="image" id="id_deb1_g'.$DB_ROW['groupe_id'].'p" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" id="id_deb2_g'.$DB_ROW['groupe_id'].'p" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>' : '<th class="nu"></th>' ) : '' ;
			$tab_affich[$DB_ROW['groupe_id'].'_0']['title'] = '<th id="groupe_'.$DB_ROW['groupe_id'].'_0">'.html($DB_ROW['groupe_nom']).'</th>' ;
			$tab_options_classes[$DB_ROW['groupe_id'].'_0'] = '<option value="'.$DB_ROW['groupe_id'].'_0">'.html($DB_ROW['groupe_nom']).'</option>';
		}
		else
		{
			// Pour les groupes, il faudra récupérer les classes dont sont issues les élèves
			$tab_groupe[$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
		}
	}
	if(count($tab_groupe))
	{
		// On récupère les classes dont sont issues les élèves des groupes et on complète $tab_classe
		$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_eleves_from_groupes( implode(',',array_keys($tab_groupe)) );
		foreach($tab_groupe as $groupe_id => $groupe_nom)
		{
			if(isset($DB_TAB[$groupe_id]))
			{
				foreach($DB_TAB[$groupe_id] as $tab)
				{
					$classe_id = $tab['eleve_classe_id'];
					$droit_modifier_statut       = FALSE ;
					$droit_appreciation_generale = (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'],'professeur')!==FALSE) ? TRUE : FALSE ;
					$droit_impression_pdf        = (strpos($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'],'professeur')!==FALSE)        ? TRUE : FALSE ;
					$tab_classe[$classe_id][$groupe_id] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' );
					$tab_affich[$classe_id.'_'.$groupe_id]['check'] =  ($affichage_formulaire_statut) ? '<th class="nu"></th>' : '' ;
					$tab_affich[$classe_id.'_'.$groupe_id]['title'] = '<th id="groupe_'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id]).'<br />'.html($groupe_nom).'</th>' ;
					$tab_options_classes[$classe_id.'_'.$groupe_id] = '<option value="'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id].' - '.$groupe_nom).'</option>';
				}
			}
		}
	}
}

if(count($tab_classe))
{

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Récupérer la liste des périodes, dans l'ordre choisi par l'admin.
	//	Initialiser au passages les cellules du tableau à afficher
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_periodes();
	if(count($DB_TAB))
	{
		$tab_ligne_id = array_keys($tab_affich);
		unset($tab_ligne_id[0],$tab_ligne_id[1]);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_affich['check'][$DB_ROW['periode_id']] = ($affichage_formulaire_statut) ? '<th class="nu"><input name="all_check" type="image" id="id_fin1_p'.$DB_ROW['periode_id'].'" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" id="id_fin2_p'.$DB_ROW['periode_id'].'" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>' : '' ;
			$tab_affich['title'][$DB_ROW['periode_id']] = '<th class="hc" id="periode_'.$DB_ROW['periode_id'].'">'.html($DB_ROW['periode_nom']).'</th>' ;
			foreach($tab_ligne_id as $ligne_id)
			{
				$tab_affich[$ligne_id][$DB_ROW['periode_id']] = '<td class="hc">-</td>' ;
				
			}
		}

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Récupérer la liste des jointures classes / périodes.
		//	Pour les groupes, on prend les dates de classes dont les élèves sont issus.
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$tab_js_disabled = '';
		$listing_classes_id = implode(',',array_keys($tab_classe));
		$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_jointure_groupe_periode($listing_classes_id);
		foreach($DB_TAB as $DB_ROW)
		{
			$classe_id = $DB_ROW['groupe_id'];
			$etat = ($DB_ROW['officiel_'.$BILAN_TYPE]) ? $DB_ROW['officiel_'.$BILAN_TYPE] : '0absence' ;
			// dates
			$date_affich_debut = convert_date_mysql_to_french($DB_ROW['jointure_date_debut']);
			$date_affich_fin   = convert_date_mysql_to_french($DB_ROW['jointure_date_fin']);
			$affich_dates = (($BILAN_TYPE=='releve')||($BILAN_TYPE=='bulletin')) ? $date_affich_debut.' ~ '.$date_affich_fin : 'au '.$date_affich_fin.' (indicatif)' ;
			// État
			$affich_etat = '<span class="off_etat '.substr($etat,1).'">'.$tab_etats[$etat].'</span>';
			// images action : vérification
			if($etat=='2rubrique')
			{
				$icone_verification = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE']) ? '<q class="detailler" title="Rechercher les saisies manquantes."></q>' : '<q class="detailler_non" title="Recherche de saisies manquantes sans objet car bilan configuré sans saisie intermédiaire."></q>' ;
			}
			elseif($etat=='3synthese')
			{
				$icone_verification = ( ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE']) || ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE']) ) ? '<q class="detailler" title="Rechercher les saisies manquantes."></q>' : '<q class="detailler_non" title="Recherche de saisies manquantes sans objet car bilan configuré sans saisie intermédiaire ni de synthèse."></q>' ;
			}
			else
			{
				$icone_verification = '<q class="detailler_non" title="La recherche de saisies manquantes est sans objet lorsque l\'accès en saisie est fermé."></q>';
			}
			// images action : consultation contenu en cours d'élaboration (bilans HTML)
			if($_SESSION['USER_PROFIL']!='administrateur')
			{
				if($etat=='1vide')
				{
					$icone_voir_html = '<q class="voir_non" title="Consultation du contenu sans objet (bilan déclaré vide)."></q>';
				}
				elseif( ($etat=='4complet') && ($tab_types[$BILAN_TYPE]['droit']=='SOCLE') )
				{
					$icone_voir_html = '<q class="voir_non" title="Consultation du contenu inopportun (bilan finalisé : utiliser les archives PDF)."></q>';
				}
				else
				{
					$icone_voir_html = '<q class="voir" title="Consulter le contenu (format HTML)."></q>';
				}
			}
			else
			{
				$icone_voir_html = '';
			}
			// images action : consultation contenu finalisé (bilans PDF)
			if(!$droit_voir_archives_pdf)
			{
				$icone_voir_pdf = '<q class="voir_archive_non" title="Accès restreint aux copies des impressions PDF : '.$profils_archives_pdf.'."></q>';
			}
			elseif($etat!='4complet')
			{
				$icone_voir_pdf = '<q class="voir_archive_non" title="Consultation du bilan imprimé sans objet (bilan déclaré non finalisé)."></q>';
			}
			else
			{
				$icone_voir_pdf = '<q class="voir_archive" title="Consulter une copie du bilan imprimé finalisé (format PDF)."></q>';
			}
			// Il n'y a pas que la ligne de la classe, il y a les lignes des groupes dont des élèves font partie de la classe
			// Les images action de saisie et d'impression dépendent du groupe
			foreach($tab_classe[$classe_id] as $groupe_id=> $tab_droits)
			{
				// checkbox de gestion
				if( ($affichage_formulaire_statut) && ($tab_droits['droit_modifier_statut']) )
				{
					$id = 'g'.$classe_id.'p'.$DB_ROW['periode_id'];
					$label_avant = '<label for="'.$id.'">' ;
					$checkbox    = ' <input id="'.$id.'" name="'.$id.'" type="checkbox" />';
					$label_apres = '</label>' ;
				}
				else
				{
					$label_avant = $checkbox = $label_apres = '' ;
				}
				// images action : saisie
				if($_SESSION['USER_PROFIL']!='administrateur')
				{
					if($etat=='2rubrique')
					{
						$icone_saisie = ($_SESSION['USER_PROFIL']=='professeur') ? ( ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE']) ? '<q class="modifier" title="Saisir '.$tab_types[$BILAN_TYPE]['modif_rubrique'].'."></q>' : '<q class="modifier_non" title="Bilan configuré sans saisie intermédiaire."></q>' ) : '<q class="modifier_non" title="Accès réservé aux professeurs."></q>' ;
					}
					else
					{
						$icone_saisie = '<q class="modifier_non" title="Accès fermé aux saisies intermédiaires."></q>';
					}
				}
				else
				{
					$icone_saisie = '';
				}
				// images action : tamponner
				if($_SESSION['USER_PROFIL']!='administrateur')
				{
					if($etat=='3synthese')
					{
						$icone_tampon = ($tab_droits['droit_appreciation_generale']) ? ( ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE']) ? '<q class="tamponner" title="Saisir l\'appréciation générale."></q>' : '<q class="tamponner_non" title="Bilan configuré sans saisie de synthèse."></q>' ) : '<q class="tamponner_non" title="Accès restreint à la saisie de l\'appréciation générale : '.$profils_appreciation_generale.'."></q>' ;
					}
					else
					{
						$icone_tampon = '<q class="tamponner_non" title="Accès fermé à la saisie de synthèse."></q>';
					}
				}
				else
				{
					$icone_tampon = '';
				}
				// images action : impression
				if($tab_droits['droit_impression_pdf'])
				{
					$icone_impression = ($etat=='4complet') ? '<q class="imprimer" title="Imprimer le bilan (PDF)."></q>' : '<q class="imprimer_non" title="L\'impression est possible une fois le bilan déclaré complet."></q>' ;
				}
				else
				{
					$icone_impression = '<q class="imprimer_non" title="Accès restreint à l\'impression PDF : '.$profils_impression_pdf.'."></q>';
				}
				if($etat!='0absence')
				{
					$tab_affich[$classe_id.'_'.$groupe_id][$DB_ROW['periode_id']] = '<td id="cgp_'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'" class="hc notnow">'.$label_avant.$affich_dates.'<br />'.$affich_etat.$checkbox.$label_apres.'<br />'.$icone_saisie.$icone_tampon.$icone_verification.$icone_voir_html.$icone_impression.$icone_voir_pdf.'</td>';
				}
				elseif($checkbox!='')
				{
					$tab_affich[$classe_id.'_'.$groupe_id][$DB_ROW['periode_id']] = '<td class="hc notnow">'.$label_avant.$affich_dates.'<br />'.$affich_etat.$checkbox.$label_apres.'</td>';
				}
				else
				{
					$tab_affich[$classe_id.'_'.$groupe_id][$DB_ROW['periode_id']] = '<td class="hc notnow">'.$affich_dates.'<br />'.$affich_etat.'</td>';
				}
				// tableau javascript pour desactiver ce qui est inaccessible
				$disabled_examiner = strpos($icone_verification,'detailler_non') ? 'true' : 'false';
				$disabled_imprimer = strpos($icone_impression,'imprimer_non')    ? 'true' : 'false';
				$tab_js_disabled .= 'tab_disabled["examiner"]["'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'"]='.$disabled_examiner.';';
				$tab_js_disabled .= 'tab_disabled["imprimer"]["'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'"]='.$disabled_imprimer.';'."\r\n";
			}
		}

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Affichage d'un tableau js utilisé pour désactiver des options d'un select.
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		echo'<script type="text/javascript">var tab_disabled = new Array();tab_disabled["examiner"] = new Array();tab_disabled["imprimer"] = new Array();'.$tab_js_disabled.'</script>';

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Affichage du tableau.
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		echo'<table id="table_bilans"><thead>';
		foreach($tab_affich as $ligne_id => $tab_colonne)
		{
			echo ( ($ligne_id!='check') ||($affichage_formulaire_statut) ) ? '<tr>'.implode('',$tab_colonne).'</tr>'."\r\n" : '' ;
			echo ($ligne_id=='title') ? '</thead><tbody>'."\r\n" : '' ;
		}
		echo'</tbody></table>';

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Affichage du formulaire pour modifier les états d'accès.
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		if($affichage_formulaire_statut)
		{
			$tab_radio = array();
			foreach($tab_etats as $etat_id => $etat_text)
			{
				$tab_radio[] = '<label for="etat_'.$etat_id.'"><input id="etat_'.$etat_id.'" name="etat" type="radio" value="'.$etat_id.'" /> <span class="off_etat '.substr($etat_id,1).'">'.$etat_text.'</span></label>';
			}
			echo'
				<form action="#" method="post" id="form_gestion">
					<hr />
					<p><span class="tab"></span><span class="u">Pour les cases cochées du tableau (classes uniquement) :</span><input id="listing_ids" name="listing_ids" type="hidden" value="" /></p>
					<div><label class="tab">Accès / Statut :</label>'.implode('<br /><span class="tab"></span>',$tab_radio).'</div>
					<p><span class="tab"></span><button id="bouton_valider" type="button" class="valider">Valider</button><label id="ajax_msg_gestion">&nbsp;</label></p>
				</form>
			';
		}

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Formulaire de choix des matières ou des piliers pour une recherche de saisies manquantes. -> zone_chx_rubriques
		//	Paramètres supplémentaires envoyés pour éviter d'avoir à les retrouver à chaque fois. -> form_hidden
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$form_hidden = '';
		$tab_checkbox_rubriques = array();
		$disabled = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE']) ? '' : ' disabled' ;
		if($tab_types[$BILAN_TYPE]['droit']=='SOCLE')
		{
			// Lister les piliers du palier concerné
			$palier_id = (int)substr($BILAN_TYPE,-1);
			$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_piliers($palier_id);
			foreach($DB_TAB as $DB_ROW)
			{
				$tab_checkbox_rubriques[$DB_ROW['valeur']] = '<input type="checkbox" name="f_rubrique[]" id="rubrique_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" checked'.$disabled.' /><label for="rubrique_'.$DB_ROW['valeur'].'"> '.html($DB_ROW['texte']).'</label><br />';
			}
			$listing_piliers_id = implode(',',array_keys($tab_checkbox_rubriques));
			$form_hidden .= '<input type="hidden" id="f_listing_piliers" name="f_listing_piliers" value="'.$listing_piliers_id.'" />';
			$commentaire_selection = ($_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE']) ? ' <div class="astuce">La recherche sera dans tous les cas aussi restreinte aux seules compétences matières ayant fait l\'objet d\'une évaluation ou d\'une validation.</div>' : '' ;
		}
		elseif(($BILAN_TYPE=='releve')||($BILAN_TYPE=='bulletin'))
		{
			// Lister les matières rattachées au prof
			$listing_matieres_id = ($_SESSION['USER_PROFIL']=='professeur') ? DB_STRUCTURE_COMMUN::DB_recuperer_matieres_professeur($_SESSION['USER_ID']) : '' ;
			$form_hidden .= '<input type="hidden" id="f_listing_matieres" name="f_listing_matieres" value="'.$listing_matieres_id.'" />';
			$tab_matieres_id = explode(',',$listing_matieres_id);
			// Lister les matières de l'établissement
			$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
			foreach($DB_TAB as $DB_ROW)
			{
				$checked = ( ($_SESSION['USER_PROFIL']!='professeur') || in_array($DB_ROW['matiere_id'],$tab_matieres_id) ) ? ' checked' : '' ;
				$tab_checkbox_rubriques[$DB_ROW['matiere_id']] = '<input type="checkbox" name="f_rubrique[]" id="rubrique_'.$DB_ROW['matiere_id'].'" value="'.$DB_ROW['matiere_id'].'"'.$checked.$disabled.' /><label for="rubrique_'.$DB_ROW['matiere_id'].'"> '.html($DB_ROW['matiere_nom']).'</label><br />';
			}
			$commentaire_selection = ' <div class="astuce">La recherche sera dans tous les cas aussi restreinte aux matières evaluées au cours de la période.</div>';
		}
		// Choix de vérifier ou pas l'appréciation générale ; le test ($etat=='3synthese') dépend de chaque classe...
		$disabled = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE']) ? '' : ' disabled' ;
		$tab_checkbox_rubriques[0] = '<input type="checkbox" name="f_rubrique[]" id="rubrique_0"'.$disabled.' value="0" /><label for="rubrique_0"> <i>Appréciation de synthèse générale</i></label><br />';
		// Présenter les rubriques en colonnes de hauteur raisonnables
		$tab_checkbox_rubriques = array_values($tab_checkbox_rubriques);
		$nb_rubriques              = count($tab_checkbox_rubriques);
		$nb_rubriques_maxi_par_col = ($tab_types[$BILAN_TYPE]['droit']=='SOCLE') ? $nb_rubriques : 10 ;
		$nb_cols               = floor(($nb_rubriques-1)/$nb_rubriques_maxi_par_col)+1;
		$nb_rubriques_par_col      = ceil($nb_rubriques/$nb_cols);
		$tab_div = array_fill(0,$nb_cols,'');
		foreach($tab_checkbox_rubriques as $i => $contenu)
		{
			$tab_div[floor($i/$nb_rubriques_par_col)] .= $contenu;
		}
		echo'
			<form action="#" method="post" id="zone_chx_rubriques" class="hide">
				<h2>Rechercher des saisies manquantes</h2>
				'.$commentaire_selection.'
				<p><a href="#zone_chx_rubriques" id="rubrique_check_all"><img src="./_img/all_check.gif" alt="Tout cocher." /> Toutes</a>&nbsp;&nbsp;&nbsp;<a href="#zone_chx_rubriques" id="rubrique_uncheck_all"><img src="./_img/all_uncheck.gif" alt="Tout décocher." /> Aucune</a></p>
				<div class="prof_liste">'.implode('</div><div class="prof_liste">',$tab_div).'</div>
				<p style="clear:both"><span class="tab"></span><button id="lancer_recherche" type="button" class="rechercher">Lancer la recherche</button> <button id="fermer_zone_chx_rubriques" type="button" class="annuler">Annuler</button><label id="ajax_msg_recherche">&nbsp;</label></p>
			</form>
		';
		echo'<form action="#" method="post" id="form_hidden" class="hide"><div>'.$form_hidden.'<input type="hidden" id="f_objet" name="f_objet" value="" /><input type="hidden" id="f_listing_rubriques" name="f_listing_rubriques" value="" /><input type="hidden" id="f_listing_eleves" name="f_listing_eleves" value="" /><input type="hidden" id="f_mode" name="f_mode" value="texte" /></div></form>';

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Formulaires utilisés pour les opérations ultérieures sur les bilans.
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		echo'<div id="zone_action_eleve"></div>';
		echo'<div id="bilan"></div>';
		echo'
			<div id="zone_action_classe" class="hide">
				<h2>Recherche de saisies manquantes | Imprimer le bilan (PDF)</h2>
				<form action="#" method="post" id="form_choix_classe"><div><b id="report_periode">Période :</b> <button id="go_precedent_classe" type="button" class="go_precedent">Précédent</button> <select id="go_selection_classe" name="go_selection_classe" class="b">'.implode('',$tab_options_classes).'</select> <button id="go_suivant_classe" type="button" class="go_suivant">Suivant</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_classe" type="button" class="retourner">Retour</button></div></form>
				<hr />
				<div id="zone_resultat_classe"></div>
				<div id="zone_imprimer" class="hide">
					<form action="#" method="post" id="form_choix_eleves">
						<p class="ti">
							<button id="valider_imprimer" type="button" class="valider">Lancer l\'impression</button><label id="ajax_msg_imprimer">&nbsp;</label>
						</p>
						<div id="imprimer_liens"></div>
						<table class="form t9">
							<thead>
								<tr>
									<th class="nu"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input id="eleve_check_all" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><input id="eleve_uncheck_all" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>
									<th>Élèves</th>
									<th>Généré</th>
								</tr>
							</thead>
							<tbody>
								<tr><td class="nu" colspan="3"></td></tr>
							</tbody>
						</table>
					</form>
				</div>
				<div id="zone_voir_archive" class="hide">
					<p class="astuce">Ces bilans ne sont que des copies partielles, laissées à disposition pour information jusqu\'à la fin de l\'année scolaire.<br /><span class="u">Seul le document original fait foi.</span></p>
					<table class="t9">
						<thead>
							<tr>
								<th>Élèves</th>
								<th>Généré</th>
							</tr>
						</thead>
						<tbody>
							<tr><td class="nu" colspan="2"></td></tr>
						</tbody>
					</table>
					<p class="ti">
						<label id="ajax_msg_voir_archive">&nbsp;</label>
					</p>
				</div>
			</div>
		';

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Formulaire pour signaler une erreur dans une appréciation.
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$date_plus3jours = date("d/m/Y",mktime(0,0,0,date("m"),date("d")+3,date("Y")));
		echo'
			<form action="#" method="post" id="zone_signaler" class="hide" onsubmit="return false">
				<h2>Signaler une erreur</h2>
				<p class="astuce">Le message sera affiché sur la page d\'accueil du professeur pendant 3 jours (jusqu\'au '.$date_plus3jours.').</p>
				<div>
					<input type="hidden" value="'.TODAY_FR.'" name="f_debut_date" id="f_debut_date" />
					<input type="hidden" value="'.$date_plus3jours.'" name="f_fin_date" id="f_fin_date" />
					<input type="hidden" value="" name="f_destinataires_liste" id="f_destinataires_liste" />
					<input type="hidden" value="ajouter" name="f_action" id="f_action" />
					<label for="f_message_contenu" class="tab">Contenu du message :</label><textarea name="f_message_contenu" id="f_message_contenu" rows="5" cols="75"></textarea><br />
					<span class="tab"></span><label id="f_message_contenu_reste"></label><br />
					<span class="tab"></span><button id="valider_signaler" type="button" class="valider">Valider</button>&nbsp;&nbsp;&nbsp;<button id="annuler_signaler" type="button" class="annuler">Annuler / Retour</button><label id="ajax_msg_signaler">&nbsp;</label>
				</div>
			</form>
		';

	}
	else
	{
		echo'<p><label class="erreur">Aucune période n\'a été configurée par les administrateurs !</label></p>';
	}
}
else
{
	echo'<p><label class="erreur">Aucune classe ni aucun groupe associé à votre compte !</label></p>';
}

?>



