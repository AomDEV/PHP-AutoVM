$(document).ready(function(){

    function isMobile() {
        if (sessionStorage.desktop) // desktop storage
            return false;
        else if (localStorage.mobile) // mobile storage
            return true;
        var mobile = ['iphone','ipad','android','blackberry','nokia','opera mini','windows mobile','windows phone','iemobile'];
        for (var i in mobile) if (navigator.userAgent.toLowerCase().indexOf(mobile[i].toLowerCase()) > 0) return true;
        return false;
    }

    function detectInputEmpty(){
      var fieldsRequired = $(".field").find("select, textarea, input").serializeArray();
      var totalEmpty = 0;
      $.each(fieldsRequired, function(i, field) {
        if (!field.value){
          totalEmpty++;
          $("."+field.name).addClass("error");
        } else{
          $("."+field.name).removeClass("error");
        }
      });
      if(totalEmpty>=1){console.log("[JS] Input is null value!");}
      return totalEmpty;
    }
    $("a[data-action=editCard]").click(function(){
      $("#showCard").slideUp(100);
      $("#editZone").slideDown(500);
    });
    $("button[data-action=hideCardForm]").click(function(){
      $("#showCard").slideDown(100);
      $("#editZone").slideUp(500);
    });
    $("input").keyup(function(){
      detectInputEmpty();
    });
    $("a[data-action=addFund]").click(function(){
      $(".addFundBox").toggle(200);
    });
    $("button[data-action=addFund]").click(function(){
      if(confirm("Are you sure you want to add fund to your account?")){
        console.log("Sending payment...");
        var postForm = "amount="+$("#amount").val()+"&initType=add_fund";
        requestPayment(postForm,"#showCard",".addFundxAlert","button[data-action=addFund]","#status_page");
      }
    });
    $("button[data-action=payment]").click(function(){
      console.log("Sending payment...");
      var totalEmpty = detectInputEmpty();
      //AJAX POST
      if(totalEmpty==0){
        var postForm = $("form[name=edit_card]").serialize();
        requestPayment(postForm,"#editZone",".xAlert","button[data-action=payment]","#status_page");
      }
      //AJAX POST
    });

    function requestPayment(postForm,zoneId,alertZoneId,btnPayment,errorInfo){
      $(btnPayment).addClass("disabled");
      $("#load_segment").addClass("loading");
      $.ajax({
        url: "libraries/credit_card/process-credit-card.php",
        type: "POST",
        data: postForm,
        dataType: 'json'
      }).done(function(doneData) {
        $(btnPayment).removeClass("disabled");
        $("#load_segment").removeClass("loading");

        var scrollToElement = "html, body";
        if(isMobile()==true){
          scrollToElement = alertZoneId;
        }
          $('html, body').animate({
              scrollTop: $(scrollToElement).offset().top
          }, 2000);
        if(doneData.status=="success"){
          if(doneData.data.ACK=="Success"){
            console.log("Payment via Credit Card Success!");
            $(zoneId).html("").load("libraries/view/page/status.page.php?status=success");
            $(errorInfo).html("Payment via Credit Card Successful!");
          } else{
            console.log("Payment via Credit Card Failure!");
          }
        } else if(doneData.status=="disabled"){
          console.log("This payment method is disabled");
          $(zoneId).html("").load("libraries/view/page/status.page.php?status=failure");
          $(errorInfo).html("This payment method is disabled");
        } else if(doneData.status=="wrong_amt"){
          $(zoneId).html("").load("libraries/view/page/status.page.php?status=failure");
          $(errorInfo).html("Wrong Amount!");
        } else if(doneData.status=="request"){
          console.log("Not found request!");
          $(zoneId).html("").load("libraries/view/page/status.page.php?status=failure");
          $(errorInfo).html("Not found request!");
        } else if(doneData.status=="has_warning"){
          $(zoneId).html("").load("libraries/view/page/status.page.php?status=success");
          $(errorInfo).html("Payment success with warning, Please contract developer!");
          console.log(doneData.message);
        } else if(doneData.status=="failed"){
          console.log("Failed to process transaction!");
          $(alertZoneId).show(500).html("<i class='warning circle icon'></i> Failed to process transaction!");
        } else{
          console.log("Sonething went wrong, Please contract developer.");
          $(zoneId).html("").load("libraries/view/page/status.page.php?status=failure");
          $(errorInfo).html("Sonething went wrong, Please contract developer.");
        }
      }).fail(function(failData){
        console.log(failData);
      }).always(function(alwaysData){
        console.log(alwaysData);
      });
    }

    $("#card_exp").on('keyup',function(){
      if($(this).val().length==2){$(this).val($(this).val()+"/");}
      if($(this).val().length!=7){$("#status_exp").addClass("error");} else{$("#status_exp").removeClass("error");}
    });
    $("#card_cvv2,#phone,#postalcode").keyup(function(){
      if (/\D/g.test(this.value)){this.value = this.value.replace(/\D/g, '');}
    });
    $("#card_no").keyup(function(e){
      $(this).val(function(_, v){ return v.replace(/\s+/g, ''); });
      if (/\D/g.test(this.value)){this.value = this.value.replace(/\D/g, '');}
    });
    $('#card_no').validateCreditCard(function(result) {
      var cardType = (result.card_type == null ? '-' : result.card_type.name);
      var cardValid = result.valid;
      var cardLength = result.length_valid;
      var cardLuhn = result.luhn_valid;
      var makeToIcon = {visa:"visa",visa_electron:"visa",maestro:"credit card alternative",mastercard:"mastercard",discover:"discover",jcb:"japan credit bureau",diners_club_carte_blanche:"diners club"};
      if(cardValid==true && cardLength==true && cardLuhn==true){
        $("#status_no").removeClass("error");
      } else{$("#status_no").addClass("error");}
      if(cardType!="-"){
        $("#cardIcon").attr("class",makeToIcon[cardType]+" icon");
        $("#card_type").val(cardType);
      }
    });
    $("#paypal[data-action=payment]").click(function(){
      $("#price").val($("#paypal_amt").val());
      $("#paypalAction").attr("action","libraries/paypal/process.php").submit();
      console.log("Sending paypal payment...");
    });
});
