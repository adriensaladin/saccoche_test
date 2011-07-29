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

		// Mis dans le js car incorrect en XHTML1 malgré l'intérêt de l'attribut (revenu en HTML5 et XHTML2)
		function autocomplete()
		{
			$('#f_password').attr('autocomplete','off');
			$('#f_login').attr('autocomplete','off');
		}

		function curseur()
		{
			if($("#f_profil").val()=='webmestre')
			{
				$('#f_password').focus();
			}
			else if($("#f_login").length)
			{
				$('#f_login').focus();
			}
		}

		// Appel en ajax pour tester le numéro de la dernière version (et le comparer avec l'actuelle).
		function tester_version()
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=tester_version',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_version').addClass("alerte").html('Echec de la connexion avec le serveur communautaire !');
						return false;
					},
					success : function(responseHTML)
					{
						if( (responseHTML.length!=10) && (responseHTML.length!=11) )
						{
							$('#ajax_version').addClass("alerte").html(responseHTML);
						}
						else if(responseHTML!=VERSION_PROG)
						{
							$('#ajax_version').addClass("alerte").html('Dernière version disponible <em>'+responseHTML+'</em>.');
						}
						else
						{
							$('#ajax_version').addClass("valide").html('Cette version est la dernière disponible.');
						}
					}
				}
			);
		}
		tester_version();

		// Appel en ajax pour initialiser le formulaire au chargement
		function chargement()
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=initialiser&f_base='+$("#f_base").val()+'&f_profil='+$("#f_profil").val(),
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
						return false;
					},
					success : function(responseHTML)
					{
						if( (responseHTML.substring(0,18)!='<label class="tab"') && (responseHTML.substring(0,17)!='<span class="tab"') )
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$("fieldset").html(responseHTML);
							autocomplete();
							curseur();
						}
					}
				}
			);
		}
		chargement();

		// Choix dans le formulaire des structures => Afficher le formulaire de la structure
		$('#f_choisir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Chargement en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=charger&f_base='+$('#f_base option:selected').val()+'&f_profil='+$("#f_profil").val(),
						dataType : "html",
						error : function(msg,string)
						{
							$('button').prop('disabled',false);
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							$('button').prop('disabled',false);
							if( (responseHTML.substring(0,18)!='<label class="tab"') && (responseHTML.substring(0,17)!='<span class="tab"') )
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$("fieldset").html(responseHTML);
								autocomplete();
								curseur();
							}
						}
					}
				);
			}
		);

		// Clic sur le lien pour changer de structure
		$('#f_changer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#f_changer').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Chargement en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=choisir&f_base='+$('#f_base').val()+'&f_profil='+$("#f_profil").val(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#f_changer').show();
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							$('#f_changer').show();
							if(responseHTML.substring(0,18)!='<label class="tab"')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$("fieldset").html(responseHTML);
								autocomplete();
								curseur();
							}
						}
					}
				);
			}
		);

		//	Afficher / masquer le formulaire d'identifiants SACoche si formulaire ENT possible
		$('input[type=radio]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('change',
			function()
			{
				if($('#f_mode_normal').is(':checked'))
				{
					$("#fieldset_normal").show();
					$('#f_login').focus();
				}
				else
				{
					$("#fieldset_normal").hide();
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
					f_base     : { required:true },
					f_login    : { required:true , maxlength:20 },
					f_password : { required:true , maxlength:20 }
				},
				messages :
				{
					f_base     : { required:"établissement manquant" },
					f_login    : { required:"nom d'utilisateur manquant" , maxlength:"20 caractères maximum" },
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
			url : 'ajax.php?page='+PAGE,
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
				if( ($('#fieldset_normal').length) && !($('#f_mode_normal').is(':checked')) )
				{
					document.location.href = './index.php?sso='+$("input[name=f_mode]:checked").val()+'&base='+$('#f_base').val();
					return false;
				}
				else
				{
					$(this).ajaxSubmit(ajaxOptions);
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
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('button').prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			$('button').prop('disabled',false);
			if(responseHTML=='ok')
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Identification réussie !");
				document.location.href = './index.php?page=compte_accueil&verif_cookie';
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

	}
);
