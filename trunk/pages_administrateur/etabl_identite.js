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
//	Clic sur le lien pour Lancer une recherche sur le serveur communautaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#ouvrir_recherche').click
		(
			function()
			{
				adresse = url_debut + '?mode=object' + '&fichier=structure_rechercher' + '&adresse_retour=' + encodeURIComponent(document.location.href);	// Mettre href sinon c'est le dernier appel ajax (non visible dans la barre d'adresse) qui compte...
				$('#form').hide();
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				if($('#object_container object').length)
				{
					$('#cadre').attr('data',adresse).parent().show();
				}
				else
				{
					$('#cadre').attr('src',adresse).parent().show();
				}
				surveiller_url_et_hauteur();
				maj_clock(1);
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Inspection de l'URL : l'ajout d'un hash indique un retour de l'iframe suite à un renvoi d'informations ou pour maj le compteur de session
//	Pour les explications : http://softwareas.com/cross-domain-communication-with-iframes (démo 1 : http://ajaxify.com/run/crossframe/ )
//	Attention, seule la 1e méthode fonctionne, la 2nde avec les iframes ajouté n'est pas compatible avec tous les navigateurs.
//	Voir aussi cette librairie : http://easyxdm.net/wp/
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function surveiller_url_et_hauteur()
		{
			// Attention à ne pas mettre un délai trop faible ; pour 1ds par exemple, certains anciens navigateurs appellent en boucle la fonction faute d'avoir eu le temps d'enlever le hash
			$("body").everyTime
			('1s', 'surveillance', function()
				{
					// Surveillance de l'URL
					var hashVal = window.location.hash.substr(1);
					if(hashVal!="")
					{
						window.location.hash='#';
						if(hashVal=='maj_clock')
						{
							maj_clock(1);
						}
						else
						{
							$("body").stopTime('surveillance');
							tab_infos = hashVal.split('-+');
							if(tab_infos.length == 4)
							{
								$('#f_sesamath_id').val(tab_infos[0]);
								$('#f_sesamath_key').val(tab_infos[1]);
								$('#f_sesamath_uai').val(tab_infos[2]); // (peut être vide)
								$('#f_sesamath_type_nom').val(tab_infos[3]);
								$('#ajax_msg').removeAttr("class").addClass("alerte").html('Pensez à valider pour confirmer votre sélection !');
								maj_clock(1);
							}
							$('#rechercher_annuler').click();
						}
					}
					// Surveillance du redimensionnement
					var hauteur_entete = 230;
					var hauteur_object_mini = 350;
					var hauteur_document = hauteur_entete+hauteur_object_mini;
					// hauteur_document = $(document).height() pose problème si on retrécit la fenêtre en hauteur : il s'adapte très lentement...
					// D'où la procédure suivante récupérée à l'adresse http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
					if( typeof( window.innerHeight ) == 'number' )
					{
						hauteur_document = window.innerHeight;	//Non-IE
					}
					else if( document.documentElement && document.documentElement.clientHeight )
					{
						hauteur_document = document.documentElement.clientHeight;	//IE 6+ in 'standards compliant mode'
					}
					else if( document.body && document.body.clientHeight )
					{
						hauteur_document = document.body.clientHeight;	//IE 4 compatible
					}
					var hauteur_object = Math.max(hauteur_document-hauteur_entete,hauteur_object_mini);
					$('#cadre').css('height',hauteur_object);
				}
			);
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour Annuler la recherche sur le serveur communautaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#rechercher_annuler').click
		(
			function()
			{
				$('form').show();
				adresse = './_img/ajax/ajax_loader.gif';
				if($('#object_container object').length)
				{
					$('#cadre').attr('data',adresse).parent().hide();
				}
				else
				{
					$('#cadre').attr('src',adresse).parent().hide();
				}
				$("body").stopTime('surveillance');
				return(false);
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form');

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
				$("#bouton_valider").attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$("#bouton_valider").removeAttr('disabled');
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$("#bouton_valider").removeAttr('disabled');
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
