<?php

require_once ('mercadopago.php');

//pegue suas credenciais no link https://www.mercadopago.com/mlb/account/credentials

$mp = new MP('TEST-3163357535105380-022813-bdcc9eeb05e2ac3476a0fd9c6f3f2000__LC_LA__-163664514'); //insira aqui o access token

$payment_data = array(
    "transaction_amount"   => 1000, //valor da compra
    "token"                => $_POST['token'], //token gerado pelo javascript da index.php
    "description"          => "Title of what you are paying for", //descrição da compra
    "installments"         => 1, //parcelas
    "payment_method_id"    => $_POST['paymentMethodId'], //forma de pagamento (visa, master, amex...)
    "payer"                => array ("email" => "test@testuser.com"), //e-mail do comprador
    "statement_descriptor" => "TuCarDealer.com", //nome para aparecer na fatura do cartão do cliente
    "additional_info"=>  array(
        "items"=> array(array(
                "id"=> "1234",
                "title"=> "Aqui coloca os itens do carrinho",
                "description"=> "Produto Teste novo",
                "picture_url"=> "https://google.com.br/images?image.jpg",
                "category_id"=> "others",
                "quantity"=> 1,
                "unit_price"=> round(600.504647,3)
            ),
        				array(
                "id"=> "1234",
                "title"=> "Aqui coloca os itens do carrinho",
                "description"=> "Produto Teste novo",
                "picture_url"=> "https://google.com.br/images?image.jpg",
                "category_id"=> "others",
                "quantity"=> 1,
                "unit_price"=> round(600.504647,3)
            )
        ),
        "payer"=>  array(
            "first_name"=> "Jose A",
            "last_name"=> "Ramirez S",
            "registration_date"=> "2014-06-28T16:53:03.176-04:00",
            "phone"=>  array(
                "area_code"=> "5511",
                "number"=> "3222-1000"
            ),
            "address"=>  array(
                "zip_code"=> "05303-090",
                "street_name"=> "Av. Queiroz Filho",
                "street_number"=> "213"
            )
        )
    ),
    "notification_url" => "http://agenciacreotuidea.com"
);

$payment = $mp->post("/v1/payments", $payment_data);

// echo "<pre>";
//print_r($payment);
echo "<center>";
//echo "Payment Status: ".$payment["response"]["status"];
$resultado_oper = $payment["response"]["status_detail"];

if ($resultado_oper=="accredited") {
echo "<h3 class='accredited'> ==== Pago acreditado ===== </h3>";
}
if ($resultado_oper=="pending_contingency") {
echo "<h3 class='pending'> ==== Pago pendiente ===== </h3>";
}
if ($resultado_oper=="cc_rejected_call_for_authorize") {
echo "<h3 class='denied'> ==== Pago rechazado - llamar para autorizar ===== </h3>";
}
if ($resultado_oper=="cc_rejected_insufficient_amount") {
echo "<h3 class='denied'> ==== Pago rechazado por monto insuficiente ===== </h3>";
}
if ($resultado_oper=="cc_rejected_bad_filled_security_code") {
echo "<h3 class='denied'> ==== Pago rechazado por código de seguridad ===== </h3>";
}
if ($resultado_oper=="cc_rejected_bad_filled_date") {
echo "<h3 class='denied'> ==== Pago rechazado por fecha de expiración ===== </h3>";
}
if ($resultado_oper=="cc_rejected_bad_filled_other") {
echo "<h3 class='denied'> ==== Pago rechazado por error en el formulario ===== </h3>";
}
if ($resultado_oper=="cc_rejected_other_reason") {
echo "<h3 class='denied'> ==== Pago rechazado por causa desconocida ===== </h3>";
}

//echo "Payment Status:" . $payment["response"]["status"] . " - " . $payment["response"]["status_detail"];
$resultado = $payment["response"]["status"];
if ($resultado=="approved") {
    echo "<img src='aprobado.png'";
}
else if ($resultado=="in_process") {
    echo "<img src='pendiente.png'";
}
else {
    echo "<img src='rechazado.png'";
}
echo "</center>";
//echo $payment["response"]["additional_info"]["items"][0]["description"];
?>