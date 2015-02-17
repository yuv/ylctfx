/**
** yl_ctfx.js
**
** (C) 2011-2015 Yuval Levy, http://www.photopla.net
**
** Released under the MIT license.
**
** Client-side HTML contact form.
**
** This file is part of the YLCTFX project.
**
** Use JavaScript to prevent contact form spam
** 99.99% of non-JS clients are bots, the vast majority of them spambots
**
*/

// the form in a variable
var foo =' \
          <div id="yl_ctfx"> \
          <div id="yl_fx_status"></div> \
            <fo';
foo = foo+'rm id="ylctact" meth'+'od="get" act'+'ion=""> \
              <p>You are writing to __TARGET_DESCRIPTION__ \
                <a id="yl_fx_close" href="javascript:closectfx()">close</a> \
              </p> \
              <p> \
                <label for="csubject">Subject</label> \
                <input id="csubject" name="csubject" size="77" maxlength="100" /> \
              </p> \
              <p> \
                <label for="ccomment">Your comment (required)</label> \
                <textarea id="ccomment" name="ccomment" class="required" cols="76" wrap="VIRTUAL" rows="8"></textarea> \
              </p> \
              <p> \
                <label for="cname">Name (required)</label> \
                <input id="cname" name="cname" class="required" minlength="2" size="77" maxlength="100" /> \
              </p> \
              <p> \
                <label for="cemail">E-Mail (required)</label> \
                <input id="cemail" name="cemail" class="required email" size="77" maxlength="100" /> \
              </p> \
              <p> \
                <label for="ccaptcha">Math question: __CAPTCHA__</label> \
                <input id="ccaptcha" name="ccaptcha" class="required integer" size="10" maxlength="10"/> \
                <br/>to discern humans from spambots. \
              </p> \
              <p> \
                <input id="csubmit" class="submit" type="submit" value="Submit"/> \
                <input type="hidden" name="i" value="__HASH__"/> \
                <input type="hidden" name="l" value="__HASH_CAPTCHA__"/> \
                <input type="hidden" name="target" value="__TARGET__" /> \
              </p> \
            </form> \
          <\div> \
          ';

// Use jQuery via $jQ(...)
var $jQ = jQuery.noConflict();
$jQ(document).ready(function(){
  // activate the links
  $jQ("a[href^='contact.php?']")
    .each(function(){
      this.href = this.href.replace(/.*contact.php\?ylctarget=/, 'javascript:contact("');
      this.href = this.href+'");';
    });
});

function closectfx() {
  $jQ("#yl_ctfx").remove();
}

// when user clicks on a contact link, toggle.
function contact (t){
  // if the contact form is already displayed
  if ( $jQ("#yl_ctfx").length > 0 )
  {
    // and it is for the same clicked contact
    if ( $jQ("input:hidden[name=target]").val() == t )
    {
      // remove the form and quit
      $jQ("#yl_ctfx").remove();
      return;
    }
    else
    {
      // else remove the form and draw the updated form for the new contact
      $jQ("#yl_ctfx").remove();        
    }
  }
  // set up the contact form
  var f = foo.replace('__TARGET__',t);
  f = f.replace('__CAPTCHA__',yl_c);
  f = f.replace('__HASH__',yl_h);
  f = f.replace('__HASH_CAPTCHA__',yl_hc);
  var u = "the unknown recipient";
  if (typeof(Recipients[t]) != "undefined")
  {
    u = Recipients[t];
  }
  f = $jQ(f.replace('__TARGET_DESCRIPTION__',u))
              .css({zindex: "254", position: "absolute", top: "10px", left: "50%",
                    width: "602px", marginLeft: "-301px" })
              .appendTo("body")
              .show();
  $jQ("#csubject").focus();
  // and activate it
  $jQ("#ylctact").validate();
}

// form action
$jQ.validator.setDefaults({
  submitHandler: function() {
    // serialize the data
    var str = $jQ("#ylctact").serialize();
    // disable submit button to prevent double submit
    $jQ("#csubmit").hide();
    // tell user to wait while sending
    // $jQ("#yl_fx_status").text("Sending...");
    $jQ('<img id="yl_hourglass" src="./img/hourglass.gif"/>')
            .css({zindex: "255", position: "absolute", top: "50%", left: "50%"})
            .appendTo("#yl_ctfx")
            .show();
    // post request
    $jQ.post(
      "yl_ajctc.php",
      str,
      function(response){
        // the wait is over
        $jQ("#yl_hourglass").remove();
        // string for return message
        var rs = "";
        // toggle for success
        var s = 0;
        var r = response.split("_");
        for (var i = 0; i < r.length; i++){
          // if we receive the response "Sent.", message was sent successfully
          if (r[i]=="Sent.") {s = 1;}
          if(r[i].length > 0){
            // compose error message
            if (rs.length > 0){
              rs=rs+"</br>";
            }
            rs=rs+r[i];
          }
        }
        if(s == 1){
          // in case of success
          $jQ("#yl_fx_status").text("Sent!");
          $jQ("#yl_ctfx")
            .html(rs)
            .css({backgroundColor: "#0F0"});
          setTimeout ( function(){$jQ("#yl_ctfx").remove();},5000);
        }else{
          // in case of error
          // flash a red
          b = $jQ("#yl_ctfx").css("backgroundColor");
          $jQ("#yl_ctfx").css({backgroundColor: "#F00"});
          // display error message
          $jQ("#yl_fx_status").html(rs);
          setTimeout(function(){
            // $jQ("#yl_fx_status").text("");
            // reactivate form and restore background color
            $jQ("#csubmit").show();
            $jQ("#yl_ctfx").css("backgroundColor",b);
          },5000);
        }
      },
      "text"
    );
  }
});
