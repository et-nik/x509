<?php

declare(strict_types=1);

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Primitive\NullType;
use ASN1\Type\Primitive\ObjectIdentifier;
use X509\Certificate\Extension\CertificatePolicy\PolicyQualifierInfo;

/**
 * @group certificate
 * @group extension
 * @group certificate-policy
 */
class PolicyQualifierInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException UnexpectedValueException
     */
    public function testFromASN1UnknownTypeFail()
    {
        $seq = new Sequence(new ObjectIdentifier("1.3.6.1.3"), new NullType());
        PolicyQualifierInfo::fromASN1($seq);
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testFromQualifierBadCall()
    {
        PolicyQualifierInfo::fromQualifierASN1(
            new UnspecifiedType(new NullType()));
    }
}
