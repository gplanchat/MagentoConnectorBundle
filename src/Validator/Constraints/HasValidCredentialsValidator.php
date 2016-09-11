<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\UrlExplorer;
use Pim\Bundle\MagentoConnectorBundle\Validator\Checks\XmlChecker;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidXmlException;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;

/**
 * Validator for Magento credentials.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasValidCredentialsValidator extends ConstraintValidator
{
    /** @var WebserviceGuesser */
    protected $webserviceGuesser;

    /** @var UrlExplorer */
    protected $urlExplorer;

    /** @var XmlChecker */
    protected $xmlChecker;

    /** @var MagentoSoapClientParametersRegistry */
    protected $clientParametersRegistry;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param UrlExplorer                         $urlExplorer
     * @param XmlChecker                          $xmlChecker
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        UrlExplorer $urlExplorer,
        XmlChecker $xmlChecker,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        $this->webserviceGuesser        = $webserviceGuesser;
        $this->urlExplorer              = $urlExplorer;
        $this->xmlChecker               = $xmlChecker;
        $this->clientParametersRegistry = $clientParametersRegistry;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param \Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement $protocol   Value to validate
     * @param Constraint                                                      $constraint Constraint for validation
     *
     * @api
     */
    public function validate($protocol, Constraint $constraint)
    {
        if (!$protocol instanceof MagentoItemStep) {
            return null;
        }

        $clientParameters = $this->clientParametersRegistry->getInstance(
            $protocol->getSoapUsername(),
            $protocol->getSoapApiKey(),
            $protocol->getMagentoUrl(),
            $protocol->getWsdlUrl(),
            $protocol->getDefaultStoreView(),
            $protocol->getHttpLogin(),
            $protocol->getHttpPassword()
        );

        if (null === $clientParameters->isValid() || false === $clientParameters->isValid()) {
            try {
                $xml = $this->urlExplorer->getUrlContent($clientParameters);
                $this->xmlChecker->checkXml($xml);
                $webservice = $this->webserviceGuesser->getWebservice($clientParameters);
                $webservice->getStoreViewsList();
                $clientParameters->setValidation(true);
            } catch (NotReachableUrlException $e) {
                $clientParameters->setValidation(false);
                $this->context->addViolationAt(
                    'wsdlUrl',
                    $constraint->messageUrlNotReachable.' "'.$e->getMessage().'"'
                );
            } catch (InvalidSoapUrlException $e) {
                $clientParameters->setValidation(false);
                $this->context->addViolationAt(
                    'wsdlUrl',
                    $constraint->messageSoapNotValid.' "'.$e->getMessage().'"'
                );
            } catch (InvalidXmlException $e) {
                $clientParameters->setValidation(false);
                $this->context->addViolationAt('wsdlUrl', $constraint->messageXmlNotValid);
            } catch (InvalidCredentialException $e) {
                $clientParameters->setValidation(false);
                $this->context->addViolationAt('soapUsername', $constraint->messageUsername);
            } catch (SoapCallException $e) {
                $clientParameters->setValidation(false);
                $this->context->addViolationAt('soapUsername', $e->getMessage());
            } catch (\Exception $e) {
                $clientParameters->setValidation(false);
                $this->context->addViolationAt('soapUsername', $e->getMessage());
            }
        }
    }

    /**
     * Are the given parameters valid ?
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return boolean
     */
    public function areValidSoapCredentials(MagentoSoapClientParameters $clientParameters)
    {
        if (null === $clientParameters->isValid()) {
            try {
                $this->urlExplorer->getUrlContent($clientParameters);
                $webservice = $this->webserviceGuesser->getWebservice($clientParameters);
                $webservice->getStoreViewsList();
                $clientParameters->setValidation(true);
            } catch (\Exception $e) {
                $clientParameters->setValidation(false);
            }
        }

        return $clientParameters->isValid();
    }
}
