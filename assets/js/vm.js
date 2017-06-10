$(document).ready(function(){
  if(typeof my_vmId !== 'undefined' && typeof my_userToken !== 'undefined'){
    var second = 10*3; //3 Minutes
    var toMilliSecond = second*1000;
    // Set Default on page ready
    // Realtime overview
    requestStats("*",my_vmId,my_userToken);
    setInterval(function(){
      requestStats("*",my_vmId,my_userToken);
    },toMilliSecond);
    // VM Action
    displayStateVM(my_vmId,my_userToken,true);
    var confirmText = "Are you sure want to do this action? This operation can result in data lose";
    $("#vm_restart").click(function(){
      if(confirm(confirmText)){
        requestVM("restart",my_vmId,my_userToken);
      }
    });
    $("#vm_power").click(function(){
      if(confirm(confirmText)){
        statePower = $("#vm_power").data("state");
        requestVM(statePower,my_vmId,my_userToken);
      }
    });
    // VM Action
  } else{
    //document.write("Not found VMID or User Token!");
  }

  //Check security of OSV ID
  setInterval(function(){
    if(typeof version_in_os!='undefined'){
      var os = $("input[type=hidden]#os").val();
      var osv = $("input[type=hidden]#osv").val();
      var msgAlert = "4LiV4Lij4Lin4LiI4Lie4Lia4LiE4Lin4Liy4Lih4Lic4Li04LiU4Lie4Lil4Liy4LiULCDguJXguKPguKfguIjguKrguK3guJrguYPguKvguYnguYHguJnguYjguYPguIjguKfguYjguLLguITguLjguJPguYTguKHguYjguYTguJTguYnguJfguLPguITguKfguLLguKHguJzguLTguJTguJXguLLguKHguJ4u4LijLuC4mi7guITguK3guKHguJ7guLTguKfguYDguJXguK3guKPguYw=";
      console.log(os+"|"+osv);
      if($.inArray(osv,version_in_os["os-"+os])===-1){console.log("OS and OS Version is incorrect!");alert(b64_to_utf8(msgAlert));window.location="./?page=laws";}
    }
  },10000);

  $("button[data-action=create_vm]").click(function(){
    if(confirm("Are you sure?")){
      $("div[data-zone=alert]").show(100).removeClass("alert-danger").removeClass("alert-success").addClass("alert-info").html("<i class='fa fa-refresh fa-spin'></i> Loading...");
      var form_name = ".create_vm_form";
      addFormLoading(form_name);
      var os = $("input[type=hidden]#os").val();
      var osv = $("input[type=hidden]#osv").val();
      var plan = $("input[type=hidden]#plan").val();
      var hostname = $("input[type=text]#hostname").val();
      var reg_hostname = /^[a-z0-9]*\.[a-z0-9]*\.[a-z]{2,4}/;
      var hasEnded = false;
      var msg=null;
      if(isNaN(os) || isNaN(osv) || isNaN(plan)){
        msg = "Parameters is incorrect!";
        hasEnded = true;
      } else if($.inArray(osv,version_in_os["os-"+os])===-1){
        msg = "OS and OS Version is incorrect!";
        hasEnded = true;
      } else if(!reg_hostname.test(hostname)){
        msg = "Hostname is invalid";
        hasEnded = true;
      } else{
        console.log("Initialize...");
        $.post("modules/api.create-vm.php",{os:os,osv:osv,plan:plan,hostname:hostname,token:access_token},function(dret){
          hasEnded = true;
          $("div[data-zone=alert]").hide(100).removeClass("alert-info").html("");

          var hasError = false;
          var errorMsg = null;
          try{
            var data = JSON.parse(dret);
            console.log("Creating VM : "+data.vm);
          } catch(err){
            hasError = true;
            errorMsg = err;
            console.log(dret);
          }

          if(hasError==false){
            console.log(data.msg);
            if(data.status==true){
              $(".create_vm_form input,button").attr("disabled","disabled");$(".create_vm_form").addClass("disabled");
              $(".create_vm_form").slideUp(500);
              $("div[data-zone=alert]").show(100).addClass("compact").html('<div class="ui positive message icon"><i class="check icon"></i><div class="content"><div class="header">Create VM Successful!</div><p>Go to your <b>Dashboard</b> page to see now.</p></div></div>');
              alert(JSON.stringify(data));
              setTimeout(function(){ window.location="./?page=install&vm="+data.vm_id; },3000);
            } else{
              $("div[data-zone=alert]").show(100).addClass("alert-danger").html("<i class='icon warning circle'></i> " + data.msg);
              setTimeout(function(){ window.location.reload(); },3000);
            }
          } else{
            $(".create_vm_form").slideUp(500);
            $("div[data-zone=alert]").show(100).addClass("compact").html('<div class="ui negative message icon"><i class="warning icon"></i><div class="content"><div class="header">Create VM Failure!</div><p>Something went wrong, Please try again later.</p></div></div>');
            console.log("ERROR: "+errorMsg);
          }
          removeFormLoading(form_name);
        });
      }
      if(hasEnded==true){
        removeFormLoading(form_name);
        if(msg!=null){
          $("div[data-zone=alert]").show(100).removeClass("alert-info").addClass("alert-danger").html("<i class='icon warning circle'></i> " + msg);
          console.log(msg);
        }
      }
    }
  });

  $("button[op=select_os]").click(function(){
    var selectedColor = "teal";
    var defaultColor = "grey";
    for(i=0;i<$("button[op=select_os]").length;i++){ $($("button[op=select_os]")[i]).removeClass(selectedColor).addClass(defaultColor); }
    for(i=0;i<$("div[data-zone=select_osv]").length;i++){ $($("div[data-zone=select_osv]")[i]).slideUp(100); }
    var os = ($(this).data("os"));
    $(this).addClass("active").removeClass(defaultColor).addClass(selectedColor);
    $("input[type=hidden]#os").val(os);
    $("#select_version_os_"+os).slideDown(500);
    var defaultOSV = versionList["os-"+os]["id"];
    selectOSV("button[data-osv="+defaultOSV+"]");
  });

  $("button[op=select_osv]").click(function(){
    selectOSV(this);
  });

  $("button[op=select_plan]").click(function(){
    selectPlan(this);
  });

  $('.confirm-box').on('show.bs.modal', function (event) {
    $(document).on ("click", ".confirm-dialog[data-confirm=remove_vm]", function () {
      //if(confirm("Are you sure?")){
        var getVM = getUrlParameter("vm_id");
        $(".segment[data-tab=other]").addClass("loading");
        $.post("modules/api.remove-vm.php",{vm_id:getVM,token:access_token},function(dret){
          var data = JSON.parse(dret);
          if(data.status==true){
            window.location = "./?page=dashboard&vm_id="+getVM+"&action=success";
          } else{ window.location = "./?page=dashboard&vm_id="+getVM+"&action=failed&reason="+data.msg; }
        });
      //}
    });

    $(document).on ("click", ".confirm-dialog[data-confirm='reinstall_vm']", function () {
      if(confirm("Are you sure?")){
        var getVM = getUrlParameter("vm_id");
        $(".segment[data-tab=other]").addClass("loading");
        $.post("modules/api.reinstall-vm.php",{vm_id:getVM,token:access_token},function(dret){
          var data = JSON.parse(dret);
          if(data.status==true){
            window.location = "./?page=dashboard&vm_id="+getVM+"&action=success";
          } else{ window.location = "./?page=dashboard&vm_id="+getVM+"&action=failed&reason="+data.msg; }
        });
      }
    });

  });

  $("button.send-message").click(function(){
    var d = new Date();
    var ampm = d.getHours() >= 12 ? 'PM' : 'AM';
    var time = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + " " + ampm;
    var msg = $("input.message-box").val();
    var meBubble = "<div align='right'><div class='chat-me' align='left'><h3>[<b style='color:blue;'>"+myUsername+"</b>] <span class='text-muted' style='font-size:12px;'>"+time+"</span></h3>"+ msg +"</div>";
    $("#chat_zone").append(meBubble);
    $.post("modules/api.send-message.php",{conversation_id:getUrlParameter('read'),message:msg,token:access_token},function(dret){
      var data = JSON.parse(dret);
      console.log(data.msg);
    });
    $("input.message-box").val("");
  });

  $("button.open-modal").click(function(){
    var getModal = $(this).data('modal');
    var getAction = $(this).data('action');
    $('.'+getModal).modal('toggle');
    $('.confirm-dialog').attr("data-confirm",getAction);
  });

  $(".menu .item[data-value]").click(function(){
    var value = $(this).data('value');
    $("#total_price").html(price[value].format(2));
  });


});

Number.prototype.format = function(n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};

function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? null : sParameterName[1];
        }
    }
}

function renewVM(){
  if(confirm("Are you sure?")){
    var getVM = getUrlParameter("vm_id"); var d = $(".menu .item[data-value]").data('value');
    $(".segment[data-tab=other]").addClass("loading");
    $.post("modules/api.renew-vm.php",{vm_id:getVM,days:d,token:access_token},function(dret){
      var data = JSON.parse(dret);
      if(data.status==true){
        window.location = "./?page=dashboard&vm_id="+getVM+"&action=success";
      } else{ window.location = "./?page=dashboard&vm_id="+getVM+"&action=failed&reason="+data.msg; }
    });
  }
}

function confirmExit(){
  return ("Are you sure want to exit?");
}

function checkingSetupVM(){
  var getVMID = getUrlParameter("vm");
  $.post("modules/api.checking-vm.php",{'token':access_token,'vm_id':getVMID},function(dret){
    var data = JSON.parse(dret);
    if(data.status==true || data.force_end==true){
      console.log("VM IP : "+data.ip);
      $(".status_checking").removeClass("alert-danger").addClass("alert-success").html("<i class='check circle icon'></i> Your VM is now ready!").show(100);
      setTimeout(function(){ window.location="./?page=dashboard&vm_id="+getVMID; },3000);
    } else{ console.log("Setting up... ("+data.msg+")"); }
  });
}

function sbs_submit(is_next){
  var step = parseInt(getUrlParameter("step"));
  var os = $("input[name=vm_info]#os").val();step++;
  var osv = $("input[name=vm_info]#osv").val();
  var plan = $("#plan[name=plan]").val();
  var hostname = $("#hostname[name=hostname]").val();
  var next_step = getUrlParameter("step");
  if(is_next==true){next_step++;} else{next_step--;}
  var urlNextStep = "./?page=create&step="+next_step;
  if(typeof os==="undefined"){os=$("#os").val();} if(typeof osv==="undefined"){osv=$("#osv").val();}
  $("form[name=next_step]").attr("action",urlNextStep);
  $("form[name=next_step]").append("<input type='hidden' name='os' value='"+os+"' />");
  $("form[name=next_step]").append("<input type='hidden' name='osv' value='"+osv+"' />");
  $("form[name=next_step]").append("<input type='hidden' name='plan' value='"+plan+"' />");
  $("form[name=next_step]").append("<input type='hidden' name='hostname' value='"+hostname+"' />");
  $("#os[name=os]").remove();$("#osv[name=osv]").remove();$("#plan[name=plan]").remove();$("#hostname[name=hostname]").remove();
  document.next_step.submit();
}

function addFormLoading(form_name){
  return $(form_name).addClass("loading");
}

function removeFormLoading(form_name){
  return $(form_name).removeClass("loading");
}

function b64_to_utf8(str) {
  return decodeURIComponent(escape(window.atob( str )));
}

function selectPlan(id_name){
  var selectedColor = "panel-primary";
  var defaultColor = "panel-default";
  for(i=0;i<$("button[op=select_plan]").length;i++){ $($("button[op=select_plan]")[i]).removeClass(selectedColor).addClass(defaultColor); }
  var plan = ($(id_name).data("plan"));
  $(id_name).addClass("active").removeClass(defaultColor).addClass(selectedColor);
  $("input[type=hidden]#plan").val(plan);
}

function selectOSV(id_name){
  var selectedColor = "teal";
  var defaultColor = "grey";
  for(i=0;i<$("button[op=select_osv]").length;i++){ $($("button[op=select_osv]")[i]).removeClass(selectedColor).addClass(defaultColor); }
  var os = ($(id_name).data("osv"));
  $(id_name).addClass("active").removeClass(defaultColor).addClass(selectedColor);
  $("input[type=hidden]#osv").val(os);
}

function requestStats(view,vmId,userToken){
  $.post("modules/api.overview.php",{"vmId":vmId,"view":view,"token":userToken},function(r){
    data = JSON.parse(r);
    if(data.status==true){
      var d = new Date();
      var timeLog = "["+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds()+"]";
      var ovList = data.overview;
      var sortedKeys = Object.keys(ovList).sort();
      var idOfKey = new  Array();
      idOfKey["cpu"] = "cpuUsage";
      idOfKey["memory"] = "memUsage";
      for(i=0;i<Object.keys(ovList).length;i++){
        var getKey = ovList[sortedKeys[i]];
        if(getKey>=30 && getKey<50){
          $(".usableReview[data-bar="+idOfKey[sortedKeys[i]]+"]").addClass("progress-bar-info").removeClass("progress-bar-success").removeClass("progress-bar-danger").removeClass("progress-bar-warning");
        } else if(getKey>=50 && getKey<80){
          $(".usableReview[data-bar="+idOfKey[sortedKeys[i]]+"]").addClass("progress-bar-warning").removeClass("progress-bar-success").removeClass("progress-bar-danger").removeClass("progress-bar-info");
        } else if(getKey>=80){
          if(getKey>100){getKey=100;}
          $(".usableReview[data-bar="+idOfKey[sortedKeys[i]]+"]").addClass("progress-bar-danger").removeClass("progress-bar-success").removeClass("progress-bar-warning").removeClass("progress-bar-info");
        } else{
          $(".usableReview[data-bar="+idOfKey[sortedKeys[i]]+"]").addClass("progress-bar-success").removeClass("progress-bar-warning").removeClass("progress-bar-danger").removeClass("progress-bar-info");
        }
        $(".usableReview[data-bar="+idOfKey[sortedKeys[i]]+"]").attr("style","width:"+getKey+"%;").html(getKey+"%");
      }

      $(".usableReview[data-bar=diskUsage]").html(ovList.disk);
      var replacePercentage = $(".usableReview[data-bar=diskUsage]").html().replace(/\%/gi, " ");
      $(".usableReview[data-bar=diskUsage]").html(replacePercentage);
    } else{
      console.log("[RETURN] "+data.msg);
    }
  });
}
function requestVM(state,vmId,userToken){
  $("#respondState").show(500).html("<div class='alert alert-info'><i class='loading refresh icon'></i> Loading...</div>");
  $(".form-vm-action").addClass("loading");
  $.post("modules/api.vm.php",{"vmId":vmId,"state":state,"token":userToken},function(r){
    $(".form-vm-action").removeClass("loading");
    $("#respondState").show(500).html("<div class='alert alert-danger'><i class='warning circle icon'></i> Something went wrong!</div>");
    data = JSON.parse(r);
    if(data.status==true){
      $("#respondState").show(500).html("<div class='alert alert-info'><i class='loading refresh icon'></i> Sending request...</div>");
      setTimeout(function(){ window.location.reload(); },3000);
      displayStateVM(vmId,userToken);
    } else{
      console.log("Access Denied to VM | "+data);
      $("#respondState").show(500).html("<div class='alert alert-danger'><i class='warning circle icon'></i> Access Denied!</div>");
      return false;
    }
  });
}
function displayStateVM(vmId,userToken,isOnStart=false){
  $.post("modules/api.powerstate.php",{"vmId":vmId,"token":userToken},function(r){
    data = JSON.parse(r);
    if(data.status==true){
      console.log("Found VM State!");

      if(isOnStart==false){
        //$("#vm_power,#vm_restart").attr("disabled","disabled").addClass("disabled");
        $("#respondState").show(500).html("<div class='alert alert-success'><i class='check circle icon'></i> Send the request was successful</div>");
      }

      $("#areaStatus").removeClass("green").removeClass("yellow").addClass(data.stateColor);
      $("#iconStatus").removeClass("check").removeClass("pause").addClass(data.iconState);
      if(data.txt=="Running"){
        $("#vm_power").removeClass("positive").removeClass("negative").addClass("negative").attr("data-state","shutdown");
        $("#powerTxt").html("Force Shutdown");
      } else{
        $("#vm_power").removeClass("positive").removeClass("negative").addClass("positive").attr("data-state","start");
        $("#powerTxt").html("Start");
      }
      $("#txtStatus").html(data.txt);
    } else{
      $("#respondState").show(500).html("<div class='alert alert-danger'><i class='warning circle icon'></i> Access Denied!</div>");
      console.log("Not found VM State!");
    }
  });
}
