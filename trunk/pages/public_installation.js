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

		arrondir_coins('#cadre_milieu','10px');

		// ********************
		// * Départ | Étape n -> Étape 1
		// ********************

		$('a.step1').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step1").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=1',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
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
								$('#form_start').html(responseHTML);
								$('#form_type_install').html('');
								$('#form_info_heberg').html('');
								$('#form_param_mysql').html('');
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
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=2',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
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
								$('#form_start').html(responseHTML);
								$('#form_type_install').html('');
								$('#form_info_heberg').html('');
								$('#form_param_mysql').html('');
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
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=3',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
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
								$('#form_start').html('');
								$('#form_type_install').html(responseHTML);
								$('#form_info_heberg').html('');
								$('#form_param_mysql').html('');
								infobulle();
								$('#f_installation').focus();
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
				var f_installation = $(this).attr('id');
				$("#step li").removeAttr("class");
				$("#step4").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=4&f_installation='+f_installation,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
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
								$('#form_start').html('');
								$('#form_type_install').html('');
								$('#form_info_heberg').html(responseHTML);
								$('#form_param_mysql').html('');
								infobulle();
								$('#f_denomination').focus();
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 4 -> Étape 41
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
		var formulaire_info_heberg = $('#form_info_heberg');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation_info_heberg = formulaire_info_heberg.validate
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
		var ajaxOptions_info_heberg =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_info_heberg_avant_envoi,
			error : retour_form_info_heberg_erreur,
			success : retour_form_info_heberg_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire_info_heberg.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions_info_heberg);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_info_heberg_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation_info_heberg.form();
			if(readytogo)
			{
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours...");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_info_heberg_erreur(msg,string)
		{
			$('button').prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_info_heberg_valide(responseHTML)
		{
			$('button').prop('disabled',false);
			if(responseHTML.substring(0,6)=='Erreur')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#form_start').html(responseHTML);
				$('#form_type_install').html('');
				$('#form_info_heberg').html('');
				$('#form_param_mysql').html('');
			}
		} 

		// ********************
		// * Étape 4|41|n -> Étape 5
		// ********************

		$('a.step5').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step5").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=5',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							$('#form_start').html('');
							$('#form_type_install').html('');
							$('#form_info_heberg').html('');
							$('#form_param_mysql').html(responseHTML);
							infobulle();
							$('#f_host').focus();
						}
					}
				);
			}
		);

		// ********************
		// * Étape 5|51 -> Étape 51|52
		// ********************

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire_param_mysql = $('#form_param_mysql');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation_param_mysql = formulaire_param_mysql.validate
		(
			{
				rules :
				{
					f_host : { required:true },
					f_port : { required:true , digits:true },
					f_name : { required:true },
					f_user : { required:true },
					f_pass : { required:false }
				},
				messages :
				{
					f_host : { required:"champ obligatoire" },
					f_port : { required:"champ obligatoire" , digits:"nombre entier requis" },
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
		var ajaxOptions_param_mysql =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_param_mysql_avant_envoi,
			error : retour_form_param_mysql_erreur,
			success : retour_form_param_mysql_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire_param_mysql.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions_param_mysql);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_param_mysql_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation_param_mysql.form();
			if(readytogo)
			{
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours...");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_param_mysql_erreur(msg,string)
		{
			$('button').prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_param_mysql_valide(responseHTML)
		{
			$('button').prop('disabled',false);
			if(responseHTML.substring(0,6)=='Erreur')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else if(responseHTML=='') // En cas de port incorrect, le test de la connexion peut durer longtemps, et on récupère une chaine vide à la place de l'erreur, qui devrait être "Une tentative de connexion a échoué car le parti connecté n’a pas répondu convenablement au-delà d’une certaine durée ou une connexion établie a échoué car l’hôte de connexion n’a pas répondu."
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html('Erreur : impossible de se connecter à  MySQL ["La tentative de connexion a échoué : MySQL n\'a pas répondu (port probablement incorrect)."] !');
			}
			else if(responseHTML.substring(0,10)=='<fieldset>')
			{
				// choix de la base (mono-structure)
				$('#form_start').html('');
				$('#form_type_install').html('');
				$('#form_info_heberg').html('');
				$('#form_param_mysql').html(responseHTML);
				infobulle();
				$('#f_name').focus();
			}
			else
			{
				// paramètres mysql et base ok
				$('#form_start').html(responseHTML);
				$('#form_type_install').html('');
				$('#form_info_heberg').html('');
				$('#form_param_mysql').html('');
			}
		} 

		// ********************
		// * Étape 5|51|52|n -> Étape 6
		// ********************

		$('a.step6').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step6").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=6',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							$('#form_start').html(responseHTML);
							$('#form_type_install').html('');
							$('#form_info_heberg').html('');
							$('#form_param_mysql').html('');
						}
					}
				);
			}
		);

	}
);
