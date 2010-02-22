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

// jQuery !
$(document).ready
(
	function()
	{

		$("#step1").addClass("on");

		// ********************
		// * Étape 0 -> Étape 1
		// ********************

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#f_submit_1',
			{
				action: 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
				name: 'userfile',
				data: {'f_step':1},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('#f_submit_1').hide();
			$('#ajax_msg').removeAttr("class").html('&nbsp;');
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#f_submit_1').show();
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if (fichier_extension!='xml' && fichier_extension!='zip' && fichier_extension!='csv' && fichier_extension!='txt')
			{
				$('#f_submit_1').show();
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "zip" ou "xml" ou "csv" ou "txt".');
				return false;
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)
		{
			if(responseHTML.substring(0,13)!='<div id="ok">')
			{
				$('#f_submit_1').show();
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				maj_clock(1);
				$('#ajax_msg').removeAttr("class").html('&nbsp;');
				$('#ajax').html(responseHTML);
			}
		}

		// ********************
		// * Étape 1 -> Étape 2
		// ********************

		$('a.step2').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step2").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=2',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 2 -> Étape 3
		// ********************

		$('a.step3').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#ajax table').hide('fast');
				$("#step li").removeAttr("class");
				$("#step3").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=3',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 3 -> Étape 4
		// ********************

		$('a.step4').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#ajax table').hide('fast');
				$("#step li").removeAttr("class");
				$("#step4").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=4&'+$("form").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html('&nbsp;');
								$('#ajax').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 4 -> Étape 5
		// ********************

		$('a.step5').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#ajax table').hide('fast');
				$("#step li").removeAttr("class");
				$("#step5").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=5&'+$("form").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html(responseHTML);
								format_liens('#ajax');
							}
						}
					}
				);
			}
		);

	}
);
