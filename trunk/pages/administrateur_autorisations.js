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

// jQuery !
$(document).ready
(
	function()
	{

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Alerter sur la nécessité de valider
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#form_profils input").change
		(
			function()
			{
				$('#ajax_msg_profils').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$('#form_demandes select').change
		(
			function()
			{
				$('#ajax_msg_demandes').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$("#form_options input").change
		(
			function()
			{
				$('#ajax_msg_options').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Initialiser le formulaire avec les valeurs par défaut
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#initialiser_defaut').click
		(
			function()
			{
				$('#form_profils input').removeAttr('checked');
				$('#form_profils input[value="directeur"]').attr('checked','checked');
				$('#form_profils input[name="profil_validation_entree"][value="professeur"]').attr("checked",'checked');
				$('#form_profils input[name="profil_validation_pilier"][value="profprincipal"]').attr("checked",'checked');
				$('#ajax_msg_profils').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à valider le socle => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#bouton_valider_profils').click
		(
			function()
			{
				var tab_entree = new Array(); $("#form_profils input[name=profil_validation_entree]:checked").each(function(){tab_entree.push($(this).val());});
				var tab_pilier = new Array(); $("#form_profils input[name=profil_validation_pilier]:checked").each(function(){tab_pilier.push($(this).val());});
				$("button").attr('disabled','disabled');
				$('#ajax_msg_profils').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=profils&f_entree='+tab_entree+'&f_pilier='+tab_pilier,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").removeAttr('disabled');
							$('#ajax_msg_profils').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").removeAttr('disabled');
							if(responseHTML!='ok')
							{
								$('#ajax_msg_profils').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_profils').removeAttr("class").addClass("valide").html("Profils autorisés enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Demandes d'évaluations des élèves => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#bouton_valider_demandes').click
		(
			function()
			{
				demandes = $("#f_demandes option:selected").val();
				$("button").attr('disabled','disabled');
				$('#ajax_msg_demandes').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=demandes&f_demandes='+demandes,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").removeAttr('disabled');
							$('#ajax_msg_demandes').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").removeAttr('disabled');
							if(responseHTML!='ok')
							{
								$('#ajax_msg_demandes').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_demandes').removeAttr("class").addClass("valide").html("Choix enregistré !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Options de l'environnement élève => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#bouton_valider_options').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=eleve_options]:checked").each(function(){tab_check.push($(this).val());});
				$("button").attr('disabled','disabled');
				$('#ajax_msg_options').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=options&f_eleve_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").removeAttr('disabled');
							$('#ajax_msg_options').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").removeAttr('disabled');
							if(responseHTML!='ok')
							{
								$('#ajax_msg_options').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_options').removeAttr("class").addClass("valide").html("Options de l'environnement élève enregistrées !");
							}
						}
					}
				);
			}
		);

	}
);
