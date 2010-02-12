<?php
/**
 * @version $Id: calcul_scores_etats_acquisitions.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Calcul des scores et des états d'acquisitions";
?>

<h2>Introduction</h2>
<p>
	Pour un item donné, évalué une ou plusieurs fois, le logiciel permet de paramétrer un calcul automatique de son état d'acquisition.
</p>

<h2>Valeur d'un code</h2>
<p>
	A chaque évaluation d'un item on associe une valeur sur 100. Les valeurs par défaut sont :
</p>
<ul class="puce">
	<li><img alt="" src="./_img/note/note_RR.gif" /> = 0</li>
	<li><img alt="" src="./_img/note/note_R.gif" /> = 33</li>
	<li><img alt="" src="./_img/note/note_V.gif" /> = 67</li>
	<li><img alt="" src="./_img/note/note_VV.gif" /> = 100</li>
</ul>
<p>
	Ceci correspond à une répartition régulière (0/3 ; 1/3 ; 2/3 ; 3/3). Une saisie codée «&nbsp;absent&nbsp;» ou «&nbsp;non&nbsp;noté&nbsp;» ou «&nbsp;dispensé&nbsp;» n'est pas comptabilisée.
</p>
<p>
	<b>Seul l'administrateur peut modifier ce réglage ; afin de le comprendre, les autres statuts y ont un accès en consultation et simulation.</b>
</p>


<h2>Méthode de calcul : coefficients et nombres de saisies</h2>
<p>
	Par défaut, quand un item est évalué plusieurs fois, les saisies les plus récentes sont celles qui ont le plus d'importance. On autorise ainsi le droit à l'erreur en cours d'apprentissage, l'essentiel étant de valoriser l'acquisition finale (mais inversement, un élève qui régresse sera davantage pénalisé). Voici les trois modes possibles :
</p>
<ul class="puce">
	<li>Coefficients multipliés par 2 d'une saisie à la suivante : 1;2;4;8;16. Avec cette méthode, la dernière saisie tend a compter pour 50%. Au delà de 5 saisies, les précédentes deviennent négligeables (&lt;2%) ; cette méthode est ainsi limitée aux 5 dernières saisies maximum.</li>
	<li>Coefficients augmentés de 1 d'une saisie à la suivante : 1;2;3;4;5... Avec cette méthode, la progression des coefficients est régulière. Au delà de 9 saisies, les précédentes deviennent négligeables (&lt;2%) ; cette méthode est ainsi limitée aux 9 dernières saisies maximum.</li>
	<li>Moyenne classique non pondérée. Avec cette méthode, on comptabilise autant chaque saisie. Ceci peut être utile pour la matière transversale, couplé ou pas avec une limitation du nombre de notes comptabilisées.</li>
</ul>
<a href="#" class="toggle">Voir / masquer les tableaux des coefficients.</a>
<div class="toggle hide"><table id="simulation">
	<tbody>
		<tr><th class="o" colspan="6">Coefficients multipliés par 2</th><th class="nu"></th><th class="o" colspan="10">Coefficients augmentés de 1</th></tr>
<?php
for($nb_devoirs=2 ; $nb_devoirs<10 ; $nb_devoirs++)
{
	// ligne d'en-tête, progression géométrique
	if($nb_devoirs<6)
	{
		echo'<tr><th class="nu"></th><th colspan="'.$nb_devoirs.'">'.$nb_devoirs.' saisies</th>';
		$colspan = 6-$nb_devoirs;
		echo'<th colspan="'.$colspan.'" class="nu"></th>';
	}
	else
	{
		echo'<tr><td colspan="7" class="nu"></td>';
	}
	// ligne d'en-tête, progression arithmétique
	echo'<th class="nu"></th><th colspan="'.$nb_devoirs.'">'.$nb_devoirs.' saisies</th>';
	$colspan = 9-$nb_devoirs;
	echo ($colspan==0) ? '' : ( ($colspan>1) ? '<th colspan="'.$colspan.'" class="nu"></th>' : '<th class="nu"></th>' ) ;
	echo'</tr>'."\r\n";
	// ligne du coef, progression géométrique
	if($nb_devoirs<6)
	{
		echo'<tr><th>coefficient</th>';
		for($num_devoir=1 ; $num_devoir<=$nb_devoirs ; $num_devoir++)
		{
			$coef = pow(2,$num_devoir-1);
			echo'<td>'.$coef.'</td>';
		}
		$colspan = 6-$nb_devoirs;
		echo ($colspan>1) ? '<td colspan="'.$colspan.'" class="nu"></td>' : '<td class="nu"></td>' ;
	}
	else
	{
		echo'<tr><td colspan="7" class="nu"></td>';
	}
	// ligne du coef, progression arithmétique
	echo'<th>coefficient</th>';
	for($num_devoir=1 ; $num_devoir<=$nb_devoirs ; $num_devoir++)
	{
		$coef = $num_devoir;
		echo'<td>'.$coef.'</td>';
	}
	$colspan = 9-$nb_devoirs;
	echo ($colspan==0) ? '' : ( ($colspan>1) ? '<td colspan="'.$colspan.'" class="nu"></td>' : '<td class="nu"></td>' ) ;
	echo'</tr>'."\r\n";
	// ligne du %, progression géométrique
	if($nb_devoirs<6)
	{
		echo'<tr><th>poids en %</th>';
		$diviseur = pow(2,$nb_devoirs)-1;
		for($num_devoir=1 ; $num_devoir<=$nb_devoirs ; $num_devoir++)
		{
			$pourcentage = round(100*pow(2,$num_devoir-1)/$diviseur);
			echo'<td>'.$pourcentage.'%</td>';
		}
		$colspan = 6-$nb_devoirs;
		echo ($colspan>1) ? '<td colspan="'.$colspan.'" class="nu"></td>' : '<td class="nu"></td>' ;
	}
	else
	{
		echo'<tr><td colspan="7" class="nu"></td>';
	}
	// ligne du %, progression arithmétique
	echo'<th>poids en %</th>';
	$diviseur = $nb_devoirs*($nb_devoirs+1)/2;
	for($num_devoir=1 ; $num_devoir<=$nb_devoirs ; $num_devoir++)
	{
		$pourcentage = round(100*$num_devoir/$diviseur);
		echo'<td>'.$pourcentage.'%</td>';
	}
	$colspan = 9-$nb_devoirs;
	echo ($colspan==0) ? '' : ( ($colspan>1) ? '<td colspan="'.$colspan.'" class="nu"></td>' : '<td class="nu"></td>' ) ;
	echo'</tr>'."\r\n";
}
?>
	</tbody>
</table></div>

<p>
	De plus, il est possible de restreindre le nombre des dernières saisies prises en compte :
</p>
<ul class="puce">
	<li>Dans le cas d'un moyenne classique, on peut ne comptabiliser que la dernière saisie, ou les 2 ; 3 ; 4 ; 5 ; 6 ; 7 ; 8 ; 9 ; 10 ; 15 ; 20 ; 30 ; 40 ; 50 dernières saisies.</li>
	<li>Dans le cas d'un moyenne pondérée, les limites sont indiquées ci-dessus.</li>
</ul>
<p>
	<b>L'administrateur fixe un réglage par défaut ; celui-ci peut être modifié pour chaque référentiel par les coordonnateurs.</b>
</p>

<h2>Seuil d'acquisition</h2>
<p>
	Enfin, une fois le score calculé pour un item donné, on détermine dans quelle tranche il est considéré comme acquis ou non. Les valeurs par défaut (sur 100) sont :
</p>
<ul class="puce">
	<li><span class="r">&lt; 40 : non acquis</span></li>
	<li><span class="v">&gt; 60 : acquis</span></li>
	<li><span class="o">entre ces valeurs : partiellement acquis</span></li>
</ul>
<p>
	En prenant des valeurs égales on supprime (ou presque) le niveau intermédiaire.
</p>
<p>
	<b>Seul l'administrateur peut modifier ce réglage ; afin de le comprendre, les autres statuts y ont un accès en consultation et simulation.</b>
</p>

<h2>Avertissement</h2>
<p>
	Déterminer si les items sont acquis ou non met en jeu un nombre important de paramètres, qu'il est impossible de tous contrôler et unifier :
</p>
<ul class="puce">
	<li>le nombre d'évaluations proposées et le nombre d'évaluations prises en compte</li>
	<li>le poids donné à chacun des items ou à chacune des évaluations</li>
	<li>la difficulté des évaluations proposées, le degré d'exigence du professeur</li>
	<li>etc.</li>
</ul>
<p>
	De plus, des coefficients peuvent être associés aux items : ils sont alors utilisés lors de l'élaboration de bilans sur une matière donnée.
</p>
