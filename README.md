# Ödemehub PHP SDK

Ödemehub ödeme geçidini kendi uygulamanızdan kullanmak için hazırlanmış PHP istemcisi. Kart çekmek, 3D ödeme başlatmak, müşteriyi ödeme sayfasına yollamak, kart saklamak, iade ve iptal yapmak ve bir kartın taksit seçeneklerini sormak için gereken her şey burada.

İstemci her isteği takımınızın gizli anahtarıyla imzalar, gelen her yanıtın imzasını doğrular. Siz imza, başlık ya da JSON ayrıntılarıyla uğraşmazsınız.

## Kurulum

PHP 8.2 ve üzeri gerekir.

```bash
composer require gurmehub/odemehub
```

## Yapılandırma

Dört bilgiye ihtiyacınız var. API anahtarı ve gizli anahtar panelde **Ayarlar → Entegrasyon** sayfasında, kanal numarası **Ayarlar → Kanallar** sayfasındadır. Takım kısa adı panel adresinizde görünür.

```php
use Gurmehub\Odemehub\Client;
use Gurmehub\Odemehub\Options;

$client = new Client(new Options(
    baseUrl: 'https://odeme.gurmehub.com',
    team: '42',                      // panel adresindeki takım kısa adı
    channelId: 7,                    // müşterinin size ulaştığı kanal
    apiKey: getenv('ODEMEHUB_API_KEY'),
    apiSecret: getenv('ODEMEHUB_API_SECRET'),
));
```

Gizli anahtar hiçbir zaman tel üzerinden gitmez; yalnızca imza üretmekte kullanılır. Anahtarları kodun içine yazmayın, ortam değişkeninde tutun.

Kanal numarası entegrasyonun tamamı için bir kez verilir. Birden çok kanalda satıyorsanız tek bir istekte `channelId` vererek o isteği başka kanala yazdırabilirsiniz.

## Karttan doğrudan çekim

Müşteriyi bankasına göndermeden çekim yapar. Başarılı yanıt, paranın alındığı anlamına gelir.

```php
use Gurmehub\Odemehub\Request\{Card, Customer, RegularPayment};

$payment = $client->regularPayment(new RegularPayment(
    channelReference: 10231,          // sizdeki sipariş numarası
    amount: '450.00',
    installmentNumber: 1,
    ip: $_SERVER['REMOTE_ADDR'],
    customer: new Customer(
        channelReference: 'musteri-88',
        firstname: 'Ahmet',
        lastname: 'Yılmaz',
        email: 'ahmet@ornek.com',
        phone: '05551112233',
        address: 'Kızılırmak Mah. Dumlupınar Blv. No:3',
        district: 'Çankaya',
        province: 'Ankara',
        country: 'Türkiye',
    ),
    card: new Card(
        holderName: 'AHMET YILMAZ',
        number: '5400360000000003',
        expiryMonth: '12',
        expiryYear: '2030',
        securityCode: '000',
    ),
));

if ($payment->result->successful) {
    // $payment->transactionId — ödemenin geçitteki numarası
}
```

## 3D ödeme

3D'de çekim iki adımdır: siz ödemeyi başlatırsınız, müşteri bankasına gider, banka sonucu sizin adresinize gönderir.

```php
use Gurmehub\Odemehub\Request\SecurePayment;

$payment = $client->securePayment(new SecurePayment(
    channelReference: 10232,
    amount: '450.00',
    installmentNumber: 1,
    ip: $_SERVER['REMOTE_ADDR'],
    callbackUrl: 'https://magazam.com/odeme/donus',
    customer: $customer,
    card: $card,
));

if ($payment->result->successful) {
    header('Location: '.$payment->redirectUrl);   // müşteriyi bankaya gönderin
}
```

Başarılı yanıt **ödeme alındı demek değildir**; yalnızca müşterinin gideceği adres hazır demektir.

Banka işini bitirince geçit, sonucu `callbackUrl` adresinize form olarak gönderir. Gelen postu olduğu gibi istemciye verin:

```php
$outcome = $client->callback($_POST);   // imza tutmazsa SignatureException atar

if ($outcome->result->successful) {
    // siparişi ödendi olarak işaretleyin
}
```

İmza doğrulanmadan hiçbir şeye inanmayın: `callback()` bunu sizin için yapar ve tutmazsa istisna atar.

## Ödeme sayfası

Kart bilgisini hiç görmek istemiyorsanız sipariş açıp müşteriyi geçidin kendi sayfasına yollayabilirsiniz.

```php
use Gurmehub\Odemehub\Request\{Checkout, OrderItem};

$order = $client->checkout(new Checkout(
    channelReference: 'SIPARIS-10233',
    amount: '450.00',
    successUrl: 'https://magazam.com/tesekkurler',
    cancelUrl: 'https://magazam.com/sepet',
    customer: $customer,
    items: [new OrderItem(name: 'Kahve makinesi', quantity: 1, unitAmount: '450.00')],
));

header('Location: '.$order->checkoutUrl);
```

Ödeme tamamlanınca sonuç, 3D'deki ile aynı biçimde `successUrl` adresinize gönderilir ve aynı `callback()` ile okunur.

## Kart sorgusu ve taksitler

Kart numarasının ilk hanelerinden kartın kim tarafından verildiğini, hangi programa ait olduğunu ve tutarın kaç taksite bölünebileceğini sorar. Hiçbir şey çekilmez.

```php
use Gurmehub\Odemehub\Request\RetrieveBin;

$bin = $client->retrieveBin(new RetrieveBin(bin: '54003600', amount: '450.00'));

if ($bin->result->successful) {
    echo $bin->issuerName;     // Garanti Bankası
    echo $bin->program;        // Bonus
    echo $bin->scheme;         // mastercard
    echo $bin->type;           // credit
    var_dump($bin->isCommercial);

    foreach ($bin->installments as $installment) {
        // 3 taksitte ayda 157.87, toplam 473.60
        echo "{$installment->number} x {$installment->amount} = {$installment->total}";
    }
}
```

Kartın tamamını göndermeyin; ilk 6-8 hane yeter ve yalnızca o kadarı kabul edilir.

Sorgu başarısız dönebilir: kart tanınmıyor olabilir ya da hesabınızın sağlayıcısı taksit vermiyor olabilir. İki durumda da satışı durdurmayın, tek çekimle devam edin.

## Tutarlar ve taksit

İki tutar vardır ve karıştırılmamalıdır:

| Alan | Anlamı |
| --- | --- |
| `amount` | **Karttan çekilecek** tutar. Vade farkı varsa içindedir. |
| `baseAmount` | **Sattığınız** tutar, vade farkından önceki hâli. Gönderilmezse `amount` ile aynı kabul edilir. |

Taksitsiz satışta ikisi eşittir ve `baseAmount` göndermenize gerek yoktur. Taksitli satışta `retrieveBin` size o taksidin toplamını verir; onu `amount` olarak, sattığınız tutarı `baseAmount` olarak gönderin:

```php
$payment = $client->regularPayment(new RegularPayment(
    channelReference: 10234,
    amount: '473.60',        // 3 taksitin toplamı
    baseAmount: '450.00',    // satılan tutar
    installmentNumber: 3,
    // ...
));
```

Bazı sağlayıcılar vade farkını kendileri ekler; geçit bunu bilir ve gerekirse sağlayıcıya taban tutarı gönderir. Sizin tarafınızda değişen bir şey yoktur.

## Kayıtlı kartlar

Müşterinin kartını saklayıp sonraki ödemelerde numara sormadan çekim yapabilirsiniz.

```php
use Gurmehub\Odemehub\Request\{DefaultSavedCard, DeleteSavedCard, NamedCustomer, SaveCard, SavedCards};

// Ödeme sırasında saklamak için: Card nesnesine shouldSave: true verin.
// Ödeme olmadan saklamak için:
$kept = $client->saveCard(new SaveCard(customer: $customer, card: $card));

$musteri = new NamedCustomer(channelReference: 'musteri-88');

// Müşterinin kartları
$cards = $client->savedCards(new SavedCards(customer: $musteri));

// Varsayılan yapma / silme
$client->defaultSavedCard(new DefaultSavedCard(customer: $musteri, savedCardId: 12));
$client->deleteSavedCard(new DeleteSavedCard(customer: $musteri, savedCardId: 12));
```

Kayıtlı kartla ödeme alırken `card` yerine kartın numarasını verin:

```php
$payment = $client->regularPayment(new RegularPayment(
    channelReference: 10235,
    amount: '120.00',
    installmentNumber: 1,
    ip: $_SERVER['REMOTE_ADDR'],
    customer: $customer,
    savedCardId: 12,
));
```

Kart saklayan bir ödemenin yanıtında `$payment->savedCard` dolu gelir; kartın numarasını oradan öğrenirsiniz.

## İade ve iptal

```php
use Gurmehub\Odemehub\Request\{Cancel, Refund};

// Gün sonu almamış ödemenin tamamını geri alır
$client->cancel(new Cancel(transactionId: 8821));

// Tutar verilirse kısmi, verilmezse kalanın tamamı iade edilir
$client->refund(new Refund(transactionId: 8821, amount: '100.00'));
```

## Hatalar

Bütün istisnalar `OdemehubException`'dan türer; tek bir `catch` hepsini yakalar.

| İstisna | Ne demek |
| --- | --- |
| `ValidationException` | Gönderdiğiniz alanlar kabul edilmedi. Ödeme denenmedi. `$e->errors` alan alan söyler. |
| `AuthenticationException` | API anahtarı bu takıma ait değil ya da imza gizli anahtarla tutmuyor. |
| `SignatureException` | Gelen yanıtın ya da bildirimin imzası tutmadı. Geçitten geldiği kanıtlanamaz; **işleme almayın**. |
| `TransportException` | Geçide ulaşılamadı ya da yanıt okunamadı. Ödemenin ne olduğu belirsizdir; geçitteki kayıt asıl doğruyu söyler. |
| `UnexpectedResponseException` | Beklenmeyen bir yanıt geldi. |

Ağ hatasında ödemeyi körlemesine tekrarlamayın: `TransportException` "olmadı" demek değil, "bilmiyorum" demektir.

## Örnekler

`example/` klasöründe çalışan küçük sayfalar var: kart çekimi, 3D, ödeme sayfası, kart sorgusu, kayıtlı kartlar, iade. Kendi anahtarlarınızı ortam değişkeniyle verip tarayıcıda açabilirsiniz:

```bash
cd example
ODEMEHUB_BASE_URL=https://odeme.gurmehub.com ODEMEHUB_TEAM=42 ODEMEHUB_CHANNEL_ID=7 ODEMEHUB_API_KEY=... ODEMEHUB_API_SECRET=... php -S localhost:8080
```

Sonra `http://localhost:8080/index.php` adresini açın. 3D denemesi yapacaksanız bankanın döneceği adresi de verin: `ODEMEHUB_CALLBACK_URL=http://localhost:8080/return.php`.

Ortam değişkeni vermezseniz örnekler yerel geliştirme kurulumuna bağlanır.
