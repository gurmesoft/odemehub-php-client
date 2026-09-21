<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\DefaultSavedCard;
use Gurmehub\Odemehub\Request\DeleteSavedCard;
use Gurmehub\Odemehub\Request\NamedCustomer;
use Gurmehub\Odemehub\Request\SavedCards;

/*
|--------------------------------------------------------------------------
| Kayıtlı kartlar
|--------------------------------------------------------------------------
|
| Müşterinin sakladığı kartlar listelenir, biri varsayılan yapılır ya da
| bırakılır. Müşteri yalnızca adıyla anılır: geldiği kanal (istemcide) ve
| sizin ona verdiğiniz numara. Hiç görülmemiş bir müşteri burada açılmaz,
| geçit onu bulamadığını söyler.
|
| Kart saklamanın kendisi ödemeyle birlikte olur ("kartımı kaydet") ya da
| save-card uç noktasıyla tek başına; listeleme kartı çekebilecek bir şey
| döndürmez, kart yalnızca numarasıyla anılır.
|
*/

$cards = null;
$message = null;
$successful = false;

/** @var array<string, list<string>> $errors */
$errors = [];

$customerReference = posted('customer_channel_reference');

if (isSubmitted() && $customerReference !== '') {
    $customer = new NamedCustomer($customerReference);

    try {
        $savedCardId = (int) posted('saved_card_id');

        if (posted('action') === 'default' && $savedCardId > 0) {
            $done = client()->defaultSavedCard(new DefaultSavedCard($customer, $savedCardId));
            $message = $done->result->message ?? 'Varsayılan kart güncellendi.';
            $successful = $done->result->successful;
        }

        if (posted('action') === 'delete' && $savedCardId > 0) {
            $done = client()->deleteSavedCard(new DeleteSavedCard($customer, $savedCardId));
            $message = $done->result->message ?? 'Kart silindi.';
            $successful = $done->result->successful;
        }

        $cards = client()->savedCards(new SavedCards($customer));
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }
}

pageStart('Kayıtlı kartlar');

echo '<p class="lead">Müşterinin sakladığı kartlar; kanal istemcide tanımlıdır.</p>';

notice($message, $successful);

echo '<form method="post">';
echo '<h2>Müşteri</h2><div class="grid"><div'.(isset($errors['customer.channel_reference']) ? ' class="invalid"' : '').'>';
echo '<label for="customer_channel_reference">Müşteri no (sizdeki)</label>';
echo '<input id="customer_channel_reference" name="customer_channel_reference" value="'.e($customerReference === '' ? (dummy()['customer_channel_reference'] ?? '') : $customerReference).'" required>';

if (isset($errors['customer.channel_reference'][0])) {
    echo '<p class="error">'.e($errors['customer.channel_reference'][0]).'</p>';
}

echo '</div></div>';
echo '<div class="actions"><button class="button" type="submit">Kartları getir</button>';
echo '<a class="button" href="index.php">Başa dön</a></div></form>';

if ($cards !== null) {
    echo '<h2>Kartlar ('.count($cards->savedCards).')</h2>';

    if ($cards->savedCards === []) {
        echo '<p class="lead">Bu müşterinin kayıtlı kartı yok.</p>';
    }

    foreach ($cards->savedCards as $card) {
        echo '<form method="post"><table>';
        echo '<tr><td>id</td><td>'.$card->id.'</td></tr>';
        echo '<tr><td>kart</td><td>'.e($card->firstEightDigit.'****'.$card->lastFourDigit).'</td></tr>';
        echo '<tr><td>şema</td><td>'.e($card->scheme ?? '-').'</td></tr>';
        echo '<tr><td>son kullanma</td><td>'.e($card->expiryMonth.'/'.$card->expiryYear).'</td></tr>';
        echo '<tr><td>ödeme hesabı</td><td>'.e((string) ($card->paymentProviderId ?? '-')).'</td></tr>';
        echo '<tr><td>varsayılan</td><td>'.var_export($card->isDefault, true).'</td></tr>';
        echo '</table>';

        echo '<input type="hidden" name="customer_channel_reference" value="'.e($customerReference).'">';
        echo '<input type="hidden" name="saved_card_id" value="'.$card->id.'">';
        echo '<div class="actions">';

        if (! $card->isDefault) {
            echo '<button class="button" type="submit" name="action" value="default">Varsayılan yap</button>';
        }

        echo '<button class="button" type="submit" name="action" value="delete">Sil</button>';
        echo '</div></form>';
    }
}

pageEnd();
