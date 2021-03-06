<?php

declare(strict_types = 1);

namespace X509\AttributeCertificate;

use ASN1\Element;
use ASN1\Type\TaggedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Tagged\ImplicitlyTaggedType;
use X501\ASN1\Name;
use X509\Certificate\Certificate;
use X509\GeneralName\GeneralNames;

/**
 * Implements <i>V2Form</i> ASN.1 type used as a attribute certificate issuer.
 *
 * @link https://tools.ietf.org/html/rfc5755#section-4.1
 */
class V2Form extends AttCertIssuer
{
    /**
     * Issuer name.
     *
     * @var GeneralNames $_issuerName
     */
    protected $_issuerName;
    
    /**
     * Issuer PKC's issuer and serial.
     *
     * @var IssuerSerial $_baseCertificateID
     */
    protected $_baseCertificateID;
    
    /**
     * Linked object.
     *
     * @var ObjectDigestInfo $_objectDigestInfo
     */
    protected $_objectDigestInfo;
    
    /**
     * Constructor.
     *
     * @param GeneralNames|null $names
     */
    public function __construct(GeneralNames $names = null)
    {
        $this->_issuerName = $names;
        $this->_baseCertificateID = null;
        $this->_objectDigestInfo = null;
    }
    
    /**
     * Initialize from ASN.1.
     *
     * @param Sequence $seq
     * @return self
     */
    public static function fromV2ASN1(Sequence $seq): self
    {
        $issuer = null;
        $cert_id = null;
        $digest_info = null;
        if ($seq->has(0, Element::TYPE_SEQUENCE)) {
            $issuer = GeneralNames::fromASN1($seq->at(0)->asSequence());
        }
        if ($seq->hasTagged(0)) {
            $cert_id = IssuerSerial::fromASN1(
                $seq->getTagged(0)
                    ->asImplicit(Element::TYPE_SEQUENCE)
                    ->asSequence());
        }
        if ($seq->hasTagged(1)) {
            $digest_info = ObjectDigestInfo::fromASN1(
                $seq->getTagged(1)
                    ->asImplicit(Element::TYPE_SEQUENCE)
                    ->asSequence());
        }
        $obj = new self($issuer);
        $obj->_baseCertificateID = $cert_id;
        $obj->_objectDigestInfo = $digest_info;
        return $obj;
    }
    
    /**
     * Check whether issuer name is set.
     *
     * @return bool
     */
    public function hasIssuerName(): bool
    {
        return isset($this->_issuerName);
    }
    
    /**
     * Get issuer name.
     *
     * @throws \LogicException
     * @return GeneralNames
     */
    public function issuerName(): GeneralNames
    {
        if (!$this->hasIssuerName()) {
            throw new \LogicException("issuerName not set.");
        }
        return $this->_issuerName;
    }
    
    /**
     * Get DN of the issuer.
     *
     * This is a convenience method conforming to RFC 5755, which states
     * that Issuer must contain only one non-empty distinguished name.
     *
     * @return \X501\ASN1\Name
     */
    public function name(): Name
    {
        return $this->issuerName()->firstDN();
    }
    
    /**
     *
     * @see \X509\AttributeCertificate\AttCertIssuer::ASN1()
     * @return ImplicitlyTaggedType Tagged Sequence
     */
    public function toASN1(): TaggedType
    {
        $elements = array();
        if (isset($this->_issuerName)) {
            $elements[] = $this->_issuerName->toASN1();
        }
        if (isset($this->_baseCertificateID)) {
            $elements[] = new ImplicitlyTaggedType(0,
                $this->_baseCertificateID->toASN1());
        }
        if (isset($this->_objectDigestInfo)) {
            $elements[] = new ImplicitlyTaggedType(1,
                $this->_objectDigestInfo->toASN1());
        }
        return new ImplicitlyTaggedType(0, new Sequence(...$elements));
    }
    
    /**
     *
     * {@inheritdoc}
     * @see \X509\AttributeCertificate\AttCertIssuer::identifiesPKC()
     * @return bool
     */
    public function identifiesPKC(Certificate $cert): bool
    {
        $name = $this->_issuerName->firstDN();
        if (!$cert->tbsCertificate()
            ->subject()
            ->equals($name)) {
            return false;
        }
        return true;
    }
}
