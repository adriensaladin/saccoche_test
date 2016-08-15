/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_uai_origine en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_uai_origine(f_eleve)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_structure_origine',
          data : 'f_eleve='+f_eleve,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_uai_origine').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg_uai_origine').removeAttr('class').html("");
              $('#f_uai_origine').html(responseJSON['value']).parent().show();
            }
            else
            {
              $('#ajax_msg_uai_origine').removeAttr('class').addClass('alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_eleve en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre=alpha'+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_groupe').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg_groupe').removeAttr('class').html("");
              $('#f_eleve').html(responseJSON['value']).parent().show();
              if( (groupe_type=='d') || (groupe_type=='n') )
              {
                var tab_eleve = new Array(); $("#f_eleve input").each(function(){tab_eleve.push($(this).val());});
                maj_uai_origine(tab_eleve);
              }
            }
            else
            {
              $('#ajax_msg_groupe').removeAttr('class').addClass('alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    function changer_groupe()
    {
      listing_eleve_id = '';
      $("#f_eleve").html('').parent().hide();
      $("#f_uai_origine").html('<option></option>').parent().hide();
      var groupe_val = $("#f_groupe option:selected").val();
      if(groupe_val)
      {
        // type = $("#f_groupe option:selected").parent().attr('label');
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
        $('#ajax_msg_groupe').removeAttr('class').addClass('loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type);
      }
      else
      {
        $('#ajax_msg_groupe').removeAttr('class').html("");
      }
    }
    $("#f_groupe").change
    (
      function()
      {
        changer_groupe();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_periode en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_periode(annee_val)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_officiel_periode',
          data : 'f_annee='+annee_val,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_annee').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg_annee').removeAttr('class').html("");
              $('#f_periode').html(responseJSON['value']).parent().show();
            }
            else
            {
              $('#ajax_msg_annee').removeAttr('class').addClass('alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    function changer_annee()
    {
      $("#f_periode").html('<option></option>').parent().hide();
      var annee_val = $("#f_annee option:selected").val();
      if( parseInt(annee_val,10) )
      {
        $('#ajax_msg_annee').removeAttr('class').addClass('loader').html("En cours&hellip;");
        maj_periode(annee_val);
      }
      else
      {
        $('#ajax_msg_annee').removeAttr('class').html("");
      }
    }
    $("#f_annee").change
    (
      function()
      {
        changer_annee();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enlever le message ajax et le résultat au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#bilan').html("");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_select");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_groupe       : { required:true },
          'f_eleve[]'    : { required:true },
          f_uai_origine  : { required:true },
          f_annee        : { required:true },
          f_periode      : { required:function(){return $('#f_annee').val()!="0";} },
          'f_type_ref[]' : { required:true }
        },
        messages :
        {
          f_groupe       : { required:"regroupement manquant" },
          'f_eleve[]'    : { required:"élève(s) manquant(s)" },
          f_uai_origine  : { required:"structure manquante" },
          f_annee        : { required:"année scolaire manquante" },
          f_periode      : { required:"période manquante" },
          'f_type_ref[]' : { required:"type d'archive manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="checkbox") {element.parent().parent().next().after(error);}
        }
        // success: function(label) {label.text("ok").removeAttr('class').addClass('valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
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

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg').removeAttr('class').addClass('loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#bouton_valider').prop('disabled',false);
      var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      $('#ajax_msg').removeAttr('class').addClass('alerte').html(message);
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('#bouton_valider').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg').removeAttr('class').addClass('valide').html("Résultat ci-dessous.");
        $('#bilan').html('<hr /><ul class="puce"><li><a target="_blank" href="'+responseJSON['href']+'">'+responseJSON['texte']+'</a></li></ul>');
      }
    }

  }
);
