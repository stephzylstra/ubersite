var currentPage = window.location.pathname;

var index = currentPage.lastIndexOf("/") + 1;
var filename = currentPage.substr(index);

if (filename == "awards.php") {
  var oldValue = false;
  $(awards_init);
}

function awards_selectAward(newID) {
  if (oldValue !== false) {
    document.getElementById('award'+oldValue).style.display = 'none';
  }
  if (newID == "none") {
    oldValue = false;
  } else {
    document.getElementById('award'+newID).style.display = '';
    oldValue = newID;
  }
}

function awards_selectPerson(category, newID) {
  if (newID == "none") {
    document.getElementById('photo'+category).src = "/resources/img/no-pic-thumb.jpg";
    document.getElementById('submit'+category).style.display = 'none';
  } else {
    document.getElementById('photo'+category).src = "/campData/profiles/"+newID+"-thumb.jpg";
    document.getElementById('submit'+category).style.display = '';
  }
}

function awards_init() {
  var newValue = document.getElementById('categorySelector').value;
  if (newValue != "none") {
    oldValue = document.getElementById('categorySelector').value;
  }
}


function pegosaurus_new() {
  $("#link").hide();
  $("#new").show();
}

function photo_tag(obj) {
  $("#tagText").toggle();
  $("#tagInput").toggle();
}

function photo_untag() {
  $("#tagText").toggle();
  $("#untagInput").toggle();
}

function photo_submit(field, variable) {
  var keyCode;

  if (window.event) {
    keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
  } else if (variable) {
    keyCode = variable.which;
  }

  if (keyCode == 13) {
    $("#tagForm").submit();
  }
}

function profile_clear() {
  var textarea = $("#facts");
  if (textarea.css('fontFamily') != "monospace") {
    textarea.val("");
    textarea.css('color', "#000000");
    textarea.css('fontFamily', "monospace");
  }
}


function questionnaire_toggle(obj, type) {
  par = obj.parentNode;
  expand = (par.style.height != "auto");

  if(expand) {
    obj.innerHTML = "Minimise this box:";
    par.style.height = "auto";
  } else {
    obj.innerHTML = "Did this elective, click to expand:";
    par.style.height = "15px";
  }
}

function quotes_multiple() {
  $("#selectionRow").hide();
  $("#none").attr("selected", "selected");
}

function quotes_single() {
  $("#selectionRow").show();
}

function helperText(obj, focus) {
  if (focus && obj.className.indexOf('helper_text') !== -1) {
    obj.value = "";
    obj.className = obj.className.replace(/ ?helper_text/, "");
    return;
  }
  if (!focus && obj.value === "") {
    obj.value = obj.helperText;
    if (obj.className.indexOf('helper_text') === -1) {
      obj.className += ' helper_text';
    }
  }
}

function ajaxFunction() {
  var xmlHttp;
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
  } catch (e) {  // Internet Explorer
    try
    {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e) {
        alert("Your browser does not support AJAX!");
        return false;
      }
    }
  }
  return xmlHttp;
}

function pair(x, f) {
  return function() {
    f(x);
  };
}
