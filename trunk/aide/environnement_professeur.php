<?php
/**
 * @version $Id: environnement_professeur.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "L'environnement professeur";
?>
<h2>Connexion</h2>
<p>
	Pour se connecter comme professeur, il faut sélectionner son établissement, saisir son nom d'utilisateur et son mot de passe (seul l'administrateur de l'établissement peut générer l'inscription du professeur et lui communiquer ses paramètres).
</p>

<h2>Changer son mot de passe</h2>
<p>
	Les professeurs peuvent modifier leur mot de passe (sauf si la connexion est dépendante d'un service extérieur, tel un ENT).<br />
	Les mots de passe sont cryptés et ne peuvent pas être renvoyés. En cas d'oubli du mot de passe professeur, contacter l'administrateur qui est seul habilité à en générer un nouveau.
</p>

<h2>Fonctionnalités</h2>
<p>Le professeur peut :</p>
<ul class="puce">
	<li>gérer les référentiels de compétences et leur contenu (<b>coordonnateurs uniquement</b>)</li>
	<li>gérer des groupes de besoin personnalisés à partir de ses élèves (facultatif)</li>
	<li>créer des évaluations par compétences, en saisir les acquisitions, imprimer des cartouches associés</li>
	<li>générer des grilles de compétences</li>
	<li>établir des bilans de compétences</li>
	<li>estimer des attestations de maîtrise du socle</li>
</ul>
<p>
	L'administrateur de <em>SACoche</em> a la charge de gérer les élèves, les professeurs, les classes, les matières, les périodes...<br />
	Il faut le contacter si on constate une anomalie dans ses données.
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
</ul>
