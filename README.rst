====================
PaymentBe2billBundle
====================

.. image:: https://poser.pugx.org/rezzza/payment-be2bill-bundle/version.png
   :target: https://packagist.org/packages/rezzza/payment-be2bill-bundle

.. image:: https://travis-ci.org/rezzza/PaymentBe2billBundle.png?branch=master
   :target: http://travis-ci.org/rezzza/PaymentBe2billBundle


A `Be2bill <http://www.be2bill.com/>`_ provider for `JMSPaymentCoreBundle <https://github.com/schmittjoh/JMSPaymentCoreBundle>`_.

**Supports only directLink payments at the moment.**

Installation
------------

With `composer <https://github.com/composer/composer/>`_
********************************************************

``composer require rezzza/payment-be2bill-bundle``

Git clone
*********

``git clone git@github.com:rezzza/PaymentBe2billBundle.git``


Setup
-----

Edit your ``AppKernel.php`` to register the bundle into your `Symfony2 <http://symfony.com/>`_ app with:

.. code-block:: php

    new Rezzza\PaymentBe2billBundle\RezzzaPaymentBe2billBundle()


TODO
----

- Add web form payment support.
- Write more unit tests.
