<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;

/*
|--------------------------------------------------------------------------
| 3D dönüşü
|--------------------------------------------------------------------------
|
| Müşteri bankasından dönünce ödeme geçidi sonucu buraya POST eder. Gelen
| alanlara, imzası doğrulanmadan güvenilmez; `callback()` bunu yapar ve imza
| tutmazsa istisna atar.
|
*/

pageStart('Ödeme sonucu');

try {
    $result = client()->callback($_POST);

    paymentResult($result);
} catch (OdemehubException $exception) {
    notice($exception->getMessage());
}

echo '<h2>Gelen alanlar</h2>';
echo '<pre>'.e(print_r($_POST, true)).'</pre>';

pageEnd();
