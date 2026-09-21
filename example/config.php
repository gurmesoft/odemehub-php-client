<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/vendor/autoload.php';

use Gurmehub\Odemehub\Client;
use Gurmehub\Odemehub\Options;

/**
 * The gateway this example talks to. The team is the slug in the address bar
 * of the application, and the key and the secret are the pair issued to it,
 * found under Ayarlar > Entegrasyon.
 *
 * The channel is named here rather than on every request: it says which shop
 * or marketplace the customer reached this merchant through, and one
 * integration speaks for one of them. Its number is on the team's Kanallar
 * page. A merchant selling on more than one may still name another on a
 * single request.
 *
 * The values below are the ones the local database was seeded with. Every one
 * of them can be overridden from the environment without editing this file.
 */
function client(): Client
{
    return new Client(new Options(
        baseUrl: getenv('ODEMEHUB_BASE_URL') ?: 'http://localhost:8000',
        team: getenv('ODEMEHUB_TEAM') ?: '1',
        channelId: (int) (getenv('ODEMEHUB_CHANNEL_ID') ?: 1),
        apiKey: getenv('ODEMEHUB_API_KEY') ?: 'key_seeded_development_credential_do_not_use',
        apiSecret: getenv('ODEMEHUB_API_SECRET') ?: 'secret_seeded_development_credential_do_not_use',
    ));
}

/**
 * Where the gateway posts the outcome of a 3D payment back to. It is the
 * merchant's own address, reached by the customer's own browser, so it has to
 * be one that browser can open: serving this folder with
 * `php -S localhost:8080 -t example` makes the address below a real one.
 */
function callbackUrl(): string
{
    return getenv('ODEMEHUB_CALLBACK_URL') ?: 'http://localhost:8080/return.php';
}

/**
 * The payment accounts this example can pay through, as the local database
 * was seeded with them, each with one of the provider's own test cards.
 *
 * Only the providers whose flow the gateway actually carries are listed: an
 * account whose provider is not implemented yet is turned down before the
 * payment is even attempted. The numbers are the ones the seeder left
 * behind, so they follow the database rather than this file.
 *
 * @return array<string, array<string, string>>
 */
function accounts(): array
{
    return [
        'iyzico' => [
            'payment_provider_id' => '1',
            'card_number' => '5526080000000006',
            'card_security_code' => '000',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'secure_password' => '283126',
        ],
        'Akbank' => [
            'payment_provider_id' => '2',
            'card_number' => '4546711234567894',
            'card_security_code' => '000',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2026',
            'secure_password' => '',
        ],
        'İş Bankası' => [
            'payment_provider_id' => '3',
            'card_number' => '4546711234567894',
            'card_security_code' => '000',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2026',
            'secure_password' => '',
        ],
        'Paratika' => [
            'payment_provider_id' => '12',
            'card_number' => '4508034508034509',
            'card_security_code' => '000',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'secure_password' => '',
        ],
        'Sipay' => [
            'payment_provider_id' => '18',
            'card_number' => '4048095010857528',
            'card_security_code' => '000',
            'card_expiry_month' => '05',
            'card_expiry_year' => '2028',
            'secure_password' => '34020',
        ],
        'QNBPay' => [
            'payment_provider_id' => '20',
            'card_number' => '4022780520669303',
            'card_security_code' => '988',
            'card_expiry_month' => '01',
            'card_expiry_year' => '2050',
            'secure_password' => '',
        ],
        'Akbank JSON' => [
            'payment_provider_id' => '4',
            'card_number' => '4355093000777068',
            'card_security_code' => '941',
            'card_expiry_month' => '06',
            'card_expiry_year' => '2027',
            'secure_password' => '',
        ],
        'QNB Finansbank' => [
            'payment_provider_id' => '5',
            'card_number' => '4155650100416111',
            'card_security_code' => '123',
            'card_expiry_month' => '01',
            'card_expiry_year' => '2025',
            'secure_password' => '',
        ],
        'Garanti' => [
            'payment_provider_id' => '6',
            'card_number' => '4282209004348015',
            'card_security_code' => '123',
            'card_expiry_month' => '08',
            'card_expiry_year' => '2030',
            'secure_password' => '',
        ],
        'Kuveyt Türk' => [
            'payment_provider_id' => '8',
            'card_number' => '5188961939192544',
            'card_security_code' => '588',
            'card_expiry_month' => '06',
            'card_expiry_year' => '2029',
            'secure_password' => '123456',
        ],
        'Vakıfbank' => [
            'payment_provider_id' => '9',
            'card_number' => '5521010140829928',
            'card_security_code' => '691',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2029',
            'secure_password' => '123456',
        ],
        'EsnekPos' => [
            'payment_provider_id' => '13',
            'card_number' => '9792100000000001',
            'card_security_code' => '000',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2026',
            'secure_password' => '',
        ],
        'Lidio' => [
            'payment_provider_id' => '15',
            'card_number' => '5404355404355405',
            'card_security_code' => '001',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2026',
            'secure_password' => '',
        ],
        'İşyerimPOS' => [
            'payment_provider_id' => '25',
            'card_number' => '5818775818772285',
            'card_security_code' => '001',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2026',
            'secure_password' => 'a',
        ],
    ];
}
