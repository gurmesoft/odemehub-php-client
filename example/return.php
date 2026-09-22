<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Request\RetrievePayment;

/*
|--------------------------------------------------------------------------
| 3D dönüşü ve ödeme sayfası dönüşü
|--------------------------------------------------------------------------
|
| Müşteri bankasından (ya da geçidin ödeme sayfasından) dönünce tarayıcısı
| buraya POST eder. Gelen alanlar sonucu değil, sonucun hazır olduğunu
| söyler: ödemenin numarası, kendi referansınız ve güvenilmez bir ipucu.
| Bunları gönderen bizim sunucumuz değil, müşterinin tarayıcısı olduğu için
| imzalanamazlar; sonucu geçide kendiniz sorarsınız ve o yanıt imzalıdır.
|
*/

pageStart('Ödeme sonucu');

$transactionId = (int) ($_POST['transaction_id'] ?? 0);

if ($transactionId === 0) {
    notice('Dönüşte işlem numarası yok.');
} else {
    try {
        paymentResult(client()->payment(new RetrievePayment(transactionId: $transactionId)));
    } catch (OdemehubException $exception) {
        notice($exception->getMessage());
    }
}

echo '<h2>Dönüşte gelen alanlar</h2>';
echo '<pre>'.e(print_r($_POST, true)).'</pre>';

pageEnd();
