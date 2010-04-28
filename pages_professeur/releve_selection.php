<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Bilans sur une sélection d'items";
?>

<?php
// Fabrication des éléments select du formulaire
$tab_cookie = load_cookie_select($_SESSION['BASE'],$_SESSION['USER_ID']);
$select_groupe      = afficher_select(DB_OPT_groupes_professeur($_SESSION['USER_ID']) , $select_nom='f_groupe'      , $option_first='oui' , $selection=false                        , $optgroup='oui');
$select_orientation = afficher_select($tab_select_orientation                         , $select_nom='f_orientation' , $option_first='non' , $selection=$tab_cookie['orientation']   , $optgroup='non');
$select_marge_min   = afficher_select($tab_select_marge_min                           , $select_nom='f_marge_min'   , $option_first='non' , $selection=$tab_cookie['marge_min']     , $optgroup='non');
$select_couleur     = afficher_select($tab_select_couleur                             , $select_nom='f_couleur'     , $option_first='non' , $selection=$tab_cookie['couleur']       , $optgroup='non');
$select_cases_nb    = afficher_select($tab_select_cases_nb                            , $select_nom='f_cases_nb'    , $option_first='non' , $selection=$tab_cookie['cases_nb']      , $optgroup='non');
$select_cases_larg  = afficher_select($tab_select_cases_size                          , $select_nom='f_cases_larg'  , $option_first='non' , $selection=$tab_cookie['cases_largeur'] , $optgroup='non');
$select_cases_haut  = afficher_select($tab_select_cases_size                          , $select_nom='f_cases_haut'  , $option_first='non' , $selection=$tab_cookie['cases_hauteur'] , $optgroup='non');
?>

<div class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=releve_selection">DOC : Bilans sur une sélection d'items.</a></span></div>

<form id="form_select" action=""><fieldset>
	<label class="tab" for="f_type">Type de bilan :</label><label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type" value="individuel" /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type" value="synthese" /> Synthèse collective</label><input type="hidden" id="types" name="types" value="" /><br />
	<span id="options_releve" class="hide">
		<label class="tab" for="f_opt_grille">Opt. relevé <img alt="" src="./_img/bulle_aide.png" title="Pour le relévé individuel, les paramètres des items peuvent être affichés." /> :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1" /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1" /> Socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1" /> Liens de remédiation</label><br />
		<label class="tab" for="f_opt_bilan">Opt. relevé <img alt="" src="./_img/bulle_aide.png" title="Pour le relévé individuel, deux lignes de synthèse peuvent être ajoutées.<br />Dans ce cas, une note sur 20 peut aussi être affichée." /> :</label><label for="f_bilan_ms"><input type="checkbox" id="f_bilan_ms" name="f_bilan_ms" value="1" checked="checked" /> Moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_bilan_pv"><input type="checkbox" id="f_bilan_pv" name="f_bilan_pv" value="1" checked="checked" /> Pourcentage d'acquisitions validées</label>&nbsp;&nbsp;&nbsp;<label for="f_conv_sur20"><input type="checkbox" id="f_conv_sur20" name="f_conv_sur20" value="1" /> Proposition de note sur 20</label><br />
	</span>
	<p />
	<label class="tab" for="f_competence">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="0 item" readonly="readonly" /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="" /><q class="choisir_compet" title="Voir ou choisir les items."></q><br />
	<label class="tab" for="f_groupe">Élève(s) :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj">&nbsp;</label><br />
	<span class="tab"></span><select id="f_eleve" name="f_eleve[]" multiple="multiple" size="9"><option></option></select><input type="hidden" id="eleves" name="eleves" value="" />
	<div class="toggle">
		<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
	</div>
	<div class="toggle hide">
		<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
		<label class="tab" for="f_orientation">Orientation :</label><?php echo $select_orientation ?> en <?php echo $select_couleur ?> avec marges minimales de </label><?php echo $select_marge_min ?><br />
		<label class="tab" for="f_cases_nb">Évaluations :</label><?php echo $select_cases_nb ?> de largeur <?php echo $select_cases_larg ?> et de hauteur <?php echo $select_cases_haut ?><p />
	</div>
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label><br />
	<hr />
</fieldset></form>

<div id="bilan">
</div>

<form action="" id="zone_compet" class="hide">
	<div class="hc">
		<a class="valider_compet" href="#"><img alt="Valider" src="./_img/action/action_valider.png" /> Valider ce choix</a><br />
		<a class="annuler_compet" href="#"><img alt="Annuler" src="./_img/action/action_annuler.png" /> Annuler / Retour</a>
	</div>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur, sur tous les niveaux
	$tab_matiere    = array();
	$tab_niveau     = array();
	$tab_domaine    = array();
	$tab_theme      = array();
	$tab_competence = array();
	$matiere_id = 0;
	$DB_TAB = DB_recuperer_arborescence($_SESSION['USER_ID'],$matiere_id=0,$niveau_id=0,$only_item=false,$socle_nom=false);
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['matiere_id']!=$matiere_id)
		{
			$matiere_id = $DB_ROW['matiere_id'];
			$tab_matiere[$matiere_id] = $DB_ROW['matiere_nom'];
			$niveau_id     = 0;
			$domaine_id    = 0;
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
		{
			$niveau_id = $DB_ROW['niveau_id'];
			$tab_niveau[$matiere_id][$niveau_id] = $DB_ROW['niveau_nom'];
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$matiere_id][$niveau_id][$domaine_id] = $DB_ROW['domaine_nom'];
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$matiere_id][$niveau_id][$domaine_id][$theme_id] = $DB_ROW['theme_nom'];
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['item_id'];
			$texte_socle = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
			$tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id][$competence_id] = $texte_socle.$DB_ROW['item_nom'];
		}
	}
	$input_competences = '<ul class="ul_m1">'."\r\n";
	if(count($tab_matiere))
	{
		foreach($tab_matiere as $matiere_id => $matiere_nom)
		{
			$input_competences .= '	<li class="li_m1"><span>'.html($matiere_nom).'</span>'."\r\n";
			$input_competences .= '		<ul class="ul_m2">'."\r\n";
			if(isset($tab_niveau[$matiere_id]))
			{
				foreach($tab_niveau[$matiere_id] as $niveau_id => $niveau_nom)
				{
					$input_competences .= '			<li class="li_m2"><span>'.html($niveau_nom).'</span>'."\r\n";
					$input_competences .= '				<ul class="ul_n1">'."\r\n";
					if(isset($tab_domaine[$matiere_id][$niveau_id]))
					{
						foreach($tab_domaine[$matiere_id][$niveau_id] as $domaine_id => $domaine_nom)
						{
							$input_competences .= '					<li class="li_n1"><span>'.html($domaine_nom).'</span>'."\r\n";
							$input_competences .= '						<ul class="ul_n2">'."\r\n";
							if(isset($tab_theme[$matiere_id][$niveau_id][$domaine_id]))
							{
								foreach($tab_theme[$matiere_id][$niveau_id][$domaine_id] as $theme_id => $theme_nom)
								{
									$input_competences .= '							<li class="li_n2"><span>'.html($theme_nom).'</span>'."\r\n";
									$input_competences .= '								<ul class="ul_n3">'."\r\n";
									if(isset($tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id]))
									{
										foreach($tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id] as $competence_id => $competence_nom)
										{
											$input_competences .= '									<li class="li_n3"><input id="id_'.$competence_id.'" name="f_competences[]" type="checkbox" value="'.$competence_id.'" /><label for="id_'.$competence_id.'"> '.html($competence_nom).'</label></li>'."\r\n";
										}
									}
									$input_competences .= '								</ul>'."\r\n";
									$input_competences .= '							</li>'."\r\n";
								}
							}
							$input_competences .= '						</ul>'."\r\n";
							$input_competences .= '					</li>'."\r\n";
						}
					}
					$input_competences .= '				</ul>'."\r\n";
					$input_competences .= '			</li>'."\r\n";
				}
			}
			$input_competences .= '		</ul>'."\r\n";
			$input_competences .= '	</li>'."\r\n";
		}
	}
	$input_competences .= '</ul>'."\r\n";
	echo $input_competences;
	?>
</form>

