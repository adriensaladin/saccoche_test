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

		// ********************
		// * Départ | Étape n -> Étape 1
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
								$('#form4').html('');
								$('#form5').html('');
								$('#form6').html('');
							}
						}
					}
				);
			}
		);

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
							if(responseHTML.substring(0,6)!='<label')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html(responseHTML);
								$('#form4').html('');
								$('#form5').html('');
								$('#form6').html('');
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
							if(responseHTML.substring(0,6)!='<label')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html(responseHTML);
								$('#form4').html('');
								$('#form5').html('');
								$('#form6').html('');
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 3|n -> Étape 4
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
							if(responseHTML.substring(0,20)!='<p><label for="rien"')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html('');
								$('#form4').html(responseHTML);
								$('#form5').html('');
								$('#form6').html('');
								infobulle();
								$('#f_installation').focus();
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
				var f_installation = $(this).attr('id');
				$("#step li").removeAttr("class");
				$("#step5").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=5&f_installation='+f_installation,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,10)!='<fieldset>')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form0').html('');
								$('#form4').html('');
								$('#form5').html(responseHTML);
								$('#form6').html('');
								infobulle();
								$('#f_denomination').focus();
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 5 -> Étape 51
		// ********************

		//	Analyse de la robustesse du mot de passe
		$('#f_password1').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('keyup',
			function()
			{
				analyse_mdp( $(this).val() );
			}
		);

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire5 = $('#form5');

		// Ajout d'une méthode pour vérifier le format du numéro UAI
		jQuery.validator.addMethod
		(
			"uai_format", function(value, element)
			{
				var uai = value.toUpperCase();
				var uai_valide = true;
				if(uai.length!=8)
				{
					uai_valide = false;
				}
				else
				{
					var uai_fin = uai.substring(7,8);
					if((uai_fin<"A")||(uai_fin>"Z"))
					{
						uai_valide = false;
					}
					else
					{
						for(i=0;i<7;i++)
						{
							var t = uai.substring(i,i+1);
							if((t<"0")||(t>"9"))
							{
								uai_valide = false;
							}
						}
					}
				}
				return this.optional(element) || uai_valide ;
			}
			, "il faut 7 chiffres suivis d'une lettre"
		); 

		// Ajout d'une méthode pour vérifier la clef de contrôle du numéro UAI
		jQuery.validator.addMethod
		(
			"uai_clef", function(value, element)
			{
				var uai = value.toUpperCase();
				var uai_valide = true;
				var uai_nombre = uai.substring(0,7);
				var uai_fin = uai.substring(7,8);
				alphabet = "ABCDEFGHJKLMNPRSTUVWXYZ";
				reste = uai_nombre-(23*Math.floor(uai_nombre/23));
				clef = alphabet.substring(reste,reste+1);;
				if(clef!=uai_fin )
				{
					uai_valide = false;
				}
				return this.optional(element) || uai_valide ;
			}
			, "clef de contrôle incompatible"
		); 

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation5 = formulaire5.validate
		(
			{
				rules :
				{
					f_installation : { required:true },
					f_denomination : { required:true , maxlength:60 },
					f_uai :          { required:false , uai_format:true , uai_clef:true },
					f_adresse_site : { required:false , maxlength:150 },
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
					f_uai :          { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
					f_adresse_site : { maxlength:"150 caractères maximum" },
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
		var ajaxOptions5 =
		{
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form5_avant_envoi,
			error : retour_form5_erreur,
			success : retour_form5_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire5.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions5);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form5_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation5.form();
			if(readytogo)
			{
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form5_erreur(msg,string)
		{
			$('button').removeAttr('disabled');
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form5_valide(responseHTML)
		{
			$('button').removeAttr('disabled');
			if(responseHTML.substring(0,6)=='Erreur')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#form0').html(responseHTML);
				$('#form4').html('');
				$('#form5').html('');
				$('#form6').html('');
			}
		} 

		// ********************
		// * Étape 5|51|n -> Étape 6
		// ********************

		$('a.step6').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step6").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=6',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							$('#form0').html('');
							$('#form4').html('');
							$('#form5').html('');
							$('#form6').html(responseHTML);
							infobulle();
							$('#f_host').focus();
						}
					}
				);
			}
		);

		// ********************
		// * Étape 6|61 -> Étape 61|62
		// ********************

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire6 = $('#form6');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation6 = formulaire6.validate
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
		var ajaxOptions6 =
		{
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_avant_envoi5,
			error : retour_form_erreur5,
			success : retour_form_valide5
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire6.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions6);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi5(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation6.form();
			if(readytogo)
			{
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur5(msg,string)
		{
			$('button').removeAttr('disabled');
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide5(responseHTML)
		{
			$('button').removeAttr('disabled');
			if(responseHTML.substring(0,6)=='Erreur')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else if(responseHTML.substring(0,10)=='<fieldset>')
			{
				// choix de la base (mono-structure)
				$('#form0').html('');
				$('#form4').html('');
				$('#form5').html('');
				$('#form6').html(responseHTML);
				infobulle();
				$('#f_name').focus();
			}
			else
			{
				// paramètres mysql et base ok
				$('#form0').html(responseHTML);
				$('#form4').html('');
				$('#form5').html('');
				$('#form6').html('');
			}
		} 

		// ********************
		// * Étape 6|61|62|n -> Étape 7
		// ********************

		$('a.step7').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step7").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=7',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							$('#form0').html(responseHTML);
							$('#form4').html('');
							$('#form5').html('');
							$('#form6').html('');
						}
					}
				);
			}
		);

	}
);
