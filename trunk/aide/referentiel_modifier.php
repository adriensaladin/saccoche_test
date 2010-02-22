<?php
/**
 * @version $Id$
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
$TITRE = "Modifier le contenu des référentiels";
?>
<h2>Introduction</h2>
<p>
	Seuls <b>les professeurs coordonnateurs</b> ont accès à cette gestion.<br />
	Les autres professeurs peuvent uniquement consulter les référentiels de leurs disciplines.<br />
	Les personnels de direction et les administrateurs peuvent uniquement consulter les référentiels de toutes les disciplines.
</p>

<h2>Arborescence : compléter, élaguer, réorganiser, renommer</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Référentiels de compétences]</em> menu <em>[Modifier le contenu des référentiels]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> (si un référentiel est présent).</li>
</ul>
<p>
	Cliquer sur <img alt="niveau" src="./_img/action/folder_n1_add.png" /><img alt="niveau" src="./_img/action/folder_n2_add.png" /><img alt="niveau" src="./_img/action/folder_n3_add.png" /> permet de compléter un référentiel en y ajoutant des domaines, thèmes ou items (que l'on parte d'un référentiel vierge ou d'un référentiel existant).
</p>
<p>
	Cliquer sur <img alt="niveau" src="./_img/action/folder_n1_move.png" /><img alt="niveau" src="./_img/action/folder_n2_move.png" /><img alt="niveau" src="./_img/action/folder_n3_move.png" /> permet de déplacer des éléments existants, y compris vers d'autres niveaux (de la 6e vers la 5e...).<br />
	<span class="danger">Rappel : ceci est susceptible de modifier les références des items.</span>
</p>
<p>
	Cliquer sur <img alt="niveau" src="./_img/action/folder_n1_del.png" /><img alt="niveau" src="./_img/action/folder_n2_del.png" /><img alt="niveau" src="./_img/action/folder_n3_del.png" /> permet de supprimer des domaines (avec leur contenu), des thèmes (avec leur contenu) ou des items.<br />
	<span class="danger">Attention : cette action supprime tous les items associés, ainsi que tous les résultats correspondants des élèves.</span>
</p>
<p>
	Cliquer sur <img alt="niveau" src="./_img/action/folder_n1_edit.png" /><img alt="niveau" src="./_img/action/folder_n2_edit.png" /><img alt="niveau" src="./_img/action/folder_n3_edit.png" /> permet de renommer des domaines (ainsi que de modifier leur référence), des thèmes ou items. L'utilisation de certains caractères spéciaux est à l'étude (à développer).
</p>
<p>
	Cliquer sur <img alt="niveau" src="./_img/action/folder_n3_fus.png" /> permet de fusionner des items : le premier item sur lequel on clique sera absorbé (nom, coefficient, etc.) par le second item (<img alt="niveau" src="./_img/action/folder_n3_fus2.png" />) ; les scores associés sont reportés vers l'item absorbant, sauf si les deux items concernés ont été utilisés lors d'une même évaluation (dans ce cas le score du 1er item est effacé).
</p>

<h2>Items : coefficients, liens au socle, liens de remédiation</h2>
Lors de la création ou lors de l'édition d'un item, plusieurs paramètres peuvent être choisis :
<ul class="puce">
	<li><b>Un coefficient</b> : nombre entier entre 0 et 5, qui sert uniquement dans le cadre d'un bilan concernant la matière concerné.</li>
	<li><b>Une association au socle commun</b> : afin de pouvoir établir un bilan d'acquisition du socle commun avec la participation de l'ensemble des matières.</li>
	<li><b>Un lien web de remédiation</b> : afin de permettre à l'élève d'avoir un accès à des ressources pour travailler des items non acquis.</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_organisation_competences">DOC : Organisation des compétences dans les référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_structure">DOC : Structure d'un référentiel.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_gerer">DOC : Gérer les référentiels.</a></span></li>
	<li><span class="manuel">DOC : Modifier le contenu des référentiels.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
</ul>
