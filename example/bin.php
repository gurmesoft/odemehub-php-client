<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\RetrieveBin;

/*
|--------------------------------------------------------------------------
| Kart sorgulama
|--------------------------------------------------------------------------
|
| Kart numarasının ilk haneleriyle kartın bankası, tipi ve tutarın kaç
| taksitle ödenebileceği sorulur. Hiçbir şey çekilmez, hiçbir şey kaydedilmez;
| müşteri kart numarasını yazarken bankayı ve taksit seçeneklerini
| gösterebilmek içindir.
|
| Kartın tamamı gönderilmez. Altı hane bankaların kendi tablolarını tuttuğu
| uzunluktur, sekiz hane geçidin sakladığı kartlardan bildiğiniz uzunluktur;
| ikisi de kabul edilir.
|
| Her ödeme hesabı bu soruya yanıt vermez. Vermeyen bir hesap sorulduğunda
| geçit "bu ödeme hesabı kart sorgulamayı desteklemiyor" der.
|
*/

$bin = null;
$message = null;
$successful = false;

/** @var array<string, list<string>> $errors */
$errors = [];

$digits = posted('bin');
$amount = posted('amount');

if (isSubmitted()) {
    try {
        $bin = client()->retrieveBin(new RetrieveBin(
            bin: $digits,
            amount: $amount,
            paymentProviderId: ((int) posted('payment_provider_id')) ?: null,
        ));

        $message = $bin->result->message ?? 'Kart sorgulandı.';
        $successful = $bin->result->successful;
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }
}

pageStart('Kart sorgulama');

echo '<p class="lead">Kartın ilk haneleriyle bankasını, tipini ve taksit seçeneklerini sorar.</p>';

notice($message, $successful);

echo '<form method="post"><h2>Kart</h2><div class="grid">';

echo '<div'.(isset($errors['card.bin']) ? ' class="invalid"' : '').'>';
echo '<label for="bin">Kartın ilk haneleri</label>';
echo '<input id="bin" name="bin" value="'.e($digits === '' ? '454671' : $digits).'" required>';

if (isset($errors['card.bin'][0])) {
    echo '<p class="error">'.e($errors['card.bin'][0]).'</p>';
}

echo '</div>';

echo '<div'.(isset($errors['transaction.amount']) ? ' class="invalid"' : '').'>';
echo '<label for="amount">Tutar</label>';
echo '<input id="amount" name="amount" value="'.e($amount === '' ? (dummy()['amount'] ?? '') : $amount).'" required>';

if (isset($errors['transaction.amount'][0])) {
    echo '<p class="error">'.e($errors['transaction.amount'][0]).'</p>';
}

echo '</div>';

echo '<div'.(isset($errors['transaction.payment_provider_id']) ? ' class="invalid"' : '').'>';
echo '<label for="payment_provider_id">Ödeme hesabı (boşsa varsayılan)</label>';
echo '<input id="payment_provider_id" name="payment_provider_id" value="'.e(posted('payment_provider_id')).'">';

if (isset($errors['transaction.payment_provider_id'][0])) {
    echo '<p class="error">'.e($errors['transaction.payment_provider_id'][0]).'</p>';
}

echo '</div></div>';

echo '<div class="actions"><button class="button" type="submit">Kartı sorgula</button>';
echo '<a class="button" href="index.php">Başa dön</a></div></form>';

if ($bin !== null && $bin->result->successful) {
    echo '<h2>Kart</h2><table>';
    echo '<tr><td>banka</td><td>'.e($bin->issuerName ?? '-').'</td></tr>';
    echo '<tr><td>banka kodu</td><td>'.e($bin->issuerCode ?? '-').'</td></tr>';
    echo '<tr><td>şema</td><td>'.e($bin->scheme ?? '-').'</td></tr>';
    echo '<tr><td>tip</td><td>'.e($bin->type ?? '-').'</td></tr>';
    echo '<tr><td>program</td><td>'.e($bin->program ?? '-').'</td></tr>';
    echo '<tr><td>ticari kart</td><td>'.var_export($bin->isCommercial, true).'</td></tr>';
    echo '</table>';

    echo '<h2>Taksitler ('.count($bin->installments).')</h2>';

    if ($bin->installments === []) {
        echo '<p class="lead">Bu kart için taksit seçeneği dönmedi.</p>';
    } else {
        echo '<table>';

        foreach ($bin->installments as $installment) {
            echo '<tr><td>'.$installment->number.' taksit</td><td>'
                .e($installment->amount).' x '.$installment->number.' = '.e($installment->total).'</td></tr>';
        }

        echo '</table>';
    }
}

pageEnd();
