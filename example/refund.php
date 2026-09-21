<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\Cancel;
use Gurmehub\Odemehub\Request\Refund;

/*
|--------------------------------------------------------------------------
| İade ve iptal
|--------------------------------------------------------------------------
|
| Ödemenin geçitteki numarası (transaction_id) yeterlidir: hangi hesaptan
| çekildiğini, hangi sağlayıcıya gittiğini ve sağlayıcının ödemeye verdiği
| referansı geçit zaten biliyor.
|
| İade tutarı verilmezse ödemenin iade edilebilir kalanının tamamı geri
| verilir; verilirse o kalandan büyük olamaz, geçit büyüğünü reddeder.
| İptalde tutar hiç gönderilmez, iptal her zaman ödemenin tamamıdır ve
| yalnızca sağlayıcı ödemeyi henüz kapatmadıysa yapılabilir.
|
*/

$result = null;
$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

if (isSubmitted()) {
    $transactionId = (int) posted('transaction_id');
    $amount = posted('amount');

    try {
        $result = posted('type') === 'cancel'
            ? client()->cancel(new Cancel($transactionId))
            : client()->refund(new Refund($transactionId, $amount === '' ? null : $amount));
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }
}

pageStart('İade ve iptal');

if ($result !== null) {
    giveBackResult($result);
} else {
    notice($message);
    giveBackForm($errors);
}

pageEnd();
