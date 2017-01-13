$(document).ready(function() {
	// 修复validate插件多个相同name不验证问题
	 
	if($.validator){
	    $.validator.prototype.elements = function () {
	        var validator = this,
	        rulesCache = {};
	        // Select all valid inputs inside the form (no submit or reset buttons)
	        return $( this.currentForm )
	        .find( "input, select, textarea, [contenteditable]" )
	        .not( ":submit, :reset, :image, :disabled" )
	        .not( this.settings.ignore )
	        .filter( function() {
	            var name = this.id || this.name || $( this ).attr( "name" ); // For contenteditable
	            if ( !name && validator.settings.debug && window.console ) {
	                console.error( "%o has no name assigned", this );
	            }
	            // Set form expando on contenteditable
	            if ( this.hasAttribute( "contenteditable" ) ) {
	                this.form = $( this ).closest( "form" )[ 0 ];
	            }
	            // Select only the first element for each name, and only those with rules specified
	            if (name in rulesCache || !validator.objectLength( $( this ).rules() ) ) {
	                return false;
	            }
	            rulesCache[ name ] = true;
	            return true;
	        } );
	    }
	   
	}
	var login_form = $('#login-form');
	var detail_form = $('#detail-form');
	var file_form = $('#file-form');
	var label = $('label');
	var add_btn = $('.add-card');
	var button_box = $('.button-box');
	var card_num = 0;
	var file_reset = $('#file-reset');
	var file_submit = $('#file-submit');
	var detail_reset = $('#detail-reset');
   
	// my jia
	var  usertel = $('#usertel');
	
	// 文件card模板
	var cardTpl = [
	    '<div class="file-card">',
	    	'<span class="card-close">×</span>',
	    	'<span>Building</span><input type="text" id="build-num#{b-num}" name="build-num">',
	    	'<span>Floor</span><input type="text" id="floor-num#{f-num}" name="floor-num">',
	    	'<span>Upload File</span>',
	    	'<div class="file-wrap">Choose File<input type="file" class="file-up" name="file-up"/></div>',
	    	'<span class="file-info" title=""></span>',
	    '</div>'
	].join('');

	bindEvents();
	login_form.validate({
		rules: {
			name: {
				required: true
			},
			pwd: {
				required: true,
				minlength: 6,
				maxlength: 10
			}
		}
	});

	var detail_valid = detail_form.validate({
		rules: {
			'projectname': {
				required: true
			},
			'dbip': {
				required: true
			},
			'dbuser': {
				required: true
			},
			'dbpwd': {
				required: true
			}
		}
	});

	var file_valid = file_form.validate({
		rules: {
			'build-num': {
				required: true,
				number: true
			},
			'floor-num': {
				required: true,
				number: true
			}
		}
	});

	// 清空输入框
	function clearInput() {
		var for_input = $(this).attr('for');
		var input_id = '#' + for_input;
		$(input_id).val('');
	}

	// 添加card
	function addCard() {
		var html = '';
		card_num++;
		html += formatString(cardTpl, {
			'b-num': card_num,
			'f-num': card_num
		});
		button_box.before(html);
		console.log(html);
	}

	// 删除card
	function closeCard() {
		var cur_card = $(this).parent();
		cur_card.remove();
	}

	// 显示文件名称
	function showFileName() {
		var file_name = $(this).val();
		console.log(file_name);
		var sub_name = getFileName(file_name);
		var file_info = $(this).parent().siblings('.file-info');
		file_info.html(sub_name);
		file_info.attr('title', sub_name).removeClass('file-warn');
	}
  //
	function showMask(){

		$("#mask").css("height",$(document).height());
		$("#mask").css("width",$(document).width());
		$("#mask").show();
	}
	
	function hideMask(){
		$("#mask").hide();
	}
	//
	// 提交file表单
	function fileSubmit() {
		var flag = true;
		var file_all = $('.file-info');
		$.each(file_all, function (idx, val) {
			var info = $(val).html();
			if (!info || info === 'Please choose a file') {
				$(val).html('Please choose a file');
				$(val).addClass('file-warn');
				flag = false;
			}
		});
		showMask()
		if (flag === false) {
			return false;
		}
	}

	// 重置file表单
	function fileReset() {
		file_valid.resetForm();
		var file_all = $('.file-info');
		file_all.html('');
	}

	// 重置detail表单
	function detailReset() {
		detail_valid.resetForm();
	}

	//获取文件名称  
	function getFileName(path) {  
	    var pos1 = path.lastIndexOf('/');  
	    var pos2 = path.lastIndexOf('\\');  
	    var pos = Math.max(pos1, pos2);  
	    if (pos < 0) {  
	        return path;  
	    }  
	    else {  
	        return path.substring(pos + 1);  
	    }  
	}  

	// 绑定事件
	function bindEvents() {
		label.on('click', clearInput);
		add_btn.on('click', addCard);
		file_form.on('click', '.card-close', closeCard);
		file_form.on('change', '.file-up', showFileName);
		file_reset.on('click', fileReset);
		file_submit.on('click', fileSubmit);
		detail_reset.on('click', detailReset);
		usertel.on('click',clearerror);
		
	}

	function clearerror(){

		$('.login-error').empty();

	}
	// 模板数据接入
	function formatString(source, opts) {
		var source = String(source);
	    var data = Array.prototype.slice.call(arguments, 1);
	    var toString = Object.prototype.toString;

	    if (data.length) {
	        data = data.length === 1 ?
	            /* ie 下 Object.prototype.toString.call(null) == '[object Object]' */
	            (opts !== null && (/\[object Array\]|\[object Object\]/.test(toString.call(opts))) ? opts : data)
	            : data;
	        return source.replace(/#\{(.+?)\}/g, function (match, key) {
	            var replacer = data[key];
	            // chrome 下 typeof /a/ == 'function'
	            if ('[object Function]' === toString.call(replacer)) {
	                replacer = replacer(key);
	            }
	            return ('undefined' === typeof replacer ? '' : replacer);
	        });
	    }
	    return source;
	}
});