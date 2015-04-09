<?php

/**
 * This file is part of Robokassa package.
 *
 * (c) 2014 IDM Agency (http://idma.ru)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Idma\Robokassa;

use Idma\Robokassa\Exception\InvalidSumException;
use Idma\Robokassa\Exception\InvalidParamException;
use Idma\Robokassa\Exception\InvalidInvoiceIdException;
use Idma\Robokassa\Exception\EmptyDescriptionException;

/**
 * Class Payment
 *
 * @author JhaoDa <jhaoda@gmail.com>
 *
 * @package Idma\Robokassa
 */
class Payment {
    const CULTURE_EN = 'en';
    const CULTURE_RU = 'ru';

    private $baseUrl      = 'https://merchant.roboxchange.com/Index.aspx?';
    private $isTestMode   = false;
    private $valid        = false;
    private $data;
    private $customParams = [];

    private $login;
    private $paymentPassword;
    private $validationPassword;

    /**
     * Class constructor.
     *
     * @param string $login              login of Merchant
     * @param string $paymentPassword    password #1
     * @param string $validationPassword password #2
     * @param bool   $testMode           use test server
     */
    public function __construct($login, $paymentPassword, $validationPassword, $testMode = false)
    {
        $this->login              = $login;
        $this->paymentPassword    = $paymentPassword;
        $this->validationPassword = $validationPassword;
        $this->isTestMode         = $testMode;

        if ($this->isTestMode) {
            $this->baseUrl = 'http://test.robokassa.ru/Index.aspx?';
        }

        $this->data = [
            'MerchantLogin'  => $this->login,
            'InvId'          => null,
            'OutSum'         => 0,
            'Desc'           => null,
            'SignatureValue' => '',
            'Encoding'       => 'utf-8',
            'Culture'        => self::CULTURE_RU,
            'IncCurrLabel'   => '',
        ];
    }

    /**
     * Create payment url.
     *
     * @throws InvalidSumException       if sum less or equals zero
     * @throws EmptyDescriptionException if description is empty or not provided
     * @throws InvalidInvoiceIdException if invoice ID less or equals zero or not provided
     *
     * @return string the payment url
     */
    public function getPaymentUrl()
    {
        if ($this->data['OutSum'] <= 0) {
            throw new InvalidSumException();
        }

        if (empty($this->data['Desc'])) {
            throw new EmptyDescriptionException();
        }

        if ($this->data['InvId'] <= 0) {
            throw new InvalidInvoiceIdException();
        }

        $signature = vsprintf('%s:%01.2f:%u:%s', [
            // '$login:$OutSum:$InvId:$passwordPayment'
            $this->login,
            $this->data['OutSum'],
            $this->data['InvId'],
            $this->paymentPassword
        ]);

        if ($this->customParams) {
            // sort params alphabetically
            ksort($this->customParams);
            $signature .= ':' . http_build_query($this->customParams, null, ':');
        }

        $this->data['SignatureValue'] = md5($signature);

        $data   = http_build_query($this->data, null, '&');
        $custom = http_build_query($this->customParams, null, '&');

        return $this->baseUrl . $data . ($custom ? '&' . $custom : '');
    }

    /**
     * Validates the Robokassa query.
     *
     * @param  string $data query data
     *
     * @return bool
     */
    public function validate($data)
    {
        $this->data = $data;

        $signature = vsprintf('%s:%u:%s%s', [
            // '$OutSum:$InvId:$password[:$params]'
            $data['OutSum'],
            $data['InvId'],
            $this->validationPassword,
            $this->getCustomParamsString($this->data)
        ]);

        $this->valid = (md5($signature) === strtolower($data['SignatureValue']));

        return $this->valid;
    }

    /**
     * Returns whether the Robokassa query is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Adds custom parameters in payment.
     * The 'shp_' prefix will be added automatically.
     *
     * @param  array $params custom parameters array
     *
     * @throws InvalidParamException if params is not an array
     *
     * @return Payment
     */
    public function addCustomParameters($params)
    {
        if (!is_array($params)) {
            throw new InvalidParamException();
        }

        foreach ($params as $key => $val) {
            $this->customParams['shp_' . $key] = $val;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSuccessAnswer() {
        return 'OK' . $this->getInvoiceId() . "\n";
    }

    private function getCustomParamsString(array $source)
    {
        $params = [];

        foreach ($source as $key => $val) {
            if (stripos($key, 'shp_') === 0) {
                $params[$key] = $val;
            }
        }

        ksort($params);
        $params = http_build_query($params, null, ':');

        return $params ? ':' . $params : '';
    }

    /**
     * @return int
     */
    public function getInvoiceId()
    {
        return $this->data['InvId'];
    }

    /**
     * @param $id
     *
     * @return Payment
     */
    public function setInvoiceId($id)
    {
        $this->data['InvId'] = (int) $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSum()
    {
        return $this->data['OutSum'];
    }

    /**
     * @param $summ
     *
     * @throws InvalidSumException
     *
     * @return Payment
     */
    public function setSum($summ)
    {
        $summ = number_format($summ, 2, '.', '');

        if ($summ > 0) {
            $this->data['OutSum'] = $summ;

            return $this;
        } else {
            throw new InvalidSumException();
        }
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->data['Desc'];
    }

    /**
     * @param $description
     *
     * @return Payment
     */
    public function setDescription($description)
    {
        $this->data['Desc'] = (string) $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getCulture()
    {
        return $this->data['Culture'];
    }

    /**
     * @param  string $culture
     *
     * @return Payment
     */
    public function setCulture($culture = self::CULTURE_RU)
    {
        $this->data['Culture'] = (string) $culture;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyLabel()
    {
        return $this->data['IncCurrLabel'];
    }

    /**
     * @param  string $currLabel
     *
     * @return Payment
     */
    public function setCurrencyLabel($currLabel)
    {
        $this->data['IncCurrLabel'] = (string) $currLabel;

        return $this;
    }
}
