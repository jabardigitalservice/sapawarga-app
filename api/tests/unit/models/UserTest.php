<?php

namespace tests\models;

use app\models\User;
use Codeception\Specify;

class UserTest extends \Codeception\Test\Unit
{

    use Specify;

    private $_username = 'username';
    private $_email = 'email';
    private $_password = 'password';
    private $_name = 'name';
    private $_address = 'address';
    private $_phone = 'phone';

    public function testValidateUserFields()
    {
        // Validation requires method to be set
        //      The function validateUsername requires Yii::$app->request to determine mode - create or update.
        //      When request method is POST, then mode is creation.
        //      When request mode is PUT, the mode is update.
        $_POST['_method'] = 'post';


        // Tests for 'username' field
        $this->specify(
            'username and email are required',
            function () {
                // Initialise User model
                $user = new User();

                // Verify validation fails as didn't provide any attributes
                $this->assertFalse($user->validate());

                // Verify that the username and email properties are required
                $this->assertTrue($user->hasErrors($this->_username));
                $this->assertTrue($user->hasErrors($this->_email));

                // Set temporary values for username and email
                $user->username = 'username';
                $user->email = 'test@example.com';

                // Verify validation succeed
                $this->assertTrue($user->validate());
            }
        );

        $this->specify(
            'username length must be between 4 and 255 characters',
            function () {
                // Initialise User model
                $user = new User();

                // Set username length as 3
                $user->username = 'abc';
                // Verify that validation must be failed
                $this->assertFalse($user->validate($this->_username));

                // Set username length as 4
                $user->username = 'abcd';
                // Verify that validation must be failed
                $this->assertTrue($user->validate($this->_username));

                // Set username length as 256
                $user->username = 'ob3kb6ge1iguezd5wdnhisujr6xg63q7q6moav1l7lc2ffv1mp36z34gil9cgdnxlis7itp6nweui9s9be7sxvr5ffckoqnk9vdkxeznopu6e07som9msbarsyvuzyjv61or7itweqck60gz9y772okdn9p4swvwsxqrwwdc29kilz60dwqdg9omyf1o6ws0eo4i5defxre7xwsgmcie6x52z2o8c6vzmm7rxo09ankebxcysobv2uz8yobuggsc';
                $this->assertFalse($user->validate($this->_username));
            }
        );

        $this->specify('username must be a combination of lowercase alphanumeric, underscores, and periods', function () {
            $user = new User();

            $user->username = 'user-name';
            $this->assertFalse($user->validate($this->_username));

            $user->username = 'user name';
            $this->assertFalse($user->validate($this->_username));

            $user->username = 'userName';
            $this->assertFalse($user->validate($this->_username));

            $user->username = 'my.user_name01';
            $this->assertTrue($user->validate($this->_username));
        });

        $this->specify('username is unique', function () {
        });


        // Tests for 'email' field
        $this->specify('email username must be less than 255 characters', function () {
            $user = new User();

            $user->email = 'test@example.com';
            $this->assertTrue($user->validate($this->_email));

            $user->email = 'user@4koz0713e0irdqhwlq9vo4osgh1dov14ywb6sxdresfu77zfeldz5ftq0w7o6dmerq2stcrqiwvg8ddzh2sjeot4vrqox9mc7px3k6uren0aqgqo3n63lbcn3mqb68a5fncp9xb51jhgqkmmuay0z6sfhrzgvtbeblvt565et7hqr4lqndfxllvprp02u3juljmdltae9vc2vrewrvvfiqmtnr3mdi2w9dghyzhsf5i585pxfjprk9.com';
            $this->assertFalse($user->validate($this->_email));
        });

        $this->specify('email must be valid', function () {
            $user = new User();

            $user->email = 'test.example.com';
            $this->assertFalse($user->validate($this->_email));

            $user->email = 'test user@example.com';
            $this->assertFalse($user->validate($this->_email));

            $user->email = 'test@example.com';
            $this->assertTrue($user->validate($this->_email));

            $user->email = 'test-user.name01@example.com';
            $this->assertTrue($user->validate($this->_email));
        });

        $this->specify('email is unique', function () {
        });

        // Tests for 'password' field
        $this->specify('password must be more than 5 characters', function () {
            $user = new User();

            $user->password = '123456';
            $this->assertTrue($user->validate($this->_password));
        });

        // Tests for 'name' field
        $this->specify('name must be less than 255 characters', function () {
            $user = new User();

            $user->name = 'User';
            $this->assertTrue($user->validate($this->_name));

            $user->name = 'GOAFNj6Mm8tA45zzngttdWBfhBy20TicdkpecjbfF33OdkrbrL9FhPiJdvQVu2nBzIZgpETcLRXINqOttlpi1V9P2cJWYQ7mvR7a0lrvl69XzMMS9AGMlfx7ISn0ubnIaRyJOb8iIFKe2DFbraX4D8gffIp4kUbIE2pKdj7AOB05l8XBHs805wYAQtW2sYJDKg2hhCtqAAUts7HwCCIZtsh0gYWOF6KEaXk2VUlTiQ7xXCvpF5qtUCJUrqRBvat6';
            $this->assertFalse($user->validate($this->_name));
        });

        // Tests for 'address' field
        $this->specify('address must be less than 255 characters', function () {
            $user = new User();

            $user->address = 'Jl. Sangkuriang No.2';
            $this->assertTrue($user->validate($this->_address));

            $user->address = 'GOAFNj6Mm8tA45zzngttdWBfhBy20TicdkpecjbfF33OdkrbrL9FhPiJdvQVu2nBzIZgpETcLRXINqOttlpi1V9P2cJWYQ7mvR7a0lrvl69XzMMS9AGMlfx7ISn0ubnIaRyJOb8iIFKe2DFbraX4D8gffIp4kUbIE2pKdj7AOB05l8XBHs805wYAQtW2sYJDKg2hhCtqAAUts7HwCCIZtsh0gYWOF6KEaXk2VUlTiQ7xXCvpF5qtUCJUrqRBvat6';
            $this->assertFalse($user->validate($this->_address));
        });

        // Tests for 'phone' field
        $this->specify('phone must be between 3 and 15 characters', function () {
            $user = new User();

            $user->phone = '08';
            $this->assertFalse($user->validate($this->_phone));

            $user->phone = '081';
            $this->assertTrue($user->validate($this->_phone));

            $user->phone = '0812345678901';
            $this->assertTrue($user->validate($this->_phone));

            $user->phone = '08123456789012';
            $this->assertFalse($user->validate($this->_phone));
        });
    }

    public function testTrimInput()
    {
        $model = new User();
        $model->name = ' test data ';
        $model->phone = ' test data ';
        $model->address = ' test data ';
        $model->rt = ' test data ';
        $model->rw = ' test data ';
        $model->lat = ' test data ';
        $model->lon = ' test data ';
        $model->photo_url = ' test data ';
        $model->facebook = ' test data ';
        $model->twitter = ' test data ';
        $model->instagram = ' test data ';

        $model->validate();

        $this->assertEquals('test data', $model->name);
        $this->assertEquals('test data', $model->phone);
        $this->assertEquals('test data', $model->address);
        $this->assertEquals('test data', $model->rt);
        $this->assertEquals('test data', $model->rw);
        $this->assertEquals('test data', $model->lat);
        $this->assertEquals('test data', $model->lon);
        $this->assertEquals('test data', $model->photo_url);
        $this->assertEquals('test data', $model->facebook);
        $this->assertEquals('test data', $model->twitter);
        $this->assertEquals('test data', $model->instagram);
    }
}
