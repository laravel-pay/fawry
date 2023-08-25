<?php

namespace LaravelPay\Fawry;

use Exception;
use Illuminate\Http\Request;
use LaravelPay\Fawry\Common\Contracts\RequiredFields;
use LaravelPay\Fawry\Common\Traits\HasRequiredFields;

class Fawry implements RequiredFields
{
    use HasRequiredFields;

    private $url;

    private $secret;

    private $merchant;

    private $verify_route_name;

    private $display_mode;

    private $pay_mode;

    private $locale;

    private $language;

    private $amount;

    private $user_id;

    private $user_first_name;

    private $user_last_name;

    private $user_email;

    private $user_phone;

    public function __construct()
    {
        $this->initializePaymentFawry();
    }

    private function initializePaymentFawry()
    {
        $this->setOnLiveMode();
        $this->setDisplayMode();
        $this->setPayMode();
        $this->setConfigurations();
    }

    private function setConfigurations()
    {
        $this->verify_route_name = config('payment-fawry.verify_route_name');
        $this->locale = config('payment-fawry.locale');
        $this->language = config('payment-fawry.language');
    }

    public function requiredFields(): array
    {
        return [
            'fawry_url' => $this->url,
            'fawry_merchant' => $this->merchant,
            'fawry_secret' => $this->secret,
            'fawry_display_mode' => $this->display_mode,
            'fawry_pay_mode' => $this->pay_mode,
            'fawry_amount' => $this->amount,
            'fawry_user_id' => $this->user_id,
            'fawry_user_first_name' => $this->user_first_name,
            'fawry_user_last_name' => $this->user_last_name,
            'fawry_user_email' => $this->user_email,
            'fawry_user_phone' => $this->user_phone,
        ];
    }

    private function setDisplayMode()
    {
        $display_mode = config('payment-fawry.display_mode');
        $allowedDisplayModes = ['POPUP', 'INSIDE_PAGE', 'SIDE_PAGE', 'SEPARATED'];

        if (! in_array($display_mode, $allowedDisplayModes)) {
            throw new Exception('Invalid display mode, allowed values are '.implode(', ', $allowedDisplayModes));
        }

        $this->display_mode = $display_mode;
    }

    private function setPayMode()
    {
        $pay_mode = config('payment-fawry.pay_mode');
        $allowedPayModes = ['CashOnDelivery', 'PayAtFawry', 'MWALLET', 'CARD', 'VALU'];

        if (! in_array($pay_mode, $allowedPayModes)) {
            throw new Exception('Invalid pay mode, allowed values are '.implode(', ', $allowedPayModes));
        }

        $this->pay_mode = $pay_mode;
    }

    public function setOnStagingMode(): self
    {
        $this->url = config('payment-fawry.staging.url');
        $this->merchant = config('payment-fawry.staging.merchant');
        $this->secret = config('payment-fawry.staging.secret');

        return $this;
    }

    public function setOnLiveMode(): self
    {
        $this->url = config('payment-fawry.live.url');
        $this->merchant = config('payment-fawry.live.merchant');
        $this->secret = config('payment-fawry.live.secret');

        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function setUserFirstName($user_first_name)
    {
        $this->user_first_name = $user_first_name;

        return $this;
    }

    public function setUserLastName($user_last_name)
    {
        $this->user_last_name = $user_last_name;

        return $this;
    }

    public function setUserEmail($user_email)
    {
        $this->user_email = $user_email;

        return $this;
    }

    public function setUserPhone($user_phone)
    {
        $this->user_phone = $user_phone;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function pay()
    {
        $this->requiredFieldsShouldExist();

        $unique_id = $this->generateUniqueId();
        $data = $this->preparePaymentData($unique_id);

        $data['secret'] = $this->getSecret($data);

        return $this->prepareResponse($unique_id, $data);
    }

    private function generateUniqueId()
    {
        return uniqid();
    }

    private function preparePaymentData($unique_id)
    {
        return [
            'fawry_url' => $this->url,
            'fawry_merchant' => $this->merchant,
            'fawry_secret' => $this->secret,
            'fawry_pay_mode' => $this->pay_mode,
            'fawry_display_mode' => $this->display_mode,
            'verify_route_name' => $this->verify_route_name,
            'locale' => $this->locale,
            'language' => $this->language,
            'user_id' => $this->user_id,
            'user_name' => "{$this->user_first_name} {$this->user_last_name}",
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'unique_id' => $unique_id,
            'item_id' => 1,
            'item_quantity' => 1,
            'amount' => $this->amount,
            'payment_id' => $unique_id,
        ];
    }

    private function prepareResponse($unique_id, $data)
    {
        return [
            'payment_id' => $unique_id,
            'html' => $this->generate_html($data),
            'redirect_url' => '',
        ];
    }

    public function verify()
    {
        $request = request();
        $chargeResponse = $this->getChargeResponse($request);

        if (! $chargeResponse) {
            return $this->failed($request);
        }

        $reference_id = $chargeResponse['merchantRefNumber'];
        $hash = $this->generateHash($this->merchant, $reference_id, $this->secret);

        $apiRequest = $this->sendApiRequest($reference_id, $hash);

        if ($apiRequest->failed()) {
            return $this->failed($request, $reference_id);
        }

        $response = $apiRequest->json();

        return $this->handleApiResponse($response, $request, $reference_id);
    }

    private function getChargeResponse($request)
    {
        if ($request->has('chargeResponse')) {
            return json_decode($request['chargeResponse'], true);
        }

        return false;
    }

    private function getVerifyRequestUrl($reference_id, $hash): string
    {
        return $this->url.'ECommerceWeb/Fawry/payments/status/v2?merchantCode='.$this->merchant.'&merchantRefNumber='.$reference_id.'&signature='.$hash;
    }

    private function success(Request $request, $reference_id): array
    {
        return [
            'success' => true,
            'payment_id' => $reference_id,
            'message' => __('fawry::messages.PAYMENT_SUCCESS'),
            'process_data' => $request->all(),
        ];
    }

    private function failed(Request $request, $reference_id = null): array
    {
        return [
            'success' => false,
            'payment_id' => $reference_id,
            'message' => __('fawry::messages.PAYMENT_FAILED'),
            'process_data' => $request->all(),
        ];
    }

    private function getSecret(array $data): string
    {
        $sequence = [
            'fawry_merchant',
            'unique_id',
            'user_id',
            'item_id',
            'item_quantity',
            'amount',
            'fawry_secret',
        ];

        return array_reduce($sequence, function ($carry, $key) use ($data) {
            return $carry.$data[$key];
        }, '');
    }

    private function generate_html($data): string
    {
        $viewData = ['model' => $this, 'data' => $data];

        return view('fawry::form', $viewData)->render();
    }
}
