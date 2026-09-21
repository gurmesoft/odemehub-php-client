<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\SecurePayment;

/*
|--------------------------------------------------------------------------
| 3D Secure ödeme
|--------------------------------------------------------------------------
|
| Ödeme geçidi bankanın istediği formu kendisi saklar ve kendisi sunar; bize
| yalnızca müşteriyi göndereceğimiz adres döner. Ödeme başlamışsa müşteri
| oraya yönlendirilir; bankasında işini bitirince sonuç, formdaki dönüş
| adresine imzalı olarak POST edilir.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

if (isSubmitted()) {
    try {
        $payment = client()->securePayment(new SecurePayment(
            ...postedPayment(),
            callbackUrl: posted('callback_url'),
        ));

        if ($payment->result->successful) {
            header('Location: '.$payment->redirectUrl);

            exit;
        }

        $message = $payment->result->message;
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }
}

pageStart('3D Secure ödeme');

notice($message);
paymentForm($errors, ['Dönüş' => ['callback_url' => 'Dönüş adresi']]);

pageEnd();
