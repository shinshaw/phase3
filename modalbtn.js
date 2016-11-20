  $(document).ready(function() {
	  

    $("#exprtndt").datepicker({ dateFormat: "yy-mm-dd",  minDate : new Date() });
	$("#exprtndt_d").datepicker({ dateFormat: "yy-mm-dd", minDate : new Date() });
	

	
  });
  $( function() {
    var dialog, form, dialog_q, form_q, row_rid,
 
      durationRegex = /^(?=.*[1-9])\d*(?:\.\d{1,2})?$/,
	  dateRegex=/(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))/,
      duration = $( "#duration" ),
	  exprtndt =$( "#exprtndt" ),
	  exprtndt_d =$("#exprtndt_d"),
	  inciid=document.getElementById("selinciid").name,
      allFields = $( [] ).add( duration ).add(exprtndt).add(exprtndt_d),
      tips = $( ".validateTips" );
	  
	  


	
    function updateTips( t ) {
      tips
        .text( t )
        .addClass( "ui-state-highlight" );
      setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
    }
 
 
    function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        updateTips( n );
        return false;
      } else {
        return true;
      }
    }
	
	function checkDate( o, k, n ) {
	  var dstr=o.val().split("-");
	  var nad=k.split("-");
	  var  dvalue= new Date(dstr[0],dstr[1]-1,dstr[2],23,59,59);
	  var  nadvalue= new Date(nad[0],nad[1]-1,nad[2],23,59,59);
	  var  tday= new Date();
	  var info="";
	  if (tday<nadvalue) {
		  info="resource next available date is : "+k+".";
	  } 
	  info=info+n;
      if ( dvalue<tday || dvalue<nadvalue) {
        o.addClass( "ui-state-error" );
        updateTips( info );
        return false;
      } else {
        return true;
      }
    }
	
	
 
    function addRepair() {
      var valid = true;
      allFields.removeClass( "ui-state-error" );
 
      
      valid = valid && checkRegexp( duration, durationRegex, "positive numbers only " );
 
      if ( valid ) {
	   	  var data = "rpr_resid=" + document.getElementById("rpr_rid").name+"&duration="+duration.val();

		
		var xhr;  
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...  
		xhr = new XMLHttpRequest();  
	}	 else if (window.ActiveXObject) { // IE 8 and older  
		xhr = new ActiveXObject("Microsoft.XMLHTTP");  
	}  

     xhr.open("POST", "repair.php", true);   
     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                    
     xhr.send(data);  
     xhr.onreadystatechange = display_data;  
    function display_data() {  
     if (xhr.readyState == 4) {  
      if (xhr.status == 200) {  
       //alert(xhr.responseText);        
      document.getElementById("suggestion").innerHTML = xhr.responseText;  
      } else {  
        alert('There was a problem with the repair request.');  
      }  
     }  
    }  

        dialog.dialog( "close" );
		
		alert("Repair Scheduled");
		location.reload(false);

      }
      return valid;
    }
	
	
	 function addRequest() {
      var valid_q = true;
	  allFields.removeClass( "ui-state-error" );
	  var nad=document.getElementById("req_nad").name;


	  
	  
	  valid_q = valid_q && checkRegexp( exprtndt, dateRegex, "This is not a valid date " ) && checkDate( exprtndt, nad, "Expected Return Date should be today or future date no less than resource's next available date" );
	
      if ( valid_q ) {

	   	  var data = "req_resid=" + document.getElementById("req_rid").name+"&exprtndt="+exprtndt.val()+"&req_incid="+inciid;
	
		
		var xhr;  
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...  
		xhr = new XMLHttpRequest();  
	}	 else if (window.ActiveXObject) { // IE 8 and older  
		xhr = new ActiveXObject("Microsoft.XMLHTTP");  
	}  

     xhr.open("POST", "request.php", true);   
     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                    
     xhr.send(data);  
     xhr.onreadystatechange = display_data;  
    function display_data() {  
     if (xhr.readyState == 4) {  
      if (xhr.status == 200) {  
       //alert(xhr.responseText);        
      document.getElementById("suggestion").innerHTML = xhr.responseText;  
      } else {  
        alert('There was a problem with the request.');  
      }  
     }  
    }  

	  	  	  
 
        dialog_q.dialog( "close" );
		alert("Request Submitted");
		location.reload(false);
      } 
      return valid;
    }
	
	 function deploy() {
      var valid_d = true;
      allFields.removeClass( "ui-state-error" );
	  var nad=document.getElementById("dply_nad").name;
  
		valid_d = valid_d && checkRegexp( exprtndt_d, dateRegex, "This is not a valid date " ) && checkDate( exprtndt_d,nad,"Expected Return Date should be today or future date no less than resource's next available date. " );
	
      if ( valid_d ) {

	   	  var data = "dply_resid=" + document.getElementById("dply_rid").name+"&exprtndt_d="+exprtndt_d.val()+"&dply_incid="+inciid;
	
		
		var xhr;  
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...  
		xhr = new XMLHttpRequest();  
	}	 else if (window.ActiveXObject) { // IE 8 and older  
		xhr = new ActiveXObject("Microsoft.XMLHTTP");  
	}  

     xhr.open("POST", "deploy.php", true);   
     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                    
     xhr.send(data);  
     xhr.onreadystatechange = display_data;  
    function display_data() {  
     if (xhr.readyState == 4) {  
      if (xhr.status == 200) {  
       //alert(xhr.responseText);        
      document.getElementById("suggestion").innerHTML = xhr.responseText;  
      } else {  
        alert('There was a problem with the deploy request.');  
      }  
     }  
    }  

  	  
 
        dialog_d.dialog( "close" );
		alert("Resource Deployed");
		location.reload(false);
      }
      return valid;
    }
 
    dialog = $( "#repair-form" ).dialog({
      autoOpen: false,
      height: 400,
      width: 350,
      modal: true,
      buttons: {
        "Schedule": addRepair,
        Cancel: function() {
          dialog.dialog( "close" );
        }
      },
      close: function() {
        form[ 0 ].reset();
        allFields.removeClass( "ui-state-error" );
      }
    });
 
    form = dialog.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      addRepair();
    });
 
    $( ".repairbtn" ).button().on( "click", function() {
	var y=$(this).attr('value');
	 	 document.getElementById("rpr_rid").name=y;
      dialog.dialog( "open" );
    });
	
	
	dialog_q = $( "#request-form" ).dialog({
      autoOpen: false,
      height: 400,
      width: 350,
      modal: true,
      buttons: {
        "Request": addRequest,
        Cancel: function() {
          dialog_q.dialog( "close" );
        }
      },
      close: function() {
        form[ 0 ].reset();
        allFields.removeClass( "ui-state-error" );
      }
    });
	
	 form_q = dialog_q.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      addrequest();
    });
 
	
	$( ".requestbtn" ).button().on( "click", function() {
	 var x=$(this).attr('value');
	 var y=$(this).attr('name'); //save next available date for expected return day validation
	  
	 	 document.getElementById("req_rid").name=x;
		 document.getElementById("req_nad").name=y;

		 
		var nad=document.getElementById("req_nad").name.split("-");	  
	  var  nadvalue= new Date(nad[0],nad[1]-1,nad[2],23,59,59);
	  var  tday= new Date();
	 if (tday>=nadvalue) {mindt=tday;} else {mindt=nadvalue;} 
	 $("#exprtndt").datepicker( "option", "minDate", mindt );;
	 
      dialog_q.dialog( "open" );
	  
    });
	
	dialog_d = $( "#deploy-form" ).dialog({
      autoOpen: false,
      height: 400,
      width: 350,
      modal: true,
      buttons: {
        "Deploy": deploy,
        Cancel: function() {
          dialog_d.dialog( "close" );
        }
      },
      close: function() {
        form[ 0 ].reset();
        allFields.removeClass( "ui-state-error" );
      }
    });
	
	 form_q = dialog_d.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      deploy();
    });
	
	$( ".deploybtn" ).button().on( "click", function() {
	 var z=$(this).attr('value');
	 var y=$(this).attr('name'); //save next available date for expected return day validation
	 document.getElementById("dply_rid").name=z;
	 document.getElementById("dply_nad").name=y;
	 var nad=document.getElementById("dply_nad").name.split("-");	  
	 var  nadvalue= new Date(nad[0],nad[1]-1,nad[2],23,59,59);
	 var  tday= new Date();
	 if (tday>=nadvalue) {mindt=tday;} else {mindt=nadvalue;} 
	 $("#exprtndt_d").datepicker( "option", "minDate", mindt );
	 
      dialog_d.dialog( "open" );
	  
    });
	

	
	
  } );
