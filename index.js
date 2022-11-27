
//------------------------------- DATA ----------------------------------
const REPORTS_FUNCTIONS = {

    getReport_hotels : function (parameter = false){

        if(!parameter){
            $('#ifrReport').attr('src',"PDFs/Hotels_report_pdf.php");
        }
        else{
    
            var query = { 
                parameters: {},
                user_name: "user_name_example"
            };
        
            if($("#cbbCities .item.selected").length > 0){
                var id_ciudad = $("#cbbCities .item.selected").attr("data-value");
                query.parameters.ciudadID = parseInt(id_ciudad);
            }
        
            $('#frmHotelsReports #ifrReport').attr('src',`PDFs/Hotels_report_pdf.php?query=${JSON.stringify(query)}`);
        }
    
    },

    getReport_users : function(url = null){

    }
}

//------------------------------- INIZIALIZE ----------------------------------

getMenu($("#mnReportes .item.active").eq(0));

$("#mnReportes .item").click(function(_this) {
    getMenu(_this.target);
});

$(".ui.dropdown").dropdown();


//------------------------------- FUNCTIONS ----------------------------------
function getMenu(_this){

    // SET MENU ITEM TO INACTIVE
    $("#mnReportes .item").removeClass("active");
    $.each($("#mnReportes .item"),function(i,item){
        var itemToHide = $(item).attr("data-target");
        $(itemToHide).hide();
    });

    // GET REPORT
    var getReport_callback = $(_this).attr("data-report");
    REPORTS_FUNCTIONS[getReport_callback]();
    //console.log(getReport_callback);
    
    // SET MENU ITEM TO ACTIVE
    $(_this).addClass("active");
    var itemToShow = $(_this).attr("data-target");
    $(itemToShow).show();
}

function ajaxRequest(url, data = {}, method = "GET") {

    var dataResponse = null;

    $.ajax({
        url: url,
        dataType : 'text',
        contentType : 'application/pdf',
        type: method,
        //async: false,
        data: data,
        success: function (data) {
            //location.href= "PDFs/Hotels_report_pdf.php";
            dataResponse = data;
        },
        error: function(data){
            console.log(data);
        }
    });

    return dataResponse;
}

function getReport(){

    $("#createReport").remove();
    query = { 
        parameters: {},
        token: "token_id",
        user_name: "user_name_example"
    };

    if($("#cbbCities .item.selected").length > 0){
        var id_ciudad = $("#cbbCities .item.selected").attr("data-value");
        query.parameters.ciudadID = parseInt(id_ciudad);
    }

    var form = document.createElement('form');
    form.action = "PDFs/Hotels_report_pdf.php";
    form.method = "POST";
    form.id = "createReport";
    var input = document.createElement("input");
    input.name = "parameters";
    input.id = "parameters";
    input.value = JSON.stringify(query);
    form.append(input);

    $("body").append(form);
    $("#createReport").hide();
    $("#createReport").submit();

    //ajaxRequest("PDFs/Hotels_report_pdf.php",query, method = "POST");
    //location.href= "PDFs/Hotels_report_pdf.php";
    //console.log(data);
}

