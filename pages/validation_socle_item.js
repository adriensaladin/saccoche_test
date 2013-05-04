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

    // Initialisation
    var modification = false;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher / masquer des éléments de formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_mode_auto').click
    (
      function()
      {
        $("#div_matiere").hide();
      }
    );

    $('#f_mode_manuel').click
    (
      function()
      {
        $("#div_matiere").show();
      }
    );


// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_pilier en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_pilier = function()
    {
      $("#f_pilier").html('<option value=""></option>').hide();
      palier_id = $("#f_palier").val();
      if(palier_id)
      {
        $('#ajax_maj_pilier').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_piliers',
            data : 'f_palier='+palier_id+'&f_multiple=0',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#ajax_maj_pilier').removeAttr("class").html('&nbsp;');
                $('#f_pilier').html(responseHTML).show();
                maj_domaine();
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
// Charger le select f_domaine en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_domaine = function()
    {
      $("#f_domaine").html('').parent().hide();
      pilier_id = $("#f_pilier").val();
      if(pilier_id)
      {
        $('#ajax_maj_domaine').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_domaines',
            data : 'f_pilier='+pilier_id+'&f_multiple=1',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_domaine').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,6)=='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#ajax_maj_domaine').removeAttr("class").html('&nbsp;');
                $('#f_domaine').html(responseHTML).parent().show();
              }
              else
              {
                $('#ajax_maj_domaine').removeAttr("class").addClass("alerte").html(responseHTML);
              }
            }
          }
        );
      }
      else
      {
        $('#ajax_maj_domaine').removeAttr("class").html("&nbsp;");
      }
    };

    $("#f_pilier").change( maj_domaine );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_eleve en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_eleve = function()
    {
      $("#f_eleve").html('').parent().hide();
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
            data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1',
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
          f_palier      : { required:true },
          f_pilier      : { required:true },
          'f_domaine[]' : { required:true },
          f_groupe      : { required:true },
          'f_eleve[]'   : { required:true },
          f_mode        : { required:true },
          'f_matiere[]' : { required:function(){return $('#f_mode_manuel').is(':checked');} }
        },
        messages :
        {
          f_palier      : { required:"palier manquant" },
          f_pilier      : { required:"compétence manquante" },
          'f_domaine[]' : { required:"domaine(s) manquant(s)" },
          f_groupe      : { required:"classe / groupe manquant" },
          'f_eleve[]'   : { required:"élève(s) manquant(s)" },
          f_mode        : { required:"choix manquant" },
          'f_matiere[]' : { required:"matière(s) manquante(s)" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="radio") {$('#div_matiere').after(error);}
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
        responseHTML = responseHTML.replace( '@PILIER@' , $("#f_pilier option:selected").text() );
        $('#tableau_validation').html(responseHTML);
        $('#zone_validation').show('fast');
        $('#ajax_msg_choix').removeAttr("class").html('');
        $('#zone_choix').hide('fast');
        var texte = ($('#f_mode_manuel').is(':checked')) ? ' [matières resteintes]' : '';
        if(texte)
        {
          // Conserver la liste des matières (besoin pour récupérer les résultats d'un item donné si sélection manuelle
          var tab_matiere = new Array();
          $('#f_matiere input[type=checkbox]:checked').each
          (
            function()
            {
              tab_matiere.push($(this).val());
            }
          );
          $('#f_memo_matieres').val(tab_matiere);
        }
        $('#span_restriction').html(texte);
        $('#zone_information').show('fast');
        $("body").oneTime("1s", function() {window.scrollTo(0,1000);} );
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher / Masquer les pourcentages d'items d'enseignements acquis
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'change',
      '#Afficher_pourcentage',
      function()
      {
        if($(this).is(':checked'))
        {
          color = '#000';
          cell_font_size = '50%';
        }
        else
        {
          color = '';
          cell_font_size = '1%'; /* 0% pour font-size pose problème au navigateur Safari. */
        }
        $('#tableau_validation tbody td').css({ 'color':color, 'font-size':cell_font_size })
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur une cellule du tableau => Modifier visuellement des états de validation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var tab_class_next = new Array;
    tab_class_next['1'] = ['0'];
    tab_class_next['0'] = ['2'];
    tab_class_next['2'] = ['1'];

    $('#tableau_validation').on
    (
      'click',
      'tbody td',
      function()
      {
        // Appliquer un état pour un item pour un élève
        var classe = $(this).attr('class');
        var new_classe = classe.charAt(0) + tab_class_next[classe.charAt(1)] ;
        $(this).removeAttr("class").addClass(new_classe);
        if(modification==false)
        {
          $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Penser à valider les modifications !');
          $('#fermer_zone_validation').removeAttr("class").addClass("annuler").html('Annuler / Retour');
          modification = true;
        }
        return false;
      }
    );

    $('#tableau_validation').on
    (
      'click',
      'tbody th',
      function()
      {
        var classe = $(this).attr('class');
        if(classe=='nu')
        {
          // Intitulé du socle
          return false;
        }
        if(modification==false)
        {
          $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Penser à valider les modifications !');
          $('#fermer_zone_validation').removeAttr("class").addClass("annuler").html('Annuler / Retour');
          modification = true;
        }
        var classe_debut = classe.substring(0,4);
        var classe_fin   = classe.charAt(4);
        var new_classe_th = classe_debut + tab_class_next[classe_fin] ;
        var new_classe_td = 'v' + classe_fin ;
        if(classe_debut=='left')
        {
          // Appliquer un état pour un item pour tous les élèves
          $(this).removeAttr("class").addClass(new_classe_th).parent().children('td').removeAttr("class").addClass(new_classe_td);
          return false;
        }
        if(classe_debut=='down')
        {
          // Appliquer un état pour tout le domaine pour un élève
          var id = $(this).attr('id') + 'E';
          $(this).removeAttr("class").addClass(new_classe_th).parent().parent().find('td[id^='+id+']').removeAttr("class").addClass(new_classe_td);
          return false;
        }
        if(classe_debut=='diag')
        {
          // Appliquer un état pour tous les items pour tous les élèves
          var id = $(this).attr('id') + 'U';
          $(this).removeAttr("class").addClass(new_classe_th).parent().parent().find('td[id^='+id+']').removeAttr("class").addClass(new_classe_td);
          return false;
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Survol prolongé d'une cellule du tableau => Recharger la zone d'informations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var last_id_survole  = '';
    var last_id_memorise = '';
    var last_id_affiche = '';

    $('#tableau_validation').on
    (
      'mouseout',
      'tbody td',
      function()
      {
        last_id_survole = '';
      }
    );

    $('#tableau_validation').on
    (
      'mouseover',
      'tbody td',
      function()
      {
        last_id_survole = $(this).attr('id');
      }
    );

    function surveiller_id()
    {
      $("body").everyTime
      ('5ds', function()
        {
          if( (last_id_survole=='') || (last_id_survole!=last_id_memorise) || (last_id_survole==last_id_affiche) )
          {
            last_id_memorise = last_id_survole;
          }
          else
          {
            last_id_memorise = last_id_survole;
            last_id_affiche  = last_id_survole;
            maj_zone_information(last_id_survole);
          }
        }
      );
    }

    surveiller_id();

    function maj_zone_information(last_id_survole)
    {
      var pos_E = last_id_survole.indexOf('E');
      var pos_U = last_id_survole.indexOf('U');
      var item_id = last_id_survole.substring(pos_E+1);
      var user_id = last_id_survole.substring(pos_U+1,pos_E);
      var langue  = ($('#L'+user_id).length) ? $('#L'+user_id).attr('class').substring(1) : 0 ;
      $('#identite').html( $('#I'+user_id).attr('alt') );
      $('#entree').html( $('#E'+item_id).next('th').children('div').text() );
      $('#stats').html('');
      $('#items').html('');
      $('#ajax_msg_information').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=Afficher_information'+'&f_user='+user_id+'&f_item='+item_id+'&f_pilier='+$('#f_pilier').val()+'&f_mode='+$('input[type=radio]:checked').val()+'&f_matiere='+$('#f_memo_matieres').val()+'&langue='+langue,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_information').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
            return false;
          },
          success : function(responseHTML)
          {
            // initialiser_compteur();
            pos = responseHTML.indexOf('@');
            if(pos==-1)
            {
              $('#ajax_msg_information').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $('#ajax_msg_information').removeAttr("class").html('&nbsp;');
              $('#stats').html( responseHTML.substring(0,pos) );
              $('#items').html( responseHTML.substring(pos+1) );
            }
          }
        }
      );
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer la zone de validation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function fermer_zone_validation()
    {
      $('#zone_choix').show('fast');
      $('#zone_validation').hide('fast');
      $('#tableau_validation').html('<tbody><tr><td></td></tr></tbody>');
      // Vider aussi la zone d'informations
      $('#zone_information').hide('fast');
      $('#identite').html('');
      $('#entree').html('');
      $('#stats').html('');
      $('#items').html('');
      $('#ajax_msg_information').removeAttr("class").html('');
      modification = false;
      return(false);
    }

    $('#tableau_validation').on
    (
      'click',
      '#fermer_zone_validation',
      function()
      {
        if(!modification)
        {
          fermer_zone_validation();
        }
        else
        {
          $.fancybox( { 'href':'#zone_confirmer_fermer_validation' , onStart:function(){$('#zone_confirmer_fermer_validation').css("display","block");} , onClosed:function(){$('#zone_confirmer_fermer_validation').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
          return(false);
        }
      }
    );

    $('#confirmer_fermer_zone_validation').click
    (
      function()
      {
        $.fancybox.close();
        fermer_zone_validation();
      }
    );

    $('#annuler_fermer_zone_validation').click
    (
      function()
      {
        $.fancybox.close();
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
        // Récupérer les infos
        var tab_valid = new Array();
        $("#tableau_validation tbody td").each
        (
          function()
          {
            tab_valid.push( $(this).attr('id') + $(this).attr('class').toUpperCase() );
          }
        );
        // Les envoyer en ajax
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Enregistrer_validation'+'&f_valid='+tab_valid,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              modification = false; // Mis ici pour le cas "aucune modification détectée"
              initialiser_compteur();
              $("button").prop('disabled',false);
              if(responseHTML.substring(0,2)!='OK')
              {
                $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_validation').removeAttr("class").addClass("valide").html("Validations enregistrées !");
                $('#fermer_zone_validation').removeAttr("class").addClass("retourner").html('Retour');
              }
            }
          }
        );
      }
    );

  }
);

