<?php

namespace Rezzza\PaymentBe2billBundle\Client;

class ParametersHashGenerator
{
    private $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function hash(array $parameters)
    {
        $signature = $this->password;
        $parameters = $this->sortParameters($parameters);

        foreach ($parameters as $name => $value) {
            if (is_array($value) == true) {
                foreach ($value as $index => $val) {
                    $signature .= sprintf('%s[%s]=%s%s', $name, $index, $val, $this->password);
                }
            } else {
                $signature .= sprintf('%s=%s%s', $name, $value, $this->password);
            }
        }

        return hash('sha256', $signature);
    }

    private function sortParameters(array $parameters)
    {
        ksort($parameters);

        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $parameters[$name] = $this->sortParameters($value);
            }
        }

        return $parameters;
    }
}
