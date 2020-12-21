var element = layui.element,
    form = layui.form,
    $ = layui.$,
    table = layui.table,
    layer = layui.layer,
    laydate = layui.laydate,
    upload = layui.upload;

layer.config({
    offset: '150px',
    maxWidth: 500,
});

;!function () {
    $('#user-password-modify').on('click', function () {
        editPassword();
    });

    $('#user-logout').on('click', function () {
        $.ajax({
            type: "POST",
            url: urls.logout,
            data: {},
            success: function(res) {
                if (parseInt(res.status)) {
                    layer.msg(res.info, {icon: 5, maxWidth: 500});
                } else {
                    window.location.href = urls.loginIndex;
                }
            }
        });
    });
}();

function editPassword() {
    layer.open({
        type: 1,
        title: "修改密码",
        area: ["430px", "300px"],
        content: `
            <form class="layui-form" action="" lay-filter="modify-password" style="padding-top: 20px; padding-right: 20px;">
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 70px;">当前密码</label>
                    <div class="layui-input-block" style="margin-left: 100px;">
                        <input type="password" name="old_password" value="" placeholder="请输入当前密码" lay-reqText="请输入当前密码" autocomplete="off" class="layui-input" lay-verify="required">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 70px;">新密码</label>
                    <div class="layui-input-block" style="margin-left: 100px;">
                        <input type="password" name="new_password" id="new_password" value="" placeholder="请输入新密码" lay-reqText="请输入新密码" autocomplete="off" class="layui-input" lay-verify="required">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 70px;">确认新密码</label>
                    <div class="layui-input-block" style="margin-left: 100px;">
                        <input type="password" name="new_password2" id="new_password2" value="" placeholder="请输入确认新密码" lay-reqText="请输入确认新密码" autocomplete="off" class="layui-input" lay-verify="required">
                    </div>
                </div>
            </form>
        `,
        btn: ["保存"],
        yes: function (index) {
            var data = form.val("modify-password");

            var old_password = $.trim(data.old_password);
            if (old_password.length === 0) {
                layer.msg('请输入当前密码', {icon: 5});
                return false;
            }

            var new_password = $.trim(data.new_password);
            if (new_password.length === 0) {
                layer.msg('请输入新密码', {icon: 5});
                return false;
            }

            var new_password2 = $.trim(data.new_password2);
            if (new_password2.length === 0) {
                layer.msg('请输入确认新密码', {icon: 5});
                return false;
            }

            if (new_password !== new_password2) {
                layer.msg('新密码两次输入不一致', {icon: 5});
                return false;
            }

            var loadIndex = layer.load(3, {offset: 'auto'});
            $.ajax({
                type: "POST",
                url: urls.modifyPassword,
                data: {
                    old_password: old_password,
                    new_password: new_password,
                    new_password2: new_password2
                },
                success: function(res) {
                    layer.close(loadIndex);
                    if (parseInt(res.status)) {
                        layer.msg(res.info, {icon: 5, maxWidth: 500});
                        if (parseInt(res.status) === 200000) {
                            setTimeout(function () {
                                redirectToLoginIndex();
                            }, 2000);
                        }
                    } else {
                        layer.close(index);
                    }
                },
                error: function (xhr, error, obj) {
                    layer.close(loadIndex);
                    layer.msg("Request Error", {icon: 5});
                }
            });
        }
    });
}

function redirectToLoginIndex() {
    window.location.href = urls.loginIndex;
}

function randomString(len) {
    len = len || 32;
    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}