/*
 * Description of mainjs 
 * Copyright (c) 2013 - 2016 Michail Fragkiskos 
 * 
 * This Framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 * @category   Js 
 * @copyright  Copyright (c) 2013 - 2016 Michail Fragkiskos (http://www.fragkiskos.uk)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    0.1.5, 2014-12-22  
 */ 

    var mainjs = {
        validarray: [{
                'name': 'Uname',
                'condition': /^(?:[a-z0-9.-]+(?:[-_]?[a-z0-9]))/,
                'defaultvalue': ''},
            {'name': 'Upass',
                'condition': /(.+)/,
                'defaultvalue': ''},
            {
                'name': 'email',
                'condition': /^([a-z\d!#jQuery%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#jQuery%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?jQuery/i,
                'defaultvalue': ''},
            {
                'name': 'nameinfo',
                'condition': /(.+)/,
                'defaultvalue': ''}],
        registerValid: false,
        registerpValid: false,

        init: function () {

            jQuery('#login-form').submit(function (event) {
                event.preventDefault();
                mainjs.loginSubmit(this);
                return false;
            });
            jQuery('#contacts-form').submit(function (event) {
                event.preventDefault();
                mainjs.contactform();
                return false;
            });

            jQuery("#Uname").keyup(function () {
                mainjs.ValidateUname();
            }.bind(this));
            jQuery("#Upass").keyup(function () {
                mainjs.ValidatePassw();
            });
           
            jQuery('[id^="letter"]').on('click',function () {
                mainjs.rates(jQuery(this).attr('letter'));
            });

            jQuery('#registerButon').on('click', function () {
                mainjs.ValidatePassw();
                mainjs.Register();
            });

        },
        loginSubmit: function (obj) {
            var url = jQuery(obj).attr('action');
            jQuery.post(url, jQuery(obj).serializeArray(), function (ev) {
                jQuery('#registerButon').css('disabled', true);
                if ((ev.error === 1) && (ev.case === '0')) {
                    window.location.href = 'members';
                } else if ((ev.error === 1) && (ev.case === '1')) {
                     window.location.href = 'admin';
                } else {
                    mainjs.errors();
                }
            }, 'json');
        }
        , contactform: function () {
            if ((jQuery('#Submitname').val() !== '') && (jQuery('#Submitemail').val() !== '')
                    && (jQuery('#Submitsubject').val() !== '')
                    && (jQuery('#Submitmessage').val() !== '')
                    && (parseInt(jQuery('#msgboxok').val()) === 1)) {
                jQuery('#Submitloginform').prop('disabled', true);
                jQuery('#answer').removeClass('error').html('').hide();
                jQuery.post(jQuery('#contacts-form').attr('action'), {data: jQuery('#contacts-form').serialize()}, function (o) {
                    if (parseInt(o.error) === 0) {
                        jQuery('#answer').addClass('success').html(o.msg).show();
                    } else {
                        jQuery('#answer').addClass('warning').html(o.msg).show();
                    }
                    jQuery('#Submitloginform').prop('disabled', false);
                }, 'json').fail(function(){
                 jQuery('#Submitloginform').css('border', 'solid thin #ff0033');
            });
            } else {
                jQuery('#answer').addClass('error').html('Please fill the form2!').show();
            }
            return false;
        }
        , ValidateUname: function () {
            jQuery.post(window.location.pathname + '/selectuser', {'data': jQuery('#Uname').val()}, function (e) {
                if ((parseInt(e) === 0) && (jQuery('#Uname').val().length > 4)) {
                    jQuery('#Uname').css('border', 'solid thin #009966');
                    jQuery('#uinfo')
                            .removeClass("error")
                            .addClass("success")
                            .html(jQuery('.errs').attr('isavaliable'))
                            .show();
                    mainjs.registerValid = false;
                     } else {
                    jQuery('#Uname').css('border', 'solid thin #ff0033');
                    jQuery('#uinfo')
                            .removeClass("success")
                            .addClass("error")
                            .html(jQuery('.errs')
                            .attr('notAvaliable'))
                            .show();
                    mainjs.registerValid = true;
                }
              },'json').done(function(){
                 jQuery('#uinfo').hide();
            }).fail(function(){
                jQuery('#Uname').css('border', 'solid thin #ff0033');
                    jQuery('#uinfo')
                            .removeClass("success")
                            .addClass("error")
                            .html(jQuery('.errs')
                            .attr('notAvaliable')).show();
                    mainjs.registerValid = true;
            });
            
        }
        , ValidatePassw: function () {
            var obj = jQuery('#Upass').val();
             setTimeout(function () {
                 jQuery('#pinfo').hide();
             }, 5000);
            if (obj.match(/[^a-zA-Z0-9-/./_@!#jQuery]/g)) {
                jQuery('#Upass').val(obj.replace(/[^a-zA-Z0-9-/./_@!#jQuery]/g, '')).css('border', 'solid thin #ff0033');
                jQuery('#pinfo').addClass("error").css('height', '42px').html(jQuery('.errs').attr('notPermited')).show();
                mainjs.registerpValid = true;
                return false;
            } else {
                jQuery('#Upass').css('border', 'solid thin #009966');
                jQuery('#pinfo').removeClass("error").hide();
                if (jQuery('#Uname').val() === jQuery('#Upass').val()) {
                    jQuery('#pinfo').addClass("error").html(jQuery('.errs').attr('notPermited')).show();
                    mainjs.registerpValid = true;
                    return false;
                }
                if (mainjs.checkLength(obj, 6, 22)) {
                    mainjs.registerpValid = false;
                }
            }
        }
        , checkLength: function (o, min, max) {
            if (parseInt(o.length) > parseInt(max) || parseInt(o.length) > parseInt(min)) {
                return true;
            }
            return false;
        }
        , Register: function () {           
            if ((mainjs.registerValid === true) || (mainjs.registerpValid === true)) {
                jQuery('#Upass,#Uname').css('position', 'relative').stop()
                        .addClass('not_valid')
                        .animate({left: "-10px"}, 100)
                        .animate({left: "10px"}, 100)
                        .animate({left: "-10px"}, 100)
                        .animate({left: "10px"}, 100)
                        .animate({left: "0px"}, 100);
                return false;
            }
            var flag = false, info=false;
            for (var i = 0; i < mainjs.validarray.length; i++) {
                var tagNameField = document.getElementsByName(mainjs.validarray[i].name)[0].tagName;
                var fieldInput = jQuery("#register-form " + tagNameField + "[name=" + mainjs.validarray[i].name + "]");
                var conditionInput = mainjs.validarray[i].condition.test(fieldInput.val());
                if (mainjs.validarray[i].name === 'email') {
                   info = true;
                } else {
                    info = mainjs.checkLength(fieldInput.val(), 5, 22);
                }

                if (conditionInput === false || fieldInput.val() === mainjs.validarray[i].defaultvalue || info === false) {
                    flag = true;
                    jQuery(fieldInput).css('position', "relative").stop()
                            .addClass('not_valid')
                            .animate({left: "-10px"}, 100)
                            .animate({left: "10px"}, 100)
                            .animate({left: "-10px"}, 100)
                            .animate({left: "10px"}, 100)
                            .animate({left: "0px"}, 100);
                } else {
                    jQuery(fieldInput).stop()
                            .removeClass('not_valid');
                }
            }

            if (jQuery('#boxok').val() === 0) {
                flag = true;
                jQuery("#drag").stop()
                        .addClass('not_valid')
                        .animate({left: "-10px"}, 100)
                        .animate({left: "10px"}, 100)
                        .animate({left: "-10px"}, 100)
                        .animate({left: "10px"}, 100)
                        .animate({left: "0px"}, 100);

            }

            if (flag === false) {
                jQuery("#registerloadingScreen").dialog('open');
                var registerdata = jQuery('#register-form').serialize();
                jQuery('#registerButon').prop('disabled', true);
                var url = jQuery('#register-form').attr('action');                
                jQuery.post(url, {data: registerdata}, function (e) {
                    if (e.error === 'ok') {
                        jQuery("#registerloadingScreen").dialog('close');
                        window.location.href = 'members/members';
                    } else {
                        jQuery("#registerloadingScreen").dialog('close');
                        jQuery('#registerButon').prop('disabled', false);
                          var l = 20;
                        for (var i = 0; i < 5; i++) {
                            jQuery("#register-form").animate({'margin-left': "+=" + (l = -l) + 'px'}, 100);
                        }
                    }

                }, 'json');

                return false;

            }
        }
        , rates: function (val) {
            jQuery.post(window.location.pathname + '/Getrates', {'l': val}, function (data) {
                    jQuery('#priselist').html(data);
            }, 'json');
            return false;
        },
        errors: function () {
            var l = 20;
            for (var i = 0; i < 6; i++) {
                jQuery("#login-form").animate({'margin-left': "+=" + (l = -l) + 'px'}, 100);
            }
            jQuery('#Uname').addClass('errors');
            jQuery('#Upass').addClass('errors');

            setTimeout(function () {
                jQuery('#Uname').removeClass('errors');
                jQuery('#Upass').removeClass('errors');
                jQuery('#Uname').val('');
                jQuery('#Upass').val('');
            }, 3000);
        }
    };