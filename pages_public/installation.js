/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

		// ********************
		// * Étape 0|n -> Étape 1
		// ********************

		$('a.step1').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step1").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=1',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,6)!='<label')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html(responseHTML);
								$('#form3').html('');
								$('#form4').html('');
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 1|n -> Étape 2
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
							if(responseHTML.substring(0,6)!='<label')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html(responseHTML);
								$('#form3').html('');
								$('#form4').html('');
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 2|n -> Étape 3
		// ********************

		$('a.step3').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
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
							if(responseHTML.substring(0,20)!='<p><label for="rien"')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html('');
								$('#form3').html(responseHTML);
								$('#form4').html('');
								$('#f_installation').focus();
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 3 -> Étape 31
		// ********************

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire3 = $('#form3');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation3 = formulaire3.validate
		(
			{
				rules :
				{
					f_installation : { required:true },
					f_denomination : { required:true , maxlength:60 },
					f_nom :          { required:true , maxlength:20 },
					f_prenom :       { required:true , maxlength:20 },
					f_courriel :     { required:true , email:true , maxlength:60 },
					f_password1 :    { required:true , minlength:6 , maxlength:20 },
					f_password2 :    { required:true , minlength:6 , maxlength:20 , equalTo: "#f_password1" }
				},
				messages :
				{
					f_installation : { required:"type manquant" },
					f_denomination : { required:"dénomination manquante" , maxlength:"60 caractères maximum" },
					f_nom :          { required:"nom manquant" , maxlength:"20 caractères maximum" },
					f_prenom :       { required:"prénom manquant" , maxlength:"20 caractères maximum" },
					f_courriel :     { required:"courriel manquant" , email:"courriel invalide", maxlength:"63 caractères maximum" },
					f_password1 :    { required:"mot de passe manquant" , minlength:"6 caractères minimum" , maxlength:"20 caractères maximum" },
					f_password2 :    { required:"mot de passe à saisir une 2e fois" , minlength:"6 caractères minimum" , maxlength:"20 caractères maximum" , equalTo:"mots de passe différents" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { element.after(error); }
				// success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions3 =
		{
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form3_avant_envoi,
			error : retour_form3_erreur,
			success : retour_form3_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire3.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions3);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form3_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation3.form();
			if(readytogo)
			{
				$('#f_submit').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form3_erreur(msg,string)
		{
			$('#f_submit').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form3_valide(responseHTML)
		{
			$('#f_submit').show();
			if(responseHTML.substring(0,6)=='Erreur')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#form0').html(responseHTML);
				$('#form3').html('');
				$('#form4').html('');
			}
		} 

		// ********************
		// * Étape 3|31|n -> Étape 4
		// ********************

		$('a.step4').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step4").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=4',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							$('#form0').html('');
							$('#form3').html('');
							$('#form4').html(responseHTML);
							$('#f_host').focus();
						}
					}
				);
			}
		);

		// ********************
		// * Étape 4|41 -> Étape 41|42
		// ********************

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form4');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_host : { required:true },
					f_name : { required:true },
					f_user : { required:true },
					f_pass : { required:false }
				},
				messages :
				{
					f_host : { required:"champ obligatoire" },
					f_name : { required:"champ obligatoire" },
					f_user : { required:"champ obligatoire" },
					f_pass : { }
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
				$('#f_submit').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('#f_submit').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			$('#f_submit').show();
			if(responseHTML.substring(0,6)=='Erreur')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else if(responseHTML.substring(0,10)=='<fieldset>')
			{
				// choix de la base (mono-structure)
				$('#form0').html('');
				$('#form3').html('');
				$('#form4').html(responseHTML);
				$('#f_name').focus();
			}
			else
			{
				// paramètres mysql et base ok
				$('#form0').html(responseHTML);
				$('#form3').html('');
				$('#form4').html('');
			}
		} 

		// ********************
		// * Étape 4|41|42|n -> Étape 5
		// ********************

		$('a.step5').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step5").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=5',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							$('#form0').html(responseHTML);
							$('#form3').html('');
							$('#form4').html('');
						}
					}
				);
			}
		);

	}
);
