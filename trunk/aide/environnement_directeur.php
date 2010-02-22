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
$TITRE = "L'environnement directeur";
?>

<h2>Introduction</h2>
<p>
	On appelle un compte <b>"directeur"</b> celui d'un <b>personnel de direction</b> : principal d'un collège, proviseur d'un lycée, leurs adjoints...
</p>

<h2>Connexion</h2>
<p>
	Pour se connecter comme directeur, il faut sélectionner son établissement, saisir son nom d'utilisateur et son mot de passe (seul l'administrateur de l'établissement peut générer l'inscription du directeur et lui communiquer ses paramètres).
</p>

<h2>Changer son mot de passe</h2>
<p>
	Les directeurs peuvent modifier leur mot de passe (sauf si la connexion est dépendante d'un service extérieur, tel un ENT).<br />
	Les mots de passe sont cryptés et ne peuvent pas être renvoyés. En cas d'oubli du mot de passe directeur, contacter l'administrateur qui est seul habilité à en générer un nouveau.
</p>

<h2>Fonctionnalités</h2>
<p>Le directeur peut :</p>
<ul class="puce">
	<li>générer des grilles de compétences</li>
	<li>établir des bilans de compétences</li>
	<li>estimer des attestations de maîtrise du socle</li>
</ul>
<p>
	L'administrateur de <em>SACoche</em> a la charge de gérer les élèves, les professeurs, les classes, les matières, les périodes...<br />
	Il faut le contacter si on constate une anomalie dans les données.
</p>
