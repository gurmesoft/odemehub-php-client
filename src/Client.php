<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub;

use Gurmehub\Odemehub\Exception\AuthenticationException;
use Gurmehub\Odemehub\Exception\SignatureException;
use Gurmehub\Odemehub\Exception\TransportException;
use Gurmehub\Odemehub\Exception\UnexpectedResponseException;
use Gurmehub\Odemehub\Exception\ValidationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * The gateway, as this application talks to it. Every request leaves signed
 * with the team's secret and every answer is checked against it, so both
 * sides can tell the other really is who it says it is.
 *
 * The channel the merchant speaks for is named once, on the options, and
 * put into each request wherever that endpoint expects it.
 */
final class Client
{
    private readonly ClientInterface $http;

    private readonly Signature $signature;

    public function __construct(
        private readonly Options $options,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleClient(['timeout' => 60]);
        $this->signature = new Signature($options->apiSecret);
    }

    /**
     * Start a payment the customer confirms with their bank. A successful
     * answer is not a settled payment: the customer still has to be sent to
     * the address it comes back with.
     */
    public function securePayment(Request\SecurePayment $payment): Response\SecurePayment
    {
        return Response\SecurePayment::fromArray($this->send($payment));
    }

    /**
     * Charge a payment straight to the card. A successful answer is a
     * settled payment.
     */
    public function regularPayment(Request\RegularPayment $payment): Response\RegularPayment
    {
        return Response\RegularPayment::fromArray($this->send($payment));
    }

    /**
     * Open an order to be paid on the gateway's own page. Nothing is charged
     * here; the customer is sent to the address that comes back and pays
     * there, and the outcome is posted back the way any payment's is.
     */
    public function checkout(Request\Checkout $checkout): Response\Checkout
    {
        return Response\Checkout::fromArray($this->send($checkout));
    }

    /**
     * Open a subscription to be paid for the first time on the gateway's
     * own page. Nothing is charged here; the customer is sent to the
     * address that comes back and pays there, and the periods after that
     * are taken from the card they pay with.
     */
    public function subscriptionPayment(Request\Subscription $subscription): Response\Subscription
    {
        return Response\Subscription::fromArray($this->send($subscription));
    }

    /**
     * Where a subscription stands: what it is for, the period it is on and
     * whether that period has been paid for.
     */
    public function subscription(Request\RetrieveSubscription $subscription): Response\Subscription
    {
        return Response\Subscription::fromArray($this->send($subscription));
    }

    /**
     * Call a subscription off. Nothing is given back: the customer keeps
     * the days they have already paid for and is served to the end of
     * them, and nothing is charged after that.
     */
    public function cancelSubscription(Request\CancelSubscription $subscription): Response\Subscription
    {
        return Response\Subscription::fromArray($this->send($subscription));
    }

    /**
     * How a payment went. A customer sent to their bank comes back to the
     * merchant with the payment's number and a hint at how it went; the
     * hint is worth nothing on its own, and this is the call that says
     * what really became of it.
     */
    public function payment(Request\RetrievePayment $payment): Response\Payment
    {
        return Response\Payment::fromArray($this->send($payment));
    }

    /**
     * Give money back out of a payment the provider has settled, whole or
     * in part. A refund that names no amount gives back everything the
     * payment has left in it.
     */
    public function refund(Request\Refund $refund): Response\GiveBack
    {
        return Response\GiveBack::fromArray($this->send($refund));
    }

    /**
     * Take back the whole of a payment the provider has not settled yet.
     * Anything less than the whole of it goes back as a refund instead.
     */
    public function cancel(Request\Cancel $cancel): Response\GiveBack
    {
        return Response\GiveBack::fromArray($this->send($cancel));
    }

    /**
     * Ask what the gateway's provider knows about a card from the head of
     * its number, and how the amount may be paid off on it. Nothing is
     * charged and nothing is written down.
     */
    public function retrieveBin(Request\RetrieveBin $retrieveBin): Response\Bin
    {
        return Response\Bin::fromArray($this->send($retrieveBin));
    }

    /**
     * Keep a card for a customer without making a payment on it.
     */
    public function saveCard(Request\SaveCard $saveCard): Response\KeptCard
    {
        return Response\KeptCard::fromArray($this->send($saveCard));
    }

    /**
     * The cards a customer has let the merchant keep, the default one first.
     */
    public function savedCards(Request\SavedCards $savedCards): Response\KeptCards
    {
        return Response\KeptCards::fromArray($this->send($savedCards));
    }

    /**
     * Let go of one of a customer's kept cards, at the provider and here.
     */
    public function deleteSavedCard(Request\DeleteSavedCard $deleteSavedCard): Response\KeptCard
    {
        return Response\KeptCard::fromArray($this->send($deleteSavedCard));
    }

    /**
     * Make one of a customer's kept cards the one they pay with unless they
     * say otherwise.
     */
    public function defaultSavedCard(Request\DefaultSavedCard $defaultSavedCard): Response\KeptCard
    {
        return Response\KeptCard::fromArray($this->send($defaultSavedCard));
    }

    /**
     * Read a word the gateway sent about a subscription: it is posted to
     * the address the subscription was opened with, as plain JSON signed
     * in the `X-Signature` header. Hand it the body exactly as it arrived,
     * character for character, together with the header; nothing in it is
     * believed until the signature is checked against the secret.
     *
     * Later kinds of word — one about a payment, say — will be read by
     * their own method, so this one says which it is about.
     *
     * @param  string  $payload  The request body, read raw: file_get_contents('php://input').
     * @param  string|null  $signature  The `X-Signature` header, as it arrived.
     *
     * @throws SignatureException
     */
    public function subscriptionWebhook(string $payload, ?string $signature): Response\SubscriptionWebhook
    {
        if (! $this->signature->verify($payload, $signature)) {
            throw new SignatureException('Bildirimin imzası doğrulanamadı; bildirim ödeme geçidinden gelmemiş olabilir.');
        }

        return Response\SubscriptionWebhook::fromArray($this->decode($payload, 0));
    }

    /**
     * Sign what is being asked for, hand it to the gateway and read the
     * answer back. The body is signed exactly as it is sent, character for
     * character, so it is written once and used for both.
     *
     * @return array<string, mixed>
     */
    private function send(Request\Message $message): array
    {
        try {
            $body = json_encode($message->toArray($this->options->channelId), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new UnexpectedResponseException('İstek gövdesi JSON olarak yazılamadı: '.$exception->getMessage(), 0, $exception);
        }

        try {
            $response = $this->http->request('POST', $this->options->url($message->path()), [
                'headers' => [
                    Options::API_KEY_HEADER => $this->options->apiKey,
                    Signature::HEADER => $this->signature->sign($body),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => $body,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $exception) {
            throw new TransportException('Ödeme geçidine ulaşılamadı: '.$exception->getMessage(), previous: $exception);
        }

        return $this->read($response);
    }

    /**
     * Read the answer. An outcome is answered with 200 and signed, however
     * the payment itself turned out: a payment the provider declined is an
     * outcome like any other and comes back rather than being raised.
     *
     * Anything else is a refusal — the request never became a payment — and
     * the status says which kind. The gateway signs some of those too, but
     * a signature does not make a refusal an outcome, so the status is read
     * first.
     *
     * @return array<string, mixed>
     */
    private function read(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $payload = (string) $response->getBody();
        $signature = $response->getHeaderLine(Signature::HEADER);

        if ($status === 200) {
            if (! $this->signature->verify($payload, $signature === '' ? null : $signature)) {
                throw new SignatureException('Yanıtın imzası doğrulanamadı; yanıt ödeme geçidinden gelmemiş olabilir.');
            }

            return $this->decode($payload, $status);
        }

        [$message, $errors] = $this->refusal($payload);

        throw match ($status) {
            401 => new AuthenticationException($message),
            422 => new ValidationException($message, $errors),
            default => new UnexpectedResponseException($message, $status),
        };
    }

    /**
     * What a refusal says. The gateway answers in the one shape it answers
     * everything in, so what went wrong and which fields it was about are
     * found under `result`.
     *
     * @return array{0: string, 1: array<string, list<string>>}
     */
    private function refusal(string $payload): array
    {
        $body = json_decode($payload, true);
        $result = is_array($body) && is_array($body['result'] ?? null) ? $body['result'] : [];

        $message = match (true) {
            is_string($result['message'] ?? null) => $result['message'],
            is_array($body) && is_string($body['message'] ?? null) => $body['message'],
            default => 'Ödeme geçidi isteği reddetti.',
        };

        $errors = match (true) {
            is_array($result['errors'] ?? null) => $result['errors'],
            is_array($body) && is_array($body['errors'] ?? null) => $body['errors'],
            default => [],
        };

        return [$message, $errors];
    }

    /**
     * Read a body the signature has already vouched for.
     *
     * @return array<string, mixed>
     */
    private function decode(string $payload, int $status): array
    {
        $body = json_decode($payload, true);

        if (! is_array($body)) {
            throw new UnexpectedResponseException("Ödeme geçidi {$status} durumuyla okunamayan bir yanıt döndü.", $status);
        }

        return $body;
    }
}
