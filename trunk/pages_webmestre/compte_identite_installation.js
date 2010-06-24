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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Appel en ajax pour initialiser/actualiser le select f_logo
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function chargement_select_logo()
		{
			$('#ajax_logo').removeAttr("class").addClass("loader").html('Chargement en cours...');
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
					data : 'f_action=select_logo',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_logo').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
						return false;
					},
					success : function(responseHTML)
					{
						if(responseHTML.substring(0,16)!='<option value=""')
						{
							$('#ajax_logo').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$('#ajax_logo').removeAttr("class").html('');
							$("#f_logo").html(responseHTML);
						}
					}
				}
			);
		}
		chargement_select_logo();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Appel en ajax pour initialiser/actualiser le ul listing_logos
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function chargement_ul_logo()
		{
			$('#ajax_listing').removeAttr("class").addClass("loader").html('Chargement en cours...');
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
					data : 'f_action=listing_logos',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_listing').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
						return false;
					},
					success : function(responseHTML)
					{
						if(responseHTML.substring(0,4)!='<li>')
						{
							$('#ajax_listing').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$('#ajax_listing').removeAttr("class").html('');
							$("#listing_logos").html(responseHTML);
						}
					}
				}
			);
		}
		chargement_ul_logo();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Appel en ajax pour supprimer un logo
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('q.supprimer').live
		( 'click' , function()
			{
				memo_li = $(this).parent();
				logo = $(this).next().next().attr('alt');
				$('#ajax_listing').removeAttr("class").addClass("loader").html('Demande transmise...');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_action=delete_logo&f_logo='+logo,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_listing').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML!='ok')
							{
								$('#ajax_listing').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_listing').removeAttr("class").html('');
								memo_li.remove();
								chargement_select_logo();
							}
						}
					}
				);
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Upload d'un fichier image avec jquery.ajaxupload.js
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		new AjaxUpload
		('#f_upload',
			{
				action: 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
				name: 'userfile',
				data: {'f_action':'upload_logo'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('#f_upload').hide();
			$('#ajax_upload').removeAttr("class").html('&nbsp;');
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#f_upload').show();
				$('#ajax_upload').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if ('.bmp.gif.jpg.jpeg.png.svg.'.indexOf('.'+fichier_extension+'.')==-1)
			{
				$('#f_upload').show();
				$('#ajax_upload').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension d\'image autorisée (bmp gif jpg jpeg png svg).');
				return false;
			}
			else
			{
				$('#ajax_upload').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)
		{
			if(responseHTML!='ok')
			{
				$('#f_upload').show();
				$('#ajax_upload').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				maj_clock(1);
				$('#f_upload').show();
				$('#ajax_upload').removeAttr("class").html('&nbsp;');
				chargement_select_logo();
				chargement_ul_logo();
			}
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form1');

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
					f_denomination : { required:true , maxlength:60 },
					f_uai :          { required:false , uai_format:true , uai_clef:true },
					f_adresse_site : { required:false , maxlength:150 },
					f_logo :         { required:false },
					f_cnil :         { required:true },
					f_nom :          { required:true , maxlength:20 },
					f_prenom :       { required:true , maxlength:20 },
					f_courriel :     { required:true , email:true , maxlength:60 }
				},
				messages :
				{
					f_denomination : { required:"dénomination manquante" , maxlength:"60 caractères maximum" },
					f_uai :          { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
					f_adresse_site : { maxlength:"150 caractères maximum" },
					f_logo :         { },
					f_cnil :         { required:"indication CNIL manquante" },
					f_nom :          { required:"nom manquant" , maxlength:"20 caractères maximum" },
					f_prenom :       { required:"prénom manquant" , maxlength:"20 caractères maximum" },
					f_courriel :     { required:"courriel manquant" , email:"courriel invalide", maxlength:"63 caractères maximum" }
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
				if (!please_wait)
				{
					$(this).ajaxSubmit(ajaxOptions);
					return false;
				}
				else
				{
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
				please_wait = true;
				$('#f_submit').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			please_wait = false;
			$('#f_submit').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			please_wait = false;
			$('#f_submit').show();
			if((responseHTML=='ok'))
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Données enregistrées !");
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

	}
);
