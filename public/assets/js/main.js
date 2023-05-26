jQuery("#crud_stores_form_create").submit(function(e){
    e.preventDefault();

    jQuery.ajax({
        url: ajax_var.url,
        type: "POST", // podría ser get, post, put o delete.
        data: jQuery(this).serialize(),
    beforeSend: function() {
        alert("voy a enviar información")
    },
    success: function ($response){
        console.log($response);
    },
    error: function (jqXHR, exception) {
        var msg = '';
        if (jqXHR.status === 0) {
            msg = 'Not connect.n Verify Network.';
        } else if (jqXHR.status == 404) {
            msg = 'Requested page not found. [404]';
        } else if (jqXHR.status == 500) {
            msg = 'Internal Server Error [500].';
        } else if (exception == 'parsererror') {
            msg = 'Requested JSON parse failed.';
        } else if (exception === 'timeout') {
            msg = 'Time out error.';
        } else if (exception === 'abort') {
            msg = 'Ajax request aborted.';
        } else {
            msg = 'Uncaught Error.n' + jqXHR.responseText;
        }
        alert(msg);
    },
});
});