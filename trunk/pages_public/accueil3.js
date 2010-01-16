/**
 * @version $Id: accueil2.js 7 2009-10-30 20:50:17Z thomas $
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

		var memo_login = '';
		var memo_password = '';
		var mode_connexion = '';

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Formatage des boites login / mdp en fonction du mode de connexion
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		function format_login_mdp()
		{
			mode_connexion      = $('#f_structure option:selected').attr('class');
			user_administrateur = $('#f_administrateur').is(":checked");
			if( (mode_connexion=='normal') || (user_administrateur) )
			{
				// Pas de SSO
				$('#f_login').val(memo_login).removeAttr('disabled');
				$('#f_password').val(memo_password).css("display","inline");
				$('#f_password2').css("display","none");
				if(user_administrateur)
				{
					$('#f_password').focus();
				}
				else
				{
					$('#f_login').focus();
				}
			}
			else
			{
				// SSO
				memo_login = ( ($('#f_login').val()!='connexion ENT') && ($('#f_login').val()!='admin-etabl-SACoche') ) ? $('#f_login').val() : memo_login ;
				memo_password = ($('#f_password').val()!='connexion ENT') ? $('#f_password').val() : memo_password ;
				$('#f_login').val('connexion ENT').attr('disabled',true);
				$('#f_password').val('connexion ENT').css("display","none");
				$('#f_password2').css("display","inline");
			}
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Gérer l'affichage suivant le choix de l'établissement (sso ou pas)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_structure').change
		(
			function()
			{
				format_login_mdp();
			}
		);
		format_login_mdp();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Gérer le passage entre un utilisateur normal et un administrateur
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_administrateur').click
		(
			function()
			{
				if($(this).is(":checked"))
				{
					memo_login = ( ($('#f_login').val()!='connexion ENT') && ($('#f_login').val()!='admin-etabl-SACoche') ) ? $('#f_login').val() : memo_login ;
					format_login_mdp();
					$('#f_login').css("display","none").val('admin-etabl-SACoche');
					$('#f_or').css("display","none");
				}
				else
				{
					format_login_mdp();
					$('#f_login').css("display","inline");
					$('#f_or').css("display","inline");
				}
			}
		);

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('form');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_structure : { required:true },
					f_login : { required:true , maxlength:20 },
					f_password : { required:true , maxlength:20 }
				},
				messages :
				{
					f_structure : { required:"établissement manquant" },
					f_login : { required:"login manquant" , maxlength:"20 caractères maximum" },
					f_password : { required:"mot de passe manquant" , maxlength:"20 caractères maximum" }
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
				if($('#f_login').val()!='connexion ENT')
				{
					$(this).ajaxSubmit(ajaxOptions);
					return false;
				}
				else
				{
					document.location.href = './index.php?fichier=login_SSO&structure_id='+$('#f_structure option:selected').val()+'&sso='+mode_connexion;
					return false;
				}
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
			if((responseHTML=='eleve')||(responseHTML=='professeur')||(responseHTML=='directeur')||(responseHTML=='administrateur'))
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Identification réussie !");
				document.location.href = './index.php?dossier='+responseHTML;
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

	}
);

// Konami Code !!!
jQuery
(
	function()
	{
		var kKeys = [];
		function Kpress(e)
		{
			kKeys.push(e.keyCode);
			if (kKeys.toString().indexOf("38,38,40,40,37,39,37,39,66,65") >= 0)
			{
				jQuery(this).unbind('keydown', Kpress);
				setVolume(50);play("bennyhill");
				$('h1,h2,p,form,hr,ul,div').fadeOut(2000,function(){	// si on applique le fadeout au body, la musique s'arrête !
 					$('h1,h2,p,form,hr,ul,div').remove();
					$('body').append('<p class="hc"><img src="./_img/konami/laurel_hardy.gif" alt="Laurel et Hardy"/><br /><img src="./_img/konami/piste_danse.gif" alt="Piste de dance"/></p>').fadeIn(2000); 
				});
			}
		}
		jQuery(document).keydown(Kpress);
	}
);
