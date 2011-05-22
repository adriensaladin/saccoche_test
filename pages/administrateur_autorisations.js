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

		$("#form_validation_socle input").change
		(
			function()
			{
				$('#ajax_msg_validation_socle').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$("#form_voir_referentiels input").change
		(
			function()
			{
				$('#ajax_msg_voir_referentiels').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$("#form_voir_score_bilan input").change
		(
			function()
			{
				$('#ajax_msg_voir_score_bilan').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$("#form_voir_algorithme input").change
		(
			function()
			{
				$('#ajax_msg_voir_algorithme').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$("#form_modifier_mdp input").change
		(
			function()
			{
				$('#ajax_msg_modifier_mdp').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$("#form_eleve_bilans input").change
		(
			function()
			{
				$('#ajax_msg_eleve_bilans').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
				view_eleve_bilans();
			}
		);

		$("#form_eleve_socle input").change
		(
			function()
			{
				$('#ajax_msg_eleve_socle').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
				view_eleve_socle();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Initialiser un formulaire avec les valeurs par défaut
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#initialiser_validation_socle').click
		(
			function()
			{
				$('#form_validation_socle input').prop('checked',false);
				$('#form_validation_socle input[value="directeur"]').prop('checked',true);
				$('#form_validation_socle input[name="droit_validation_entree"][value="professeur"]').prop('checked',true);
				$('#form_validation_socle input[name="droit_validation_pilier"][value="profprincipal"]').prop('checked',true);
				$('#form_validation_socle input[name="droit_annulation_pilier"][value="aucunprof"]').prop('checked',true);
				$('#ajax_msg_validation_socle').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$('#initialiser_voir_referentiels').click
		(
			function()
			{
				$('#form_voir_referentiels input[value="directeur"]').prop('checked',true);
				$('#form_voir_referentiels input[value="professeur"]').prop('checked',true);
				$('#form_voir_referentiels input[value="eleve"]').prop('checked',true);
				$('#ajax_msg_voir_referentiels').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$('#initialiser_voir_score_bilan').click
		(
			function()
			{
				$('#form_voir_score_bilan input[value="directeur"]').prop('checked',true);
				$('#form_voir_score_bilan input[value="professeur"]').prop('checked',true);
				$('#form_voir_score_bilan input[value="eleve"]').prop('checked',true);
				$('#ajax_msg_voir_score_bilan').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$('#initialiser_voir_algorithme').click
		(
			function()
			{
				$('#form_voir_algorithme input[value="directeur"]').prop('checked',true);
				$('#form_voir_algorithme input[value="professeur"]').prop('checked',true);
				$('#form_voir_algorithme input[value="eleve"]').prop('checked',true);
				$('#ajax_msg_voir_algorithme').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$('#initialiser_modifier_mdp').click
		(
			function()
			{
				$('#form_modifier_mdp input[value="directeur"]').prop('checked',true);
				$('#form_modifier_mdp input[value="professeur"]').prop('checked',true);
				$('#form_modifier_mdp input[value="eleve"]').prop('checked',true);
				$('#ajax_msg_modifier_mdp').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
			}
		);

		$('#initialiser_eleve_bilans').click
		(
			function()
			{
				$('#form_eleve_bilans input[value="BilanMoyenneScore"]').prop('checked',true);
				$('#form_eleve_bilans input[value="BilanPourcentageAcquis"]').prop('checked',true);
				$('#form_eleve_bilans input[value="BilanNoteSurVingt"]').prop('checked',false);
				$('#ajax_msg_eleve_bilans').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
				view_eleve_bilans();
			}
		);

		$('#initialiser_eleve_socle').click
		(
			function()
			{
				$('#form_eleve_socle input[value="SocleAcces"]').prop('checked',true);
				$('#form_eleve_socle input[value="SoclePourcentageAcquis"]').prop('checked',true);
				$('#form_eleve_socle input[value="SocleEtatValidation"]').prop('checked',false);
				$('#ajax_msg_eleve_socle').removeAttr("class").addClass("alerte").html("Penser à enregistrer les modifications.");
				view_eleve_socle();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher ou masquer des éléments de formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function view_eleve_bilans()
		{
			var opacite = ( $('#form_eleve_bilans input[value="BilanMoyenneScore"]').is(':checked') || $('#form_eleve_bilans input[value="BilanPourcentageAcquis"]').is(':checked') ) ? 1 : 0 ;
			$('#form_eleve_bilans input[value="BilanNoteSurVingt"]').parent().parent().fadeTo(0,opacite);
		}
		view_eleve_bilans();

		function view_eleve_socle()
		{
			var opacite = $('#form_eleve_socle input[value="SocleAcces"]').is(':checked') ? 1 : 0 ;
			$('#form_eleve_socle input[value="SoclePourcentageAcquis"]').parent().parent().fadeTo(0,opacite);
			$('#form_eleve_socle input[value="SocleEtatValidation"]').parent().parent().fadeTo(0,opacite);
		}
		view_eleve_socle();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à valider le socle => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_validation_socle').click
		(
			function()
			{
				var tab_entree  = new Array(); $("#form_validation_socle input[name=droit_validation_entree]:checked").each(function(){tab_entree.push($(this).val());});
				var tab_pilier  = new Array(); $("#form_validation_socle input[name=droit_validation_pilier]:checked").each(function(){tab_pilier.push($(this).val());});
				var tab_annuler = new Array(); $("#form_validation_socle input[name=droit_annulation_pilier]:checked").each(function(){tab_annuler.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_validation_socle').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=validation_socle&f_entree='+tab_entree+'&f_pilier='+tab_pilier+'&f_annuler='+tab_annuler,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_validation_socle').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_validation_socle').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_validation_socle').removeAttr("class").addClass("valide").html("Profils autorisés enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à consulter tous les référentiels de l'établissement => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_voir_referentiels').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=droit_voir_referentiels]:checked").each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_voir_referentiels').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=voir_referentiels&f_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_voir_referentiels').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_voir_referentiels').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_voir_referentiels').removeAttr("class").addClass("valide").html("Profils autorisés enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à voir les scores bilan des items => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_voir_score_bilan').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=droit_voir_score_bilan]:checked").each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_voir_score_bilan').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=voir_score_bilan&f_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_voir_score_bilan').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_voir_score_bilan').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_voir_score_bilan').removeAttr("class").addClass("valide").html("Profils autorisés enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à voir et simuler l'algorithme de calcul d'un état d'acquisition => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_voir_algorithme').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=droit_voir_algorithme]:checked").each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_voir_algorithme').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=voir_algorithme&f_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_voir_algorithme').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_voir_algorithme').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_voir_algorithme').removeAttr("class").addClass("valide").html("Profils autorisés enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à modifier leur mot de passe => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_modifier_mdp').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=droit_modifier_mdp]:checked").each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_modifier_mdp').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=modifier_mdp&f_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_modifier_mdp').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_modifier_mdp').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_modifier_mdp').removeAttr("class").addClass("valide").html("Profils autorisés enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Environnement élève - Bilan d'items d'une matière => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_eleve_bilans').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=droit_eleve_bilans]:checked").each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_eleve_bilans').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=eleve_bilans&f_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_eleve_bilans').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_eleve_bilans').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_eleve_bilans').removeAttr("class").addClass("valide").html("Options de l'environnement élève enregistrées !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Environnement élève - Attestation de socle => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#valider_eleve_socle').click
		(
			function()
			{
				var tab_check = new Array(); $("input[name=droit_eleve_socle]:checked").each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_eleve_socle').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet=eleve_socle&f_options='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_eleve_socle').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_eleve_socle').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_eleve_socle').removeAttr("class").addClass("valide").html("Options de l'environnement élève enregistrées !");
							}
						}
					}
				);
			}
		);

	}
);
