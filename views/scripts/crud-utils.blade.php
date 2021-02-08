<script>
    // utils
    function loading(selector, isLoading, label = "") {
        //selector can be jq selector string or a jq object
        var selectorObj;
        if (typeof selector === 'string' || selector instanceof String) {
            selectorObj = $(selector);
        } else if (selector instanceof jQuery) {
            selectorObj = selector;
        }
        if(isLoading) {
            selectorObj.html("<div class='loader'></div>").attr('disabled', true);
        } else {
            selectorObj.html(label).attr('disabled', false);
        }
    }

    function show_alert(msg, isSuccess = true) {
        if(isSuccess) {
            $(".alert-wrap").html('<div class="alert alert-success">'+ msg+'</div>')
        } else {
            $(".alert-wrap").html('<div class="alert alert-danger">'+ msg+'</div>')
        }
    }

    function remove_alert() {
        $(".alert-wrap").html('');
    }

    function printErrorMsg (msg) {
        var html = '';
        $.each( msg, function( key, value ) {
            html += '<li>'+value+'</li>';
        });
        show_alert(html, false);
    }

    function showSnackbar(msg, isSuccess) {
        var x = document.getElementById("snackbar");
        x.style.backgroundColor = isSuccess ? '#0c0' : '#c00';
        x.innerText = msg;
        x.className = "show";
        setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }

    function setValueOnEditForm(formFields, data) {
        for (const [key, value] of Object.entries(formFields)) {
            if(value.type === 'text' || value.type === 'email' || value.type === 'number') {
                $(`input[name=${key}]`).val(data[key]);
            }
            if(value.type === 'select' || value.type === 'select_db') {
                $(`select[name=${key}]`).val(data[key]);
            }
            if(value.type === 'checkbox') {
                if(data[key]) {
                    $(`input[name=${key}]`).attr("checked", true);
                } else {
                    $(`input[name=${key}]`).attr("checked", false);
                }
            }
            if(value.type === 'radio') {
                $(`input[name=${key}][value=${data[key]}]`).attr("checked", true);
            }

        }
    }

    function resetForm(formFields) {
        for (const [key, value] of Object.entries(formFields)) {
            if(value.type === 'text' || value.type === 'email' || value.type === 'number') {
                $(`input[name=${key}]`).val('');
            }
            if(value.type === 'select' || value.type === 'select_db') {
                $(`select[name=${key}]`).val('--Select One--');
            }
            if(value.type === 'checkbox') {
                $(`input[name=${key}]`).attr("checked", false);
            }
            if(value.type === 'radio') {
                $(`input[name=${key}]`).attr("checked", false);
            }

        }
    }
</script>
