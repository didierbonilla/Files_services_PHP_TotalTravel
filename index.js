$(".ui.dropdown").dropdown();
//getReportWithAjax();

function ajaxRequest(url, data = {}, method = "GET") {

    var dataResponse = null;

    $.ajax({
        url: url,
        dataType : 'text',
        contentType : 'application/octet-stream',
        type: method,
        async: false,
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

function getReportWithAjax(){

    var query = { 
        parameters: {
            id: 2,
            ciudadID: 2,
            iD_Direc: 21
        },
        token: "token_id",
        user_name: "user_name_example"
    };

    const response = ajaxRequest(
        url = "PDFs/Hotels_report_pdf.php", 
        data = { parameters: JSON.stringify(query) }, 
        method = "POST"
    );

    var enc = btoa(unescape(encodeURIComponent(response)));
    console.log(enc);
    $('#ifrReport').attr('src','data:application/pdf;base64,'+ enc);

    //console.log(response);

    //$("#ifrReport").prop("src","PDFs/Hotels_report_pdf.php");

}
