/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
    // Enlever le message ajax et le résultat précédent au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
        $('#bilan').html("&nbsp;");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_type_individuel').click
    (
      function()
      {
        $("#options_individuel").toggle();
      }
    );

    $('#f_type_synthese').click
    (
      function()
      {
        $("#options_synthese").toggle();
      }
    );

    $('#f_etat_acquisition').click
    (
      function()
      {
        $("#span_etat_acquisition").toggle();
      }
    );

    $('#f_moyenne_scores , #f_pourcentage_acquis').click
    (
      function()
      {
        if( ($('#f_moyenne_scores').is(':checked')) || ($('#f_pourcentage_acquis').is(':checked')) )
        {
          $('label[for=f_conversion_sur_20]').show();
        }
        else
        {
          $('label[for=f_conversion_sur_20]').hide();
        }
      }
    );

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    function view_dates_perso()
    {
      var periode_val = $("#f_periode").val();
      if(periode_val!=0)
      {
        $("#dates_perso").attr("class","hide");
      }
      else
      {
        $("#dates_perso").attr("class","show");
      }
    }

    $('#f_periode').change
    (
      function()
      {
        view_dates_perso();
        autoperiode = false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de groupe
// -> desactiver les périodes prédéfinies en cas de groupe de besoin (prof uniquement)
// -> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
// -> afficher le formulaire de périodes s'il est masqué
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function selectionner_periode_adaptee()
    {
      var id_groupe = $('#f_groupe option:selected').val();
      if(typeof(tab_groupe_periode[id_groupe])!='undefined')
      {
        for(var id_periode in tab_groupe_periode[id_groupe]) // Parcourir un tableau associatif...
        {
          var tab_split = tab_groupe_periode[id_groupe][id_periode].split('_');
          if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
          {
            $("#f_periode option[value="+id_periode+"]").prop('selected',true);
            view_dates_perso();
            break;
          }
        }
      }
    }

    $('#f_groupe').change
    (
      function()
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label');
        $("#f_periode option").each
        (
          function()
          {
            periode_id = $(this).val();
            // La période personnalisée est tout le temps accessible
            if(periode_id!=0)
            {
              // classe ou groupe classique -> toutes périodes accessibles
              if(groupe_type!='Besoins')
              {
                $(this).prop('disabled',false);
              }
              // groupe de besoin -> desactiver les périodes prédéfinies
              else
              {
                $(this).prop('disabled',true);
              }
            }
          }
        );
        // Sélectionner si besoin la période personnalisée
        if(groupe_type=='Besoins')
        {
          $("#f_periode option[value=0]").prop('selected',true);
          $("#dates_perso").attr("class","show");
        }
        // Modification automatique du formulaire : périodes
        if(autoperiode)
        {
          if( (typeof(groupe_type)!='undefined') && (groupe_type!='Besoins') )
          {
            // Rechercher automatiquement la meilleure période
            selectionner_periode_adaptee();
          }
          // Afficher la zone de choix des périodes
          if(typeof(groupe_type)!='undefined')
          {
            $('#zone_periodes').removeAttr("class");
          }
          else
          {
            $('#zone_periodes').addClass("hide");
          }
        }
      }
    );

    // Rechercher automatiquement la meilleure période au chargement de la page (uniquement pour un élève, seul cas où la classe est préselectionnée)
    selectionner_periode_adaptee();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger les selects f_eleve (pour le professeur et le directeur et les parents de plusieurs enfants) et f_matiere (pour le directeur) en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var matiere_id  = 0;
    var groupe_id   = 0;
    var groupe_type = '';

    function maj_matiere(groupe_id,matiere_id) // Uniquement pour un directeur
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_matieres',
          data : 'f_groupe='+groupe_id+'&f_matiere='+matiere_id+'&f_multiple=0',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#f_matiere').html(responseHTML).show();
              maj_eleve(groupe_id,groupe_type);
            }
          else
            {
              $('#ajax_maj').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    function maj_eleve(groupe_id,groupe_type)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple='+is_multiple+'&f_selection=1',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if( ( is_multiple && (responseHTML.substring(0,6)=='<label') ) || ( !is_multiple && (responseHTML.substring(0,7)=='<option') ) ) // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_maj').removeAttr("class").html("&nbsp;");
              $('#f_eleve').html(responseHTML).parent().show();
              // Demande d'affichage du relevé pour un parent avec plusieurs enfants
              if(auto_select_eleve_id)
              {
                $('#f_eleve option[value='+auto_select_eleve_id+']').prop('selected',true);
                $('#bouton_valider').click();
                auto_voir_releve = false;
              }
            }
            else
            {
              $('#ajax_maj').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    $("#f_groupe").change
    (
      function charger_eleves()
      {
        // Pour un directeur, on met à jour f_matiere (on mémorise avant matiere_id) puis f_eleve
        // Pour un professeur ou un parent de plusieurs enfants, on met à jour f_eleve uniquement
        // Pour un élève ou un parent d'un seul enfant cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
        if(PROFIL_TYPE=='directeur')
        {
          matiere_id = $("#f_matiere").val();
          $("#f_matiere").html('<option value=""></option>').hide();
        }
        $("#f_eleve").html('').parent().hide();
        groupe_id = $("#f_groupe").val();
        if(groupe_id)
        {
          groupe_type = $("#f_groupe option:selected").parent().attr('label');
          $('#ajax_maj').removeAttr("class").addClass("loader").html("En cours&hellip;");
          if(PROFIL_TYPE=='directeur')
          {
            maj_matiere(groupe_id,matiere_id);
          }
          else if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='parent') )
          {
            maj_eleve(groupe_id,groupe_type);
          }
        }
        else
        {
          $('#ajax_maj').removeAttr("class").html("&nbsp;");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger toutes les matières ou seulement les matières affectées (pour un prof)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var modifier_action = 'ajouter';
    $("#modifier_matiere").click
    (
      function()
      {
        $('button').prop('disabled',true);
        var matiere_id = $("#f_matiere option:selected").val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_matieres_prof',
            data : 'f_matiere='+matiere_id+'&f_action='+modifier_action+'&f_multiple=0',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                modifier_action = (modifier_action=='ajouter') ? 'retirer' : 'ajouter' ;
                $('#modifier_matiere').removeAttr("class").addClass("form_"+modifier_action);
                $('#f_matiere').html(responseHTML);
              }
              $('button').prop('disabled',false);
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumettre le formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_select");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          'f_type[]'           : { required:true },
          f_etat_acquisition   : { required:false },
          f_moyenne_scores     : { required:false },
          f_pourcentage_acquis : { required:false },
          f_conversion_sur_20  : { required:false },
          f_tri_objet          : { required:true },
          f_tri_mode           : { required:true },
          f_groupe             : { required:true },
          'f_eleve[]'          : { required:true },
          f_periode            : { required:true },
          f_date_debut         : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_date_fin           : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_retroactif         : { required:true },
          f_matiere            : { required:true },
          f_restriction        : { required:false },
          f_coef               : { required:false },
          f_socle              : { required:false },
          f_lien               : { required:false },
          f_domaine            : { required:false },
          f_theme              : { required:false },
          f_orientation        : { required:true },
          f_couleur            : { required:true },
          f_legende            : { required:true },
          f_marge_min          : { required:true },
          f_pages_nb           : { required:true },
          f_cases_nb           : { required:true },
          f_cases_larg         : { required:true },
          f_highlight_id       : { required:false }
        },
        messages :
        {
          'f_type[]'           : { required:"type(s) manquant(s)" },
          f_etat_acquisition   : { },
          f_moyenne_scores     : { },
          f_pourcentage_acquis : { },
          f_conversion_sur_20  : { },
          f_tri_objet          : { required:"choix manquant" },
          f_tri_mode           : { required:"choix manquant" },
          f_groupe             : { required:"groupe manquant" },
          'f_eleve[]'          : { required:"élève(s) manquant(s)" },
          f_periode            : { required:"période manquante" },
          f_date_debut         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_fin           : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_retroactif         : { required:"choix manquant" },
          f_matiere            : { required:"matière manquante" },
          f_restriction        : { },
          f_coef               : { },
          f_socle              : { },
          f_lien               : { },
          f_domaine            : { },
          f_theme              : { },
          f_orientation        : { required:"orientation manquante" },
          f_couleur            : { required:"couleur manquante" },
          f_legende            : { required:"légende manquante" },
          f_marge_min          : { required:"marge mini manquante" },
          f_pages_nb           : { required:"choix manquant" },
          f_cases_nb           : { required:"nombre manquant" },
          f_cases_larg         : { required:"largeur manquante" },
          f_highlight_id       : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("id")=='f_matiere') { element.next().after(error); }
          else if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
          else if(element.attr("type")=="radio") {element.parent().next().next().after(error);}
          else if(element.attr("type")=="checkbox") {
            if(element.parent().parent().hasClass('select_multiple')) {element.parent().parent().next().after(error);}
            else {element.parent().next().next().after(error);}
          }
        }
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
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
        // récupération du nom de la matière & du groupe
        $('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
        $('#f_groupe_nom').val( $("#f_groupe option:selected").text() );
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr("class").html("&nbsp;");
      if( ($('#f_type_individuel').is(':checked')) && (!$('#f_etat_acquisition').is(':checked')) && ($('#f_cases_nb option:selected').val()==0) )
      {
        $('#ajax_msg').removeAttr("class").addClass("erreur").html("Choisir au moins une indication à faire figurer sur le relevé individuel !");
        return false;
      }
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('#bilan').html('');
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#bouton_valider').prop('disabled',false);
      var message = (jqXHR.status!=500) ? 'Échec de la connexion !' : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      $('#ajax_msg').removeAttr("class").addClass("alerte").html(message);
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      $('#bouton_valider').prop('disabled',false);
      if(responseHTML.substring(0,6)=='<hr />')
      {
        $('#ajax_msg').removeAttr("class").addClass("valide").html("Résultat ci-dessous.");
        $('#bilan').html(responseHTML);
      }
      else if(responseHTML.substring(0,4)=='<h2>')
      {
        $('#ajax_msg').removeAttr("class").html('');
        // Mis dans le div bilan et pas balancé directement dans le fancybox sinon la mise en forme des liens nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
        // Malgré tout, pour Chrome par exemple, la largeur est mal clculée et provoque des retours à la ligne, d'où le minWidth ajouté.
        $('#bilan').html('<div class="noprint">Afin de préserver l\'environnement, n\'imprimer qu\'en cas de nécessité !</div>'+responseHTML);
        $.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':550 } );
      }
      else
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Forcer le report de notes vers un bulletin SACoche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bilan').on
    (
      'click',
      '#bouton_report',
      function()
      {
        $('#form_report_bulletin button, #form_report_bulletin select').prop('disabled',true);
        $('#ajax_msg_report').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=reporter_notes'+'&f_periode_eleves='+$('#f_periode_eleves').val()+'&f_eleves_moyennes='+$('#f_eleves_moyennes').val()+'&f_rubrique='+$('#f_rubrique').val(),
            // data : $('#form_report_bulletin').serialize(), le select f_rubrique n'est curieusement pas envoyé...
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_report').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              $('#form_report_bulletin button, #form_report_bulletin select').prop('disabled',false);
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#form_report_bulletin button, #form_report_bulletin select').prop('disabled',false);
              if(responseHTML.substring(0,4)!='Note')
              {
                $('#ajax_msg_report').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_report').removeAttr("class").addClass("valide").html(responseHTML);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Afficher un relevé au chargement
if(auto_voir_releve)
{
  // Parent avec plusieurs enfants : d'abord charger la liste des enfants de la classe
  if(auto_select_eleve_id)
  {
    groupe_id = $("#f_groupe").val();
    groupe_type = 'Classes';
    maj_eleve(groupe_id,groupe_type);
  }
  // Parent avec un seul enfant ou élève : on peut lancer le formulaire de suite
  else
  {
    $('#bouton_valider').click();
    auto_voir_releve = false;
  }
}

  }
);
