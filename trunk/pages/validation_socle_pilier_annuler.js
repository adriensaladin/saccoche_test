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
//	Tester l'affichage du bouton de validation au changement des formulaires
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var maj_bouton_validation = function()
		{
			if($("#f_eleve").val())
			{
				$('#Afficher_validation').prop('disabled',false);
			}
			else
			{
				$('#Afficher_validation').prop('disabled',true);
			}
		};

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Charger le select f_pilier en ajax
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		var maj_pilier = function()
		{
			$("#f_pilier").html('<option value=""></option>').hide();
			palier_id = $("#f_palier").val();
			if(palier_id)
			{
				$('#ajax_maj_pilier').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page=_maj_select_piliers',
						data : 'f_palier='+palier_id+'&f_first='+'non',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$('#ajax_maj_pilier').removeAttr("class").html('&nbsp;');
								$('#f_pilier').html(responseHTML).attr('size',$('#f_pilier option').size()).show();
							}
							else
							{
								$('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
			else
			{
				$('#ajax_maj_pilier').removeAttr("class").html("&nbsp;");
			}
		};

		$("#f_palier").change( maj_pilier );

		maj_pilier();

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Charger le select f_eleve en ajax
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		var maj_eleve = function()
		{
			$("#f_eleve").html('<option value=""></option>').hide();
			groupe_id = $("#f_groupe").val();
			if(groupe_id)
			{
				groupe_type = $("#f_groupe option:selected").parent().attr('label');
				if(typeof(groupe_type)=='undefined') {groupe_type = 'Classes';} // Cas d'un P.P.
				$('#ajax_maj_eleve').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page=_maj_select_eleves',
						data : 'f_groupe='+groupe_id+'&f_type='+groupe_type+'&f_statut=1',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_maj_eleve').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$('#ajax_maj_eleve').removeAttr("class").html("&nbsp;");
								$('#f_eleve').html(responseHTML).show();
								maj_bouton_validation();
							}
							else
							{
								$('#ajax_maj_eleve').removeAttr("class").addClass("alerte").html(responseHTML);
								maj_bouton_validation();
							}
						}
					}
				);
			}
			else
			{
				$('#ajax_maj_eleve').removeAttr("class").html("&nbsp;");
				maj_bouton_validation();
			}
		};

		$("#f_groupe").change( maj_eleve );

		maj_eleve(); // Dans le cas d'un P.P.

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du premier formulaire pour afficher le tableau avec les états de validations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire0 = $('#zone_choix');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation0 = formulaire0.validate
		(
			{
				rules :
				{
					f_pilier : { required:true },
					f_groupe : { required:true },
					f_eleve  : { required:true }
				},
				messages :
				{
					f_pilier : { required:"compétence(s) manquante(s)" },
					f_groupe : { required:"classe / groupe manquant" },
					f_eleve  : { required:"élève(s) manquant(s)" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element){element.after(error);}
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions0 =
		{
			type : 'POST',
			url : 'ajax.php?page='+PAGE,
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg_choix",
			beforeSubmit : test_form_avant_envoi0,
			error : retour_form_erreur0,
			success : retour_form_valide0
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire0.submit
		(
			function()
			{
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai copié le tableau dans un champ hidden...
				var tab_eleve = new Array(); $("#f_eleve option:selected").each(function(){tab_eleve.push($(this).val());});
				$('#eleves').val(tab_eleve);
				var tab_pilier = new Array(); $("#f_pilier option:selected").each(function(){tab_pilier.push($(this).val());});
				$('#piliers').val(tab_pilier);
				$(this).ajaxSubmit(ajaxOptions0);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi0(formData, jqForm, options)
		{
			$('#ajax_msg_choix').removeAttr("class").html("&nbsp;");
			var readytogo = validation0.form();
			if(readytogo)
			{
				$("button").prop('disabled',true);
				$('#ajax_msg_choix').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur0(msg,string)
		{
			$("button").prop('disabled',false);
			$('#ajax_msg_choix').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide0(responseHTML)
		{
			initialiser_compteur();
			$("button").prop('disabled',false);
			if(responseHTML.substring(0,7)!='<thead>')
			{
				$('#ajax_msg_choix').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				responseHTML = responseHTML.replace( '@PALIER@' , $("#f_palier option:selected").text() );
				$('#tableau_validation').html(responseHTML);
				infobulle();
				$('#zone_validation').show('fast');
				$('#ajax_msg_choix').removeAttr("class").html('');
				$('#zone_choix').hide('fast');
				$("body").oneTime("1s", function() {window.scrollTo(0,1000);} );
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une cellule validée du tableau => Afficher le message de confirmation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var td_id = '';

		$('td.v1').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				td_id = $(this).attr('id');
				var user_id  = td_id.substring(1,td_id.indexOf('C'));
				$('#report_nom').html( $('#I'+user_id).attr('alt') );
				$('#report_compet').html( $(this).parent().children('th').text().substring(0,12) );
				$('#confirmation').css('opacity',1);
				$('#fermer_zone_validation').removeAttr("class").addClass("annuler").html('Annuler / Retour');
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer la zone de validation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#fermer_zone_validation').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#zone_choix').show('fast');
				$('#zone_validation').hide('fast');
				$('#tableau_validation').html('<tbody><tr><td></td></tr></tbody>');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour envoyer les validations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#Enregistrer_validation').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("button").prop('disabled',true);
				$('#ajax_msg_validation').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=Enregistrer_validation&delete_id='+td_id,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$("button").prop('disabled',false);
							if(responseHTML.substring(0,2)!='OK')
							{
								$('#ajax_msg_validation').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#'+td_id).removeAttr("class").removeAttr("lang").addClass("v3");
								$('#fermer_zone_validation').removeAttr("class").addClass("retourner").html('Retour');
								$('#confirmation').css('opacity',0);
								$('#ajax_msg_validation').removeAttr("class").html("&nbsp;");
							}
						}
					}
				);
			}
		);

	}
);

