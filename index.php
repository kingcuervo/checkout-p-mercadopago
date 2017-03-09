<!DOCTYPE html>
<html lang="pt">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />
    <script type='text/javascript' src='js/jquery.js'></script>
    <script type='text/javascript' src='js/jquery.simplemodal.js'></script>
    <script type='text/javascript' src='js/basic.js'></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Integrar MP</title>
    <link rel="stylesheet" href="payment.css">
    <!-- incluir o SDK e inserir a PUBLIC KEY -->
    <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
    <script>Mercadopago.setPublishableKey("TEST-bee3cbbd-6e1e-4c1d-abbb-81b4287d6336");</script> <!-- insira aqui sua public key -->
</head>
<body>

<h1>Pagos con tarjeta de credito</h1>

<form method="post" id="pay" name="pay" >
    <fieldset>
        <ul>
            <li>
                <label for="email">Email</label>
                <input id="email" name="email" value="sandbox@agenciacreotuidea.com" type="email" placeholder="your email"/>
            </li>
            <li>
                <label for="cardNumber">Número de tarjeta de credito:</label>
                <input type="text" id="cardNumber" data-checkout="cardNumber" placeholder="4509 9535 6623 3704" />
            </li>
            <li>
                <label for="securityCode">Código de seguridad:</label>
                <input type="text" id="securityCode" data-checkout="securityCode" placeholder="123" value="123" />
            </li>
            <li>
                <label for="cardExpirationMonth">Mes de expiración:</label>
                <input type="text" id="cardExpirationMonth" data-checkout="cardExpirationMonth" placeholder="12" />
            </li>
            <li>
                <label for="cardExpirationYear">Año de expiración:</label>
                <input type="text" id="cardExpirationYear" data-checkout="cardExpirationYear" placeholder="2015" />
            </li>
            <li>
                <label for="cardholderName">Nombre del tarjetahabiente:</label>
                <input type="text" id="cardholderName" data-checkout="cardholderName" placeholder="APRO" />
            </li>
            <li>
                <label for="docType">Tipo de documento:</label>
                <select id="docType" data-checkout="docType"></select>
            </li>
            <li>
                <label for="docNumber">Número de documento:</label>
                <input type="text" id="docNumber" data-checkout="docNumber" placeholder="12345678" value="12345678" />
            </li>
            
            <!-- inicio código opcional para vender parcelado -->
            <input type="hidden" id="amount" name="amount" value="500" />

            <!-- fim código opcional para vender parcelado -->
            <li>
            <input id="btn_enviar" type="submit" value="Pagar">
            </li>
        </ul>
    </fieldset>
</form>
<div id="respuesta"></div>
<div id="loading" style="
    width: 100%;
    height: 100%;
    background: white url('loader.gif') center center;
    background-repeat: no-repeat;
    position: absolute;
    top: 0;
    left: 0;
    display: none;
"></div>
</body>

<script>

Mercadopago.getIdentificationTypes();
//obter bandeira do cartão
function addEvent(el, eventName, handler){
    if (el.addEventListener) {
           el.addEventListener(eventName, handler);
    } else {
        el.attachEvent('on' + eventName, function(){
          handler.call(el);
        });
    }
};

function getBin() {
    var ccNumber = document.querySelector('input[data-checkout="cardNumber"]');
    return ccNumber.value.replace(/[ .-]/g, '').slice(0, 6);
};

function guessingPaymentMethod(event) {
    var bin = getBin();

    if (event.type == "keyup") {
        if (bin.length >= 6) {
            Mercadopago.getPaymentMethod({
                "bin": bin
            }, setPaymentMethodInfo);
        }
    } else {
        setTimeout(function() {
            if (bin.length >= 6) {
                Mercadopago.getPaymentMethod({
                    "bin": bin
                }, setPaymentMethodInfo);
            }
        }, 100);
    }
};

function setPaymentMethodInfo(status, response) {
    if (status == 200) {
        // do somethings ex: show logo of the payment method
        var form = document.querySelector('#pay');

        if (document.querySelector("input[name=paymentMethodId]") == null) {
            var paymentMethod = document.createElement('input');
            paymentMethod.setAttribute('name', "paymentMethodId");
            paymentMethod.setAttribute('type', "hidden");
            paymentMethod.setAttribute('value', response[0].id);

            form.appendChild(paymentMethod);
        } else {
            document.querySelector("input[name=paymentMethodId]").value = response[0].id;
        }
    }
};

addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'keyup', guessingPaymentMethod);
addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'change', guessingPaymentMethod);


//gerar token do cartão
doSubmit = false;
addEvent(document.querySelector('#pay'),'submit',doPay);
function doPay(event){
    event.preventDefault();
    if(!doSubmit){
        var $form = document.querySelector('#pay');
        
        Mercadopago.createToken($form, sdkResponseHandler); // The function "sdkResponseHandler" is defined below

        return false;
    }
    if(doSubmit==true){
        location.reload();
    }
};


//verificar dados preenchidos e inserir token no form
function sdkResponseHandler(status, response) {
    if (status != 200 && status != 201) {
        alert("Preencha os campos corretamente");
    }else{
       
        var form = document.querySelector('#pay');

        var card = document.createElement('input');
        card.setAttribute('name',"token");
        card.setAttribute('type',"hidden");
        card.setAttribute('value',response.id);
        form.appendChild(card);
        doSubmit=true;
        // alert(card.getAttribute('value'));
        // enviar formulario con ajax
         var url = "pago.php"; // El script a dónde se realizará la petición.
         $( document ).ajaxStart(function() {
        $( "#loading" ).show();
        });
         $( document ).ajaxStop(function() {
        $( "#loading" ).hide();
        });
        $.ajax({
           type: "POST",
           url: url,
           data: $("#pay").serialize(), // Adjuntar los campos del formulario enviado.
           success: function(data)
           {
               //$("#respuesta").html(data); // Mostrar la respuestas del script PHP.
               //$("body").html(data); // Mostrar la respuestas del script PHP.
               $.modal(data);
           }
         });
        //form.submit();
    }
};


//mostra as parcelas e os bancos
function getBin() {
    var cardSelector = document.querySelector("#cardId");
    if (cardSelector && cardSelector[cardSelector.options.selectedIndex].value != "-1") {
        return cardSelector[cardSelector.options.selectedIndex].getAttribute('first_six_digits');
    }
    var ccNumber = document.querySelector('input[data-checkout="cardNumber"]');
    return ccNumber.value.replace(/[ .-]/g, '').slice(0, 6);
}

function guessingPaymentMethod(event) {
    var bin = getBin(),
        amount = document.querySelector('#amount').value;
    if (event.type == "keyup") {
        if (bin.length == 6) {
            Mercadopago.getPaymentMethod({
                "bin": bin,
                "amount": amount
            }, setPaymentMethodInfo);
        }
    } else {
        setTimeout(function() {
            if (bin.length >= 6) {
                Mercadopago.getPaymentMethod({
                    "bin": bin,
                    "amount": amount
                }, setPaymentMethodInfo);
            }
        }, 100);
    }
};

function setPaymentMethodInfo(status, response) {
    if (status == 200) {
        // do somethings ex: show logo of the payment method
        var form = document.querySelector('#pay');

        if (document.querySelector("input[name=paymentMethodId]") == null) {
            var paymentMethod = document.createElement('input');
            paymentMethod.setAttribute('name', "paymentMethodId");
            paymentMethod.setAttribute('type', "hidden");
            paymentMethod.setAttribute('value', response[0].id);
            form.appendChild(paymentMethod);
        } else {
            document.querySelector("input[name=paymentMethodId]").value = response[0].id;
        }

        // check if the security code (ex: Tarshop) is required
        var cardConfiguration = response[0].settings,
            bin = getBin(),
            amount = document.querySelector('#amount').value;

        for (var index = 0; index < cardConfiguration.length; index++) {
            if (bin.match(cardConfiguration[index].bin.pattern) != null && cardConfiguration[index].security_code.length == 0) {
                /*
                * In this case you do not need the Security code. You can hide the input.
                */
            } else {
                /*
                * In this case you NEED the Security code. You MUST show the input.
                */
            }
        }

        // check if the issuer is necessary to pay
        var issuerMandatory = false,
            additionalInfo = response[0].additional_info_needed;

        for (var i = 0; i < additionalInfo.length; i++) {
            if (additionalInfo[i] == "issuer_id") {
                issuerMandatory = true;
            }
        };
    }
};

function showCardIssuers(status, issuers) {
    var issuersSelector = document.querySelector("#issuer"),
        fragment = document.createDocumentFragment();

    issuersSelector.options.length = 0;
    var option = new Option("Choose...", '-1');
    fragment.appendChild(option);

    for (var i = 0; i < issuers.length; i++) {
        if (issuers[i].name != "default") {
            option = new Option(issuers[i].name, issuers[i].id);
        } else {
            option = new Option("Otro", issuers[i].id);
        }
        fragment.appendChild(option);
    }
    issuersSelector.appendChild(fragment);
    issuersSelector.removeAttribute('disabled');
    document.querySelector("#issuer").removeAttribute('style');
};

function setInstallmentsByIssuerId(status, response) {
    var issuerId = document.querySelector('#issuer').value,
        amount = document.querySelector('#amount').value;

    if (issuerId === '-1') {
        return;
    }

    Mercadopago.getInstallments({
        "bin": getBin(),
        "amount": amount,
        "issuer_id": issuerId
    }, setInstallmentInfo);
};

function cardsHandler() {
    var cardSelector = document.querySelector("#cardId"),
        amount = document.querySelector('#amount').value;

    if (cardSelector && cardSelector[cardSelector.options.selectedIndex].value != "-1") {
        var _bin = cardSelector[cardSelector.options.selectedIndex].getAttribute("first_six_digits");
        Mercadopago.getPaymentMethod({
            "bin": _bin
        }, setPaymentMethodInfo);
    }
}

addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'keyup', guessingPaymentMethod);
//addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'keyup', clearOptions);
addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'change', guessingPaymentMethod);
cardsHandler();
</script>
</html>