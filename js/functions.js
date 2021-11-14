//  Ajax API request
function postRequest(name){
    if(name){
        var inputName = $("#search_input").val();
        if(inputName == '' || inputName.length < 3){
            alert('Enter name of 3+ characters to be checked');
            return false;
        }
    }

    if(!name){
        // Show the loader-adnimation + scroll to top of the div
        $('#container-loader').show();
        var loaderDiv = document.getElementById("container-loader");
        loaderDiv.scrollIntoView();
    }
    
    $.ajax({
        url: 'find.php',
        method: 'POST',
        data: {
            requestType: name ? inputName : 'database',
        }, success: function(response){
            if(inputName){
                var result = JSON.parse(response);
                $('#input-data').show();
                $('#no-data').hide();
                $('#dataModal #data-name').val(result.name);
                $('#dataModal #data-gender').val(result.gender);
                $('#dataModal #data-probability').val(result.probability);
                $('#dataModal #data-count').val(result.count);
                $('#dataModal').modal('show');
                jQuery('html,body').animate({scrollTop:0},500);
            }
            else if(response == 'LAST_ROW'){
                $('#input-data').hide();
                $('#no-data').show();
                $("#dataModal").modal('show');
            }else{
                $('#tbody_data').append(response);
            }
            $('#container-loader').hide();	// hide the loader
        }, complete: function(message){},
            error: function(error){}
    });
}

// Scroll To Top
function goToTop(){
    jQuery('html,body').animate({scrollTop:0},1000);
}