<?php
/**
 * @version $Id: referentiel_detail.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Modifier / paramétrer un référentiel";
?>

<span class="manuel"><a class="pop_up" href="./aide.php?fichier=referentiel_modifier_parametrer">DOC : Modifier / paramétrer un référentiel.</a></span><br />
<span class="manuel"><a class="pop_up" href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span><br />
<div class="danger">Retirer des items supprime les résultats associés de tous les élèves !</div>

<hr />

<form action="" onsubmit="return false;">

<?php
// J'ai séparé en plusieurs requêtes au bout de plusieurs heures sans m'en sortir (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();

// On récupère la liste des matières où le professeur est rattaché, et s'il en est coordonnateur
$DB_SQL = 'SELECT livret_matiere_id,livret_matiere_nom,livret_jointure_coord,livret_matiere_structure_id FROM livret_jointure_user_matiere ';
$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID']);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
if(count($DB_TAB))
{
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_matiere[$DB_ROW['livret_matiere_id']] = array( 'nom'=>html($DB_ROW['livret_matiere_nom']) , 'coord'=>$DB_ROW['livret_jointure_coord'] , 'niveau_nb'=>0 );
	}
}
$liste_matieres = implode(',',array_keys($tab_matiere));

if(!$liste_matieres)
{
	echo'<p><span class="danger">Vous n\'êtes coordonnateur d\'aucune matière de cet établissement !</span></p>';
}
elseif(!$_SESSION['NIVEAUX'])
{
	echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
}
else
{
	// On récupère la liste des niveaux utilisés par l'établissement
	$DB_SQL = 'SELECT livret_niveau_id,livret_niveau_nom FROM livret_niveau ';
	$DB_SQL.= 'WHERE livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
	$nb_niveaux = count($DB_TAB);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_niveau[$DB_ROW['livret_niveau_id']] = html($DB_ROW['livret_niveau_nom']);
	}
	// On récupère la liste des référentiels par matière et niveau
	$tab_partage = array('oui'=>'<img title="Référentiel accessible aux autres établissements." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel caché aux autres établissements." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	$DB_SQL = 'SELECT livret_matiere_id,COUNT(livret_niveau_id) AS niveau_nb FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id IN('.$liste_matieres.') AND livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'GROUP BY livret_matiere_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_id ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $key => $DB_ROW)
		{
			$tab_matiere[$DB_ROW['livret_matiere_id']]['niveau_nb'] = $DB_ROW['niveau_nb'];
		}
	}
	// On construit et affiche le tableau résultant
	$affichage = '<table class="comp_view"><thead><tr><th>Matière</th><th>Référentiel</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$matiere_nom   = $tab['nom'];
		$matiere_coord = $tab['coord'];
		$affichage .= '<tr lang="'.$matiere_nom.'"><td>'.$matiere_nom.'</td>';
		$id = 'm1_'.$matiere_id;
		if($tab_matiere[$matiere_id]['niveau_nb']>0)
		{
			$x = ($tab_matiere[$matiere_id]['niveau_nb'])>1 ? 'x' : '';
			$affichage .= '<td class="v">Référentiel présent sur '.$tab_matiere[$matiere_id]['niveau_nb'].' niveau'.$x.'.</td>';
			$affichage .= ($matiere_coord) ? '<td class="nu" id="'.$id.'"><q class="modifier" title="Paramétrer les référentiels de cette matière."></q></td>' : '<td class="nu"><q class="modifier_non" title="Action réservée aux coordonnateurs."></q></td>' ;

		}
		else
		{
			$affichage .= '<td class="r">Absence de référentiel.</td><td class="nu"></td>';
		}
		$affichage .= '</tr>'."\r\n";
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>

<hr />

<p>
	<span class="astuce">Référentiels de compétences partagés :</span><br />
	<a class="lien_ext" href="./index.php?dossier=professeur&amp;fichier=recherche_generale">Recherche générale sur plusieurs établissements, pour une matière et un niveau donnés.</a><br />
	<a class="lien_ext" href="./index.php?dossier=professeur&amp;fichier=recherche_precise">Recherche sur un établissement précis, pour une matière donnée.</a>
</p>

<hr />

<div id="zone_compet">
</div>

<div id="zone_socle">
	<h2>Relation au socle commun</h2>
	<label class="tab" for="rien">Item disciplinaire :</label><span class="f_nom i"></span><br />
	<label class="tab" for="f_lien">Socle commun :</label>Cocher ci-dessous.<q class="valider" lang="choisir" title="Valider la modification de la relation au socle commun."></q><q class="annuler" lang="choisir" title="Annuler la modification de la relation au socle commun."></q>
	<p />
	<ul class="ul_n1"><li class="li_n3"><input id="socle_0" name="f_socle" type="radio" value="0" /><label for="socle_0">Hors-socle.</label></li></ul>
	<p />
	<?php
	// Affichage de la liste des items du socle pour chaque palier
	if($_SESSION['PALIERS'])
	{
		$DB_SQL = 'SELECT * FROM livret_socle_palier ';
		$DB_SQL.= 'LEFT JOIN livret_socle_pilier USING (livret_palier_id) ';
		$DB_SQL.= 'LEFT JOIN livret_socle_section USING (livret_pilier_id) ';
		$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_section_id) ';
		$DB_SQL.= 'WHERE livret_palier_id IN('.$_SESSION['PALIERS'].') ';
		$DB_SQL.= 'ORDER BY livret_palier_ordre ASC, livret_pilier_ordre ASC, livret_section_ordre ASC, livret_socle_ordre ASC';
		$DB_VAR = array();
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$tab_palier  = array();
		$tab_pilier  = array();
		$tab_section = array();
		$tab_socle   = array();
		$palier_id = 0;
		$affich_socle = '';
		foreach($DB_TAB as $key => $DB_ROW)
		{
			if($DB_ROW['livret_palier_id']!=$palier_id)
			{
				$palier_id = $DB_ROW['livret_palier_id'];
				$tab_palier[$palier_id] = $DB_ROW['livret_palier_nom'];
				$pilier_id  = 0;
				$section_id = 0;
				$socle_id   = 0;
			}
			if( (!is_null($DB_ROW['livret_pilier_id'])) && ($DB_ROW['livret_pilier_id']!=$pilier_id) )
			{
				$pilier_id = $DB_ROW['livret_pilier_id'];
				$tab_pilier[$palier_id][$pilier_id] = $DB_ROW['livret_pilier_nom'];
			}
			if( (!is_null($DB_ROW['livret_section_id'])) && ($DB_ROW['livret_section_id']!=$section_id) )
			{
				$section_id = $DB_ROW['livret_section_id'];
				$tab_section[$palier_id][$pilier_id][$section_id] = $DB_ROW['livret_section_nom'];
			}
			if( (!is_null($DB_ROW['livret_socle_id'])) && ($DB_ROW['livret_socle_id']!=$socle_id) )
			{
				$socle_id = $DB_ROW['livret_socle_id'];
				$tab_socle[$palier_id][$pilier_id][$section_id][$socle_id] = $DB_ROW['livret_socle_nom'];
			}
		}
		$affich_socle .= '<ul class="ul_m1">'."\r\n";
		foreach($tab_palier as $palier_id => $palier_nom)
		{
			$affich_socle .= '	<li class="li_m1" id="palier_'.$palier_id.'"><span>'.html($palier_nom).'</span>'."\r\n";
			$affich_socle .= '		<ul class="ul_n1">'."\r\n";
			if(count($tab_pilier[$palier_id]))
			{
				foreach($tab_pilier[$palier_id] as $pilier_id => $pilier_nom)
				{
					$affich_socle .= '			<li class="li_n1"><span>'.html($pilier_nom).'</span>'."\r\n";
					$affich_socle .= '				<ul class="ul_n2">'."\r\n";
					if(count($tab_section[$palier_id][$pilier_id]))
					{
						foreach($tab_section[$palier_id][$pilier_id] as $section_id => $section_nom)
						{
							$affich_socle .= '					<li class="li_n2"><span>'.html($section_nom).'</span>'."\r\n";
							$affich_socle .= '						<ul class="ul_n3">'."\r\n";
							if(count($tab_socle[$palier_id][$pilier_id][$section_id]))
							{
								foreach($tab_socle[$palier_id][$pilier_id][$section_id] as $socle_id => $socle_nom)
								{
									$affich_socle .= '							<li class="li_n3"><input id="socle_'.$socle_id.'" name="f_socle" type="radio" value="'.$socle_id.'" /> <label for="socle_'.$socle_id.'">'.html($socle_nom).'</label></li>'."\r\n";
								}
							}
							$affich_socle .= '						</ul>'."\r\n";
							$affich_socle .= '					</li>'."\r\n";
						}
					}
					$affich_socle .= '				</ul>'."\r\n";
					$affich_socle .= '			</li>'."\r\n";
				}
			}
			$affich_socle .= '		</ul>'."\r\n";
			$affich_socle .= '	</li>'."\r\n";
		}
		$affich_socle .= '</ul>'."\r\n";
	}
	else
	{
		$affich_socle = '<p><span class="danger"> Aucun palier du socle n\'est associé à l\'établissement ! L\'administrateur doit préalablement choisir les paliers évalués...</span></p>'."\r\n";
	}
	echo $affich_socle;
	?>
</div>

</form>

<p />

