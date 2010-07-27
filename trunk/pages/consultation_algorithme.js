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
//	Changement de méthode -> desactiver les limites autorisées suivant les cas
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Tableaux utilisés pour savoir quelles options desactiver
		var tableau_limites_autorisees = new Array();
		tableau_limites_autorisees['geometrique']  = '.1.2.3.4.5.';
		tableau_limites_autorisees['arithmetique'] = '.1.2.3.4.5.6.7.8.9.';
		tableau_limites_autorisees['classique']    = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
		tableau_limites_autorisees['bestof1']      = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
		tableau_limites_autorisees['bestof2']      =   '.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
		tableau_limites_autorisees['bestof3']      =     '.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
		// La fonction qui s'en occupe
		var actualiser_select_limite = function()
		{
			// Déterminer s'il faut modifier l'option sélectionnée
			limite_valeur = $('#f_limite option:selected').val();
			findme = '.'+limite_valeur+'.';
			methode_valeur = $('#f_methode option:selected').val();
			chaine_autorisee = tableau_limites_autorisees[methode_valeur];
			modifier_limite_selected = (chaine_autorisee.indexOf(findme)==-1) ? true : false ; // 1|3 Si true alors il faudra changer le selected actuel qui ne sera plus dans les nouveaux choix.
			if(modifier_limite_selected)
			{
				modifier_limite_selected = chaine_autorisee.substr(chaine_autorisee.length-2,1) ; // 2|3 On prendra alors la valeur maximale dans les nouveaux choix.
			}
			$("#f_limite option").each
			(
				function()
				{
					// On boucle pour activer / desactiver les options du select.
					limite_valeur = $(this).val();
					findme = '.'+limite_valeur+'.';
					if(chaine_autorisee.indexOf(findme)==-1)
					{
						$(this).attr('disabled','disabled');
					}
					else
					{
						$(this).removeAttr('disabled');
					}
					if(limite_valeur===modifier_limite_selected) // === pour éviter un (false==0) qui sélectionne la 1ère option...
					{
						$(this).attr('selected','selected'); // 3|3 C'est ici que le selected se fait.
					}
				}
			);
		};
		// Appel de la fonction au chargement de la page puis à chaque changement de méthode
		actualiser_select_limite();
		$('#f_methode').change( actualiser_select_limite );


		// Demande de soumission du formulaire
		$('#calculer').click
		(
			function()
			{
				$('#action').val('calculer');
				formulaire.submit();
			}
		);

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
				$('#f_methode option[value='+memo_methode+']').attr("selected",true);
				$('#f_limite option[value='+memo_limite+']').attr("selected",true);
				$('#seuilR').val(memo_seuilR);
				$('#seuilV').val(memo_seuilV);
				actualiser_select_limite();
			}
		);
		// Donc il faut retenir les valeurs initiales et les replacer
		var memo_valeurRR = $('#valeurRR').val();
		var memo_valeurR  = $('#valeurR').val();
		var memo_valeurV  = $('#valeurV').val();
		var memo_valeurVV = $('#valeurVV').val();
		var memo_methode  = $('#f_methode option:selected').val();
		var memo_limite   = $('#f_limite option:selected').val();
		var memo_seuilR   = $('#seuilR').val();
		var memo_seuilV   = $('#seuilV').val();

		// Demande d'initialisation du formulaire avec les valeurs par défaut
		$('#initialiser_defaut').click
		(
			function()
			{
				$('#valeurRR').val(0);
				$('#valeurR').val(33);
				$('#valeurV').val(67);
				$('#valeurVV').val(100);
				$('#f_methode option[value="geometrique"]').attr("selected",true);
				$('#f_limite option[value="5"]').attr("selected",true);
				$('#seuilR').val(40);
				$('#seuilV').val(60);
				actualiser_select_limite();
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
					valeurRR  : { required:true, digits:true },
					valeurR   : { required:true, digits:true },
					valeurV   : { required:true, digits:true },
					valeurVV  : { required:true, digits:true },
					f_methode : { required:true },
					f_limite  : { required:true },
					seuilR    : { required:true, digits:true },
					seuilV    : { required:true, digits:true }
				},
				messages :
				{
					valeurRR :  { required:"valeur requise", digits:"nombre entier requis" },
					valeurR :   { required:"valeur requise", digits:"nombre entier requis" },
					valeurV :   { required:"valeur requise", digits:"nombre entier requis" },
					valeurVV :  { required:"valeur requise", digits:"nombre entier requis" },
					f_methode : { required:"méthode requise" },
					f_limite :  { required:"méthode requise" },
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
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Valeur d'un code : valeurs entre 0 et 100 requises.").show();
				}
				else if( (parseInt($('#valeurRR').val())>parseInt($('#valeurR').val())) || (parseInt($('#valeurR').val())>parseInt($('#valeurV').val())) || (parseInt($('#valeurV').val())>parseInt($('#valeurVV').val())) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Valeur d'un code : valeurs croissantes requises.").show();
				}
				else if( (Math.min($('#seuilR').val(),$('#seuilV').val())<0) || (Math.max($('#seuilR').val(),$('#seuilV').val())>100) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Seuil d'aquisition : valeurs entre 0 et 100 requises.").show();
				}
				else if( parseInt($('#seuilR').val()) > parseInt($('#seuilV').val()) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Seuil d'aquisition : valeurs croissantes requises.").show();
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
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.").show();
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('button').removeAttr('disabled');
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$('button').removeAttr('disabled');
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
