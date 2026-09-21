<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

/*
|--------------------------------------------------------------------------
| Örneklerin girişi
|--------------------------------------------------------------------------
|
| Geçidin yaptığı her iş için bir sayfa. Üç ödeme türü aynı formu örnek
| verilerle açar; fark, geçidin kartı doğrudan çekmesi, müşteriyi bankasına
| göndermesi ve kartı kendi sayfasında sorması arasındadır.
|
| Bu klasör `php -S localhost:8080 -t example` ile sunulur; hem 3D dönüşünün
| hem de ödeme sayfasının sonucu (config.php'deki callbackUrl) bu sunucuya
| POST edilir ve ikisini de return.php okur.
|
*/

pageStart('Ödemehub örneği');

echo '<p class="lead">Ödeme geçidine gerçek bir istek atılır. Kimlik bilgileri ve kanal config.php dosyasındadır.</p>';

echo '<h2>Ödeme</h2><div class="actions">';
echo '<a class="button" href="regular.php">Normal ödeme</a>';
echo '<a class="button" href="secure.php">3D Secure ödeme</a>';
echo '<a class="button" href="checkout.php">Ödeme sayfası</a>';
echo '</div>';

echo '<h2>Sonrası</h2><div class="actions">';
echo '<a class="button" href="refund.php">İade / iptal</a>';
echo '<a class="button" href="cards.php">Kayıtlı kartlar</a>';
echo '<a class="button" href="bin.php">Kart sorgulama</a>';
echo '</div>';

pageEnd();
