<?php
require_once(dirname(__FILE__) . '/../../init.php');

use Capirussa\Eobot\Client;

/**
 * Tests Capirussa\Eobot\Client
 *
 */
class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWithoutParameters()
    {
        $client = new Client();

        $this->assertNull($this->getObjectAttribute($client, 'userId'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testConstructWithInvalidParameters()
    {
        new Client('foo');
    }

    public function testConstructWithValidParameters()
    {
        $client = new Client(1234);

        $this->assertEquals(1234, $this->getObjectAttribute($client, 'userId'));
    }

    public function testDisableSslVerification()
    {
        $client = new Client();

        $this->assertTrue($this->getObjectAttribute($client, 'validateSsl'));

        $client->disableSslVerification();

        $this->assertFalse($this->getObjectAttribute($client, 'validateSsl'));
    }

    public function testDisableSslVerificationToRequestPassthrough()
    {
        $client = new Client();

        $this->assertTrue($this->getObjectAttribute($client, 'validateSsl'));

        // getting the request is done through a protected function, we have to call that through reflection
        $reflectionClient = new ReflectionObject($client);
        $reflectionMethod = $reflectionClient->getMethod('getRequest');
        $reflectionMethod->setAccessible(true);

        $request = $reflectionMethod->invoke($client);

        $this->assertTrue($this->getObjectAttribute($request, 'validateSsl'));

        $client->disableSslVerification();

        $this->assertFalse($this->getObjectAttribute($client, 'validateSsl'));

        $request = $reflectionMethod->invoke($client);

        $this->assertFalse($this->getObjectAttribute($request, 'validateSsl'));
    }

    public function testGetCoinValueWithoutParameters()
    {
        $client = new MockClient();

        $coinValue = $client->getCoinValue();

        $this->assertEquals(458.36, $coinValue);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid coin type
     */
    public function testGetCoinValueWithInvalidCoin()
    {
        $client = new MockClient();

        $client->getCoinValue('foo');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid API response
     */
    public function testGetCoinValueWithInvalidApiResponse()
    {
        $client = new MockClient();

        $client->getCoinValue(Client::COIN_DARKCOIN);
    }

    public function testGetCoinValueWithValidCoins()
    {
        $client = new MockClient();

        $coins = array(
            Client::COIN_BITCOIN      => 458.36,
            Client::COIN_BITSHARESX   => 0.04157706,
            Client::COIN_BLACKCOIN    => 0.040105,
            Client::COIN_CURECOIN     => 0.033222,
            Client::COIN_DOGECOIN     => 0.000106,
            Client::COIN_LITECOIN     => 3.62,
            Client::COIN_NAMECOIN     => 0.874518,
            Client::COIN_NAUTILUSCOIN => 0.084194,
            Client::COIN_NXT          => 0.029618,
            Client::COIN_PEERCOIN     => 0.60667,
            Client::COIN_VERTCOIN     => 0.091551,

            Client::EO_CLOUD_FOLDING  => 0.05,
            Client::EO_CLOUD_SCRYPT   => 0.07,
            Client::EO_CLOUD_SHA256   => 1.79,
        );

        foreach ($coins as $coin => $expectedValue) {
            $coinValue = $client->getCoinValue($coin);

            $this->assertEquals($expectedValue, $coinValue, $coin);
        }
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid currency type
     */
    public function testGetCoinValueWithInvalidCurrency()
    {
        $client = new MockClient();

        $client->getCoinValue(Client::COIN_BITCOIN, 'foo');
    }

    public function testGetCoinValueWithValidCurrency()
    {
        $client = new MockClient();

        $coinValue = $client->getCoinValue(Client::COIN_BITCOIN, Client::CURRENCY_US_DOLLAR);

        $this->assertEquals(458.36, $coinValue);

        $coinValue = $client->getCoinValue(Client::COIN_BITCOIN, Client::CURRENCY_EURO);

        $this->assertEquals(343.02837352, $coinValue);
    }

    public function testGetExchangeRateWithoutParameters()
    {
        $client = new MockClient();

        $exchangeRate = $client->getExchangeRate();

        $this->assertEquals(0.748382, $exchangeRate);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid currency type
     */
    public function testGetExchangeRateWithInvalidCurrency()
    {
        $client = new MockClient();

        $client->getExchangeRate('foo');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid API response
     */
    public function testGetExchangeRateWithInvalidApiResponse()
    {
        $client = new MockClient();

        $client->getExchangeRate(Client::CURRENCY_AUSTRALIAN_DOLLAR);
    }

    public function testGetExchangeRateWithValidCurrencies()
    {
        $client = new MockClient();

        $currencies = array(
            Client::CURRENCY_BRITISH_POUND         => 0.597745,
            Client::CURRENCY_CANADIAN_DOLLAR       => 1.0886,
            Client::CURRENCY_CHINESE_YUAN_RENMINBI => 6.14322,
            Client::CURRENCY_CZECH_KORUNA          => 20.889,
            Client::CURRENCY_DANISH_KRONE          => 5.74794,
            Client::CURRENCY_EURO                  => 0.748382,
            Client::CURRENCY_INDIAN_RUPEE          => 60.88,
            Client::CURRENCY_INDONESIAN_RUPIAH     => 11689.78,
            Client::CURRENCY_JAPANESE_YEN          => 102.563,
            Client::CURRENCY_MEXICAN_PESO          => 13.0479,
            Client::CURRENCY_NORWEGIAN_KRONE       => 6.15738,
            Client::CURRENCY_POLISH_ZLOTY          => 3.13386,
            Client::CURRENCY_ROMANIAN_NEW_LEU      => 3.41312,
            Client::CURRENCY_RUSSIAN_RUBLE         => 36.0362,
            Client::CURRENCY_US_DOLLAR             => 1.0,
        );

        foreach ($currencies as $currency => $expectedRate) {
            $exchangeRate = $client->getExchangeRate($currency);

            $this->assertEquals($expectedRate, $exchangeRate, $currency);
        }
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testGetBalanceWithoutParameters()
    {
        $client = new MockClient();

        $client->getBalance();
    }

    public function testGetBalanceWithoutParametersAndPriorUserId()
    {
        $client = new MockClient(1234);

        $balances = $client->getBalance();

        $this->assertInternalType('array', $balances);
        $this->assertCount(15, $balances);

        $this->assertEquals(0.32751004, $balances['Total']);
        $this->assertEquals(0.00040978, $balances[Client::COIN_BITCOIN]);
        $this->assertEquals(0.0141392, $balances[Client::COIN_BITSHARESX]);
        $this->assertEquals(0.08188563, $balances[Client::COIN_BLACKCOIN]);
        $this->assertEquals(0.05292104, $balances[Client::COIN_CURECOIN]);
        $this->assertEquals(23.78557417, $balances[Client::COIN_DOGECOIN]);
        $this->assertEquals(0.03013698, $balances[Client::COIN_LITECOIN]);
        $this->assertEquals(0.00188207, $balances[Client::COIN_NAMECOIN]);
        $this->assertEquals(0.03115914, $balances[Client::COIN_NAUTILUSCOIN]);
        $this->assertEquals(0.10494402, $balances[Client::COIN_NXT]);
        $this->assertEquals(0.00502554, $balances[Client::COIN_PEERCOIN]);
        $this->assertEquals(0.02830923, $balances[Client::COIN_VERTCOIN]);
        $this->assertEquals(2.16726154, $balances[Client::EO_CLOUD_FOLDING]);
        $this->assertEquals(0.01115809, $balances[Client::EO_CLOUD_SCRYPT]);
        $this->assertEquals(20.00019989, $balances[Client::EO_CLOUD_SHA256]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage not a valid coin type
     */
    public function testGetBalanceWithInvalidCoin()
    {
        $client = new MockClient();

        $client->getBalance('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testGetBalanceWithInvalidUserId()
    {
        $client = new MockClient();

        $client->getBalance(null, 'foo');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid API response
     */
    public function testGetBalanceWithInvalidApiResponse()
    {
        $client = new MockClient();

        $client->getBalance(null, 2345);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage not in the balance sheet
     */
    public function testGetBalanceForMissingCoin()
    {
        $client = new MockClient(1234);

        $client->getBalance(Client::COIN_DARKCOIN);
    }

    public function testGetBalanceTotalInCurrency()
    {
        $client = new MockClient(1234);

        $balance = $client->getBalance(Client::CURRENCY_BRITISH_POUND);
        $this->assertEquals(0.1957674888598, $balance);

        $balance = $client->getBalance(Client::CURRENCY_CANADIAN_DOLLAR);
        $this->assertEquals(0.356527429544, $balance);

        $balance = $client->getBalance(Client::CURRENCY_CHINESE_YUAN_RENMINBI);
        $this->assertEquals(2.0119662279288, $balance);

        $balance = $client->getBalance(Client::CURRENCY_CZECH_KORUNA);
        $this->assertEquals(6.84135722556, $balance);

        $balance = $client->getBalance(Client::CURRENCY_DANISH_KRONE);
        $this->assertEquals(1.8825080593176, $balance);

        $balance = $client->getBalance(Client::CURRENCY_EURO);
        $this->assertEquals(0.24510261875528, $balance);

        $balance = $client->getBalance(Client::CURRENCY_INDONESIAN_RUPIAH);
        $this->assertEquals(3828.5203153912, $balance);

        $balance = $client->getBalance(Client::CURRENCY_INDIAN_RUPEE);
        $this->assertEquals(19.9388112352, $balance);

        $balance = $client->getBalance(Client::CURRENCY_JAPANESE_YEN);
        $this->assertEquals(33.59041223252, $balance);

        $balance = $client->getBalance(Client::CURRENCY_MEXICAN_PESO);
        $this->assertEquals(4.273318250916, $balance);

        $balance = $client->getBalance(Client::CURRENCY_NORWEGIAN_KRONE);
        $this->assertEquals(2.0166037700952, $balance);

        $balance = $client->getBalance(Client::CURRENCY_POLISH_ZLOTY);
        $this->assertEquals(1.0263706139544, $balance);

        $balance = $client->getBalance(Client::CURRENCY_ROMANIAN_NEW_LEU);
        $this->assertEquals(1.1178310677248, $balance);

        $balance = $client->getBalance(Client::CURRENCY_RUSSIAN_RUBLE);
        $this->assertEquals(11.802217303448, $balance);

        $balance = $client->getBalance(Client::CURRENCY_US_DOLLAR);
        $this->assertEquals(0.32751004, $balance);
    }

    public function testGetBalanceCoin()
    {
        $client = new MockClient(1234);

        $balance = $client->getBalance(Client::COIN_BITCOIN);
        $this->assertEquals(0.00040978, $balance);

        $balance = $client->getBalance(Client::COIN_BITSHARESX);
        $this->assertEquals(0.0141392, $balance);

        $balance = $client->getBalance(Client::COIN_BLACKCOIN);
        $this->assertEquals(0.08188563, $balance);

        $balance = $client->getBalance(Client::COIN_CURECOIN);
        $this->assertEquals(0.05292104, $balance);

        $balance = $client->getBalance(Client::COIN_DOGECOIN);
        $this->assertEquals(23.78557417, $balance);

        $balance = $client->getBalance(Client::COIN_LITECOIN);
        $this->assertEquals(0.03013698, $balance);

        $balance = $client->getBalance(Client::COIN_NAMECOIN);
        $this->assertEquals(0.00188207, $balance);

        $balance = $client->getBalance(Client::COIN_NAUTILUSCOIN);
        $this->assertEquals(0.03115914, $balance);

        $balance = $client->getBalance(Client::COIN_NXT);
        $this->assertEquals(0.10494402, $balance);

        $balance = $client->getBalance(Client::COIN_PEERCOIN);
        $this->assertEquals(0.00502554, $balance);

        $balance = $client->getBalance(Client::COIN_VERTCOIN);
        $this->assertEquals(0.02830923, $balance);

        $balance = $client->getBalance(Client::EO_CLOUD_FOLDING);
        $this->assertEquals(2.16726154, $balance);

        $balance = $client->getBalance(Client::EO_CLOUD_SCRYPT);
        $this->assertEquals(0.01115809, $balance);

        $balance = $client->getBalance(Client::EO_CLOUD_SHA256);
        $this->assertEquals(20.00019989, $balance);

        $balance = $client->getBalance(Client::EO_CLOUD_SCRYPT_CONTRACT);
        $this->assertEquals(0.01115809, $balance);

        $balance = $client->getBalance(Client::EO_CLOUD_SHA256_CONTRACT);
        $this->assertEquals(20.00019989, $balance);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testGetMiningModeWithoutParameters()
    {
        $client = new MockClient();

        $client->getMiningMode();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testGetMiningModeWithInvalidUserId()
    {
        $client = new MockClient();

        $client->getMiningMode('foo');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid API response
     */
    public function testGetMiningModeWithInvalidApiResponse()
    {
        $client = new MockClient(2345);

        $client->getMiningMode();
    }

    public function testGetMiningModeContractConversion()
    {
        $client = new MockClient();

        $miningMode = $client->getMiningMode(3456);
        $this->assertEquals(Client::EO_CLOUD_SHA256, $miningMode);

        $miningMode = $client->getMiningMode(4567);
        $this->assertEquals(Client::EO_CLOUD_SCRYPT, $miningMode);
    }

    public function testGetMiningMode()
    {
        $client = new MockClient(1234);

        $miningMode = $client->getMiningMode();
        $this->assertEquals(Client::COIN_BITCOIN, $miningMode);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testGetSpeedWithoutParameters()
    {
        $client = new MockClient();

        $client->getSpeed();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testGetSpeedWithInvalidUserId()
    {
        $client = new MockClient();

        $client->getSpeed('foo');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid API response
     */
    public function testGetSpeedWithInvalidApiResponse()
    {
        $client = new MockClient(2345);

        $client->getSpeed();
    }

    public function testGetSpeed()
    {
        $client = new MockClient(1234);

        $speeds = $client->getSpeed();

        $this->assertInternalType('array', $speeds);
        $this->assertCount(4, $speeds);
        $this->assertEquals(0.0, $speeds['MiningSHA-256']);
        $this->assertEquals(0.0, $speeds['MiningScrypt']);
        $this->assertEquals(20.0001998933406, $speeds['CloudSHA-256']);
        $this->assertEquals(0.0111580929310733, $speeds['CloudScrypt']);
    }

    public function testGetLastResponse()
    {
        $client = new MockClient();

        $this->assertNull($client->getLastResponse());

        $coinValue = $client->getCoinValue();

        $response = $client->getLastResponse();
        $this->assertNotNull($response);
        $this->assertInstanceof('Capirussa\\Http\\Response', $response);

        $coinValue2 = floatval(trim($response->getRawBody()));

        $this->assertEquals($coinValue2, $coinValue);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testGetDepositAddressWithoutParameters()
    {
        $client = new MockClient();

        $client->getDepositAddress();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid coin type
     */
    public function testGetDepositAddressWithInvalidCoinType()
    {
        $client = new MockClient();

        $client->getDepositAddress('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testGetDepositAddressWithInvalidUserId()
    {
        $client = new MockClient();

        $client->getDepositAddress(Client::COIN_BITCOIN, 'foo');
    }

    public function testGetDepositAddress()
    {
        $client = new MockClient(1234);

        $address = $client->getDepositAddress(Client::COIN_BITCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_BITCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        // for Bitshares-X you need to contact support, the API will not give an address for it
        $address = $client->getDepositAddress(Client::COIN_BITSHARESX);
        $this->assertEmpty($address);

        $address = $client->getDepositAddress(Client::COIN_BLACKCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_CURECOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_DARKCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_DOGECOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_LITECOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_NAMECOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_NAUTILUSCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_NXT);
        $this->assertEquals('NXT-1234-5678-90AB-CDEF', $address);

        $address = $client->getDepositAddress(Client::COIN_PEERCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);

        $address = $client->getDepositAddress(Client::COIN_VERTCOIN);
        $this->assertEquals('1234567890abcdefghijklmnopqrstuvwx', $address);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage No email address given, but it is required when no user ID is set
     */
    public function testGetUserIdWithoutParameters()
    {
        $client = new MockClient();

        $client->getUserId();
    }

    public function testGetUserIdWithoutParametersWithPresetUserId()
    {
        $client = new MockClient(1234);

        $userId = $client->getUserId();

        $this->assertEquals(1234, $userId);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage No password given, but it is required when a user ID is being fetched
     */
    public function testGetUserIdWithoutPassword()
    {
        $client = new MockClient();

        $client->getUserId('test@example.com');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid password given
     */
    public function testGetUserIdWithIncorrectPassword()
    {
        $client = new MockClient();

        $client->getUserId('test@example.com', 'incorrectPassword');
    }

    public function testGetUserId()
    {
        $client = new MockClient();

        $userId = $client->getUserId('test@example.com', 'correctPassword');

        $this->assertEquals(1234, $userId);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetMiningModeWithoutParameters()
    {
        $client = new MockClient();

        /** @noinspection PhpParamsInspection (this is on purpose) */
        $client->setMiningMode();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid mining type given
     */
    public function testSetMiningModeWithInvalidCoinType()
    {
        $client = new MockClient();

        $client->setMiningMode('foo', null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testSetMiningModeWithoutUserId()
    {
        $client = new MockClient();

        $client->setMiningMode(Client::COIN_BITCOIN, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testSetMiningModeWithInvalidUserId()
    {
        $client = new MockClient();

        $client->setMiningMode(Client::COIN_BITCOIN, null, null, 'foo');
    }

    public function testSetMiningModeWithInvalidCredentials()
    {
        $client = new MockClient(1234);

        $this->assertFalse($client->setMiningMode(Client::COIN_DARKCOIN, 'test@example.com', 'incorrectPassword'));
    }

    public function testSetMiningModeContract()
    {
        $client = new MockClient();

        $this->assertTrue($client->setMiningMode(Client::EO_CLOUD_SHA256_CONTRACT, 'test@example.com', 'correctPassword', 3456));
        $this->assertTrue($client->setMiningMode(Client::EO_CLOUD_SCRYPT_CONTRACT, 'test@example.com', 'correctPassword', 4567));
    }

    public function testSetMiningMode()
    {
        $client = new MockClient(1234);

        $this->assertTrue($client->setMiningMode(Client::COIN_BITCOIN, 'test@example.com', 'correctPassword'));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetAutomaticWithdrawWithoutParameters()
    {
        $client = new MockClient();

        /** @noinspection PhpParamsInspection (this is on purpose) */
        $client->setAutomaticWithdraw();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid coin type given
     */
    public function testSetAutomaticWithdrawWithInvalidCoinType()
    {
        $client = new MockClient();

        $client->setAutomaticWithdraw('foo', null, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testSetAutomaticWithdrawWithInvalidAmount()
    {
        $client = new MockClient();

        $client->setAutomaticWithdraw(Client::COIN_BITCOIN, 'foo', null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testSetAutomaticWithdrawWithNegativeAmount()
    {
        $client = new MockClient();

        $client->setAutomaticWithdraw(Client::COIN_BITCOIN, -5, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testSetAutomaticWithdrawWithZeroAmount()
    {
        $client = new MockClient();

        $client->setAutomaticWithdraw(Client::COIN_BITCOIN, 0, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testSetAutomaticWithdrawWithoutUserId()
    {
        $client = new MockClient();

        $client->setAutomaticWithdraw(Client::COIN_BITCOIN, 1, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testSetAutomaticWithdrawWithInvalidUserId()
    {
        $client = new MockClient();

        $client->setAutomaticWithdraw(Client::COIN_BITCOIN, 1, null, null, null, 'foo');
    }

    public function testSetAutomaticWithdrawWithInvalidCredentials()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->setAutomaticWithdraw(Client::COIN_BITCOIN, 1, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'incorrectPassword'));
    }

    public function testSetAutomaticWithdrawWithInvalidWallet()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->setAutomaticWithdraw(Client::COIN_BITCOIN, 1, 'invalid', 'test@example.com', 'correctPassword'));
    }

    /**
     * At the time of writing, the minimum amount for automatic withdrawal of Bitcoins is 0.001 BTC
     */
    public function testSetAutomaticWithdrawWithInsufficientAmount()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->setAutomaticWithdraw(Client::COIN_BITCOIN, 0.00001, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'correctPassword'));
    }

    public function testSetAutomaticWithdraw()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->setAutomaticWithdraw(Client::COIN_BITCOIN, 1, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'correctPassword'));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testWithdrawFundsWithoutParameters()
    {
        $client = new MockClient();

        /** @noinspection PhpParamsInspection (this is on purpose) */
        $client->withdrawFunds();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid coin type given
     */
    public function testWithdrawFundsWithInvalidCoinType()
    {
        $client = new MockClient();

        $client->withdrawFunds('foo', null, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testWithdrawFundsWithInvalidAmount()
    {
        $client = new MockClient();

        $client->withdrawFunds(Client::COIN_BITCOIN, 'foo', null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testWithdrawFundsWithNegativeAmount()
    {
        $client = new MockClient();

        $client->withdrawFunds(Client::COIN_BITCOIN, -5, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testWithdrawFundsWithZeroAmount()
    {
        $client = new MockClient();

        $client->withdrawFunds(Client::COIN_BITCOIN, 0, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testWithdrawFundsWithoutUserId()
    {
        $client = new MockClient();

        $client->withdrawFunds(Client::COIN_BITCOIN, 1, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testWithdrawFundsWithInvalidUserId()
    {
        $client = new MockClient();

        $client->withdrawFunds(Client::COIN_BITCOIN, 1, null, null, null, 'foo');
    }

    public function testWithdrawFundsWithInvalidCredentials()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->withdrawFunds(Client::COIN_BITCOIN, 1, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'incorrectPassword'));
    }

    public function testWithdrawFundsWithInvalidWallet()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->withdrawFunds(Client::COIN_BITCOIN, 1, 'invalid', 'test@example.com', 'correctPassword'));
    }

    /**
     * At the time of writing, the minimum amount for manual withdrawal of Bitcoins is 0.001 BTC
     */
    public function testWithdrawFundsWithInsufficientAmount()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->withdrawFunds(Client::COIN_BITCOIN, 0.00001, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'correctPassword'));
    }

    public function testWithdrawFundsWithInsufficientFunds()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->withdrawFunds(Client::COIN_BITCOIN, 100, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'correctPassword'));
    }

    public function testWithdrawFunds()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->withdrawFunds(Client::COIN_BITCOIN, 0.002, '1234567890abcdefghijklmnopqrstuvwx', 'test@example.com', 'correctPassword'));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testConvertCoinToCloudWithoutParameters()
    {
        $client = new MockClient();

        /** @noinspection PhpParamsInspection (this is on purpose) */
        $client->convertCoinToCloud();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid coin type given
     */
    public function testConvertCoinToCloudWithInvalidCoinType()
    {
        $client = new MockClient();

        $client->convertCoinToCloud('foo', null, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testConvertCoinToCloudWithInvalidAmount()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, 'foo', null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testConvertCoinToCloudWithNegativeAmount()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, -5, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid amount given
     */
    public function testConvertCoinToCloudWithZeroAmount()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, 0, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid cloud type
     */
    public function testConvertCoinToCloudWithInvalidCloudType()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, 1, 'foo', null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid cloud type
     */
    public function testConvertCoinToCloudWithCoinAsCloudType()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, 1, Client::COIN_DARKCOIN, null, null);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Cannot convert a cloud type to itself
     */
    public function testConvertCoinToCloudWithConversionToSelf()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::EO_CLOUD_SHA256, 1, Client::EO_CLOUD_SHA256, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage no user ID is known
     */
    public function testConvertCoinToCloudWithoutUserId()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, 1, Client::EO_CLOUD_SHA256, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be numeric
     */
    public function testConvertCoinToCloudWithInvalidUserId()
    {
        $client = new MockClient();

        $client->convertCoinToCloud(Client::COIN_BITCOIN, 1, Client::EO_CLOUD_SHA256, null, null, 'foo');
    }

    public function testConvertCoinToCloudWithInvalidCredentials()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->convertCoinToCloud(Client::COIN_BITCOIN, 0.00002, Client::EO_CLOUD_SHA256, 'test@example.com', 'incorrectPassword'));
    }

    public function testConvertCoinToCloudWithInsufficientFunds()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->convertCoinToCloud(Client::COIN_BITCOIN, 100, Client::EO_CLOUD_SHA256_CONTRACT, 'test@example.com', 'correctPassword'));
    }

    public function testConvertCoinToCloudFromCloud()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->convertCoinToCloud(Client::EO_CLOUD_SHA256_CONTRACT, 0.00002, Client::RENTAL_SCRYPT, 'test@example.com', 'correctPassword'));
        $this->assertTrue($client->convertCoinToCloud(Client::EO_CLOUD_SCRYPT_CONTRACT, 0.00002, Client::RENTAL_SHA256, 'test@example.com', 'correctPassword'));
    }

    public function testConvertCoinToCloud()
    {
        $client = new MockClient(1234);

        // Unfortunately, the Eobot API does not currently respond in a way that can be used to determine whether the
        // change was successful, so the Client always assumes it worked
        $this->assertTrue($client->convertCoinToCloud(Client::COIN_BITCOIN, 0.00002, Client::EO_CLOUD_SCRYPT_CONTRACT, 'test@example.com', 'correctPassword'));
    }
}