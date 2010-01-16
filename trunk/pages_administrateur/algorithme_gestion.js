/**
 * @version $Id: algorithme_gestion.js 8 2009-10-30 20:56:02Z thomas $
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

// jQuery !
$(document).ready
(
	function()
	{

		// Demande de soumission du formulaire
		$('#calculer').click
		(
			function()
			{
				$('#action').val('calculer');
				formulaire.submit();
			}
		);
		$('#enregistrer').click
		(
			function()
			{
				$('#action').val('enregistrer');
				formulaire.submit();
			}
		);

		// Vaiables globales
		var memo_valeurRR  = 0;
		var memo_valeurR   = 0;
		var memo_valeurV   = 0;
		var memo_valeurVV  = 0;
		var memo_coef1sur2 = 0;
		var memo_coef2sur2 = 0;
		var memo_coef1sur3 = 0;
		var memo_coef2sur3 = 0;
		var memo_coef3sur3 = 0;
		var memo_coef1sur4 = 0;
		var memo_coef2sur4 = 0;
		var memo_coef3sur4 = 0;
		var memo_coef4sur4 = 0;
		var memo_seuilR    = 0;
		var memo_seuilV    = 0;
		// Demande d'initialisation du formulaire avec les valeurs de l'établissement
		// Un simple boutton de type "reset" ne peut être utilisé en cas d'enregistrement en cours de procédure
		$('#initialiser_etablissement').click
		(
			function()
			{
				$('#valeurRR').val(memo_valeurRR);
				$('#valeurR').val(memo_valeurR);
				$('#valeurV').val(memo_valeurV);
				$('#valeurVV').val(memo_valeurVV);
				$('#coef1sur2').val(memo_coef1sur2);
				$('#coef2sur2').val(memo_coef2sur2);
				$('#coef1sur3').val(memo_coef1sur3);
				$('#coef2sur3').val(memo_coef2sur3);
				$('#coef3sur3').val(memo_coef3sur3);
				$('#coef1sur4').val(memo_coef1sur4);
				$('#coef2sur4').val(memo_coef2sur4);
				$('#coef3sur4').val(memo_coef3sur4);
				$('#coef4sur4').val(memo_coef4sur4);
				$('#seuilR').val(memo_seuilR);
				$('#seuilV').val(memo_seuilV);
			}
		);
		// Donc il faut retenir les valeurs initiales et les replacer
		function memoriser_valeurs()
		{
			memo_valeurRR  = $('#valeurRR').val();
			memo_valeurR   = $('#valeurR').val();
			memo_valeurV   = $('#valeurV').val();
			memo_valeurVV  = $('#valeurVV').val();
			memo_coef1sur2 = $('#coef1sur2').val();
			memo_coef2sur2 = $('#coef2sur2').val();
			memo_coef1sur3 = $('#coef1sur3').val();
			memo_coef2sur3 = $('#coef2sur3').val();
			memo_coef3sur3 = $('#coef3sur3').val();
			memo_coef1sur4 = $('#coef1sur4').val();
			memo_coef2sur4 = $('#coef2sur4').val();
			memo_coef3sur4 = $('#coef3sur4').val();
			memo_coef4sur4 = $('#coef4sur4').val();
			memo_seuilR    = $('#seuilR').val();
			memo_seuilV    = $('#seuilV').val();
		}
		memoriser_valeurs();

		// Demande d'initialisation du formulaire avec les valeurs par défaut
		$('#initialiser_defaut').click
		(
			function()
			{
				$('#valeurRR').val(0);
				$('#valeurR').val(33);
				$('#valeurV').val(67);
				$('#valeurVV').val(100);
				$('#coef1sur2').val(0.25);
				$('#coef2sur2').val(0.75);
				$('#coef1sur3').val(0.2);
				$('#coef2sur3').val(0.3);
				$('#coef3sur3').val(0.5);
				$('#coef1sur4').val(0.1);
				$('#coef2sur4').val(0.2);
				$('#coef3sur4').val(0.3);
				$('#coef4sur4').val(0.4);
				$('#seuilR').val(40);
				$('#seuilV').val(60);
			}
		);

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $("#form_input");

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					valeurRR :  { required:true, digits:true },
					valeurR :   { required:true, digits:true },
					valeurV :   { required:true, digits:true },
					valeurVV :  { required:true, digits:true },
					coef1sur2 : { required:true, number:true },
					coef2sur2 : { required:true, number:true },
					coef1sur3 : { required:true, number:true },
					coef2sur3 : { required:true, number:true },
					coef3sur3 : { required:true, number:true },
					coef1sur4 : { required:true, number:true },
					coef2sur4 : { required:true, number:true },
					coef3sur4 : { required:true, number:true },
					coef4sur4 : { required:true, number:true },
					seuilR :    { required:true, digits:true },
					seuilV :    { required:true, digits:true }
				},
				messages :
				{
					valeurRR :  { required:"valeur requise", digits:"nombre entier requis" },
					valeurR :   { required:"valeur requise", digits:"nombre entier requis" },
					valeurV :   { required:"valeur requise", digits:"nombre entier requis" },
					valeurVV :  { required:"valeur requise", digits:"nombre entier requis" },
					coef1sur2 : { required:"valeur requise", number:"nombre décimal requis" },
					coef2sur2 : { required:"valeur requise", number:"nombre décimal requis" },
					coef1sur3 : { required:"valeur requise", number:"nombre décimal requis" },
					coef2sur3 : { required:"valeur requise", number:"nombre décimal requis" },
					coef3sur3 : { required:"valeur requise", number:"nombre décimal requis" },
					coef1sur4 : { required:"valeur requise", number:"nombre décimal requis" },
					coef2sur4 : { required:"valeur requise", number:"nombre décimal requis" },
					coef3sur4 : { required:"valeur requise", number:"nombre décimal requis" },
					coef4sur4 : { required:"valeur requise", number:"nombre décimal requis" },
					seuilR :    { required:"valeur requise", digits:"nombre entier requis" },
					seuilV :    { required:"valeur requise", digits:"nombre entier requis" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { element.after(error); }
				// success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_avant_envoi,
			error : retour_form_erreur,
			success : retour_form_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				readytogo = false;
				if( (Math.min($('#valeurRR').val(),$('#valeurR').val(),$('#valeurV').val(),$('#valeurVV').val())<0) || (Math.max($('#valeurRR').val(),$('#valeurR').val(),$('#valeurV').val(),$('#valeurVV').val())>100) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 1 : valeurs entre 0 et 100 requises.").show();
				}
				else if( (Math.min($('#coef1sur2').val(),$('#coef2sur2').val())<0) || (Math.max($('#coef1sur2').val(),$('#coef2sur2').val())>1) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 2 : valeurs entre 0 et 1 requises.").show();
				}
				else if(eval($('#coef1sur2').val())+eval($('#coef2sur2').val())!=1)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 2 : somme requise égale à 1.").show();
				}
				else if( (Math.min($('#coef1sur3').val(),$('#coef2sur3').val(),$('#coef3sur3').val())<0) || (Math.max($('#coef1sur3').val(),$('#coef2sur3').val(),$('#coef3sur3').val())>1) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 3 : valeurs entre 0 et 1 requises.").show();
				}
				else if(eval($('#coef1sur3').val())+eval($('#coef2sur3').val())+eval($('#coef3sur3').val())!=1)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 3 : somme requise égale à 1.").show();
				}
				else if( (Math.min($('#coef1sur4').val(),$('#coef2sur4').val(),$('#coef3sur4').val(),$('#coef4sur4').val())<0) || (Math.max($('#coef1sur4').val(),$('#coef2sur4').val(),$('#coef3sur4').val(),$('#coef4sur4').val())>1) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 4 : valeurs entre 0 et 1 requises.").show();
				}
				else if(eval($('#coef1sur4').val())+eval($('#coef2sur4').val())+eval($('#coef3sur4').val())+eval($('#coef4sur4').val())!=1)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 4 : somme requise égale à 1.").show();
				}
				else if( (Math.min($('#seuilR').val(),$('#seuilV').val())<0) || (Math.max($('#seuilR').val(),$('#seuilV').val())>100) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 5 : valeurs entre 0 et 100 requises.").show();
				}
				else if( $('#seuilR').val() > $('#seuilV').val() )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Colonne 5 : valeurs croissantes requises.").show();
				}
				else
				{
					readytogo = true;
				}
			}
			if(readytogo)
			{
				if( $('#action').val()=='calculer' )
				{
					$('#bilan table tbody').hide();
				}
				else if( $('#action').val()=='enregistrer' )
				{
				memoriser_valeurs();
				}
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.").show();
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			if(responseHTML.substring(0,1)!='<')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else if(responseHTML.substring(0,4)=='<tr>')
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Calcul effectué !");
				$('#bilan table tbody').html(responseHTML).show();
			}
			else if(responseHTML.substring(0,4)=='<ok>')
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Valeurs mémorisées !");
			}
		} 

		// Initialisation
		formulaire.submit();

	}
);
