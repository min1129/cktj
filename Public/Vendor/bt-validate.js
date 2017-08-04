var validationDefaults =  [
        {name: 're', validate: function(value) {return ($.trim(value) == '');}, defaultMsg: '请输入内容。'},
        {name: 'number', validate: function(value) {return (!/^[0-9]\d*$/.test(value));}, defaultMsg: '请输入数字。'},
        {name: 'mail', validate: function(value) {return (!/^[a-zA-Z0-9]{1}([\._a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+){1,3}$/.test(value));}, defaultMsg: '请输入邮箱地址。'},
        {name: 'char', validate: function(value) {return (!/^[a-z\_\-A-Z]*$/.test(value));}, defaultMsg: '请输入英文字符。'},
        {name: 'chinese', validate: function(value) {return (!/^[\u4e00-\u9fff]$/.test(value));}, defaultMsg: '请输入汉字。'},
        {name:'equal', validate: function(value,el) {
            return $($(el).data('to')).val() != value
        },
            defaultMsg: '输入不相等'}
    ];

function bindValidateForm(form){
    $(form).find('input,textarea,select').each(function() {
        var el = $(this), valid = (el.attr('btvd-type')==undefined)?null:el.attr('btvd-type');
        var valid1 = (el.attr('btvd-el')==undefined)?null:el.attr('btvd-el');
        if (valid || valid1) {
            el.blur(function(){
                validateField(this);
            }).change(function() {
                validateField(this);
            });
        }
    });
    form.validate = function(){

    }
}

function validateForm(form){
    var wFocus = false;
    var validationError = false;
    $(form).find('input,textarea,select').each(function () {
        var el = $(this), valid = (el.attr('btvd-type')==undefined)?null:el.attr('btvd-type').split(' ');
        var valid1 = (el.attr('btvd-el')==undefined)?null:el.attr('btvd-el');
        if (valid != null && valid.length > 0 || valid1) {
            if (!validateField(this)) {
                if (wFocus == false) {
                    scrollTo(0, el[0].offsetTop - 50);
                    wFocus = true;
                }
                validationError = true;
            }
        }
    });
    wFocus = false;
    return !validationError;
}



function validateField (field) { // 验证字段
    var el = $(field);
    var error = false;
    var errorMsg = '';
    var crule=el.attr('btvd-el');
    var msg = (el.attr('btvd-err')==undefined)?null:el.attr('btvd-err');
    if(crule){
        var re =  new RegExp(crule);
        if(!re.test(el.val()) ) {
            error = true;
            errorMsg =msg;
        }
    } else {
        var valid = (el.attr('btvd-type')==undefined)?null:el.attr('btvd-type');
        var rules = validationDefaults;
        for (var j = 0; j < rules.length; j++) {
            var rule = rules[j];
            if (valid == rule.name) {
                if (rule.validate.call(field, el.val(),el)) {
                    error = true;
                    errorMsg = (msg == null)?rule.defaultMsg:msg;
                    break;
                }
            }
        }
    }
    $(field).parent().children('.control-label').remove();
    if (error) {
        var html = '<label class="control-label" for="'+$(field).attr('id')+'">'+errorMsg+'</label>';
        $(field).parent().append(html);
        $(field).parent().removeClass('has-success').addClass('has-error');
    } else {
        $(field).parent().removeClass('has-error').addClass('has-success');
    }
    return !error;
};