;!function () {
    $('#user-login-captcha').bind('keypress', function (event) {
        var keyCode = event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode);
        if (keyCode === 13) {
            $('#user-login-submit').trigger('click');
            return false;
        }
    });

    form.on('submit(user-login-submit)', function(data) {
        var loadIndex = layer.load(3, {offset: 'auto'});
        $.ajax({
            type: "POST",
            url: urls.login,
            data: {
                username: data.field.username,
                password: data.field.password,
                captcha: data.field.captcha
            },
            success: function(res) {
                layer.close(loadIndex);
                if (parseInt(res.status)) {
                    layer.msg(res.info, {icon: 5, maxWidth: 500});
                } else {
                    window.location.href = urls.home;
                }
            }
        });
        return false;
    });
}();