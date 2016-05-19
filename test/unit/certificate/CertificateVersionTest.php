<?php

use CryptoUtil\ASN1\AlgorithmIdentifier\Signature\SHA1WithRSAEncryptionAlgorithmIdentifier;
use CryptoUtil\ASN1\PrivateKeyInfo;
use CryptoUtil\ASN1\PublicKeyInfo;
use CryptoUtil\Crypto\Crypto;
use CryptoUtil\PEM\PEM;
use X501\ASN1\Name;
use X509\Certificate\Certificate;
use X509\Certificate\Extension\KeyUsageExtension;
use X509\Certificate\Extensions;
use X509\Certificate\TBSCertificate;
use X509\Certificate\UniqueIdentifier;
use X509\Certificate\Validity;


/**
 * @group certificate
 */
class CertificateVersionTest extends PHPUnit_Framework_TestCase
{
	private static $_privateKeyInfo;
	
	private static $_tbsCert;
	
	public static function setUpBeforeClass() {
		self::$_privateKeyInfo = PrivateKeyInfo::fromPEM(
			PEM::fromFile(TEST_ASSETS_DIR . "/rsa/private_key.pem"));
		$subject = Name::fromString("cn=Test Subject");
		$issuer = Name::fromString("cn=Test Issuer");
		$pki = self::$_privateKeyInfo->publicKeyInfo();
		$validity = Validity::fromStrings("now", "now + 1 day", "UTC");
		self::$_tbsCert = new TBSCertificate($subject, $pki, $issuer, $validity);
	}
	
	public static function tearDownAfterClass() {
		self::$_privateKeyInfo = null;
		self::$_tbsCert = null;
	}
	
	public function testVersion1() {
		$cert = self::$_tbsCert->sign(Crypto::getDefault(), 
			new SHA1WithRSAEncryptionAlgorithmIdentifier(), 
			self::$_privateKeyInfo);
		$this->assertEquals($cert->tbsCertificate()
			->version(), TBSCertificate::VERSION_1);
	}
	
	public function testVersion2SubjectUID() {
		$tbsCert = self::$_tbsCert->withSubjectUniqueID(
			UniqueIdentifier::fromString("subject"));
		$cert = $tbsCert->sign(Crypto::getDefault(), 
			new SHA1WithRSAEncryptionAlgorithmIdentifier(), 
			self::$_privateKeyInfo);
		$this->assertEquals($cert->tbsCertificate()
			->version(), TBSCertificate::VERSION_2);
	}
	
	public function testVersion2IssuerUID() {
		$tbsCert = self::$_tbsCert->withIssuerUniqueID(
			UniqueIdentifier::fromString("issuer"));
		$cert = $tbsCert->sign(Crypto::getDefault(), 
			new SHA1WithRSAEncryptionAlgorithmIdentifier(), 
			self::$_privateKeyInfo);
		$this->assertEquals($cert->tbsCertificate()
			->version(), TBSCertificate::VERSION_2);
	}
	
	public function testVersion2BothUID() {
		$tbsCert = self::$_tbsCert->withSubjectUniqueID(
			UniqueIdentifier::fromString("subject"))->withIssuerUniqueID(
			UniqueIdentifier::fromString("issuer"));
		$cert = $tbsCert->sign(Crypto::getDefault(), 
			new SHA1WithRSAEncryptionAlgorithmIdentifier(), 
			self::$_privateKeyInfo);
		$this->assertEquals($cert->tbsCertificate()
			->version(), TBSCertificate::VERSION_2);
	}
	
	public function testVersion3() {
		$tbsCert = self::$_tbsCert->withExtensions(
			new Extensions(
				new KeyUsageExtension(true, KeyUsageExtension::DIGITAL_SIGNATURE)));
		$cert = $tbsCert->sign(Crypto::getDefault(), 
			new SHA1WithRSAEncryptionAlgorithmIdentifier(), 
			self::$_privateKeyInfo);
		$this->assertEquals($cert->tbsCertificate()
			->version(), TBSCertificate::VERSION_3);
	}
}
