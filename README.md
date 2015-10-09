# Introduction

This Bundle provides a client to access the **Nacex SOAP API**.

## Installation

 1. Download the Bundle

    Open a command console, enter your project directory and execute the
    following command to download the latest stable version of this bundle:

    ```bash
    $ composer require selltag/nacex-bundle
    ```

    This command requires you to have Composer installed globally, as explained
    in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
    of the Composer documentation.

 2. Enable the Bundle

    Add the following line in the `app/AppKernel.php` file to enable this bundle:

    ```php
    <?php
    // app/AppKernel.php

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new Selltag\NacexBundle\SelltagNacexBundle()

                // ...
            )

            return $bundles;
        }

        // ...
    }
    ```

 3. Enable the `nacex` service adding the following configuration:

    ```yaml
    # app/config/config.yml
    selltag_nacex:
        nacex_password: %nacex_username%
        nacex_password: %nacex_password%
        nacex_url: %nacex_url%
    ```

 4. You can define these parameters in your `parameters.yml`:

    ```yaml
    # app/config/parameters.yml.dist
    nacex_username: MYUSERNAME
    nacex_password: MYPASSWORD
    nacex_url: http://gprs.nacex.com/nacex_ws/soap
    ```


## Tests

You can run the tests using:

```
phpunit
```

I couldn't make tests for the NacexClient class because I don't have credentials anymore and I can't get a response soap to use as reference. If you find any errors, you can send me the SOAP response so I could also make tests for that part, thank you!

## Use example on Symfony

```php
$nacexClient = $this->getContainer()
    ->get('selltag_nacex.nacex_client');

$data = array(
    'reco_codigo' => null,
    'Del_Sol'     => '0001',
    'Num_Rec'     => '123456',
    'ref'         => null
);

$result = $nacexClient->putRecogida($data);

$recogida = array(
    'code'        => $result[0],
    'date'        => $result[1],
    'time'        => $result[2],
    'observation' => $result[3],
    'status'      => $result[4],
    'status_code' => $result[5]
);
```

## Use example without using Symfony

```php
use Selltag\NacexBundle\Services\NacexClientService;

$nacexClient = new NacexClientService(
    $nacexUser,
    $nacexPassword,
    $nacexUrl
);

$data = array(
    'reco_codigo' => null,
    'Del_Sol'     => '0001',
    'Num_Rec'     => '123456',
    'ref'         => null
);

$result = $nacexClient->putRecogida($data);

$recogida = array(
    'code'        => $result[0],
    'date'        => $result[1],
    'time'        => $result[2],
    'observation' => $result[3],
    'status'      => $result[4],
    'status_code' => $result[5]
);
```

## NacexException

If the Nacex SOAP response returns any errors, a `NacexClientException`
exception is raised, you can treat It accordingly for you project.

```php
use Selltag\NacexBundle\Exceptions\NacexClientException;
```
