;!function(){
    table.render({
        elem: '#LicenseList',
        url: urls.getLicenseList,
        cols: [[
            {field:'cname', title:'客户名称', width:"15%"},
            {field:'ename', title:'企业名称', width:"15%"},
            {field:'sid', title:'机器码', width:"25%"},
            {field:'total', title: '机器人总数', width:"7%"},
            {field:'expire_time', title: 'License文件有效期', width:"15%", templet: function (r) {
                return r.effective_time + ' 至 ' + r.expire_time;
            }},
            {field:'create_time', title: '创建时间', width:"15%"},
            {fixed:'right', title:'操作', width:"20%", templet: function (r) {
                return '<a class="layui-btn layui-btn-xs" lay-event="view">查看</a>' +
                    '<a class="layui-btn layui-btn-primary layui-btn-xs'+ (parseInt(r.download) ? '" lay-event="download"' : ' layui-btn-disabled"') +'>下载</a>' +
                    '<a class="layui-btn layui-btn-xs layui-btn-normal'+ (parseInt(r.extend) ? '" lay-event="extend"' : ' layui-btn-disabled"') +'>扩容</a>' +
                    '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';
            }}
        ]],
        page: {
            limits: [20, 50, 100]
        },
        parseData: function(res) {
            if (parseInt(res.status)) {
                layer.msg(res.info, {icon: 5});
                if (parseInt(res.status) === 200000) {
                    setTimeout(function () {
                        redirectToLoginIndex();
                    }, 2000);
                }
            }
            return {
                "code": res.status,
                "msg": res.info,
                "count": res.data.total,
                "data": res.data.item
            };
        },
        id: 'licenseList',
        theme: "#1E9FFF"
    });

    table.on('tool(license-list)', function(obj) {
        var data = obj.data;
        if (obj.event === 'download') {
            window.location.href = urls.download + "?license_id=" + data.id;
        } else if (obj.event === 'extend') {
            getLicenseInfo(data.id, function (license) {
                showExtendLicense(license);
            });
        } else if (obj.event === 'delete') {
            deleteLicenseInfo(data.id);
        } else if (obj.event === 'view') {
            getLicenseInfo(data.id, function (license) {
                showViewLicenseDetail(license);
            });
        }
    });

    var active = {
        createLicense: function() {
            showCreateLicense();
        },
        reload: function () {
            table.reload('licenseList', {
                page: {
                    curr: 1
                },
                where: {
                    cname: $("#scname").val(),
                    ename: $("#sename").val()
                }
            }, 'data');
        }
    };

    $('.license-table .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });
}();

function getLicenseInfo(licenseId, callback) {
    var loadIndex = layer.load();
    $.ajax({
        type: "GET",
        url: urls.getLicense,
        data: {
            license_id: licenseId
        },
        success: function(res) {
            layer.close(loadIndex);
            if (parseInt(res.status)) {
                layer.msg(res.info, {icon: 5});
                if (parseInt(res.status) === 200000) {
                    setTimeout(function () {
                        redirectToLoginIndex();
                    }, 2000);
                }
            } else {
                callback(res.data);
            }
        }
    });
}

function showCreateLicense() {
    layer.open({
        type: 1,
        title: "新增授权",
        area: ["800px", "500px"],
        content: `
                <form class="layui-form" action="" lay-filter="create-license" style="padding-top: 20px; padding-right: 20px;">
                    <div class="layui-form-item">
                        <label class="layui-form-label">客户名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="cname" value="" placeholder="请输入客户名称" autocomplete="off" class="layui-input" lay-verify="required">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">企业ID</label>
                        <div class="layui-input-inline">
                            <input type="number" name="eid" id="eid" value="" placeholder="请输入企业ID" autocomplete="off" class="layui-input" lay-verify="required">
                        </div>
                        <div class="layui-form-mid" style="padding: 4px 0 !important;">
                            <button type="button" class="layui-btn layui-btn-normal layui-btn-sm" id="importEnterpriseInfo">导入企业信息</button>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">企业名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="ename" id="ename" value="" placeholder="请输入企业名称" autocomplete="off" class="layui-input" lay-verify="required">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">机器码</label>
                        <div class="layui-input-block">
                            <input type="text" name="sid" id="sid" value="" placeholder="请输入机器码" autocomplete="off" class="layui-input" lay-verify="required">
                        </div>
                    </div>
                    <div class="layui-form-item layui-form-text">
                        <label class="layui-form-label">机器人配置</label>
                        <div class="layui-input-block">
                            <table class="layui-table" lay-filter="license-table">
                                <thead>
                                    <tr><th>数量</th><th>开始时间</th><th>到期时间</th><th></th></tr>
                                </thead>
                                <tbody id="license-robot-config" class="license-robot-config">
                                    <tr>
                                        <td><input type="number" name="number[]" min="1" max="1000" autocomplete="off" class="layui-input robot-number"></td>
                                        <td><input type="text" name="start_date[]" autocomplete="off" id="first-robot-start-date" class="layui-input robot-start-date" readonly="readonly"></td>
                                        <td><input type="text" name="end_date[]" autocomplete="off" id="first-robot-end-date" class="layui-input robot-end-date" readonly="readonly"></td>
                                        <td><i class="layui-icon layui-icon-addition robot-config-plus"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">License文件有效期</label>
                            <div class="layui-input-inline">
                                <div class="layui-form-mid" id="effective_time_content" style="width: 270px;text-align: center;"></div>
                                <input type="hidden" name="effective_time" id="effective_time" value="" placeholder="请选择生效时间" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-form-mid"> 至 </div>
                            <div class="layui-input-inline">
                                <input type="text" name="expire_time" id="expire_time" value="" placeholder="请选择到期时间" autocomplete="off" class="layui-input" readonly="readonly">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">备注</label>
                        <div class="layui-input-block">
                            <textarea name="remark" class="layui-textarea"></textarea>
                        </div>
                    </div>
                </form>
            `,
        success: function (layero, index) {
            laydate.render({
                elem: '#first-robot-start-date',
                type: 'date',
                min: currentDate,
                theme: '#1E9FFF',
                done: function(currentDate) {
                    $('.robot-start-date-content').text(currentDate);
                    $('.robot-start-date').val(currentDate);
                    $('#effective_time_content').text(currentDate);
                    $('#effective_time').val(currentDate);
                }
            });

            laydate.render({
                elem: '#first-robot-end-date',
                type: 'date',
                min: currentDate,
                theme: '#1E9FFF'
            });

            laydate.render({
                elem: '#expire_time',
                type: 'date',
                min: currentDate,
                theme: '#1E9FFF'
            });

            upload.render({
                elem: '#importEnterpriseInfo',
                url: urls.upload,
                accept: 'file',
                field: 'eFile',
                exts: 'info',
                acceptMime: ".info",
                done: function(res) {
                    layer.closeAll('loading');
                    if (parseInt(res.status)) {
                        layer.msg(res.info, {icon: 5});
                        if (parseInt(res.status) === 200000) {
                            setTimeout(function () {
                                redirectToLoginIndex();
                            }, 2000);
                        }
                    } else {
                        layer.msg('上传成功', {icon: 1});
                        $("#eid").val(res.data.eid);
                        $("#ename").val(res.data.ename);
                        $("#sid").val(res.data.sid);
                    }
                },
                before: function(obj) {
                    layer.load();
                },
                error: function(index, upload) {
                    layer.closeAll('loading');
                }
            });

            $('.license-robot-config').on('click', '.robot-config-plus', function(){
                var startDateId = 'start_date_' + randomString(16);
                var endDateId = 'end_date_' + randomString(16);
                var firstRobotStartDate = $('#license-robot-config').find('.robot-start-date').first().val();
                var dateHtml = '<tr>';
                dateHtml += '<td><input type="number" name="number[]" min="1" max="1000" autocomplete="off" class="layui-input robot-number"></td>';
                dateHtml += '<td><div class="layui-form-mid robot-start-date-content">' + firstRobotStartDate + '</div><input type="hidden" name="start_date[]" value="' + firstRobotStartDate + '" id="'+ startDateId +'" autocomplete="off" class="layui-input robot-start-date"></td>';
                dateHtml += '<td><input type="text" name="end_date[]" id="'+ endDateId +'" autocomplete="off" class="layui-input robot-end-date" readonly="readonly"></td>';
                dateHtml += '<td><i class="layui-icon layui-icon-subtraction robot-config-reduce"></i></td>';
                dateHtml += '</tr>';
                $('#license-robot-config').append(dateHtml);

                laydate.render({
                    elem: '#' + endDateId,
                    type: 'date',
                    min: currentDate,
                    theme: '#1E9FFF'
                });
                form.render();
            });

            $('.license-robot-config').on('click', '.robot-config-reduce', function(){
                $(this).parents('tr').remove();
                form.render();
            });
        },
        btn: ["保存"],
        yes: function (index, layero) {
            var data = form.val("create-license");

            var cname = $.trim(data.cname);
            if (cname === '') {
                layer.msg('请输入客户名称', {icon: 5});
                return false;
            }

            var eid = data.eid;
            if (!eid) {
                layer.msg('请输入企业ID', {icon: 5});
                return false;
            }

            if (eid <= 0) {
                layer.msg('企业ID必须为大于0的整数', {icon: 5});
                return false;
            }

            var ename = $.trim(data.ename);
            if (ename === '') {
                layer.msg('请输入项目名称', {icon: 5});
                return false;
            }

            var sid = $.trim(data.sid);
            if (sid === '') {
                layer.msg('请输入机器码', {icon: 5});
                return false;
            }

            var number = [];
            $('.robot-number').each(function () {
                var val = $(this).val();
                if (!val) {
                    number = [];
                } else {
                    number.push(val);
                }
            });
            if (number.length === 0) {
                layer.msg('请输入机器人个数', {icon: 5});
                return false;
            }

            var start_date = [];
            $('.robot-start-date').each(function () {
                var val = $(this).val();
                if (val === "") {
                    start_date = [];
                } else {
                    start_date.push(val);
                }
            });
            if (start_date.length === 0) {
                layer.msg('请选择机器人开始时间', {icon: 5});
                return false;
            }

            var end_date = [];
            $('.robot-end-date').each(function () {
                var val = $(this).val();
                if (val === "") {
                    end_date = [];
                } else {
                    end_date.push(val);
                }
            });
            if (end_date.length === 0) {
                layer.msg('请选择机器人到期时间', {icon: 5});
                return false;
            }

            var effective_time = data.effective_time;
            if (effective_time === '') {
                layer.msg('请选择License文件生效时间', {icon: 5});
                return false;
            }

            var expire_time = data.expire_time;
            if (expire_time === '') {
                layer.msg('请选择License文件到期时间', {icon: 5});
                return false;
            }

            var loadIndex = layer.load();
            $.ajax({
                type: "POST",
                url: urls.createLicense,
                data: {
                    cname: cname,
                    eid: eid,
                    ename: ename,
                    sid: sid,
                    number: number,
                    start_date: start_date,
                    end_date: end_date,
                    effective_time: effective_time,
                    expire_time: expire_time,
                    remark: data.remark
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
                        table.reload('licenseList', {
                            page: {
                                curr: 1
                            }
                        }, 'data');
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

function showExtendLicense(license) {
    var robot_detail = ``;
    if (license) {
        effective_time = license.effective_time;
        expire_time = license.expire_time;
        remark = license.remark;
        var detail = license.detail;
        for (key in detail) {
            robot_detail += `<tr>
                    <td>`+ detail[key]['num'] +`</td>
                    <td>`+ detail[key]['start_time'] + `</td>
                    <td>`+ detail[key]['end_time'] + `</td>
                    <td></td>
                </tr>`;
        }
    }

    layer.open({
        type: 1,
        title: "扩容",
        area: ["800px", "500px"],
        content: `
            <form class="layui-form" action="" lay-filter="extend-license" style="padding-top: 20px; padding-right: 20px;">
                <div class="layui-form-item">
                    <label class="layui-form-label">客户名称</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid">` + license.cname + `</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">企业ID</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid">` + license.eid + `</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">企业名称</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid">` + license.ename + `</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">机器码</label>
                    <div class="layui-input-block">
                       <div class="layui-form-mid" style="word-break: break-word;">` + license.sid + `</div>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">机器人配置</label>
                    <div class="layui-input-block">
                        <table class="layui-table" lay-filter="license-table">
                            <thead>
                                <tr><th>数量</th><th>开始时间</th><th>到期时间</th><th></th></tr>
                            </thead>
                            <tbody id="license-robot-config" class="license-robot-config">` + robot_detail + `
                                <tr>
                                    <td><input type="number" name="number[]" min="1" max="1000" autocomplete="off" class="layui-input robot-number"></td>
                                    <td><input type="text" name="start_date[]" autocomplete="off" id="first-robot-start-date" class="layui-input robot-start-date" readonly="readonly"></td>
                                    <td><input type="text" name="end_date[]" autocomplete="off" id="first-robot-end-date" class="layui-input robot-end-date" readonly="readonly"></td>
                                    <td><i class="layui-icon layui-icon-addition robot-config-plus"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">License文件有效期</label>
                        <div class="layui-input-inline">
                            <div class="layui-form-mid" id="effective_time_content" style="width: 270px;text-align: center;"></div>
                            <input type="hidden" name="effective_time" id="effective_time" value="" placeholder="请选择生效时间" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-form-mid"> 至 </div>
                        <div class="layui-input-inline">
                            <input type="text" name="expire_time" id="expire_time" value="" placeholder="请选择到期时间" autocomplete="off" class="layui-input" readonly="readonly">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <textarea name="remark" class="layui-textarea"></textarea>
                    </div>
                </div>
            </form>
        `,
        success: function (layero, index) {
            laydate.render({
                elem: '#first-robot-start-date',
                type: 'date',
                min: currentDate,
                theme: '#1E9FFF',
                done: function(currentDate) {
                    $('.robot-start-date-content').text(currentDate);
                    $('.robot-start-date').val(currentDate);
                    $('#effective_time_content').text(currentDate);
                    $('#effective_time').val(currentDate);
                }
            });

            laydate.render({
                elem: '#first-robot-end-date',
                type: 'date',
                min: currentDate,
                theme: '#1E9FFF'
            });

            laydate.render({
                elem: '#expire_time',
                type: 'date',
                min: currentDate,
                theme: '#1E9FFF'
            });

            $('.license-robot-config').on('click', '.robot-config-plus', function() {
                var startDateId = 'start_date_' + randomString(16);
                var endDateId = 'end_date_' + randomString(16);
                var firstRobotStartDate = $('#license-robot-config').find('.robot-start-date').first().val();
                var dateHtml = '<tr>';
                dateHtml += '<td><input type="number" name="number[]" min="1" autocomplete="off" class="layui-input robot-number"></td>';
                dateHtml += '<td><div class="layui-form-mid robot-start-date-content">' + firstRobotStartDate + '</div><input type="hidden" name="start_date[]" id="'+ startDateId +'" value="' + firstRobotStartDate + '" autocomplete="off" class="layui-input robot-start-date"></td>';
                dateHtml += '<td><input type="text" name="end_date[]" id="'+ endDateId +'" autocomplete="off" class="layui-input robot-end-date" readonly="readonly"></td>';
                dateHtml += '<td><i class="layui-icon layui-icon-subtraction robot-config-reduce"></i></td>';
                dateHtml += '</tr>';

                $('#license-robot-config').append(dateHtml);
                form.render();

                laydate.render({
                    elem: '#' + endDateId,
                    type: 'date',
                    min: currentDate,
                    theme: '#1E9FFF'
                });
            });

            $('.license-robot-config').on('click', '.robot-config-reduce', function(){
                $(this).parents('tr').remove();
                form.render();
            });
        },
        btn: ["保存"],
        yes: function (index, layero) {
            var data = form.val("extend-license");
            var number = [];
            $('.robot-number').each(function () {
                var val = $(this).val();
                if (!val) {
                    number = [];
                } else {
                    number.push(val);
                }
            });
            if (number.length === 0) {
                layer.msg('请输入机器人个数', {icon: 5});
                return false;
            }

            var start_date = [];
            $('.robot-start-date').each(function () {
                var val = $(this).val();
                if (val === "") {
                    start_date = [];
                } else {
                    start_date.push(val);
                }
            });
            if (start_date.length === 0) {
                layer.msg('请选择机器人开始时间', {icon: 5});
                return false;
            }

            var end_date = [];
            $('.robot-end-date').each(function () {
                var val = $(this).val();
                if (val === "") {
                    end_date = [];
                } else {
                    end_date.push(val);
                }
            });
            if (end_date.length === 0) {
                layer.msg('请选择机器人到期时间', {icon: 5});
                return false;
            }

            var effective_time = data.effective_time;
            if (effective_time === '') {
                layer.msg('请选择License文件生效时间', {icon: 5});
                return false;
            }

            var expire_time = data.expire_time;
            if (expire_time === '') {
                layer.msg('请选择License文件到期时间', {icon: 5});
                return false;
            }

            var loadIndex = layer.load();
            $.ajax({
                type: "POST",
                url: urls.extendLicense,
                data: {
                    license_id: license.id,
                    number: number,
                    start_date: start_date,
                    end_date: end_date,
                    effective_time: effective_time,
                    expire_time: expire_time,
                    remark: data.remark
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
                        table.reload('licenseList', {
                            page: {
                                curr: 1
                            }
                        }, 'data');
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

function deleteLicenseInfo(licenseId) {
    layer.confirm('您确定要删除此条授权信息吗?', {
        icon: 3,
        title: '提示-删除授权',
        offset: '100px'
    }, function(index) {
        var loadIndex = layer.load();
        $.ajax({
            type: "POST",
            url: urls.deleteLicense,
            data: {
                license_id: licenseId
            },
            success: function(res) {
                layer.close(loadIndex);
                if (parseInt(res.status)) {
                    layer.msg(res.info, {icon: 5});
                    if (parseInt(res.status) === 200000) {
                        setTimeout(function () {
                            redirectToLoginIndex();
                        }, 2000);
                    }
                } else {
                    table.reload('licenseList', {
                        page: {
                            curr: 1
                        }
                    }, 'data');
                }
            },
            error: function (xhr, error, obj) {
                layer.close(loadIndex);
                layer.msg("Request Error", {icon: 5});
            }
        });
        layer.close(index);
    });
}

function showViewLicenseDetail(license) {
    var content = generateHtml(license);
    layer.open({
        type: 1,
        title: "授权详情",
        area: ["800px", "500px"],
        content: content,
        btn: ["关闭"]
    });
}

function generateHtml(license) {
    var cname = eid = ename = sid = effective_time = expire_time = remark = robot_detail = '';
    if (license) {
        cname = license.cname;
        eid = license.eid;
        ename = license.ename;
        sid = license.sid;
        effective_time = license.effective_time;
        expire_time = license.expire_time;
        remark = license.remark;
        robot_detail = '';
        var detail = license.detail;
        for (key in detail) {
            robot_detail += '<tr>';
            robot_detail += '<td>' + detail[key]['num'] + '</td>';
            robot_detail += '<td>' + detail[key]['start_time'] + '</td>';
            robot_detail += '<td>' + detail[key]['end_time'] + '</td>';
            robot_detail += '</tr>';
        }
    }

    var html = '<form class="layui-form" action="" style="padding-top: 20px; padding-right: 20px;">' +
        '<div class="layui-form-item">' +
        '   <label class="layui-form-label">客户名称</label>' +
        '   <div class="layui-input-block">' +
        '       <div class="layui-form-mid">' + cname + '</div>' +
        '   </div>' +
        '</div>' +
        '<div class="layui-form-item">' +
        '   <label class="layui-form-label">企业ID</label>' +
        '   <div class="layui-input-block">' +
        '       <div class="layui-form-mid">' + eid + '</div>' +
        '   </div>' +
        '</div>' +
        '<div class="layui-form-item">' +
        '   <label class="layui-form-label">企业名称</label>' +
        '   <div class="layui-input-block">' +
        '       <div class="layui-form-mid">' + ename + '</div>' +
        '   </div>' +
        '</div>' +
        '<div class="layui-form-item">' +
        '   <label class="layui-form-label">机器码</label>' +
        '   <div class="layui-input-block">' +
        '       <div class="layui-form-mid" style="word-break: break-word;">' + sid + '</div>' +
        '   </div>' +
        '</div>' +
        '<div class="layui-form-item layui-form-text">' +
        '   <label class="layui-form-label">机器人配置</label>' +
        '   <div class="layui-input-block">' +
        '       <table class="layui-table" lay-filter="license-table">' +
        '           <thead>' +
        '               <tr><th>数量</th><th>开始时间</th><th>到期时间</th></tr>' +
        '           </thead>' +
        '           <tbody>' + robot_detail + '</tbody>' +
        '       </table>' +
        '   </div>' +
        '</div>' +
        '<div class="layui-form-item">' +
        '   <div class="layui-inline">' +
        '       <label class="layui-form-label">License文件有效期</label>' +
        '       <div class="layui-input-inline">' +
        '           <div class="layui-form-mid">' + effective_time + ' 至 ' + expire_time + '</div>' +
        '       </div>' +
        '   </div>' +
        '</div>' +
        '<div class="layui-form-item">' +
        '   <label class="layui-form-label">备注</label>' +
        '   <div class="layui-input-block">' +
        '       <div class="layui-form-mid" style="word-break: break-word;">' + remark + '</div>' +
        '   </div>' +
        '</div>' +
        '</form>';
    return html;
}