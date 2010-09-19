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
//	Clic sur le lien pour Lancer une recherche de structure sur le serveur communautaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#ouvrir_recherche').click
		(
			function()
			{
				$('#form_instance').hide();
				$('#ajax_msg_instance').removeAttr("class").html("&nbsp;");
				// Décocher tous les boutons radio
				$('#f_recherche_mode input[type=radio]').each
				(
					function()
					{
						this.checked = false;
					}
				);
				$('#f_recherche_uai').hide();
				$('#f_recherche_geo').hide();
				$('#f_recherche_resultat').html('<li></li>').hide();
				$('#form_communautaire').show();
				maj_clock(1);
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour Annuler la recherche sur le serveur communautaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#rechercher_annuler').click
		(
			function()
			{
				$('#form_instance').show();
				$('#form_communautaire').hide();
				return(false);
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form_instance');

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
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_sesamath_id       : { required:true , digits:true },
					f_sesamath_uai      : { required:false , uai_format:true , uai_clef:true },
					f_sesamath_type_nom : { required:true , maxlength:50 },
					f_sesamath_key      : { required:true , rangelength:[32,32] }
				},
				messages :
				{
					f_sesamath_id       : { required:"identifiant manquant" , digits:"identifiant uniquement composé de chiffres" },
					f_sesamath_uai      : { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
					f_sesamath_type_nom : { required:"dénomination manquante" , maxlength:"50 caractères maximum" },
					f_sesamath_key      : { required:"clef manquante" , rangelength:"la clef doit comporter 32 caractères" }
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
			target : "#ajax_msg_instance",
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
			$('#ajax_msg_instance').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				$("#bouton_valider").attr('disabled','disabled');
				$('#ajax_msg_instance').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$("#bouton_valider").removeAttr('disabled');
			$('#ajax_msg_instance').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$("#bouton_valider").removeAttr('disabled');
			if(responseHTML=='ok')
			{
				$('#ajax_msg_instance').removeAttr("class").addClass("valide").html("Données enregistrées !");
			}
			else
			{
				$('#ajax_msg_instance').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Intercepter la touche entrée pour éviter une soumission d'un formulaire sans contrôle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#form_communautaire').submit
		(
			function()
			{
				if( $('#f_mode_uai').attr('checked')==true )
				{
					$("#rechercher_uai").click();
				}
				return false;
			}
		);

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Charger le select geo1 en ajax (appel au serveur communautaire)
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_geo1()
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'action=Afficher_form_geo1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						if(responseHTML.substring(0,26)=='<option value=""></option>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
							$('#f_geo1').html(responseHTML).fadeIn('fast').focus();
							maj_clock(1);
						}
						else
						{
							$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select geo2 en ajax (appel au serveur communautaire)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_geo2(geo1_val)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'action=Afficher_form_geo2&f_geo1='+geo1_val,
					dataType : "html",
					error : function(msg,string)
					{
						$('#f_recherche_geo select').removeAttr('disabled');
						$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						$('#f_recherche_geo select').removeAttr('disabled');
						if(responseHTML.substring(0,26)=='<option value=""></option>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
							$('#f_geo2').html(responseHTML).fadeIn('fast').focus();
							maj_clock(1);
						}
						else
						{
							$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select geo3 en ajax (appel au serveur communautaire)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_geo3(geo1_val,geo2_val)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'action=Afficher_form_geo3&f_geo1='+geo1_val+'&f_geo2='+geo2_val,
					dataType : "html",
					error : function(msg,string)
					{
						$('#f_recherche_geo select').removeAttr('disabled');
						$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						$('#f_recherche_geo select').removeAttr('disabled');
						if(responseHTML.substring(0,26)=='<option value=""></option>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
							$('#f_geo3').html(responseHTML).fadeIn('fast').focus();
							maj_clock(1);
						}
						else
						{
							$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Rechercher les structures à partir du select geo3 en ajax (appel au serveur communautaire)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_resultat_geo(geo3_val)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'action=Afficher_structures&f_geo3='+geo3_val,
					dataType : "html",
					error : function(msg,string)
					{
						$('#f_recherche_geo select').removeAttr('disabled');
						$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						$('#f_recherche_geo select').removeAttr('disabled');
						if(responseHTML.substring(0,3)=='<li')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
							$('#f_recherche_resultat').html(responseHTML).show();
							format_liens('#f_recherche_resultat');
							infobulle();
							maj_clock(1);
						}
						else
						{
							$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Rechercher les structures à partir du select uai en ajax (appel au serveur communautaire)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_resultat_uai(uai_val)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'action=Afficher_structures&f_uai='+uai_val,
					dataType : "html",
					error : function(msg,string)
					{
						$('#rechercher_uai').removeAttr('disabled');
						$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						$('#rechercher_uai').removeAttr('disabled');
						if(responseHTML.substring(0,3)=='<li')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
							$('#f_recherche_resultat').html(responseHTML).show();
							format_liens('#f_recherche_resultat');
							infobulle();
							maj_clock(1);
						}
						else
						{
							$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix du mode de recherche de la structure => demande d'actualisation éventuelle du select geo1
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#f_recherche_mode input').click
		(
			function()
			{
				mode = $(this).val();
				$("#f_recherche_resultat").html('<li></li>').hide();
				if(mode=='geo')
				{
					$('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
					$('#f_geo1').html('<option value=""></option>').fadeOut('fast'); // Ne pas utiliser "hide()" sinon pb de display block
					$('#f_geo2').html('<option value=""></option>').fadeOut('fast'); // Ne pas utiliser "hide()" sinon pb de display block
					$('#f_geo3').html('<option value=""></option>').fadeOut('fast'); // Ne pas utiliser "hide()" sinon pb de display block
					$("#f_recherche_uai").hide();
					$("#f_recherche_geo").show();
					maj_geo1();
				}
				else if(mode=='uai')
				{
					$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
					$("#f_recherche_geo").hide();
					$("#f_recherche_uai").show();
					$("#f_uai2").focus();
					maj_clock(1);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement du select geo1 => demande d'actualisation du select geo2
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_geo1").change
		(
			function()
			{
				$("#f_recherche_resultat").html('<li></li>').hide();
				$('#f_geo2').html('<option value=""></option>').fadeOut('fast');
				$('#f_geo3').html('<option value=""></option>').fadeOut('fast');
				var geo1_val = $("#f_geo1").val();
				if(geo1_val)
				{
					$('#f_recherche_geo select').attr('disabled','disabled');
					$('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
					maj_geo2(geo1_val);
				}
				else
				{
					$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement du select geo2 => demande d'actualisation du select geo3
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_geo2").change
		(
			function()
			{
				$("#f_recherche_resultat").html('<li></li>').hide();
				$('#f_geo3').html('<option value=""></option>').fadeOut('fast');
				var geo1_val = $("#f_geo1").val();
				var geo2_val = $("#f_geo2").val();
				if(geo1_val && geo2_val)
				{
					$('#f_recherche_geo select').attr('disabled','disabled');
					$('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
					maj_geo3(geo1_val,geo2_val);
				}
				else
				{
					$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement du select geo3 => demande d'actualisation du résultat de la recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_geo3").change
		(
			function()
			{
				$("#f_recherche_resultat").html('<li></li>').hide();
				var geo3_val = $("#f_geo3").val();
				if(geo3_val)
				{
					$('#f_recherche_geo select').attr('disabled','disabled');
					$('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
					maj_resultat_geo(geo3_val);
				}
				else
				{
					$('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Validation du numéro uai => demande d'actualisation du résultat de la recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#rechercher_uai").click
		(
			function()
			{
				$("#f_recherche_resultat").html('<li></li>').hide();
				var uai_val = $("#f_uai2").val();
				// Vérifier le format du numéro UAI
				uai_val = uai_val.toUpperCase();
				if(uai_val.length!=8)
				{
					$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Erreur : il faut 7 chiffres suivis d'une lettre !");
					return false;
				}
				var uai_fin = uai_val.substring(7,8);
				if((uai_fin<"A")||(uai_fin>"Z"))
				{
					$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Erreur : il faut 7 chiffres suivis d'une lettre !");
					return false;
				}
				for(i=0;i<7;i++)
				{
					var t = uai_val.substring(i,i+1);
					if((t<"0")||(t>"9"))
					{
						$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Erreur : il faut 7 chiffres suivis d'une lettre !");
						return false;
					}
				}
				// Vérifier la géographie du numéro UAI
				// => sans objet
				// Vérifier la clef de contrôle du numéro UAI
				var uai_nombre = uai_val.substring(0,7);
				alphabet = "ABCDEFGHJKLMNPRSTUVWXYZ";
				reste = uai_nombre-(23*Math.floor(uai_nombre/23));
				clef = alphabet.substring(reste,reste+1);;
				if(clef!=uai_fin )
				{
					$('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Erreur : clef de contrôle incompatible !");
					return false;
				}
				// Si on arrive jusque là c'est que le n° UAI est valide
				$('#rechercher_uai').attr('disabled','disabled');
				$('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				maj_resultat_uai(uai_val);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une image pour Valider le choix d'une structure
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#f_recherche_resultat q.valider').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var id_key_uai = $(this).parent().attr('id').substr(3); // id ; key ; uai séparés par '_' ; attention, le n°UAI peut être vide...
				var denomination = $(this).parent().text();
				var tab_infos = id_key_uai.split('_');
				$('#f_sesamath_id').val(tab_infos[0]);
				$('#f_sesamath_key').val(tab_infos[1]);
				$('#f_sesamath_uai').val(tab_infos[2]); // (peut être vide)
				$('#f_sesamath_type_nom').val(denomination);
				$('#ajax_msg_instance').removeAttr("class").addClass("alerte").html('Pensez à valider pour confirmer votre sélection !');
				maj_clock(1);
				$('#rechercher_annuler').click();
			}
		);

	}
);
