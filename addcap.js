    function get_caps() {
        var caps = new Array;
        var caps_str = localStorage.getItem('cap');
        if (caps_str !== null) {
            caps = JSON.parse(caps_str); 
        }
        return caps;
    }
     
    function add_caps() {
        var capin = document.getElementById('capin').value;
     
        var caps = get_caps();
		var dup=false;

		for(var i=0; i<caps.length; i++) {
		if (capin === caps[i]) dup=true;}
		

		
		if (!dup && capin.trim() ) {
        caps.push(capin);
        localStorage.setItem('cap', JSON.stringify(caps));
		} else {
			window.alert("duplicate entry or empty input");
		}
        show_caps();
		
        return false;
    }
     
    function rmv_caps() {
        var id = this.getAttribute('id');
        var caps = get_caps();
        caps.splice(id, 1);
        localStorage.setItem('cap', JSON.stringify(caps));
     
        show_caps();
     
        return false;
    }
     
    function show_caps() {
        var caps = get_caps();
     
        var html = '<ul>';
        for(var i=0; i<caps.length; i++) {
            html += '<li>' + caps[i] + '<button class="remove" id="' + i  + '">x</button></li>';
			html += '<input type="hidden" name="capblty[]" value="' + caps[i] + '"/>';
        };
        html += '</ul>';
     
        document.getElementById('caps').innerHTML = html;
     
        var buttons = document.getElementsByClassName('remove');
        for (var i=0; i < buttons.length; i++) {
            buttons[i].addEventListener('click', rmv_caps);
        };
    }
     
    document.getElementById('addcaps').addEventListener('click', add_caps);
    show();