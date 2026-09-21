<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\RegularPayment;

/*
|--------------------------------------------------------------------------
| Normal ödeme
|--------------------------------------------------------------------------
|
| Kart doğrudan çekilir; müşteri hiçbir yere gitmez ve başarılı yanıt tahsil
| edilmiş ödeme demektir. Geçit isteği reddederse form, hatalarıyla birlikte
| geri gelir.
|
*/

$payment = null;
$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

if (isSubmitted()) {
    try {
        $payment = client()->regularPayment(new RegularPayment(...postedPayment()));
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }
}

pageStart('Normal ödeme');

if ($payment !== null) {
    paymentResult($payment);
} else {
    notice($message);
    paymentForm($errors);
}

pageEnd();
