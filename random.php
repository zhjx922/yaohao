<?php
/**
 * C# Random移植版(感谢微软开源，要不根本没办法移植到PHP上)
 * @see https://github.com/dotnet/coreclr/blob/32f0f9721afb584b4a14d69135bea7ddc129f755/src/mscorlib/src/System/Random.cs
 * @author zhjx922
 */
class Random {
    CONST BIG = 2147483647;
    CONST MIN = -2147483648;
    CONST SEED = 161803398;

    private $seedArray = array();
    private $inext;
    private $inextp;

    public function __construct($seed) {
        $ii = 0;

        $subtraction = ($seed == self::MIN) ? self::BIG : abs($seed);
        $mj = self::SEED - $subtraction;
        $this->seedArray[55] = $mj;
        $mk = 1;
        for($i = 1; $i < 55; $i++) {
            if (($ii += 21) >= 55)
                $ii -= 55;

            $this->seedArray[$ii] = $mk;
            $mk = $mj - $mk;

            if ($mk < 0)
                $mk += self::BIG;
            $mj = $this->seedArray[$ii];
        }
        for($k = 1; $k < 5; $k++) {
            for ($i = 1; $i < 56; $i++) {
                $n = $i + 30;
                if ($n >= 55)
                    $n -= 55;

                $this->seedArray[$i] -= $this->seedArray[1 + $n];

                if ($this->seedArray[$i]<0)
                    $this->seedArray[$i] += self::BIG;
          }
        }
        $this->inext = 0;
        $this->inextp = 21;
    }

    protected function sample() {
        return ($this->internalSample() * (1.0 / self::BIG));
    }

    protected function internalSample() {
        $locINext = $this->inext;
        $locINextp = $this->inextp;

        if (++$locINext >=56)
            $locINext=1;

        if (++$locINextp>= 56)
            $locINextp = 1;

        $retVal = $this->seedArray[$locINext] - $this->seedArray[$locINextp];

          if ($retVal == self::BIG)
              $retVal--;

          if ($retVal<0)
              $retVal += self::BIG;

        $this->seedArray[$locINext] = $retVal;

        $this->inext = $locINext;
        $this->inextp = $locINextp;

        return $retVal;
    }

    public function next($value) {
        return (int)($this->sample() * $value);
    }
}