///**


$(document).ready(function(){
	
		var startTime, endTime, longpress;
		
		var prel =  '<div class="preloader-wrapper small active">';
			prel += '<div class="spinner-layer spinner-blue-only">';
			prel += '	<div class="circle-clipper left">';
	      	prel += '		<div class="circle"></div>';
	      	prel += '			</div><div class="gap-patch">';
	      	prel += '				<div class="circle"></div>';
	      	prel += '			</div><div class="circle-clipper right">';
	      	prel += '			<div class="circle"></div>';
	      	prel += '		</div>';
	      	prel += '	</div>';
	      	prel += '</div>';
	
		$(".button-collapse-materialize").sideNav({
		      menuWidth: 300, // Default is 240
		      edge: 'left', // Choose the horizontal origin
		      closeOnClick: true // Closes side-nav on <a> clicks, useful for Angular/Meteor
		    }
		  );

		$(".collapsible").collapsible();
		$(".dropdown-trigger").dropdown({constrainWidth: false});
		$('.modal').modal();
		
		$('div.fixed-action-btn a.compose').html("<i class='material-icons'>create</i>");
		
		$('.formbuttons').css("text-align","center");
		$('.formbuttons input[value="Login"]').addClass("btn waves-effect waves-light blue darken-1");
		
		$('a.button-addressbook').prepend("<i class='material-icons'>contacts</i>");
		$('a.button-settings').prepend("<i class='material-icons'>settings</i>");
		$('a.button-logout').prepend("<i class='material-icons'>power_settings_new</i>");
		
		$('ul.attachmentslist li').addClass("grey lighten-3 waves-effect");
		
		$('ul.email-actions li.tab a.reply').html("<i class='material-icons grey-text text-darken-3'>reply</i>");
		$('ul.email-actions li.tab a.reply-all').html("<i class='material-icons grey-text text-darken-3'>reply_all</i>");
		$('ul.email-actions li.tab a.forwardlink').html("<i class='material-icons grey-text text-darken-3'>forward</i>");
		$('ul.email-actions li.tab a.download').html("<i class='material-icons grey-text text-darken-3'>file_download</i>");
		
		$('#nav-mobile li a.button.attach').html('<i class="material-icons grey-text text-lighten-3">attachment</i>');
		$('#nav-mobile li a.button.send').html('<i class="material-icons grey-text text-lighten-3">send</i>');
		
		$('.btnCollapse').click(function() {
			$('.customcollapse').toggle("slide",{direction:"up"});
			$('.btnCollapse').toggleClass("active");
			$('span#time-header').toggle();
		})
		
		$('a[href="#delete"]').html('<i class="material-icons">clear</i>');
		
		$('#compose-add-headers').click(function() {
			$('span#compose-headers').show();
			$(this).parent().parent().find(".input-field.col.s9").toggleClass("s9 s11");
			$("ul.compose form li#liSubject").addClass("header-shown");
			$(this).parent().remove();
		});
		
        $('.row.composebody textarea').attr("placeholder","Compose email");
        
        $('.row.composebody textarea').bind('input propertychange', function() {
        	$('div.dummybox').css("height",$(this).height());
        });
        
        var composeOnSend = $('a.button.send').attr('onclick');
        $('a.button.send').attr('onclick','getRecipients();'+composeOnSend);
        
        $("table.MsoNormalTable").wrap("<div class='tablewrap'></div>");        
        $(".rcmBody table").wrap("<div class='tablewrap'></div>");
        
        $('#popupnotifydialog').modal().show();
        $('#popupnotifydialog .modal-close').click(function() {
        	$('#popupnotifydialog').hide();
        });
        
        //RECIPIENTSSSS FIELDS
        
        $('ul.compose form li').click(function() { $(this).find(".dummy-recipient").focus(); });
        
        $('.dummy-recipient').addClass("materialize-textarea");
        
        rcmail.init_address_input_events($(".dummy-recipient"),{threads:1,sources:""});
        
        $('.dummy-recipient').on("input propertychange",function() {
        	prettyRecipient($(this));
        });
        
        $('.dummy-recipient').on("keydown", function(e) {
        	if (e.keyCode == 8) {
        		if ($(this).val().length==0) {
        			$(this).siblings('.pretty-recipient').children('a').last().remove();
        		}
        	}
        });
        
        if ($('#_to').val().length!=0) $('#_to').siblings('.dummy-recipient').val($('#_to').val());
        if ($('#_cc').val().length!=0) $('#dummyCc').val($('#_cc').val());
        if ($('#_bcc').val().length!=0) $('#dummyBcc').val($('#_bcc').val());
		
        setInterval(function() {
        	if ($('ul.attachmentslist').children().length > 0) {
        		$('div.attachmentscontainer').show();
        	}
        	$('a[href="#delete"]').html('<i class="material-icons">clear</i>');
        	
        	$('#rcmKSearchpane ul').addClass("collection");
        	$('#rcmKSearchpane ul li').addClass("collection-item waves-effect grey lighten-3 grey-text text-darken-4");
        	
        	$('ul.compose form textarea.materialize-textarea').trigger('autoresize');
        	
        	$('textarea#composebody.materialize-textarea').trigger('autoresize');
        	
        	$('.dropdown-trigger').dropdown();
        	
        	var spanunreadcount = $('span.unreadcount').html() || "";

        	var unreadstr = spanunreadcount.replace(/\(/,"").replace(/\)/,"") || "";
        	
        	$('span.unreadcount').html(unreadstr).addClass("new badge blue");
        	
        },500)
        
        setInterval(function() {
        	$('#message .loading').html(prel);
        },1000)

});
		
//**/

rcmail.addEventListener('init',function(e) {
	rcmail.addEventListener('plugin.reloadmore_cb', matLoadMore);
    $('#loadmore').bind('click', reloadmore_handler); 
    
});

function reloadmore_handler() {
	
	var prel =  '<div class="preloader-wrapper small active">';
		prel += '<div class="spinner-layer spinner-blue-only">';
		prel += '	<div class="circle-clipper left">';
      	prel += '		<div class="circle"></div>';
      	prel += '			</div><div class="gap-patch">';
      	prel += '				<div class="circle"></div>';
      	prel += '			</div><div class="circle-clipper right">';
      	prel += '			<div class="circle"></div>';
      	prel += '		</div>';
      	prel += '	</div>';
      	prel += '</div>';
	
	$('#loadmore').html(prel)
	$('#loadmore').unbind('click');
	rcmail.http_post('plugin.reloadmore',{reloadmore:1}, true); 
}

function matLoadMore(response) {
	$('#loadmore').parent().remove();
	$('ul.collection').append(response.messagelist);
	$('#loadmore').bind('click', reloadmore_handler);
}

function formatRecipient(recipient,pass) {
	var formatTag = "<a href='#modalrecipremove' class='modal-trigger %=pass=%' data-name='%=name=%' data-email='%=email=%'>%=recipient=%</span>";
	
	var recip = "";
  
	if (recipient.name) {
		recip=recipient.name;
	}
	else {
		recip=recipient.email.toLowerCase();
	}
  
	return formatTag.replace('%=pass=%',pass)
  					.replace('%=name=%',recipient.name)
                    .replace('%=email=%',recipient.email.toLowerCase())
                    .replace('%=recipient=%',recip);
}


function getRecipients() {
	$("#_to").val(buildRecipientsStr($('#dummyTo.pretty-recipient a')).trim());
	$("#_cc").val(buildRecipientsStr($('#dummyCc.pretty-recipient a')).trim());
	$("#_bcc").val(buildRecipientsStr($('#dummyBcc.pretty-recipient a')).trim());
}

function buildRecipientsStr(el) {
	var recipStr = "";
	for(var i=0;i<el.length;i++) {
		recipStr += ($(el[i]).data('name').trim())+' <'+$(el[i]).data('email').trim()+'>; '
	}
	return recipStr;
}

function prettyRecipient(el) {
	
	var reEmail = /\S+@\w-\.+\.[A-Za-z]{2,}/;
    var reRecipient = /(?:")[\w\s]+(?:")/
    var reNormalRecipient = /<?(\S+@[\w-\.]+\.[A-Za-z]{2,})>?[;,\s]/;
    var reProperRecipient = /"?([A-Za-z0-9_-\s]+)"?[\s]?<(\S+@[\w-\.]+\.[A-Za-z]{2,})>?[;,\s]/;
    var reInvalidRecipient = /[.]+[;,\s]/;
    var reSplit = /[;,\s]/;
    
    var me  = el;
  	var val = me.val();
    
    if(val.match(reProperRecipient)){
    
    	var match = val.match(reProperRecipient)[0];
    	var newstr = me.val().replace(match,'');
    	
    	newstr = newstr.replace(reSplit,'');
    	me.val(newstr);

    	var recip = {
    			email : match.replace(reProperRecipient,'$2'),
    			name  : match.replace(reProperRecipient,'$1'),
    	}

    	$(me).parent().children(':first-child').append(formatRecipient(recip,"valid"));
    	    	
    }
    
    else if(val.match(reNormalRecipient)[0]) {
        
    	var match = val.match(reNormalRecipient)[0];
    	var newstr = me.val().replace(match,'');
    	
    	newstr = newstr.replace(reSplit,'');
    	me.val(newstr);
      
    	var recip = {
    			email : match.replace(reNormalRecipient,'$1'),
    			name  : "",
    	}
      
    	$(me).parent().children(':first-child').append(formatRecipient(recip,"valid"));
      
    }
    
    //this condi branch not working
    else if(val.match(reInvalidRecipient)[0]) {
        
    	var match = val.match(reInvalidRecipient)[0];
        var newstr = me.val().replace(match,'');
        
        newstr = newstr.replace(reSplit,'');
        me.val(newstr);
    
        var recip = {
          email : '',
          name  : match.replace(reInvalidRecipient,'$1'),
        }
      
        $(me).parent().children(':first-child').append(formatRecipient(recip,"invalid"));
    
    }
    
    $(me).parent().children("div:first-child").children("a").bind("recipremove",function(e,id) {
    	var me = $(this);
    	$(me).parent().children("div:first-child").children("a[data-email='"+id+"']").remove();
    });

    $(me).parent().children("div:first-child").children("a").bind("click",function(e,id) {
    	console.log($(this).attr("data-email"));
    	var me = $(this);
    	me.addClass("selected");
    	
    	$('.modal h5').text($(this).attr("data-name"));
    	$('.modal p').text($(this).attr("data-email"));
    	$('.modal a').click(function() {
    		$(".pretty-recipient a.selected").remove();
    	});
    	
    });
    
    $('#modalrecipremove').modal({
    	complete: function() {
    		$(".pretty-recipient a.selected").removeClass("selected");
    	}
    });
	
}
