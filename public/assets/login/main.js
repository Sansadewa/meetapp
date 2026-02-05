
(function ($) {
    "use strict";

    let loader = `            
                <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
                </div>
        `,
        btn_login = `<div class="login100-form-bgbtn"></div>
                    <button class="login100-form-btn">
                        Login
                    </button>`,
        token = document.head.querySelector('meta[name="_token"]').content,
        base_url = document.head.querySelector('meta[name="base_url"]').content;

    /*==================================================================
    [ Focus input ]*/
    $('.input100').each(function(){
        $(this).on('blur', function(){
            if($(this).val().trim() != "") {
                $(this).addClass('has-val');
            }
            else {
                $(this).removeClass('has-val');
            }
        })    
    })
  
  
    /*==================================================================
    [ Validate ]*/
    var input = $('.validate-input .input100');

    $('.validate-form').on('submit',function(e){
        e.preventDefault();
        var check = true;

        for(var i=0; i<input.length; i++) {
            if(validate(input[i]) == false){
                showValidate(input[i]);
                check=false;
            }
        }

        if(check)
        {
            $('.wrap-login100-form-btn').html(loader);
            $.ajax({
                url: base_url+'/login',
                dataType: 'json',
                type: 'POST',
                data: { _token: token, data: {username: $("#username").val(), password: $("#password").val()} },
                success: function(data) {
                    $('.wrap-login100-form-btn').html(btn_login)
                    Swal.fire({
                        icon: `${data.status}`,
                        title: `${data.status == 'success' ? 'Sukses!!': 'Waduuuh!'}`,
                        html: `<p style="font-size: smaller">${data.message}</p>`,
                        showConfirmButton: false
                    })
                    location.reload();
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Jaringan!!',
                        html: '<p style="font-size:smaller">Terdapat kesalahan pada halaman ini. Refresh halaman ini. Terimakasih</p>'
                    }).then(function(){
                        location.reload();
                    })
                }
            })        
        }
    });


    $('.validate-form .input100').each(function(){
        $(this).focus(function(){
           hideValidate(this);
        });
    });

    function validate (input) {
        if($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                return false;
            }
        }
        else {
            if($(input).val().trim() == ''){
                return false;
            }
        }
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }
    
    /*==================================================================
    [ Show pass ]*/
    var showPass = 0;
    $('.btn-show-pass').on('click', function(){
        if(showPass == 0) {
            $(this).next('input').attr('type','text');
            $(this).find('i').removeClass('zmdi-eye');
            $(this).find('i').addClass('zmdi-eye-off');
            showPass = 1;
        }
        else {
            $(this).next('input').attr('type','password');
            $(this).find('i').addClass('zmdi-eye');
            $(this).find('i').removeClass('zmdi-eye-off');
            showPass = 0;
        }
        
    });

    $('.txt2').click(function() {
        Swal.fire({
            icon: 'info',
            title: 'Info!',
            html: '<p style="font-size: smaller">Silahkan hubungi admin IPDS</p>'
        })
    })

})(jQuery);