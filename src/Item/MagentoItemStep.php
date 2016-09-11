<?php

namespace Pim\Bundle\MagentoConnectorBundle\Item;

use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Magento item step.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials(groups={"Execution"})
 */
abstract class MagentoItemStep extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var Webservice */
    protected $webservice;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var WebserviceGuesser */
    protected $webserviceGuesser;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $defaultStoreView = Webservice::SOAP_DEFAULT_STORE_VIEW;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $soapUsername;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $wsdlUrl = MagentoSoapClientParameters::SOAP_WSDL_URL;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Assert\Url(groups={"Execution"})
     */
    protected $magentoUrl;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $soapApiKey;

    /** @var string */
    protected $httpLogin;

    /** @var string */
    protected $httpPassword;

    /** @var MagentoSoapClientParameters */
    protected $clientParameters;

    /** @var MagentoSoapClientParametersRegistry */
    protected $clientParametersRegistry;

    /** @var boolean */
    protected $beforeExecute = false;

    /** @var boolean */
    protected $afterConfiguration = false;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        $this->clientParametersRegistry = $clientParametersRegistry;
        $this->webserviceGuesser        = $webserviceGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (!$this->afterConfiguration) {
            $this->afterConfigurationSet();

            $this->afterConfiguration = true;
        }
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get fields for the twig.
     *
     * @return array
     */
    public function getConfigurationFields()
    {
        return [
            'soapUsername' => [
                'options' => [
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapUsername.help',
                    'label'    => 'pim_magento_connector.export.soapUsername.label',
                ],
            ],
            'soapApiKey'   => [
                //Should be replaced by a password formType but which doesn't
                //empty the field at each edit
                'type'    => 'text',
                'options' => [
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapApiKey.help',
                    'label'    => 'pim_magento_connector.export.soapApiKey.label',
                ],
            ],
            'magentoUrl' => [
                'options' => [
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.magentoUrl.help',
                    'label'    => 'pim_magento_connector.export.magentoUrl.label',
                ],
            ],
            'wsdlUrl' => [
                'options' => [
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.wsdlUrl.help',
                    'label'    => 'pim_magento_connector.export.wsdlUrl.label',
                    'data'     => $this->getWsdlUrl(),
                ],
            ],
            'httpLogin' => [
                'options' => [
                    'required' => false,
                    'help'     => 'pim_magento_connector.export.httpLogin.help',
                    'label'    => 'pim_magento_connector.export.httpLogin.label',
                ],
            ],
            'httpPassword' => [
                'options' => [
                    'required' => false,
                    'help'     => 'pim_magento_connector.export.httpPassword.help',
                    'label'    => 'pim_magento_connector.export.httpPassword.label',
                ],
            ],
            'defaultStoreView' => [
                'options' => [
                    'required' => false,
                    'help'     => 'pim_magento_connector.export.defaultStoreView.help',
                    'label'    => 'pim_magento_connector.export.defaultStoreView.label',
                    'data'     => $this->getDefaultStoreView(),
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * @param string $soapUsername
     *
     * @return MagentoItemStep
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultStoreView()
    {
        return $this->defaultStoreView;
    }

    /**
     * @param string $defaultStoreView
     *
     * @return MagentoItemStep
     */
    public function setDefaultStoreView($defaultStoreView)
    {
        $this->defaultStoreView = $defaultStoreView;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * @param string $soapApiKey
     *
     * @return MagentoItemStep
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * @param string $wsdlUrl
     *
     * @return MagentoItemStep
     */
    public function setWsdlUrl($wsdlUrl)
    {
        $this->wsdlUrl = $wsdlUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    /**
     * @param string $magentoUrl
     *
     * @return MagentoItemStep
     */
    public function setMagentoUrl($magentoUrl)
    {
        $this->magentoUrl = $magentoUrl;

        return $this;
    }

    /**
     * @return string magento soap url
     */
    public function getSoapUrl()
    {
        return $this->magentoUrl.$this->wsdlUrl;
    }

    /**
     * @param string $httpLogin
     *
     * @return MagentoItemStep
     */
    public function setHttpLogin($httpLogin)
    {
        $this->httpLogin = $httpLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpLogin()
    {
        return $this->httpLogin;
    }

    /**
     * @param string $httpPassword
     *
     * @return MagentoItemStep
     */
    public function setHttpPassword($httpPassword)
    {
        $this->httpPassword = $httpPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpPassword()
    {
        return $this->httpPassword;
    }

    /**
     * Function called before all item step execution.
     */
    protected function beforeExecute()
    {
        if ($this->beforeExecute) {
            return null;
        }

        $this->beforeExecute = true;

        $this->webservice = $this->webserviceGuesser->getWebservice($this->getClientParameters());
    }

    /**
     * Called after configuration set.
     */
    protected function afterConfigurationSet()
    {
    }

    /**
     * Get the magento soap client parameters.
     *
     * @return MagentoSoapClientParameters
     */
    protected function getClientParameters()
    {
        $this->clientParameters = $this->clientParametersRegistry->getInstance(
            $this->soapUsername,
            $this->soapApiKey,
            $this->magentoUrl,
            $this->wsdlUrl,
            $this->defaultStoreView,
            $this->httpLogin,
            $this->httpPassword
        );

        return $this->clientParameters;
    }

    /**
     * Add a warning based on the stepExecution.
     *
     * @param string $message
     * @param array  $messageParameters
     * @param mixed  $item
     */
    protected function addWarning($message, array $messageParameters = [], $item = null)
    {
        $this->stepExecution->addWarning(
            $this->getName(),
            $message,
            $messageParameters,
            $item
        );

        if (!is_array($item)) {
            $item = array();
        }

        $item = $this->cleanupImageContent($item);

        $event = new InvalidItemEvent(get_class($this), $message, $messageParameters, $item);
        $this->eventDispatcher->dispatch(EventInterface::INVALID_ITEM, $event);
    }

    /**
     * Cleanup image content from item in order to avoid large
     * item line in error log.
     *
     * @param array $item
     *
     * @return array
     */
    protected function cleanupImageContent(array $item)
    {
        array_walk_recursive(
            $item,
            function (&$entry, $key) {
                if ('content' === $key) {
                    $entry = "<CUT>";
                }
            }
        );

        return $item;
    }
}
