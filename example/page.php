<?php

declare(strict_types=1);

require_once __DIR__.'/config.php';

use Gurmehub\Odemehub\Request\Card;
use Gurmehub\Odemehub\Request\Customer;
use Gurmehub\Odemehub\Response\GiveBack;
use Gurmehub\Odemehub\Response\Payment;

/*
|--------------------------------------------------------------------------
| Örneklerin ortak parçaları
|--------------------------------------------------------------------------
|
| İki ödeme türü de aynı formu doldurur, aynı alanları POST eder ve sonucu
| aynı biçimde gösterir. Türden türe değişen tek şey isteğin kendisi olduğu
| için sayfa iskeleti, form ve gönderilen alanları okuma işi burada durur.
|
*/

/**
 * Formun açıldığı örnek değerler. Sipariş numarası her açılışta değişir ki
 * aynı numara iki kez gönderilmek zorunda kalınmasın.
 *
 * @return array<string, string>
 */
function dummy(): array
{
    return [
        'channel_reference' => (string) random_int(1000, 9999),
        'amount' => '100.50',
        'base_amount' => '',
        'currency' => 'TRY',
        'installment_number' => '1',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'payment_provider_id' => '',

        'customer_channel_reference' => 'musteri-1',
        'customer_firstname' => 'Ahmet',
        'customer_lastname' => 'Yılmaz',
        'customer_address' => 'Kızılırmak Mah. Dumlupınar Blv. No:3',
        'customer_district' => 'Çankaya',
        'customer_province' => 'Ankara',
        'customer_country' => 'Türkiye',
        'customer_email' => 'ahmet@ornek.com',
        'customer_phone' => '05550000000',

        'card_holder_name' => 'Ahmet Yılmaz',
        'card_number' => '5528790000000008',
        'card_security_code' => '123',
        'card_expiry_month' => '12',
        'card_expiry_year' => '2030',

        'callback_url' => callbackUrl(),
    ];
}

/**
 * Formun alanları, başlıkları altında. Alan adlarındaki alt çizgi, istek
 * gövdesindeki nokta yerine geçer: `customer_firstname` -> `customer.firstname`.
 *
 * @return array<string, array<string, string>>
 */
function fields(): array
{
    return [
        'Ödeme' => [
            'channel_reference' => 'Sipariş no',
            'amount' => 'Çekilecek tutar',
            'base_amount' => 'Satılan tutar (boş bırakılırsa çekilecek tutarla aynı)',
            'currency' => 'Para birimi',
            'installment_number' => 'Taksit',
            'ip' => 'Müşterinin IP adresi',
            'payment_provider_id' => 'Ödeme hesabı no',
        ],
        'Müşteri' => [
            'customer_channel_reference' => 'Müşteri no (sizdeki)',
            'customer_firstname' => 'Ad',
            'customer_lastname' => 'Soyad',
            'customer_email' => 'E-posta',
            'customer_phone' => 'Telefon',
            'customer_address' => 'Adres',
            'customer_district' => 'İlçe',
            'customer_province' => 'İl',
            'customer_country' => 'Ülke',
        ],
        'Kart' => [
            'card_holder_name' => 'Kart üzerindeki isim',
            'card_number' => 'Kart numarası',
            'card_security_code' => 'CVV',
            'card_expiry_month' => 'Son kullanma ayı',
            'card_expiry_year' => 'Son kullanma yılı',
        ],
    ];
}

/**
 * Boş bırakılabilen alanlar: ödeme hesabı verilmezse firmanın varsayılanı,
 * para birimi verilmezse lira kullanılır.
 *
 * @return list<string>
 */
function optionalFields(): array
{
    return ['payment_provider_id', 'currency'];
}

/**
 * Bir alanın gösterilecek değeri: form geri geldiyse gönderilen değer,
 * ilk açılışta örnek değer.
 */
function value(string $field): string
{
    return isset($_POST[$field]) ? posted($field) : (dummy()[$field] ?? '');
}

/**
 * Gönderilen bir alanın değeri.
 */
function posted(string $field): string
{
    return trim((string) ($_POST[$field] ?? ''));
}

/**
 * Formdaki müşteri.
 */
function postedCustomer(): Customer
{
    return new Customer(
        channelReference: posted('customer_channel_reference'),
        firstname: posted('customer_firstname'),
        lastname: posted('customer_lastname'),
        address: posted('customer_address'),
        district: posted('customer_district'),
        province: posted('customer_province'),
        country: posted('customer_country'),
        email: posted('customer_email'),
        phone: posted('customer_phone'),
    );
}

/**
 * Formdaki kart.
 */
function postedCard(): Card
{
    return new Card(
        holderName: posted('card_holder_name'),
        number: posted('card_number'),
        securityCode: posted('card_security_code'),
        expiryMonth: posted('card_expiry_month'),
        expiryYear: posted('card_expiry_year'),
    );
}

/**
 * Her iki ödeme türünün de ortak aldığı alanlar, istek nesnesine adlarıyla
 * açılmak üzere: `new RegularPayment(...postedPayment())`.
 *
 * @return array<string, mixed>
 */
function postedPayment(): array
{
    return [
        'channelReference' => (int) posted('channel_reference'),
        'amount' => posted('amount'),
        'baseAmount' => posted('base_amount') === '' ? null : posted('base_amount'),
        'installmentNumber' => (int) posted('installment_number'),
        'ip' => posted('ip'),
        'customer' => postedCustomer(),
        'card' => postedCard(),
        'currency' => posted('currency') === '' ? null : posted('currency'),
        'paymentProviderId' => posted('payment_provider_id') === '' ? null : (int) posted('payment_provider_id'),
    ];
}

/**
 * Sayfaya form gönderildi mi.
 */
function isSubmitted(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function pageStart(string $title): void
{
    header('Content-Type: text/html; charset=utf-8');

    echo '<!doctype html><html lang="tr"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>'.e($title).' — Ödemehub örneği</title>';
    echo '<style>
        :root { color-scheme: light dark; }
        body { margin: 0; padding: 2rem 1rem; font: 15px/1.5 system-ui, sans-serif; }
        main { max-width: 46rem; margin: 0 auto; }
        h1 { font-size: 1.4rem; margin: 0 0 1.5rem; }
        h2 { font-size: .95rem; margin: 1.75rem 0 .75rem; opacity: .6; }
        a { color: inherit; }
        p.lead { opacity: .7; margin: -1rem 0 1.5rem; }
        .actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.75rem; }
        .button { display: inline-block; padding: .7rem 1.2rem; border: 1px solid currentColor; border-radius: .5rem; text-decoration: none; font: inherit; font-weight: 600; cursor: pointer; background: none; color: inherit; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); gap: 1rem; }
        label { display: block; font-size: .8rem; opacity: .7; margin-bottom: .25rem; }
        .required { color: #dc2626; }
        input { width: 100%; box-sizing: border-box; padding: .5rem; border: 1px solid; border-radius: .375rem; background: none; color: inherit; font: inherit; }
        .invalid input { border-color: #dc2626; }
        .invalid label { opacity: 1; color: #dc2626; }
        .error { font-size: .8rem; color: #dc2626; margin: .25rem 0 0; }
        .notice { padding: .75rem 1rem; border: 1px solid #dc2626; border-radius: .5rem; margin-bottom: 1.5rem; }
        .notice.ok { border-color: #16a34a; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: .4rem .5rem; border-bottom: 1px solid; vertical-align: top; }
        td:first-child { opacity: .6; width: 12rem; }
    </style></head><body><main>';
    echo '<h1>'.e($title).'</h1>';
}

function pageEnd(): void
{
    echo '</main></body></html>';
}

/**
 * Sayfanın başındaki tek satırlık bildirim: ödeme geçidinin reddi ya da
 * sonucun kendi mesajı.
 */
function notice(?string $message, bool $successful = false): void
{
    if ($message === null || $message === '') {
        return;
    }

    echo '<p class="notice'.($successful ? ' ok' : '').'">'.e($message).'</p>';
}

/**
 * Bir alanın adının istek gövdesindeki karşılığı; alan hataları geçitten o
 * adla gelir.
 */
function parameter(string $field): string
{
    return preg_replace('/^(customer|card)_/', '$1.', $field) ?? $field;
}

/**
 * Kart sormayan sayfaların formu: başlıklar altında düz alanlar, her biri
 * etiketi ve formun açıldığı değerle. Geçitten dönen alan hataları hem
 * alanın kendi adıyla hem de gövdedeki noktalı adıyla aranır, çünkü aynı
 * alan `order.channel_reference` gibi bir grubun altında da olabilir.
 *
 * @param  array<string, array<string, array{0: string, 1: string}>>  $sections
 * @param  array<string, list<string>>  $errors
 */
function sections(array $sections, array $errors, string $submitLabel): void
{
    echo '<form method="post">';

    foreach ($sections as $section => $labels) {
        echo '<h2>'.e($section).'</h2><div class="grid">';

        foreach ($labels as $field => [$label, $default]) {
            $error = $errors[$field][0] ?? $errors[parameter($field)][0] ?? null;
            $value = isset($_POST[$field]) ? posted($field) : $default;

            echo '<div'.($error === null ? '' : ' class="invalid"').'>';
            echo '<label for="'.e($field).'">'.e($label).'</label>';
            echo '<input id="'.e($field).'" name="'.e($field).'" value="'.e($value).'">';

            if ($error !== null) {
                echo '<p class="error">'.e($error).'</p>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    echo '<div class="actions">';
    echo '<button class="button" type="submit">'.e($submitLabel).'</button>';
    echo '<a class="button" href="index.php">Vazgeç</a>';
    echo '</div></form>';
}

/**
 * Müşterinin alanları, bütün bir müşteri taşıyan her formun sorduğu gibi:
 * etiketleri ve örnek değerleriyle.
 *
 * @return array<string, array{0: string, 1: string}>
 */
function customerSection(): array
{
    $customer = [];

    foreach (fields()['Müşteri'] as $field => $label) {
        $customer[$field] = [$label, dummy()[$field] ?? ''];
    }

    return $customer;
}

/**
 * Ödeme formu, dönen alan hatalarıyla birlikte. Türe özgü alanlar, kendi
 * başlıkları altında sona eklenir.
 *
 * @param  array<string, list<string>>  $errors
 * @param  array<string, array<string, string>>  $extraSections
 */
function paymentForm(array $errors = [], array $extraSections = []): void
{
    echo '<form method="post">';

    accountButtons();

    foreach ([...fields(), ...$extraSections] as $section => $labels) {
        echo '<h2>'.e($section).'</h2><div class="grid">';

        foreach ($labels as $field => $label) {
            $error = $errors[parameter($field)][0] ?? null;
            $required = in_array($field, optionalFields(), true) ? '' : ' required';

            echo '<div'.($error === null ? '' : ' class="invalid"').'>';
            echo '<label for="'.e($field).'">'.e($label).($required === '' ? '' : ' <span class="required">*</span>').'</label>';
            echo '<input id="'.e($field).'" name="'.e($field).'" value="'.e(value($field)).'"'.$required.'>';

            if ($error !== null) {
                echo '<p class="error">'.e($error).'</p>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    echo '<div class="actions">';
    echo '<button class="button" type="submit">Ödeme yap</button>';
    echo '<a class="button" href="index.php">Vazgeç</a>';
    echo '</div></form>';
}

/**
 * Hangi ödeme hesabıyla deneneceğini seçen düğmeler. Seçilen hesabın
 * numarası ve o sağlayıcının kendi test kartı forma yazılır; kart alanları
 * elle de değiştirilebilir, düğme yalnızca doldurur.
 */
function accountButtons(): void
{
    echo '<h2>Test hesabı</h2><div class="actions">';

    foreach (accounts() as $label => $account) {
        $password = $account['secure_password'];
        unset($account['secure_password']);

        echo '<button type="button" class="button" data-account="'.e(json_encode($account)).'"';
        echo $password === '' ? '' : ' title="3D şifresi: '.e($password).'"';
        echo '>'.e($label).'</button>';
    }

    echo '</div>';

    echo '<script>
        document.querySelectorAll("[data-account]").forEach((button) => {
            button.addEventListener("click", () => {
                Object.entries(JSON.parse(button.dataset.account)).forEach(([name, value]) => {
                    const field = document.querySelector(`[name="${name}"]`);

                    if (field) {
                        field.value = value;
                    }
                });
            });
        });
    </script>';
}

/**
 * Bir ödemenin sonucu, geçidin bildirdiği gibi. Reddedilmiş bir ödeme de bir
 * sonuçtur, hata değildir.
 *
 * @param  array<string, ?string>  $extra  Türe özgü alanlar, örneğin payment_id.
 */
function paymentResult(Payment $payment, array $extra = []): void
{
    notice($payment->result->message, $payment->result->successful);

    echo '<table>';
    echo '<tr><td>result.successful</td><td>'.var_export($payment->result->successful, true).'</td></tr>';
    echo '<tr><td>transaction.id</td><td>'.$payment->transactionId.'</td></tr>';
    echo '<tr><td>transaction.channel_id</td><td>'.$payment->channelId.'</td></tr>';
    echo '<tr><td>transaction.channel_reference</td><td>'.e($payment->channelReference).'</td></tr>';
    echo '<tr><td>customer.channel_reference</td><td>'.e($payment->customerChannelReference).'</td></tr>';

    if ($payment->savedCard !== null) {
        echo '<tr><td>saved_card.id</td><td>'.$payment->savedCard->id.'</td></tr>';
        echo '<tr><td>saved_card</td><td>'.e($payment->savedCard->firstEightDigit.'****'.$payment->savedCard->lastFourDigit).'</td></tr>';
    }

    foreach ($extra as $field => $extraValue) {
        echo '<tr><td>'.e($field).'</td><td>'.e($extraValue ?? '-').'</td></tr>';
    }

    echo '</table>';
    echo '<div class="actions"><a class="button" href="index.php">Yeni ödeme</a></div>';
}

/**
 * İade/iptal formu. Ödemenin geçitteki numarası yeterlidir; hesabı,
 * sağlayıcıyı ve sağlayıcının ödemeye verdiği referansı geçit zaten bilir.
 *
 * Tutar boş bırakılabilir: o zaman ödemenin iade edilebilir kalanının tamamı
 * geri verilir. İptalde tutar hiç gönderilmez, iptal her zaman tamamıdır.
 *
 * @param  array<string, list<string>>  $errors
 */
function giveBackForm(array $errors = []): void
{
    $labels = [
        'transaction_id' => 'İşlem numarası (transaction_id)',
        'amount' => 'Tutar (boş bırakılırsa kalanın tamamı)',
    ];

    echo '<form method="post">';
    echo '<h2>İade / iptal</h2><div class="grid">';

    foreach ($labels as $field => $label) {
        $error = $errors[$field][0] ?? null;
        $required = $field === 'transaction_id' ? ' required' : '';

        echo '<div'.($error === null ? '' : ' class="invalid"').'>';
        echo '<label for="'.e($field).'">'.e($label).'</label>';
        echo '<input id="'.e($field).'" name="'.e($field).'" value="'.e(posted($field)).'"'.$required.'>';

        if ($error !== null) {
            echo '<p class="error">'.e($error).'</p>';
        }

        echo '</div>';
    }

    echo '</div>';

    echo '<div class="actions">';
    echo '<button class="button" type="submit" name="type" value="refund">İade et</button>';
    echo '<button class="button" type="submit" name="type" value="cancel">İptal et</button>';
    echo '<a class="button" href="index.php">Vazgeç</a>';
    echo '</div></form>';
}

/**
 * İade ya da iptalin sonucu. Sağlayıcının reddettiği bir deneme de bir
 * sonuçtur, hata değildir.
 */
function giveBackResult(GiveBack $result): void
{
    notice($result->result->message, $result->result->successful);

    echo '<table>';
    echo '<tr><td>result.successful</td><td>'.var_export($result->result->successful, true).'</td></tr>';
    echo '<tr><td>transaction.id</td><td>'.$result->transactionId.'</td></tr>';
    echo '<tr><td>transaction.channel_reference</td><td>'.e($result->channelReference).'</td></tr>';
    echo '<tr><td>refund.type</td><td>'.e($result->type).'</td></tr>';
    echo '<tr><td>refund.amount</td><td>'.e($result->amount ?? '-').'</td></tr>';
    echo '</table>';
    echo '<div class="actions"><a class="button" href="refund.php">Yeni iade</a><a class="button" href="index.php">Başa dön</a></div>';
}
