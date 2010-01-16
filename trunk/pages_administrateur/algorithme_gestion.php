<?php
/**
 * @version $Id: algorithme_gestion.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Algorithme de calcul";
?>

<form id="form_input" action="">
	<table summary="">
	<thead>
		<tr><th>
			Valeur d'un code (sur 100)
		</th><th>
			Importance pour 2 devoirs
		</th><th>
			Importance pour 3 devoirs
		</th><th>
			Importance pour 4 devoirs ou +
		</th><th>
			Seuil d'aquisition (sur 100)
		</th></tr>
	</thead>
	<tbody>
		<tr><td>
			<label class="tab mini" for="valeurRR">acquisition <img alt="" src="./_img/note/note_RR.gif" /> :</label><input type="text" size="3" id="valeurRR" name="valeurRR" value="<?php echo $_SESSION['PARAM_CALCUL']['valeur']['RR'] ?>" /><br />
			<label class="tab mini" for="valeurR" >acquisition <img alt="" src="./_img/note/note_R.gif" />  :</label><input type="text" size="3" id="valeurR"  name="valeurR"  value="<?php echo $_SESSION['PARAM_CALCUL']['valeur']['R']  ?>" /><br />
			<label class="tab mini" for="valeurV" >acquisition <img alt="" src="./_img/note/note_V.gif" />  :</label><input type="text" size="3" id="valeurV"  name="valeurV"  value="<?php echo $_SESSION['PARAM_CALCUL']['valeur']['V']  ?>" /><br />
			<label class="tab mini" for="valeurVV">acquisition <img alt="" src="./_img/note/note_VV.gif" /> :</label><input type="text" size="3" id="valeurVV" name="valeurVV" value="<?php echo $_SESSION['PARAM_CALCUL']['valeur']['VV'] ?>" /><br />
		</td><td>
			<label class="tab mini" for="coef1sur2">devoir ancien :</label><input type="text" size="3" id="coef1sur2" name="coef1sur2" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][2][1] ?>" /><br />
			<label class="tab mini" for="coef2sur2">devoir récent :</label><input type="text" size="3" id="coef2sur2" name="coef2sur2" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][2][2] ?>" /><br />
		</td><td>
			<label class="tab mini" for="coef1sur3">devoir ancien :</label><input type="text" size="3" id="coef1sur3" name="coef1sur3" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][3][1] ?>" /><br />
			<label class="tab mini" for="coef2sur3">devoir médian :</label><input type="text" size="3" id="coef2sur3" name="coef2sur3" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][3][2] ?>" /><br />
			<label class="tab mini" for="coef3sur3">devoir récent :</label><input type="text" size="3" id="coef3sur3" name="coef3sur3" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][3][3] ?>" /><br />
		</td><td>
			<label class="tab" for="coef1sur4">devoir très ancien :</label><input type="text" size="3" id="coef1sur4" name="coef1sur4" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][4][1] ?>" /><br />
			<label class="tab" for="coef2sur4">devoir ancien :</label><input type="text" size="3" id="coef2sur4" name="coef2sur4" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][4][2] ?>" /><br />
			<label class="tab" for="coef3sur4">devoir récent :</label><input type="text" size="3" id="coef3sur4" name="coef3sur4" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][4][3] ?>" /><br />
			<label class="tab" for="coef4sur4">devoir très récent :</label><input type="text" size="3" id="coef4sur4" name="coef4sur4" value="<?php echo $_SESSION['PARAM_CALCUL']['coef'][4][4] ?>" /><br />
		</td><td>
			<label class="tab mini" for="seuilR">non acquis :</label>&lt; <input type="text" size="3" id="seuilR" name="seuilR" value="<?php echo $_SESSION['PARAM_CALCUL']['seuil']['R'] ?>" /><br />
			<label class="tab mini" for="seuilV">acquis :</label>&gt; <input type="text" size="3" id="seuilV" name="seuilV" value="<?php echo $_SESSION['PARAM_CALCUL']['seuil']['V'] ?>" /><br />
		</td></tr>
	</tbody>
	</table>
	<p><input type="hidden" id="action" name="action" value="calculer" /> <input id="initialiser_defaut" type="button" value="Afficher les valeurs par défaut." /> <input id="initialiser_etablissement" type="button" value="Afficher les valeurs de l'établissement." /> <input id="calculer" type="button" value="Simuler avec ces valeurs." /> <input id="enregistrer" type="button" value="Enregistrer ces valeurs." /><label id="ajax_msg">&nbsp;</label></p>
	<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></p>
</form>

<hr />

<div id="bilan">
<table id="simulation">
	<thead>
		<tr>
			<th colspan="2">Cas de 1 devoir</th>
			<th></th>
			<th colspan="3">Cas de 2 devoirs</th>
			<th></th>
			<th colspan="4">Cas de 3 devoirs</th>
			<th></th>
			<th colspan="5">Cas de 4 devoirs</th>
		</tr>
		<tr>
			<th>unique</th><th>score</th>
			<th></th>
			<th>ancien</th><th>récent</th><th>score</th>
			<th></th>
			<th>ancien</th><th>médian</th><th>récent</th><th>score</th>
			<th></th>
			<th>très ancien</th><th>ancien</th><th>récent</th><th>très récent</th><th>score</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td><td></td><td></td><td></td><td></td><td></td><td></td>
		</tr>
	</tbody>
</table>

</div>

