PaymentBe2billBundle
====================

.. image:: https://poser.pugx.org/rezzza/payment-be2bill-bundle/version.png
   :target: https://packagist.org/packages/rezzza/payment-be2bill-bundle

.. image:: https://travis-ci.org/rezzza/PaymentBe2billBundle.png?branch=master
   :target: http://travis-ci.org/rezzza/PaymentBe2billBundle

Payment Bundle providing access to the Be2bill API.

Installation
------------
Use `Composer <https://github.com/composer/composer/>`_ to install: ``rezzza/payment-be2bill-bundle``.

In your ``composer.json`` you should have:

.. code-block:: yaml

    {
        "require": {
            "rezzza/payment-be2bill-bundle": "1.0.*"
        }
    }

Then update your ``AppKernel.php`` to register the bundle with:

.. code-block:: php

    new Rezzza\PaymentBe2billBundle\RezzzaPaymentBe2billBundle()
