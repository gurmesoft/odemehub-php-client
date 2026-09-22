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

Banka işini bitirince müşteri, tarayıcısı üzerinden `callbackUrl` adresinize döner. O POST **sonucu taşımaz**, yalnızca sonucun hazır olduğunu haber verir:

| Alan | Anlamı |
| --- | --- |
| `transaction_id` | ödemenin geçitteki numarası |
| `channel_reference` | sizin kendi referansınız |
| `successful` | `1` / `0` — yalnızca ipucu, **güvenilmez** |

Sonucu kendi imzalı bağlantınızdan sorun:

```php
use Gurmehub\Odemehub\Request\RetrievePayment;

$outcome = $client->payment(new RetrievePayment(
    transactionId: (int) $_POST['transaction_id'],
));

if ($outcome->result->successful) {
    // siparişi ödendi olarak işaretleyin
}
```

Neden böyle: o POST'u bizim sunucumuz değil, müşterinin tarayıcısı gönderir; tarayıcıya imzalayacak bir sır verilemez. `successful` alanına bakıp sipariş kapatmayın — onu herkes gönderebilir; yalnız "başarısız" ipucunda gereksiz sorgudan kaçınmak için kullanın. Geçide sorduğunuz yanıt ise her zaman imzalıdır ve SDK imzayı sizin için doğrular. Başkasının işlemini sorarsanız `ValidationException` alırsınız.

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

Ödeme tamamlanınca müşteri, 3D'dekiyle aynı biçimde `successUrl` adresinize döner: aynı üç alan gelir, sonucu yine `payment()` ile sorarsınız. Müşteri ödeme sayfasında karttan kaynaklı bir hata alırsa size dönmez, sayfada kalıp başka kartla dener.

## Abonelikler

Müşteriden dönem dönem tahsilat yapmak için abonelik açarsınız. Neye abone olunduğu panelde tanımladığınız **abonelik ürünüdür** (Ürünler sayfası); tutarı, para birimini ve dönemini ürün taşır, burada göndermezsiniz.

```php
use Gurmehub\Odemehub\Request\Subscription;

$subscription = $client->subscriptionPayment(new Subscription(
    productId: 7,
    channelReference: 'UYELIK-4471',
    successUrl: 'https://magazam.com/tesekkurler',
    customer: $customer,
));

header('Location: '.$subscription->checkoutUrl);
```

İlk ödeme her zaman geçidin kendi sayfasında yapılır ve kart zorunlu olarak saklanır: sonraki dönemler o karttan çekilir. Ödeme tamamlanınca müşteri `successUrl` adresinize döner ve sonucu yine `payment()` ile sorarsınız; abonelik `active` olur ve aşağıdaki bildirim de gider.

Dönem bitince yeni dönem açılır ve müşterinin varsayılan kartından çekilir. Banka kabul etmezse çekim bir buçuk gün içinde beş kez denenir (araları 3, 6, 9 ve 12 saat); bu sırada abonelik `active` kalır. Beşinci deneme de olmazsa abonelik `past_due` olur ve müşteriye, o dönemi dilediği kartla ödeyebileceği bağlantı e-postayla gider. Süre sınırı yoktur; müşteri ödediği anda abonelik kaldığı yerden devam eder.

Aboneliğin durumunu sorabilirsiniz:

```php
use Gurmehub\Odemehub\Request\RetrieveSubscription;

$subscription = $client->subscription(new RetrieveSubscription(subscriptionId: 41));

echo $subscription->status;      // pending | active | past_due | cancelled
echo $subscription->amount;      // 149.90 — içinde bulunulan dönemin fiyatı
echo $subscription->endsAt;      // sonraki tahsilat zamanı
echo $subscription->checkoutUrl; // ödenmemiş dönem varsa müşteriye verilecek adres

if ($subscription->isPastDue()) {
    // müşteriyi kendi ödeme sayfanızda uyarabilirsiniz
}
```

Tutar, aboneliğin **içinde bulunduğu dönemin** fiyatıdır. Ürünün fiyatını yükseltirseniz yürüyen dönem çekildiği fiyatta kalır, yeni fiyat sonraki dönemden itibaren işler.

İptalde ödenmiş günler yanmaz:

```php
use Gurmehub\Odemehub\Request\CancelSubscription;

$subscription = $client->cancelSubscription(new CancelSubscription(subscriptionId: 41));

$subscription->cancelledAt;  // iptal edildiği an
$subscription->endsAt;       // hizmetin süreceği son gün
$subscription->isCancelled(); // ödenmiş dönem sürüyorsa henüz false
```

Müşteri, ödediği dönemin sonuna kadar hizmeti almaya devam eder; o güne kadar abonelik `active` görünür, dönem bitince `cancelled` olur ve bir daha tahsilat yapılmaz. Ödenmemiş bir aboneliğin (ilk ödemesi yapılmamış ya da `past_due`) iptali hemen geçerlidir. İade yapılmaz.

Aboneliğin açılabilmesi için varsayılan ödeme hesabınızın kart saklayabiliyor olması gerekir; saklamayan bir hesapla açmaya çalışırsanız istek `subscription.payment_provider_id` alanında reddedilir.

### Abonelik bildirimleri (webhook)

Abonelik açarken `webhookUrl` verirseniz, aboneliğin durumu her değiştiğinde o adrese imzalı bir POST gönderilir. Gövde düz JSON'dur ve imza `X-Signature` başlığındadır — yani geçidin API yanıtlarıyla aynı yöntem.

```php
use Gurmehub\Odemehub\Request\Subscription;

$subscription = $client->subscriptionPayment(new Subscription(
    productId: 7,
    channelReference: 'UYELIK-4471',
    successUrl: 'https://magazam.com/tesekkurler',
    customer: $customer,
    webhookUrl: 'https://magazam.com/odemehub/abonelik',
));
```

Bildirimi karşılayan uçta gövdeyi ham okuyup imzayla birlikte SDK'ya verin:

```php
use Gurmehub\Odemehub\Exception\SignatureException;

try {
    $webhook = $client->subscriptionWebhook(
        file_get_contents('php://input'),
        $_SERVER['HTTP_X_SIGNATURE'] ?? null,
    );
} catch (SignatureException $exception) {
    http_response_code(400);
    exit;
}

$subscription = $webhook->subscription;   // sorgudakiyle aynı nesne

match (true) {
    $webhook->isActive() => aboneligiAc($subscription->channelReference, $subscription->endsAt),
    $webhook->isPastDue() => musteriyiUyar($subscription->checkoutUrl),
    $webhook->isCancelled() => yenilemeyiDurdur($subscription->endsAt),
    $webhook->isEnded() => erisimiKapat($subscription->channelReference),
};

http_response_code(200);
```

Gönderilen olaylar aboneliğin **durumudur**, yapılan işlem değil:

| Olay | Ne zaman gider |
| --- | --- |
| `active` | bir dönem ödendi (ilk ödeme ya da yenileme) |
| `past_due` | dönem kayıtlı karttan tahsil edilemedi, müşteriden bekleniyor |
| `cancelled` | abonelik iptal edildi; müşteri `endsAt` tarihine kadar hizmeti almaya devam eder |
| `ended` | ödenmiş dönem doldu, abonelik kapandı |

2xx dışında bir yanıt (ya da yanıtsızlık) başarısız sayılır; bildirim 5 dakika sonra bir kez daha denenir. Ulaşmayan bildirimler panelde aboneliğin sayfasında HTTP kodu ve yanıtıyla listelenir.

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
