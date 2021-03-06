<?php

/**
 * Form client
 *
 * @package Be2bill
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Implements Be2bill payment API
 */
class Be2bill_Api_FormClient
{
    /**
     * API version
     */
    protected $version = '2.0';

    // Credentials
    /**
     * @var string Be2bill identifier
     */
    protected $identifier;

    /**
     * @var string Be2bill password
     */
    protected $password;

    // Internals

    /**
     * @var Be2bill_Api_Renderer_Renderable How to render the payment form
     */
    protected $renderer = null;

    /**
     * @var Be2bill_Api_Hash_Hashable How to hash the payment form
     */
    protected $hash = null;

    /**
     * Instanciate
     *
     * @param string $identifier The Be2bill identifier
     * @param string $password The Be2bill password
     * @param Be2bill_Api_Renderer_Renderable $renderer How to render the form
     * @param Be2bill_Api_Hash_Hashable $hash
     */
    public function __construct(
        $identifier,
        $password,
        Be2bill_Api_Renderer_Renderable $renderer,
        Be2bill_Api_Hash_Hashable $hash
    ) {
        $this->setCredentials($identifier, $password);

        $this->renderer = $renderer;
        $this->hash     = $hash;
    }

    /**
     * Configure credentials
     *
     * @param string $identifier
     * @param string $password
     */
    public function setCredentials($identifier, $password)
    {
        $this->identifier = $identifier;
        $this->password   = $password;
    }

    /**
     * Set default Be2bill VERSION parameter
     *
     * @param string $version The VERSION number (ex: 3.0)
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Build authorization form and submit button
     *
     * Return the authorization form and all hidden input configuring the be2bill transaction.
     * @param integer|array $amount The transaction amount in cents.
     *  If $amount is an array it will be used as NTime transaction (fragmented payment).
     *
     * In this case, the array should be formatted this way:
     * ```php
     * $amounts = array('2014-01-01' => 100, '2014-02-01' => 200, '2014-03-01' => 100)
     * ```
     *
     * The first entry's date should be the current date (today)
     * @api
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $description The transaction description
     * @param array $htmlOptions An array of HTML attributes to add to the submit or form button
     * (allowing to change name, style, class attribute etc.).
     * Example:
     * ```php
     * $htmlOptions['SUBMIT'] = array('class' => 'my_class');
     * $htmlOptions['FORM'] = array('class' => 'my_form', 'target' => 'my_target');
     * ```
     * @param array $options Others be2bill options. See Be2bill documentation for more information
     * (3DS, CREATEALIAS, etc.)
     * @return string The HTML output to display
     */
    public function buildAuthorizationFormButton(
        $amount,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    ) {
        $params = $options;

        $params["AMOUNT"] = $amount;

        return $this->buildProcessButton(
            'authorization',
            $orderId,
            $clientIdentifier,
            $description,
            $htmlOptions,
            $params
        );
    }

    // Be2bill toolkit methods

    /**
     * Build payment form and submit button
     *
     * Return the payment form and all hidden input configuring the be2bill transaction.
     * @param integer|array $amount The transaction amount in cents.
     *  If $amount is an array it will be used as NTime transaction (fragmented payment).
     *
     * In this case, the array should be formatted this way:
     * ```php
     * $amounts = array('2014-01-01' => 100, '2014-02-01' => 200, '2014-03-01' => 100)
     * ```
     *
     * The first entry's date should be the current date (today)
     * @api
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $description The transaction description
     * @param array $htmlOptions An array of HTML attributes to add to the submit or form button
     * (allowing to change name, style, class attribute etc.).
     * Example:
     * ```php
     * $htmlOptions['SUBMIT'] = array('class' => 'my_class');
     * $htmlOptions['FORM'] = array('class' => 'my_form', 'target' => 'my_target');
     * ```
     * @param array $options Others be2bill options. See Be2bill documentation for more information
     * (3DS, CREATEALIAS, etc.)
     * @return string The HTML output to display
     */
    public function buildPaymentFormButton(
        $amount,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    ) {
        $params = $options;

        // Handle N-Time payments
        if (is_array($amount)) {
            $params["AMOUNTS"] = $amount;
        } else {
            $params["AMOUNT"] = $amount;
        }

        return $this->buildProcessButton('payment', $orderId, $clientIdentifier, $description, $htmlOptions, $params);
    }

    /**
     * Compute form hash
     *
     * @param array $params
     * @return string
     */
    public function hash(array $params = array())
    {
        return $this->hash->compute($this->password, $params);
    }

    /**
     * Check a hash received in a notification/redirection URL
     *
     * @param array $params The POST or GET variable to verify
     * @return bool
     */
    public function checkHash($params)
    {
        return $this->hash->checkHash($this->password, $params);
    }

    // Internals

    /**
     * Return process button html
     *
     * @param string $operationType
     * @param string $orderId
     * @param string $clientIdentifier
     * @param string $description
     * @param array $htmlOptions
     * @param array $options
     * @return string
     */
    protected function buildProcessButton(
        $operationType,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    ) {
        $params = $options;

        $params['IDENTIFIER']    = $this->identifier;
        $params['OPERATIONTYPE'] = $operationType;
        $params['ORDERID']       = $orderId;
        $params['CLIENTIDENT']   = $clientIdentifier;
        $params['DESCRIPTION']   = $description;
        $params['VERSION']       = $this->getVersion($options);

        $params['HASH'] = $this->hash($params);

        $renderer = $this->renderer;

        return $renderer->render($params, $htmlOptions);
    }

    /**
     * Get Be2bill API VERSION
     *
     * @param array $options
     * @return string The version number
     */
    protected function getVersion(array $options = array())
    {
        if (isset($options['VERSION'])) {
            return $options['VERSION'];
        } else {
            return $this->version;
        }
    }
}
