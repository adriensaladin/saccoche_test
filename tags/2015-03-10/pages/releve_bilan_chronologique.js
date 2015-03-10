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

// Variable globale Highcharts
var graphique;
var ChartOptions;

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var matiere_id   = 0;
    var groupe_id    = 0;
    var groupe_type  = $("#f_groupe option:selected").parent().attr('label'); // Il faut indiquer une valeur initiale au moins pour le profil élève
    var eleves_ordre = '';

    $("#f_eleve").hide();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Thème personnalisé pour le graphique : sont complétées ensuite avec les données personnalisées
    // @author Torstein Hønsi
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    Highcharts.theme = {
       colors: ['#0563C7', '#ED642E', '#009B00', '#050112', '#CE63CB', '#FB0A0A', '#24CBE5', '#066C0D', '#8313BD', '#626262', '#D6D900', '#07CE2F', '#0004BD', '#583216', '#BD1363'],
       chart: {
          plotBorderWidth: 1
       },
       xAxis: {
          gridLineWidth: 1,
          lineColor: '#000',
          tickColor: '#000',
          labels: {
             style: {
                color: '#000',
                font: '11px Trebuchet MS, Verdana, sans-serif'
             }
          },
          title: {
             style: {
                color: '#333',
                fontWeight: 'bold',
                fontSize: '12px',
                fontFamily: 'Trebuchet MS, Verdana, sans-serif'

             }
          }
       },
       yAxis: {
          minorTickInterval: 'auto',
          lineColor: '#000',
          lineWidth: 1,
          tickWidth: 1,
          tickColor: '#000',
          labels: {
             style: {
                color: '#000',
                font: '11px Trebuchet MS, Verdana, sans-serif'
             }
          },
          title: {
             style: {
                color: '#333',
                fontWeight: 'bold',
                fontSize: '12px',
                fontFamily: 'Trebuchet MS, Verdana, sans-serif'
             }
          }
       },
       legend: {
          itemStyle: {
             font: '9pt Trebuchet MS, Verdana, sans-serif',
             color: '#139'

          },
          itemHoverStyle: {
             color: '#D41'
          },
          itemHiddenStyle: {
             color: 'gray'
          }
       },
       labels: {
          style: {
             color: '#99b'
          }
       }
    };

    // Apply the theme
    var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Options de base pour le graphique : sont complétées ensuite avec les données personnalisées
    // @see   http://docs.highcharts.com/
    // @see   http://www.highcharts.com/ref
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    ChartOptions = {
      chart: {
        renderTo: 'div_graphique',
        type: 'spline',
        height:450
       },
      title: {
        style: { color: '#333' } ,
        text: null
      },
      legend: {
        layout: 'horizontal',
        align: 'center',
        verticalAlign: 'bottom',
        x: 15
      },
      xAxis: {
        type: 'datetime',
        labels: { enabled: false }
      },
      yAxis: {
        labels: { enabled: false },
        min: 0,
        max: 100,
        title: { style: { color: '#333' } , text: '???' } // MAJ ensuite
      },
      tooltip: {
        formatter: function() {
          return this.series.name + '<br/>' + Highcharts.dateFormat('%d/%m/%Y', this.x) + '<br/><b>' + (this.y) + '</b>';
        }
      },
      series: [] // MAJ ensuite
      ,
      credits: {
        enabled: false
      }
    };

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enlever le message ajax et le résultat précédent au changement d'un select
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select').change
    (
      function()
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
      }
    );

    $('#f_indicateur_MS , #f_indicateur_PA').click
    (
      function()
      {
        if( ($('#f_indicateur_MS').is(':checked')) || ($('#f_indicateur_PA').is(':checked')) )
        {
          $('label[for=f_conversion_sur_20]').show();
        }
        else
        {
          $('label[for=f_conversion_sur_20]').hide();
        }
      }
    );

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
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de groupe
// -> desactiver les périodes prédéfinies en cas de groupe de besoin (prof uniquement)
// -> afficher le formulaire de périodes s'il est masqué
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger les selects f_eleve (pour le professeur et le directeur et les parents de plusieurs enfants) et f_matiere (pour le directeur) en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_matiere(groupe_id,matiere_id,eleves_ordre) // Uniquement pour un directeur
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_matieres',
          data : 'f_groupe='+groupe_id+'&f_matiere='+matiere_id+'&f_multiple=1',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,6)=='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#f_matiere').html(responseHTML);
              maj_eleve(groupe_id,groupe_type,eleves_ordre);
            }
          else
            {
              $('#ajax_maj').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    function maj_eleve(groupe_id,groupe_type,eleves_ordre)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre='+eleves_ordre+'&f_statut=1'+'&f_multiple=0',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(groupe_type=='Classes')
            {
              $("#bloc_ordre").hide();
            }
            else
            {
              $("#bloc_ordre").show();
            }
            if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_maj').removeAttr("class").html("&nbsp;");
              $('#f_eleve').html(responseHTML).show();
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
      function()
      {
        // Pour un directeur, on met à jour f_matiere (on mémorise avant matiere_id) puis f_eleve
        // Pour un professeur ou un parent de plusieurs enfants, on met à jour f_eleve uniquement
        // Pour un élève ou un parent d'un seul enfant cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
        if(PROFIL_TYPE=='directeur')
        {
          matiere_id = $("#f_matiere").val();
          $("#f_matiere").html('');
        }
        $("#f_eleve").html('<option value=""></option>').hide();
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          eleves_ordre = $("#f_eleves_ordre option:selected").val();
          groupe_type  = $("#f_groupe option:selected").parent().attr('label');
          $('#ajax_maj').removeAttr("class").addClass("loader").html("En cours&hellip;");
          if(PROFIL_TYPE=='directeur')
          {
            maj_matiere(groupe_id,matiere_id,eleves_ordre);
          }
          else if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='parent') )
          {
            maj_eleve(groupe_id,groupe_type,eleves_ordre);
          }
        }
        else
        {
          $("#bloc_ordre").hide();
          $('#ajax_maj').removeAttr("class").html("&nbsp;");
        }
      }
    );

    $("#f_groupe").change
    (
      function()
      {
        // Pour un directeur, on met à jour f_matiere (on mémorise avant matiere_id) puis f_eleve
        // Pour un professeur ou un parent de plusieurs enfants, on met à jour f_eleve uniquement
        // Pour un élève ou un parent d'un seul enfant cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
        if(PROFIL_TYPE=='directeur')
        {
          matiere_id = $("#f_matiere").val();
          $("#f_matiere").html('');
        }
        $("#f_eleve").html('<option value=""></option>').hide();
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          groupe_type  = $("#f_groupe option:selected").parent().attr('label');
          eleves_ordre = $("#f_eleves_ordre option:selected").val();
          $('#ajax_maj').removeAttr("class").addClass("loader").html("En cours&hellip;");
          if(PROFIL_TYPE=='directeur')
          {
            maj_matiere(groupe_id,matiere_id,eleves_ordre);
          }
          else if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='parent') )
          {
            maj_eleve(groupe_id,groupe_type,eleves_ordre);
          }
        }
        else
        {
          $('#ajax_maj').removeAttr("class").html("&nbsp;");
        }
      }
    );

    $("#f_eleves_ordre").change
    (
      function()
      {
        groupe_id    = $("#f_groupe option:selected").val();
        groupe_type  = $("#f_groupe option:selected").parent().attr('label');
        eleves_ordre = $("#f_eleves_ordre option:selected").val();
        $("#f_eleve").html('<option value=""></option>').hide();
        $('#ajax_maj').removeAttr("class").addClass("loader").html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type,eleves_ordre);
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
        matiere_id = $("#f_matiere input:checked").val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_matieres_prof',
            data : 'f_matiere='+matiere_id+'&f_action='+modifier_action+'&f_multiple=1',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,6)=='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
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
          f_indicateur         : { required:true },
          f_conversion_sur_20  : { required:false },
          f_groupe             : { required:true },
          f_groupe             : { required:true },
          f_eleve              : { required:true },
          f_eleves_ordre       : { required:true },
          f_periode            : { required:true },
          f_date_debut         : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_date_fin           : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
          f_retroactif         : { required:true },
          'f_matiere[]'        : { required:true },
          f_restriction        : { required:false }
        },
        messages :
        {
          f_indicateur         : { required:"choix manquant" },
          f_conversion_sur_20  : { },
          f_tri_mode           : { required:"choix manquant" },
          f_groupe             : { required:"groupe manquant" },
          f_groupe             : { required:"groupe manquant" },
          f_eleve              : { required:"élève manquant" },
          f_eleves_ordre       : { required:"ordre manquant" },
          f_periode            : { required:"période manquante" },
          f_date_debut         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_fin           : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_retroactif         : { required:"choix manquant" },
          'f_matiere[]'        : { required:"matière(s) manquante(s)" },
          f_restriction        : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
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
        // récupération d'éléments
        $('#f_groupe_type').val( groupe_type );
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      if(bilan_affiche)
      {
        return true;
      }
      else
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
        var readytogo = validation.form();
        if(readytogo)
        {
          $('#form_select button').prop('disabled',true);
          $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        }
        return readytogo;
      }
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      var message = (jqXHR.status!=500) ? 'Échec de la connexion !' : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner une période plus restreinte ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      if(bilan_affiche)
      {
        $('#bilan button , #bilan select').prop('disabled',false);
        $('#div_graphique').html('<label class="alerte">'+message+'</label>');
      }
      else
      {
        $('#form_select button').prop('disabled',false);
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(message);
      }
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      if(bilan_affiche)
      {
        $('#bilan button , #bilan select').prop('disabled',false);
      }
      else
      {
        $('#form_select button').prop('disabled',false);
      }
      if(responseHTML.substring(0,8)=='<SCRIPT>')
      {
        if(bilan_affiche)
        {
          $('#go_selection_eleve option[value='+memo_eleve+']').prop('selected',true);
          masquer_element_navigation_choix_eleve();
          eval( responseHTML.substring(8) );
        }
        else
        {
          if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='directeur') )
          {
            memo_eleve = $("#f_eleve option:selected").val();
            $('#go_selection_eleve').html( $('#f_eleve').html().substring(26) );
            $("#go_selection_eleve option[value="+memo_eleve+"]").prop('selected',true);
            memo_eleve_first = $('#go_selection_eleve option:first').val();
            memo_eleve_last  = $('#go_selection_eleve option:last').val();
            masquer_element_navigation_choix_eleve();
          }
          eval( responseHTML.substring(8) );
          $('#ajax_msg').removeAttr("class").html('');
          var titre = ($('#f_indicateur_MS').is(':checked')) ? "Moyenne des scores :" : "Pourcentage d'items acquis :" ;
          $('#report_titre').html(titre);
          $('#form_select').hide();
          $('#bilan').show();
          bilan_affiche = true;
        }
      }
      else
      {
        if(bilan_affiche)
        {
          $('#div_graphique').html('<label class="alerte">'+responseHTML+'</label>');
        }
        else
        {
          $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
        }
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Navigation d'un élève à un autre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var bilan_affiche    = false;
    var memo_eleve       = 0;
    var memo_eleve_first = 0;
    var memo_eleve_last  = 0;

    function charger_nouvel_eleve(eleve_id)
    {
      $("#f_eleve option[value="+eleve_id+"]").prop('selected',true);
      memo_eleve = eleve_id;
      $('#bilan button , #bilan select').prop('disabled',true);
      $('#div_graphique').html('<label class="loader">En cours&hellip;</label>');
      formulaire.submit();
    }

    function masquer_element_navigation_choix_eleve()
    {
      $('#bilan button').css('visibility','visible');
      if(memo_eleve==memo_eleve_first)
      {
        $('#go_premier_eleve , #go_precedent_eleve').css('visibility','hidden');
      }
      if(memo_eleve==memo_eleve_last)
      {
        $('#go_dernier_eleve , #go_suivant_eleve').css('visibility','hidden');
      }
    }

    $('#go_premier_eleve').click
    (
      function()
      {
        var eleve_id = $('#go_selection_eleve option:first').val();
        charger_nouvel_eleve(eleve_id);
      }
    );

    $('#go_dernier_eleve').click
    (
      function()
      {
        var eleve_id = $('#go_selection_eleve option:last').val();
        charger_nouvel_eleve(eleve_id);
      }
    );

    $('#go_precedent_eleve').click
    (
      function()
      {
        if( $('#go_selection_eleve option:selected').prev().length )
        {
          var eleve_id = $('#go_selection_eleve option:selected').prev().val();
          charger_nouvel_eleve(eleve_id);
        }
      }
    );

    $('#go_suivant_eleve').click
    (
      function()
      {
        if( $('#go_selection_eleve option:selected').next().length )
        {
          var eleve_id = $('#go_selection_eleve option:selected').next().val();
          charger_nouvel_eleve(eleve_id);
        }
      }
    );

    $('#go_selection_eleve').change
    (
      function()
      {
        var eleve_id = $('#go_selection_eleve option:selected').val();
        charger_nouvel_eleve(eleve_id);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer la zone bilan
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fermer_zone_bilan').click
    (
      function()
      {
        $('#bilan').hide();
        $('#form_select').show();
        bilan_affiche = false;
        return false;
      }
    );

  }
);
