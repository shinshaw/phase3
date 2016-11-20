
 $( function() {
	 
	 
    function display_data() {
	 
     if (xhr.readyState == 4) {  
      if (xhr.status == 200) {  
       alert(xhr.responseText);        
      document.getElementById("suggestion").innerHTML = xhr.responseText;  
      } else {  
        alert('There was a problem with the deploy request.');  
      }  
     }  
    } 
	
	
	function pull_data(td,cdloc) {
		
	var xhr;  
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...  
		xhr = new XMLHttpRequest();  
	}	 else if (window.ActiveXObject) { // IE 8 and older  
		xhr = new ActiveXObject("Microsoft.XMLHTTP");  
	}  

     xhr.open("POST", cdloc, true);   
     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                    
     xhr.send(td);  
     xhr.onreadystatechange = display_data; 
		
	}
	
	
	
	$( ".returnbtn" ).button().on( "click", function() {
	 	 var rid=$(this).attr('value');
	 var iid=$(this).attr('name');
	 document.getElementById("btnresid").innerHTML=rid;
	 document.getElementById("btnincid").innerHTML=iid;

	  var data = "btnresid=" + rid+"&btnincid="+iid;
		pull_data(data,"return.php") ;
		alert("Resource Returned");
		location.reload(false);
		  
    });
	
	
	
	$( ".rmvreqbtn" ).button().on( "click", function() {
	 	 var rid=$(this).attr('value');
	 var iid=$(this).attr('name');
	 document.getElementById("btnresid").innerHTML=rid;
	 document.getElementById("btnincid").innerHTML=iid;

	  var data = "btnresid=" + rid+"&btnincid="+iid;
		
		pull_data(data,"rmvreq.php") ;
		alert("Request Canceled");
		location.reload(false);
	  
    });
	
	
	
	
	$( ".rjtreqbtn" ).button().on( "click", function() {
	var rid=$(this).attr('value');
	 var iid=$(this).attr('name');
	 document.getElementById("btnresid").innerHTML=rid;
	 document.getElementById("btnincid").innerHTML=iid;

	  var data = "btnresid=" + rid+"&btnincid="+iid;
		
		pull_data(data,"reject.php") ;
		alert("Request Rejected");
		location.reload(false);
	  
    });
	
	$( ".appvreqbtn" ).button().on( "click", function() {
	var rid=$(this).attr('value');
	 var iid=$(this).attr('name');
	 document.getElementById("btnresid").innerHTML=rid;
	 document.getElementById("btnincid").innerHTML=iid;

	  var data = "btnresid=" + rid+"&btnincid="+iid;
		
		pull_data(data,"appvreq.php") ;
		alert("Request Approved and Resource Deployed");
		location.reload(false);
	  
    });
	
	$( ".rmvrprbtn" ).button().on( "click", function() {
	 
	var rid=$(this).attr('value');
	 var iid=$(this).attr('name');
	 document.getElementById("btnresid").innerHTML=rid;
	 document.getElementById("btnincid").innerHTML=iid;

	  var data = "btnresid=" + rid+"&btnincid="+iid;
		pull_data(data,"rmvrpr.php") ;
		alert("Repair Canceled");
		location.reload(false);
	 
	 
    });
	
 } );