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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_pilier en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_pilier = function()
    {
      $("#f_pilier").html('').parent().hide();
      palier_id = $("#f_palier").val();
      if(palier_id)
      {
        $('#ajax_maj_pilier').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_piliers',
            data : 'f_palier='+palier_id+'&f_multiple=1',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,6)=='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#ajax_maj_pilier').removeAttr("class").html('&nbsp;');
                $('#f_pilier').html(responseHTML).parent().show();
              }
              else
              {
                $('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html(responseHTML);
              }
            }
          }
        );
      }
      else
      {
        $('#ajax_maj_pilier').removeAttr("class").html("&nbsp;");
      }
    };

    $("#f_palier").change( maj_pilier );

    maj_pilier();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_eleve en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_eleve = function()
    {
      $("#f_eleve").html('');
      groupe_id = $("#f_groupe").val();
      if(groupe_id)
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label');
        if(typeof(groupe_type)=='undefined') {groupe_type = 'Classes';} // Cas d'un P.P.
        $('#ajax_maj_eleve').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_eleves',
            data : 'f_groupe='+groupe_id+'&f_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_eleve').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,6)=='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#ajax_maj_eleve').removeAttr("class").html("&nbsp;");
                $('#f_eleve').html(responseHTML).parent().show();
              }
              else
              {
                $('#ajax_maj_eleve').removeAttr("class").addClass("alerte").html(responseHTML);
              }
            }
          }
        );
      }
      else
      {
        $('#ajax_maj_eleve').removeAttr("class").html("&nbsp;");
      }
    };

    $("#f_groupe").change( maj_eleve );

    maj_eleve(); // Dans le cas d'un P.P.

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire pour afficher le tableau avec les états de validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire0 = $('#zone_choix');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation0 = formulaire0.validate
    (
      {
        rules :
        {
          'f_pilier[]' : { required:true },
          f_groupe     : { required:true },
          'f_eleve[]'  : { required:true }
        },
        messages :
        {
          'f_pilier[]' : { required:"compétence(s) manquante(s)" },
          f_groupe     : { required:"classe / groupe manquant" },
          'f_eleve[]'  : { required:"élève(s) manquant(s)" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="checkbox") {element.parent().parent().next().after(error);}
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions0 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_choix",
      beforeSubmit : test_form_avant_envoi0,
      error : retour_form_erreur0,
      success : retour_form_valide0
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire0.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions0);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi0(formData, jqForm, options)
    {
      $('#ajax_msg_choix').removeAttr("class").html("&nbsp;");
      var readytogo = validation0.form();
      if(readytogo)
      {
        $("#Afficher_validation").prop('disabled',true);
        $('#ajax_msg_choix').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur0(jqXHR, textStatus, errorThrown)
    {
      $("#Afficher_validation").prop('disabled',false);
      $('#ajax_msg_choix').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide0(responseHTML)
    {
      initialiser_compteur();
      $("#Afficher_validation").prop('disabled',false);
      if(responseHTML.substring(0,7)!='<thead>')
      {
        $('#ajax_msg_choix').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        responseHTML = responseHTML.replace( '@PALIER@' , $("#f_palier option:selected").text() );
        $('#tableau_validation').html(responseHTML);
        $('#zone_validation').show('fast');
        $('#ajax_msg_choix').removeAttr("class").html('');
        $('#zone_choix').hide('fast');
        $("body").oneTime("1s", function() {window.scrollTo(0,1000);} );
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur une cellule validée du tableau => Afficher le message de confirmation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var td_id = '';

    $('#tableau_validation').on
    (
      'click',
      'td.v1',
      function()
      {
        td_id = $(this).attr('id');
        var user_id  = td_id.substring(1,td_id.indexOf('C'));
        $('#report_nom').html( $('#I'+user_id).attr('alt') );
        $('#report_compet').html( $(this).parent().children('th').text().substring(0,12) );
        $('#confirmation').css('opacity',1);
        $('#fermer_zone_validation').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer la zone de validation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'click',
      '#fermer_zone_validation',
      function()
      {
        $('#zone_choix').show('fast');
        $('#zone_validation').hide('fast');
        $('#tableau_validation').html('<tbody><tr><td></td></tr></tbody>');
        return(false);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour envoyer les validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'click',
      '#Enregistrer_validation',
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_validation').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Enregistrer_validation'+'&delete_id='+td_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("button").prop('disabled',false);
              if(responseHTML.substring(0,2)!='OK')
              {
                $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#'+td_id).removeAttr("class").removeAttr("lang").addClass("v3");
                $('#fermer_zone_validation').removeAttr("class").addClass("retourner").html('Retour');
                $('#confirmation').css('opacity',0);
                $('#ajax_msg_validation').removeAttr("class").html("&nbsp;");
              }
            }
          }
        );
      }
    );

  }
);

